<?php
/**
 * @version 1.0 $Id: image.class.php 298 2009-06-24 07:42:35Z julien $
 * @package Joomla
 * @subpackage redEVENT
 * @copyright redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license GNU/GPL, see LICENSE.php
 * redEVENT is based on EventList made by Christoph Lukes from schlu.net
 * redEVENT can be downloaded from www.redcomponent.com
 * redEVENT is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * redEVENT is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with redEVENT; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Holds the logic for attachments manipulation
 *
 * @package Joomla
 * @subpackage redEVENT
 */
class REAttach extends JObject {

	/**
	 * upload files for the specified object
	 * 
	 * @param array data from JRequest 'files'
	 * @param string object identification (should be event<eventid>, category<categoryid>, etc...)
	 */
	function postUpload($post_files, $object)
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		$app = &JFactory::getApplication();
		$user = &JFactory::getUser();
		$params = JComponentHelper::getParams('com_redevent');

		$path = self::getBasePath().DS.$object;
		
		if (!(is_array($post_files) && count($post_files))) {
			return false;
		}
		
		$allowed = explode(",", $params->get('attachments_types', 'txt,csv,htm,html,xml,css,doc,xls,rtf,ppt,pdf,swf,flv,avi,wmv,mov,jpg,jpeg,gif,png,zip,tar.gz'));
		foreach ($allowed as $k => $v) {
			$allowed[$k] = trim($v);
		}
		
