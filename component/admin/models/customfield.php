<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * RedEvent Model Customfield
 *
 * @package  Redevent.admin
 * @since    0.9
 */
class RedeventModelCustomfield extends RModelAdmin
{
	/**
	 * Function serve for upload from dragndrop ajax
	 *
	 * @param   array   $file          File posted
	 * @param   string  $uploadType    type of files posted [file/image/gallery]
	 * @param   string  $uploadTarget  fieldcode
	 *
	 * @return  string  path of uploaded files, '' if not in types [file/image/gallery]
	 */
	public function dragndropUpload($file, $uploadType, $uploadTarget)
	{
		$path = '';

		// Prepare filename
		$fileExtension = JFile::getExt($file['name']);
		$fileName      = JFilterOutput::stringURLSafe(JFile::stripExt($file['name']));

		$file['name'] = $fileName . '.' . $fileExtension;
		$fileFolder   = JPATH_ROOT . '/images/com_redevent/customfields/image/';

		// Get global configuration
		$config = JComponentHelper::getParams('com_redevent');
		$maxFileSize       = $config->get('customfieldFileUploadMaxSize', 2);
		$extensions        = $config->get('customfieldFileUploadExtensions', "jpg,jpeg,gif,png");
		$mimes             = $config->get('customfieldFileUploadMimes', "application/zip,application/doc,application/xls,application/pdf");
		$useCustomFileName = (boolean) $config->get('customfieldFileUploadUseCustomName', true);

		$result = RedeventHelperFileupload::uploadFile($file, $fileFolder, $maxFileSize, $extensions, $mimes);

		if ($result && isset($result['mangled_filename']))
		{
			$path = $result['mangled_filename'];
		}

		return $path;
	}
}
