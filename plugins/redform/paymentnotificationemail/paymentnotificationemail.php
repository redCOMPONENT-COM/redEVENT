<?php
/**
 * @package     Redevent.Plugin
 * @subpackage  paymentnotificationemail
 *
 * @copyright   Copyright (C) 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */


defined('JPATH_BASE') or die;

// Import library dependencies
jimport('joomla.plugin.plugin');

JLoader::registerPrefix('Redevent', JPATH_LIBRARIES . '/redevent');

/**
 * Specific parameters for redEVENT.
 *
 * @package     Redevent.Plugin
 * @subpackage  paymentnotificationemail
 * @since       2.5
 */
class plgRedformPaymentnotificationemail extends JPlugin
{
	/**
	 * The plugin identifier.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $context = 'paymentnotificationemail';

	private $submitkey;

	/**
	 * Send an email to attendee for payment accepted
	 *
	 * @param   string  $submit_key  submit key associated to payment
	 *
	 * @return bool true on success
	 */
	public function onAfterPaymentVerified($submit_key)
	{
		$this->submitkey = $submit_key;

		try
		{
			if ($this->isCustomPayment())
			{
				return true;
			}

			$this->sendNotificationEmail();
		}
		catch (Exception $e)
		{
			RedeventHelperLog::simpleLog($e->getMessage());
		}

		return true;
	}

	/**
	 * Check if this is a custom payment
	 *
	 * @return bool
	 *
	 * @throws Exception
	 */
	private function isCustomPayment()
	{
		if (!$payment = $this->getPayment())
		{
			throw new Exception('No payment found');
		}

		return strcasecmp($payment->gateway, 'custom') == 0;
	}

	/**
	 * Get latest payment info
	 *
	 * @return mixed
	 */
	private function getPayment()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('p.*');
		$query->from('#__rwf_payment AS p');
		$query->where('p.submit_key = ' . $this->submitkey);
		$query->where('p.paid = 1');
		$query->order('p.id DESC');

		$db->setQuery($query);
		$res = $db->loadObject();

		return $res;
	}

	/**
	 * Send the email
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	private function sendNotificationEmail()
	{
		$mailer = $this->getMailer();
		$attendee_id = $this->getAnAttendeeId();

		if (!$attendee_id)
		{
			// Not a redevent registration

			return;
		}

		$attendee = new RedeventAttendee($attendee_id);

		$recipient = $attendee->getEmail();

		$mailer->addAddress($recipient);

		$subject = $attendee->replaceTags($this->params->get('subject'));
		$body = $attendee->replaceTags($this->params->get('body'));

		$mailer->IsHTML(true);
		$mailer->setSubject($subject);
		$mailer->setBody($body);

		if (!$mailer->send())
		{
			throw new Exception('Failed sending payment notification email: ' . $mailer->ErrorInfo);
		}
	}

	/**
	 * Return one of the attendee id associated to submit key
	 *
	 * @return int
	 */
	private function getAnAttendeeId()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('id');
		$query->from('#__redevent_register');
		$query->where('submit_key = ' . $db->quote($this->submitkey));

		$db->setQuery($query);
		$res = $db->loadResult();

		return $res;
	}

	/**
	 * Get the mailer configured
	 *
	 * @return JMail
	 */
	private function getMailer()
	{
		$app = JFactory::getApplication();
		$mailer = JFactory::getMailer();

		$sender = array($app->getCfg('mailfrom'), $app->getCfg('sitename'));
		$mailer->setSender($sender);
		$mailer->addReplyTo($sender);

		return $mailer;
	}
}
