<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevent Component Customfield Controller
 *
 * @package  Redevent.admin
 * @since    2.0
 */
class RedeventControllerCustomfield extends RControllerForm
{
	/**
	 * Method for upload an image using AJAX method
	 *
	 * @return  void
	 */
	public function ajaxUpload()
	{
		$app = JFactory::getApplication();
		$files = $app->input->files->get('dragFile');

		$uploadType = $app->input->getString('uploadType', '');

		if (!in_array($uploadType, array('file', 'image', 'gallery')))
		{
			$app->close();
		}

		$uploadTarget = $app->input->getString('uploadTarget', '');

		if (trim($uploadTarget) == '')
		{
			$app->close();
		}

		$model = RModel::getAdminInstance('customfield', array('ignore_request' => true));
		echo $model->dragndropUpload($files, $uploadType, $uploadTarget);

		$app->close();
	}
}
