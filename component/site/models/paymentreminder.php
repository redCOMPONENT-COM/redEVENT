<?php
/**
 * @package    Redevent.Site
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevent Model Payment reminder
 *
 * @package  Redevent.Site
 * @since    3.0
 */
class RedeventModelPaymentreminder extends RModel
{
	/**
	 * Send reminder to attendees that didn't pay
	 *
	 * @param   array  $attendeeIds  attendee ids
	 *
	 * @return int count of notifications sent
	 *
	 * @throws RuntimeException
	 */
	public function send($attendeeIds = null)
	{
		if (!$attendeeIds)
		{
			$attendeeIds = $this->getAttendees();
		}

		if (!count($attendeeIds))
		{
			return 0;
		}

		$subject = JText::_('LIB_REDEVENT_PAYMENT_REMINDER_SUBJECT');
		$body = JText::_('LIB_REDEVENT_PAYMENT_REMINDER_BODY');

		foreach ($attendeeIds as $attendeeId)
		{
			$attendee = new RedeventAttendee($attendeeId);

			/* Load the mailer */
			$mailer = $attendee->prepareEmail($subject, $body);
			$emails = $attendee->getContactEmails();

			foreach ($emails as $email)
			{
				/* Add the email address */
				$mailer->AddAddress($email['email'], $email['fullname']);
			}

			/* send */
			if (!$mailer->Send())
			{
				throw new RuntimeException('Error sending payment reminder message');
			}
		}

		return count($attendeeIds);
	}

	/**
	 * Get attendees that need to be reminded
	 *
	 * @return array
	 */
	private function getAttendees()
	{
		$query = $this->_db->getQuery(true)
				->select('r.id')
				->from('#__redevent_register AS r')
				->join('INNER', '#__redevent_event_venue_xref AS x ON x.id = r.xref')
				->join('INNER', '#__rwf_submitters AS s ON s.id = r.sid')
				->join('INNER', '#__rwf_payment_request AS pr ON pr.submission_id = s.id AND paid = 0')
				->where('(x.dates = 0 OR x.dates > NOW())')
				->where('r.cancelled = 0')
				->where('pr.price > 0');

		$this->_db->setQuery($query);

		return $this->_db->loadColumn();
	}
}