		$maxsize = $params->get('attachments_maxsize', 1000)*1000;
		if (!isset($post_files['name'])) {
			return true;
		}
		foreach ($post_files['name'] as $k => $file)
		{
			if (empty($file)) {
				continue;
			}
			// check extension
			if (!in_array(end(explode(".", strtolower($file))), $allowed)) {
				JError::raiseWarning(0, JText::_('COM_REDEVENT_ERROR_ATTACHMENT_EXTENSION_NOT_ALLOWED').': '.$file);
				continue;
			}
			// check size
			if ($post_files['size'][$k] > $maxsize) {
				JError::raiseWarning(0, JText::sprintf('COM_REDEVENT_ERROR_ATTACHMENT_FILE_TOO_BIG', $file, $post_files['size'][$k], $maxsize));
				continue;
			}
			
			if (!JFolder::exists($path)) 
			{
				// try to create it
				$res = JFolder::create($path);
				if (!$res) {
					JError::raiseWarning(0, JText::_('COM_REDEVENT_ERROR_COULD_NOT_CREATE_FOLDER').': '.$path);
					return false;
				}
				$txt = '<html><body bgcolor="#FFFFFF"></body></html>';
				JFile::write($path.DS.'index.html', $txt, false);
			}
			
			JFile::copy($post_files['tmp_name'][$k], $path.DS.$file);
			
			$table = &JTable::getInstance('redevent_attachments', '');
			$table->file = $file;
			$table->object = $object;
			if (isset($post_files['customname'][$k]) && !empty($post_files['customname'][$k])) {
				$table->name = $post_files['customname'][$k];				
			}
			if (isset($post_files['description'][$k]) && !empty($post_files['description'][$k])) {
				$table->description = $post_files['description'][$k];				
			}
			if (isset($post_files['access'][$k])) {
				$table->access = intval($post_files['access'][$k]);				
			}			
			$table->added = strftime('%F %T');
			$table->added_by = $user->get('id');
			
			if (!($table->check() && $table->store())) {
				JError::raiseWarning(0, JText::_('COM_REDEVENT_ATTACHMENT_ERROR_SAVING_TO_DB').': '.$table->getError());				
			}
		}
		return true;
	}
	
	/**
	 * update attachment record in db
	 * @param array (id, name, description, access)
	 */
	function update($attach)
	{
		if (!is_array($attach) || !isset($attach['id']) || !(intval($attach['id']))) {
			return false;
		}
		$table = &JTable::getInstance('redevent_attachments', '');
		$table->load($attach['id']);
		$table->bind($attach);
		if (!($table->check() && $table->store())) {
			JError::raiseWarning(0, JText::_('COM_REDEVENT_ATTACHMENT_ERROR_UPDATING_RECORD').': '.$table->getError());		
			return false;		
		}		
		return true;
	}
	
	/**
	 * return attachments for objects
	 * @param string object identification (should be event<eventid>, category<categoryid>, etc...)
	 * @return array
	 */
	function getAttachments($object, $aid = null)
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		$app = &JFactory::getApplication();
		$params = JComponentHelper::getParams('com_redevent');
		
		$path = self::getBasePath().DS.$object;
		
		if (!file_exists($path)) {
			return array();
		}
		// first list files in the folder
		$files = JFolder::files($path, null, false, false, array('index.html'));

		// then get info for files from db
		$db = &JFactory::getDBO();
		$fnames = array();
		foreach ($files as $f) {
			$fnames[] = $db->Quote($f);
		}
		if (!count($fnames)) {
			return array();
		}
				
		$query = ' SELECT * ' 
		       . ' FROM #__redevent_attachments ' 
		       . ' WHERE file IN ('. implode(',', $fnames) .')'
		       . '   AND object = '. $db->Quote($object);
		if (!is_null($aid)) {
			$query .= ' AND access <= '.$db->Quote($aid);
		}
		$query .= ' ORDER BY ordering ASC ';
		
		$db->setQuery($query);
		$res = $db->loadObjectList();
		
		return $res;
	}
	
	/**
	 * get the file
	 * 
	 * @param int $id
	 */
	function getAttachmentPath($id, $aid = null) 
	{		
		$params = JComponentHelper::getParams('com_redevent');
		
		$db = &JFactory::getDBO();
		$query = ' SELECT * ' 
		       . ' FROM #__redevent_attachments ' 
		       . ' WHERE id = '. $db->Quote(intval($id));
		$db->setQuery($query);
		$res = $db->loadObject();
		if (!$res) {
			JError::raiseError(404, JText::_('COM_REDEVENT_FILE_UNKNOWN'));
		}		
		
		if (!is_null($aid) && $res->access > $aid) {
			JError::raiseError(403, JText::_('COM_REDEVENT_YOU_DONT_HAVE_ACCESS_TO_THIS_FILE'));			
		}
		
		$path = self::getBasePath().DS.$res->object.DS.$res->file;		
		if (!file_exists($path)) {
			JError::raiseError(404, JText::_('COM_REDEVENT_FILE_NOT_FOUND'));
		}
		
		return $path;
	}
	
	
	/**
	 * remove attachment for objects
	 * 
	 * @param id from db
	 * @param string object identification (should be event<eventid>, category<categoryid>, etc...)
	 * @return boolean
	 */
	function remove($id)
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		$app = &JFactory::getApplication();
		$params = JComponentHelper::getParams('com_redevent');

		
		// then get info for files from db
		$db = &JFactory::getDBO();
		
		$query = ' SELECT file, object ' 
		       . ' FROM #__redevent_attachments ' 
		       . ' WHERE id = ' . $db->Quote($id);
		$db->setQuery($query);
		$res = $db->loadObject();
		if (!$res)
		{
			return false;
		}
				
		$path = self::getBasePath().DS.$res->object.DS.$res->file;
		if (file_exists($path)) {
			JFile::delete($path);
		}
				
		$query = ' DELETE FROM #__redevent_attachments ' 
		       . ' WHERE id = '. $db->Quote($id);
		$db->setQuery($query);
		$res = $db->query();
		if (!$res) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * Store attachments for key from post data (variables need specific names)
	 * 
	 * @param string $key
	 * @return boolean true on success
	 */
	function store($key)
	{
		// new ones first
		$attachments = JRequest::getVar( 'attach', array(), 'files', 'array' );
		$attachments['customname'] = JRequest::getVar( 'attach-name', array(), 'post', 'array' );
		$attachments['description'] = JRequest::getVar( 'attach-desc', array(), 'post', 'array' );
		$attachments['access'] = JRequest::getVar( 'attach-access', array(), 'post', 'array' );
		if (!self::postUpload($attachments, $key)) {
			return false;
		}
		
		// and update old ones
		$attachments = array();
		$old['id'] = JRequest::getVar( 'attached-id', array(), 'post', 'array' );
		$old['name'] = JRequest::getVar( 'attached-name', array(), 'post', 'array' );
		$old['description'] = JRequest::getVar( 'attached-desc', array(), 'post', 'array' );
		$old['access'] = JRequest::getVar( 'attached-access', array(), 'post', 'array' );
		foreach ($old['id'] as $k => $id)
		{
			$attach = array();
			$attach['id'] = $id;
			$attach['name'] = $old['name'][$k];
			$attach['description'] = $old['description'][$k];
			$attach['access'] = $old['access'][$k];
			if (!self::update($attach)) {
				return false;
			}
		}
		return true;		
	}
	
	/**
	 * return base path for attachments storage
	 * 
	 * @return string path
	 */
	function getBasePath()
	{
		$params = JComponentHelper::getParams('com_redevent');

		$path = JPATH_SITE.DS.$params->get('attachments_path', 'media'.DS.'com_redevent'.DS.'attachments');
		if (!file_exists($path)) {
			jimport('joomla.filesystem.folder');
			if (!JFolder::create($path)) {
				JError::raiseWarning(0, Jtext::_('COM_REDEVENT_ATTACHMENTS_ERROR_CANNOT_CREATE_BASE_FOLDER'));
				return false;
			}
		}
		return $path;
	}
}