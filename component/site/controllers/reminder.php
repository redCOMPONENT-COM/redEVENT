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
	/**
	 * Task handler
	 *
	 * @return void
	 */
	public function payment()
	{
		$model = $this->getModel('Paymentreminder');
		$sent = $this->input->getInt('sent');
		$total = $this->input->getInt('total');

		try
		{
			$total = $total ?: $model->getTotal();
			$new = $model->send(null, $this->input->getInt('force', 0));
		}
		catch (RuntimeException $e)
		{
			echo $e->getMessage();

			return;
		}

		$sent += $new;
		echo JText::sprintf("COM_REDEVENT_PAYMENT_REMINDER_D_REMINDERS_SENT", $sent, $total);

		// We only send a few emails at a time, to prevent execution time issue.
		if ($new)
		{
			$uri = JURI::getInstance();
			$uri->setVar('sent', $sent);
			$uri->setVar('total', $total);

			$this->setRedirect($uri->toString());
		}
	}
}
