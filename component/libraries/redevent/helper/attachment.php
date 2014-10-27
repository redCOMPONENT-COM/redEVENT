<?php
/**
 * @package    Redevent.Library
 * @copyright  Copyright (C) 2008 - 2014 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Holds the logic for attachments manipulation
 *
 * @package  Redevent.Library
 * @since    2.5
 */
class RedeventHelperAttachment extends JObject
{
	/**
	 * upload files for the specified object
	 *
	 * @param   array   $post_files  data from JRequest 'files'
	 * @param   string  $object      identification (should be event<eventid>, category<categoryid>, etc...)
	 *
	 * @return bool
	 */
	private static function postUpload($post_files, $object)
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');

		$user = JFactory::getUser();
		$params = JComponentHelper::getParams('com_redevent');

		$path = self::getBasePath() . '/' . $object;

		if (!(is_array($post_files) && count($post_files)))
		{
			return false;
		}

		$allowed = explode(",", $params->get('attachments_types', 'txt,csv,htm,html,xml,css,doc,xls,rtf,ppt,pdf,swf,flv,avi,wmv,mov,jpg,jpeg,gif,png,zip,tar.gz'));

		foreach ($allowed as $k => $v)
		{
			$allowed[$k] = trim($v);
		}

		$maxsize = $params->get('attachments_maxsize', 1000) * 1000;

		if (!isset($post_files['name']))
		{
			return true;
		}

		foreach ($post_files['name'] as $k => $file)
		{
			if (empty($file))
			{
				continue;
			}

			// Check extension
			if (!in_array(end(explode(".", strtolower($file))), $allowed))
			{
				JError::raiseWarning(0, JText::_('COM_REDEVENT_ERROR_ATTACHMENT_EXTENSION_NOT_ALLOWED') . ': ' . $file);
				continue;
			}

			// Check size
			if ($post_files['size'][$k] > $maxsize)
			{
				JError::raiseWarning(0, JText::sprintf('COM_REDEVENT_ERROR_ATTACHMENT_FILE_TOO_BIG', $file, $post_files['size'][$k], $maxsize));
				continue;
			}

			if (!JFolder::exists($path))
			{
				// Try to create it
				$res = JFolder::create($path);

				if (!$res)
				{
					JError::raiseWarning(0, JText::_('COM_REDEVENT_ERROR_COULD_NOT_CREATE_FOLDER') . ': ' . $path);

					return false;
				}

				$txt = '<html><body bgcolor="#FFFFFF"></body></html>';
				JFile::write($path . '/index.html', $txt, false);
			}

			JFile::copy($post_files['tmp_name'][$k], $path . '/' . $file);

			$table = JTable::getInstance('redevent_attachments', '');
			$table->file = $file;
			$table->object = $object;

			if (isset($post_files['customname'][$k]) && !empty($post_files['customname'][$k]))
			{
				$table->name = $post_files['customname'][$k];
			}

			if (isset($post_files['description'][$k]) && !empty($post_files['description'][$k]))
			{
				$table->description = $post_files['description'][$k];
			}

			if (isset($post_files['access'][$k]))
			{
				$table->access = intval($post_files['access'][$k]);
			}

			$table->added = strftime('%F %T');
			$table->added_by = $user->get('id');

			if (!($table->check() && $table->store()))
			{
				JError::raiseWarning(0, JText::_('COM_REDEVENT_ATTACHMENT_ERROR_SAVING_TO_DB') . ': ' . $table->getError());
			}
		}

