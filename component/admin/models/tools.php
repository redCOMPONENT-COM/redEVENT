<?php
/**
 * @version 1.0 $Id$
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

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

/**
 * EventList Component Tools Model
 *
 * @package Joomla
 * @subpackage redevent
 * @since		0.9
 */
class RedEventModelTools extends JModelLegacy
{
	/**
	 * target
	 *
	 * @var string
	 */
	var $_target = null;

	/**
	 * images to delete
	 *
	 * @var array
	 */
	var $_images = null;

	/**
	 * assigned images
	 *
	 * @var array
	 */
	var $_assigned = null;

	/**
	 * unassigned images
	 *
	 * @var array
	 */
	var $_unassigned = null;

	/**
	 * Constructor
	 *
	 * @since 0.9
	 */
	function __construct()
	{
		parent::__construct();

		if (JRequest::getCmd('task') == 'cleaneventimg') {
			$target = 'events';
		} else {
			$target = 'venues';
		}
		$this->settarget($target);
	}

	/**
	 * Method to set the target
	 *
	 * @access	public
	 * @param	string the target directory
	 */
	function settarget($target)
	{
		// Set id and wipe data
		$this->_target	 = $target;
	}

	/**
	 * Method to delete the images
	 *
	 * @access	public
	 * @since 0.9
	 * @return int
	 */
	function delete()
	{
		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');

		// Get some data from the request
		$images	= $this->_getImages();
		$folder = $this->_target;

		$count = count($images);

		if ($count) {

			$fail = 0;

			foreach ($images as $image)
			{
				if ($image !== JFilterInput::clean($image, 'path')) {
					RedeventError::raiseWarning(100, JText::_('COM_REDEVENT_UNABLE_TO_DELETE').' '.htmlspecialchars($image, ENT_COMPAT, 'UTF-8'));
					$fail++;
					continue;
				}

				$fullPath = JPath::clean(JPATH_SITE.DS.'images'.DS.'redevent'.DS.$folder.DS.$image);
				$fullPaththumb = JPath::clean(JPATH_SITE.DS.'images'.DS.'redevent'.DS.$folder.DS.'small'.DS.$image);

				if (is_file($fullPath)) {
					JFile::delete($fullPath);
					if (JFile::exists($fullPaththumb)) {
						JFile::delete($fullPaththumb);
					}
				}
			}
		}

		$deleted = $count - $fail;

		return $deleted;
	}

	/**
	 * perform integrity check on db
	 *
	 * @return bool true if no problem
	 */
	function checkdb()
	{
		$errors = array();
		$dbok   = true;

		/** check for registers without corresponding records in redform **/

		$query = ' SELECT r.id, r.xref, x.eventid '
		       . ' FROM #__redevent_register AS r '
		       . ' LEFT JOIN #__redevent_event_venue_xref AS x ON r.xref = x.id '
		       . ' LEFT JOIN #__rwf_submitters AS s ON r.sid = s.id '
		       . ' WHERE s.id IS NULL '
		       ;
		$this->_db->setQuery($query);
		$res = $this->_db->loadobjectList();

		if (count($res)) {
			$dbok = false;
			$error = array();
			$error[] = JText::sprintf('COM_REDEVENT_d_attendees_records_without_matching_redform_submitters', count($res));
			foreach ($res as $r)
			{
				$error[] = JText::sprintf('COM_REDEVENT_event_d_session_d_slash_register_id_d', ($r->eventid ? $r->eventid : '?'), $r->xref, $r->id);
			}
			$errors[] = implode('<br/>', $error);
		}

		if (!$dbok) {
			$this->setError(implode('<br/>', $errors));
		}
		return $dbok;
	}

	/**
	 * perform integrity fix on db
	 *
	 * @return bool true if no problem
	 */
	function fixdb()
	{
		// all the redevent_register records in redevent without an associated record in redform submitters can be deleted
		$q =  ' SELECT r.id FROM #__redevent_register AS r '
        . ' LEFT JOIN #__rwf_submitters AS s ON s.id = r.sid '
        . ' WHERE s.id IS NULL '
        ;
    $this->_db->setQuery($q);
    $register_ids = $this->_db->loadResultArray();
    if (!empty($register_ids))
    {
			$q =  ' DELETE r.* FROM #__redevent_register AS r '
	        . ' LEFT JOIN #__rwf_submitters AS s ON s.id = r.sid '
	        . ' WHERE s.id IS NULL '
	        ;
			$this->_db->setQuery($q);
			if(!$this->_db->query())
			{
	      RedeventError::raiseWarning(0, JText::_( "COM_REDEVENT_CANT_DELETE_REGISTRATIONS" ) . ': ' . $this->_db->getErrorMsg() );
				$this->setError(JText::_( "COM_REDEVENT_CANT_DELETE_REGISTRATIONS" ) . ': '. $this->_db->getErrorMsg());
				return false;
			}
    }

		return true;
	}

	/**
	 * Method to determine the images to delete
	 *
	 * @access	private
	 * @since 0.9
	 * @return array
	 */
	function _getImages()
	{
		$this->_images = array_diff($this->_getavailable(), $this->_getassigned());

		return $this->_images;
	}

	/**
	 * Method to determine the assigned images
	 *
	 * @access	private
	 * @since 0.9
	 * @return array
	 */
	function _getassigned()
	{
		if ($this->_target == 'events') {
			$field = 'datimage';
		} else {
			$field = 'locimage';
		}

		$query = 'SELECT '.$field.' FROM #__redevent_'.$this->_target;

		$this->_db->setQuery($query);

		$this->_assigned = $this->_db->loadResultArray();

		return $this->_assigned;
	}

	/**
	 * Method to determine the unassigned images
	 *
	 * @access	private
	 * @since 0.9
	 * @return array
	 */
	function _getavailable()
	{
		// Initialize variables
		$basePath = JPATH_SITE.DS.'images'.DS.'redevent'.DS.$this->_target;

		$images 	= array ();

		// Get the list of files and folders from the given folder
		$fileList 	= JFolder::files($basePath);

		// Iterate over the files if they exist
		if ($fileList !== false) {
			foreach ($fileList as $file)
			{
				if (is_file($basePath.DS.$file) && substr($file, 0, 1) != '.' && strtolower($file) !== 'index.html') {

					$images[] = $file;

				}
			}
		}

		$this->_unassigned = $images;

		return $this->_unassigned;
	}
}
