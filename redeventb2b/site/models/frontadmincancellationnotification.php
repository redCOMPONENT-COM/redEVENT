<?php
/**
 * @package    Redeventb2b.site
 * @copyright  Copyright (C) 2008 - 2016 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * B2B Cancellation Notification model
 *
 * @package  Redevent
 * @since    2.5
 */
class Redeventb2bModelFrontadminCancellationNotification extends JModelLegacy
{
	/**
	 * @var int
	 */
	private $attendeeId;

	/**
	 * @var int
	 */
	private $organizationId;

	/**
	 * @var RedeventAttendee
	 */
	private $attendee;

	/**
	 * Set attendee id
	 *
	 * @param   int  $id  id
	 *
	 * @return $this
	 */
	public function setAttendeeId($id)
	{
		$this->attendeeId = (int) $id;

		return $this;
	}

	/**
	 * Set Organization id
	 *
	 * @param   int  $id  id
	 *
	 * @return $this
	 */
	public function setOrganizationId($id)
	{
		$this->organizationId = (int) $id;

		return $this;
	}

	/**
	 * Send the notifications
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function notify()
	{
		$orgSettings = RedeventHelperOrganization::getSettings($this->organizationId);

		$email = $this->prepareNotify();

		// Check the organization flow setting for 'attendee' notification
		switch ($orgSettings->get('b2b_attendee_notification_mailflow', 0))
		{
			case '0':
				// Just the attendee
				$this->addAttendee($email);
				break;

			case '1':
				// Just the organizations admins
				$this->addOrganizationAdmin($email);
				break;

			case '2':
				// Both
				$this->addAttendee($email);
				$this->addOrganizationAdmin($email);
				break;
		}

		if (!$email->send())
		{
			RedeventHelperLog::simpleLog('B2B registration cancellation: failed sending org admins email');
		}

		// Notify managers
		JPluginHelper::importPlugin('redevent');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onB2BCancellationNotifyAdmins', array($this->attendeeId));
	}

	/**
	 * Notify attendee
	 *
	 * @param   JMail  $email  email
	 *
	 * @return void
	 */
	private function addAttendee($email)
	{
		$attendee = $this->getAttendee();
		$email->addRecipient($attendee->getEmail());
	}

	/**
	 * Notify org admin
	 *
	 * @param   JMail  $email  email
	 *
	 * @return void
	 */
	private function addOrganizationAdmin($email)
	{
		$user = JFactory::getUser();
		$email->addRecipient($user->get('email'));
	}

	/**
	 * Prepare email
	 *
	 * @return JMail
	 *
	 * @throws Exception
	 */
	private function prepareNotify()
	{
		$attendee = $this->getAttendee();

		$orgSettings = RedeventHelperOrganization::getSettings($this->organizationId);

		$subject = $orgSettings->get('b2b_orgadmin_mailflow_cancellation_subject_tag')
			? '[' . $orgSettings->get('b2b_orgadmin_mailflow_cancellation_subject_tag') . ']'
			: JText::_('COM_REDEVENT_ATTENDEE_NOTIFICATION_MAILFLOW_ORGADMIN_cancellation_DEFAULT_SUBJECT');

		$body = $orgSettings->get('b2b_orgadmin_mailflow_cancellation_body_tag')
			? '[' . $orgSettings->get('b2b_orgadmin_mailflow_cancellation_body_tag') . ']'
			: JText::_('COM_REDEVENT_ATTENDEE_NOTIFICATION_MAILFLOW_ORGADMIN_cancellation_DEFAULT_BODY');
		$email = $attendee->prepareEmail($subject, $body);

		return $email;
	}

	/**
	 * Return attendee object
	 *
	 * @return RedeventAttendee
	 */
	private function getAttendee()
	{
		if (!$this->attendee)
		{
			$this->attendee = new RedeventAttendee($this->attendeeId);
		}

		return $this->attendee;
	}
}