		return true;
	}

	/**
	 * update attachment record in db
	 *
	 * @param   array  $attach  (id, name, description, access)
	 *
	 * @return bool
	 */
	private static function update($attach)
	{
		if (!is_array($attach) || !isset($attach['id']) || !(intval($attach['id'])))
		{
			return false;
		}

		$table = JTable::getInstance('redevent_attachments', '');
		$table->load($attach['id']);
		$table->bind($attach);

		if (!($table->check() && $table->store()))
		{
			JError::raiseWarning(0, JText::_('COM_REDEVENT_ATTACHMENT_ERROR_UPDATING_RECORD') . ': ' . $table->getError());

			return false;
		}

		return true;
	}

	/**
	 * return attachments for objects
	 *
	 * @param   string  $object  object identification (should be event<eventid>, category<categoryid>, etc...)
	 * @param   array   $aid     allowed access levels
	 *
	 * @return array
	 */
	public static function getAttachments($object, $aid = null)
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');

		$db = JFactory::getDbo();

		$path = self::getBasePath() . '/' . $object;

		if (!file_exists($path))
		{
			return array();
		}

		// First list files in the folder
		$files = JFolder::files($path, null, false, false, array('index.html'));

		// Then get info for files from db
		$fnames = array();

		foreach ($files as $f)
		{
			$fnames[] = $db->quote($f);
		}

		if (!count($fnames))
		{
			return array();
		}

		$query = $db->getQuery(true);

		$query->select('*');
		$query->from('#__redevent_attachments');
		$query->where('file IN (' . implode(',', $fnames) . ')');
		$query->where('object = ' . $db->Quote($object));

		if (!is_null($aid) && count($aid))
		{
			$query->where('access IN (' . implode(',', $aid) . ')');
		}

		$query->order('ordering ASC');

		$db->setQuery($query);
		$res = $db->loadObjectList();

		return $res;
	}

	/**
	 * get the file
	 *
	 * @param   int  $id   file id
	 * @param   int  $aid  access id
	 *
	 * @return string path
	 */
	public static function getAttachmentPath($id, $aid = null)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('*');
		$query->from('#__redevent_attachments');
		$query->where('id = ' . $db->Quote(intval($id)));

		$db->setQuery($query);
		$res = $db->loadObject();

		if (!$res)
		{
			JError::raiseError(404, JText::_('COM_REDEVENT_FILE_UNKNOWN'));
		}

		if (!is_null($aid) && $res->access > $aid)
		{
			JError::raiseError(403, JText::_('COM_REDEVENT_YOU_DONT_HAVE_ACCESS_TO_THIS_FILE'));
		}

		$path = self::getBasePath() . '/' . $res->object . '/' . $res->file;

		if (!file_exists($path))
		{
			JError::raiseError(404, JText::_('COM_REDEVENT_FILE_NOT_FOUND'));
		}

		return $path;
	}


	/**
	 * remove attachment for objects
	 *
	 * @param   int  $id  id from db
	 *
	 * @return boolean
	 */
	public static function remove($id)
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');

		// Get info for files from db
		$db = JFactory::getDBO();
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('file, object');
		$query->from('#__redevent_attachments');
		$query->where('id = ' . $db->Quote($id));

		$db->setQuery($query);
		$res = $db->loadObject();

		if (!$res)
		{
			return false;
		}

		$path = self::getBasePath() . '/' . $res->object . '/' . $res->file;

		if (file_exists($path))
		{
			JFile::delete($path);
		}

		$query = $db->getQuery(true);

		$query->delete('#__redevent_attachments');
		$query->where('id = ' . (int) $id);

		$db->setQuery($query);
		$res = $db->execute();

		if (!$res)
		{
			return false;
		}

		return true;
	}

	/**
	 * Store attachments for key from post data (variables need specific names)
	 *
	 * @param   string  $key  key for attachment
	 *
	 * @return boolean true on success
	 */
	public static function store($key)
	{
		$app = JFactory::getApplication();

		// New ones first
		$attachments = $app->input->get('attach', array(), 'files', 'array');
		$attachments['customname'] = $app->input->get('attach-name', array(), 'post', 'array');
		$attachments['description'] = $app->input->get('attach-desc', array(), 'post', 'array');
		$attachments['access'] = $app->input->get('attach-access', array(), 'post', 'array');

		if (!self::postUpload($attachments, $key))
		{
			return false;
		}

		// And update old ones
		$attachments = array();
		$old['id'] = $app->input->get('attached-id', array(), 'post', 'array');
		$old['name'] = $app->input->get('attached-name', array(), 'post', 'array');
		$old['description'] = $app->input->get('attached-desc', array(), 'post', 'array');
		$old['access'] = $app->input->get('attached-access', array(), 'post', 'array');

		foreach ($old['id'] as $k => $id)
		{
			$attach = array();
			$attach['id'] = $id;
			$attach['name'] = $old['name'][$k];
			$attach['description'] = $old['description'][$k];
			$attach['access'] = $old['access'][$k];

			if (!self::update($attach))
			{
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
	private static function getBasePath()
	{
		$params = JComponentHelper::getParams('com_redevent');

		$path = JPATH_SITE . '/' . $params->get('attachments_path', 'media/com_redevent/attachments');

		if (!file_exists($path))
		{
			jimport('joomla.filesystem.folder');

			if (!JFolder::create($path))
			{
				JError::raiseWarning(0, Jtext::_('COM_REDEVENT_ATTACHMENTS_ERROR_CANNOT_CREATE_BASE_FOLDER'));

				return false;
			}
		}

		return $path;
	}
}
