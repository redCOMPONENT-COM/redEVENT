<?php
/**
 * @package    Redevent.Site
 *
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevent Component Controller
 *
 * @package  Redevent.Site
 * @since    3.2.2
 */
class RedeventController extends RedeventControllerFront
{
	/**
	 * for attachement downloads
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

		// The header is fine tuned to work with grump ie8... if you modify a property, make sure it's still ok !
		header('Content-Description: File Transfer');

		// Mime
		$mime = RedeventHelper::getMime($path);
		$doc = JFactory::getDocument();
		$doc->setMimeEncoding($mime);

		header('Content-Disposition: attachment; filename="' . basename($path) . '"');
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: no-store, no-cache');
		header('Pragma: no-cache');

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
}
