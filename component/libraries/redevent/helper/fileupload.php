<?php
/**
 * @package     Redevent.Helper
 * @subpackage  Library
 *
 * @copyright   Copyright (C) 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */
defined('_JEXEC') or die;

/**
 * File upload helper.
 *
 * @package     Redevent.Helper
 * @subpackage  Library
 *
 * @since       1.0
 */
class RedeventHelperFileupload
{
	/**
	 * Uploads file to the given media folder.
	 *
	 * @param   array    $file               The file descriptor returned by PHP
	 * @param   string   $destinationFolder  Name of a folder in media/com_reditem/.
	 * @param   int      $maxFileSize        Maximum allowed file size.
	 * @param   string   $okFileExtensions   Comma separated string list of allowed file extensions.
	 * @param   string   $okMIMETypes        Comma separated string list of allowed MIME types.
	 * @param   boolean  $customName         If true, system will auto create file name. If false, filename is original name
	 *
	 * @return array|bool
	 */
	public static function uploadFile($file, $destinationFolder, $maxFileSize = 2, $okFileExtensions = null, $okMIMETypes = null, $customName = true)
	{
		$app = JFactory::getApplication();
		$fileExtension = JFile::getExt($file['name']);

		/* @todo: Can we upload this file type? */
		/*if (!self::canUpload($file, $maxFileSize, $okFileExtensions, $okMIMETypes))
		{
			return false;
		}*/

		if ($customName === false)
		{
			$mangledName = JFilterOutput::stringURLSafe(JFile::stripExt($file['name']));
		}
		else
		{
			$mangledName = self::getUniqueName($file['name']);
		}

		// ...and its full path
		$filepath = JPath::clean($destinationFolder . '/' . $mangledName . '.' . $fileExtension);

		// If we have a name clash, abort the upload
		if (JFile::exists($filepath))
		{
			$app->enqueueMessage(JText::sprintf('COM_REDEVENT_FILE_HELPER_FILENAMEALREADYEXIST', $filepath), 'error');

			return false;
		}

		// Do the upload
		if (!JFile::upload($file['tmp_name'], $filepath))
		{
			$app->enqueueMessage(JText::_('COM_REDEVENT_FILE_HELPER_CANTJFILEUPLOAD'), 'error');

			return false;
		}

		// Get the MIME type
		if (function_exists('mime_content_type'))
		{
			$mime = mime_content_type($filepath);
		}
		elseif (function_exists('finfo_open'))
		{
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$mime = finfo_file($finfo, $filepath);
		}
		else
		{
			$mime = 'application/postscript';
		}

		$resultFile = array(
			'original_filename' => $file['name'],
			'mangled_filename' => $mangledName . '.' . $fileExtension,
			'mime_type' => $mime,
			'filepath' => $filepath
		);

		// Return the file info
		return $resultFile;
	}

	/**
	 * Checks if the file can be uploaded.
	 *
	 * @param   string  $name  Additional string you want to put into hash
	 *
	 * @return  boolean
	 */
	public static function getUniqueName($name = '')
	{
		// Get a (very!) randomised name
		if (version_compare(JVERSION, '3.0', 'ge'))
		{
			$serverKey = JFactory::getConfig()->get('secret', '');
		}
		else
		{
			$serverKey = JFactory::getConfig()->getValue('secret', '');
		}

		$sig = $name . microtime() . $serverKey;

		if (function_exists('sha256'))
		{
			$mangledName = sha256($sig);
		}
		elseif (function_exists('sha1'))
		{
			$mangledName = sha1($sig);
		}
		else
		{
			$mangledName = md5($sig);
		}

		return $mangledName;
	}

	/**
	 * Checks if the file can be uploaded.
	 *
	 * @param   array   $file              File information.
	 * @param   int     $maxFileSize       Maximum allowed file size.
	 * @param   string  $okFileExtensions  Comma separated string list of allowed file extensions.
	 * @param   string  $okMIMETypes       Comma separated string list of allowed MIME types.
	 *
	 * @return  boolean
	 */
	private static function canUpload($file, $maxFileSize = 2, $okFileExtensions = null, $okMIMETypes = null)
	{
		$app = JFactory::getApplication();

		if (empty($file['name']))
		{
			$app->enqueueMessage(JText::_('COM_REDEVENT_FILE_HELPER_FILE_NAME_EMPTY'), 'error');

			return false;
		}

		jimport('joomla.filesystem.file');

		if ($file['name'] !== JFile::makesafe($file['name']))
		{
			$app->enqueueMessage(JText::sprintf('COM_REDEVENT_FILE_HELPER_ERROR_FILE_NAME', $file['name']), 'error');

			return false;
		}

		// Allowed file extensions
		if (!empty($okFileExtensions))
		{
			$format = strtolower(JFile::getExt($file['name']));
			$allowable = array_map('trim', explode(",", $okFileExtensions));

			if (!in_array($format, $allowable))
			{
				$app->enqueueMessage(JText::sprintf('COM_REDEVENT_FILE_HELPER_ERROR_WRONG_FILE_EXTENSION', $format, $okFileExtensions), 'error');

				return false;
			}
		}

		// Max file size is set by config.xml
		$maxSize = (int) ($maxFileSize * 1024 * 1024);

		if ($maxSize > 0 && (int) $file['size'] > $maxSize)
		{
			$app->enqueueMessage(JText::sprintf('COM_REDEVENT_FILE_HELPER_ERROR_FILE_TOOLARGE', $maxFileSize), 'error');

			return false;
		}

		// Allowed file extensions
		if (!empty($okMIMETypes))
		{
			$validFileTypes = array_map('trim', explode(",", $okMIMETypes));

			if (!in_array($file['type'], $validFileTypes))
			{
				$app->enqueueMessage(JText::sprintf('COM_REDEVENT_FILE_HELPER_ERROR_INVALID_MIME', $file['type'], $okMIMETypes));

				return false;
			}
		}

		return true;
	}
}
