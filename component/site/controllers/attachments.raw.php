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
 * @since    __deploy_version__
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
		$id = $this->input->getInt('file', 0);
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

		$fd = fopen($path, "r");

		if ($fd)
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
