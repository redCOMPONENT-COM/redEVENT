<?php
/**
 * @package    RedEVENT
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 * redEVENT is based on EventList made by Christoph Lukes from schlu.net
 * redEVENT can be downloaded from www.redcomponent.com
 * redEVENT is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * redEVENT is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with redEVENT; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

// No direct access
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
			throw new Redeventb2bExceptionNotice('failed sending org admins email');
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
