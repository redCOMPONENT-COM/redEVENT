<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * redevent Component Attachments Controller
 *
 * @package  Redevent.admin
 * @since    2.5
 */
class RedeventControllerAttachments extends RControllerAdmin
{
	/**
	 * Delete attachment
	 *
	 * @return void
	 */
	public function remove()
	{
		$app = JFactory::getApplication();
		$id = $app->input->getInt('id', 0);

		$response = new stdClass;

		$helper = new RedeventHelperAttachment;

		$res = $helper->remove($id);

		if ($res)
		{
			$response->success = 1;
			$cache = JFactory::getCache('com_redevent');
			$cache->clean();
		}
		else
		{
			$response->success = 0;
			$response->error = $helper->getError();
		}

		echo json_encode($response);
		$app->close();
	}
}
