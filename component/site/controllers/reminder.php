<?php
/**
 * @package    Redevent.Site
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * redEVENT Component attendees Controller
 *
 * @package  Redevent.Site
 * @since    2.0
 */
class RedeventControllerReminder extends RedeventControllerFront
{
	const TIMESTAMP_FILE = JPATH_COMPONENT_SITE . '/paymentreminder.txt';

	/**
	 * Task handler
	 *
	 * @return void
	 */
	public function payment()
	{
		if (!$this->canSendPaymentReminder())
		{
			echo "Not enough time since last time reminders were sent.";

			return;
		}

		$model = $this->getModel('Paymentreminder');

		try
		{
			$model->send();
		}
		catch (RuntimeException $e)
		{
			echo $e->getMessage();
		}

		touch(self::TIMESTAMP_FILE);
	}

	/**
	 * Check if we should actually send reminders
	 *
	 * @return bool
	 *
	 * @throws Exception
	 */
	private function canSendPaymentReminder()
	{
		jimport('joomla.filesystem.file');
		$app = JFactory::getApplication();
		$params = $app->getParams('com_redevent');

		// Check if sending is forced
		if ($this->input->getInt('force', 0))
		{
			return true;
		}

		// Check if there is a minimal gap between reminders
		if (!$minimumGap = (int) $params->get('check_payment_reminder_every', 60 * 24))
		{
			return true;
		}

		// Check for the modification file, if it isn't there it means we didn't send any reminder yet.
		if (!file_exists(self::TIMESTAMP_FILE))
		{
			return true;
		}

		return (time() - filemtime(self::TIMESTAMP_FILE)) >= $minimumGap * 60;
	}
}
