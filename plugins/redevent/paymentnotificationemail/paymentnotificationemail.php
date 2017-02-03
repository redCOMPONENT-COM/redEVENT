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
}
