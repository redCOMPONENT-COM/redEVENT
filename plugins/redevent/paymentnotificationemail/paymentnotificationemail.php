<?php
/**
 * @package     Redevent.Plugin
 * @subpackage  paymentnotificationemail
 *
 * @copyright   Copyright (C) 2008-2015 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

// Import library dependencies
jimport('joomla.plugin.plugin');

// Register library prefix
JLoader::registerPrefix('R', JPATH_LIBRARIES . '/redcore');
RLoader::registerPrefix('Redevent', JPATH_LIBRARIES . '/redevent');
RLoader::registerPrefix('Rdf', JPATH_LIBRARIES . '/redform');

/**
 * Specific parameters for redEVENT.
 *
 * @since  2.5
 */
class PlgRedeventPaymentnotificationemail extends JPlugin
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
	public function onAfterPaymentVerifiedRedevent($submit_key)
	{
		$this->submitkey = $submit_key;

		try
		{
			$this->sendNotificationEmail();
		}
		catch (Exception $e)
		{
			RedeventHelperLog::simpleLog($e->getMessage());
		}

		return true;
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

		$payment = $this->getPayment($attendee);

		if ($payment->gateway == 'custom')
		{
			// Don't send for custom payment gateway
			return;
		}

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
		$mailer = RdfHelper::getMailer();

		$sender = array($app->getCfg('mailfrom'), $app->getCfg('sitename'));
		$mailer->setSender($sender);
		$mailer->addReplyTo($sender);

		return $mailer;
	}

	/**
	 * Get associated payment
	 *
	 * @param   RedeventAttendee  $attendee  attendee
	 *
	 * @return RdfEntityPayment
	 */
	private function getPayment($attendee)
	{
		$submitter = RdfEntitySubmitter::load($attendee->get('sid'));

		$payment = null;

		foreach ($submitter->getPaymentRequests() as $paymentRequest)
		{
			if ($payment = $this->getPaymentRequestPayment($paymentRequest->id))
			{
				break;
			}
		}

		return $payment;
	}

	/**
	 * Get associated payment
	 *
	 * @return RdfEntityPayment
	 *
	 * @since 3.3.18
	 */
	public function getPaymentRequestPayment($id)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('p.*')
			->from('#__rwf_payment AS p')
			->innerJoin('#__rwf_cart_item AS ci ON ci.cart_id = p.cart_id')
			->where('ci.payment_request_id = ' . $id)
			->where('p.paid = 1');

		$db->setQuery($query);

		if (!$res = $db->loadObject())
		{
			return false;
		}

		$entity = RdfEntityPayment::getInstance($res->id)->bind($res);

		return $entity;
	}
}
