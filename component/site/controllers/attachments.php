<?php
/**
 * @package    Redevent.Site
 * @copyright  Copyright (C) 2008 - 2018 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * redEVENT Component Myevents Controller
 *
 * @package  Redevent.Site
 * @since    3.2.9
 */
class RedeventControllerAttachments extends JControllerLegacy
{
	/**
	 * return sessions html table
	 *
	 * @return void
	 */
	public function getfile()
	{
		$app = JFactory::getApplication();
		$id = $app->input->getInt('file', 0);
		$user = JFactory::getUser();
		$helper = new RedeventHelperAttachment;
		$path = $helper->getAttachmentPath($id, max($user->getAuthorisedViewLevels()));
		$fileName = basename($path);

		// The header is fine tuned to work with grump ie8... if you modify a property, make sure it's still ok !
		header('Content-Description: File Transfer');

		// Mime
		$mime = RedeventHelper::getMime($path) ?: 'octet-stream';

		header("Pragma: public");
		header("Expires: -1");
		header("Cache-Control: public, must-revalidate, post-check=0, pre-check=0");
		header("Content-Disposition: attachment; filename=\"$fileName\"");
		header("Content-Type: " . $mime);

		if ($fd = fopen($path, "r"))
		{
			$fsize = filesize($path);
			header("Content-length: $fsize");

			while (!feof($fd))
			{
				$buffer = fread($fd, 2048);
				echo $buffer;
			}
		}

		fclose($fd);

		$app->close();
	}

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
