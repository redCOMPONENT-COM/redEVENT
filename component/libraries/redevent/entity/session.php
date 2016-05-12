<?php
/**
 * @package     Redevent.Library
 * @subpackage  Entity
 *
 * @copyright   Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Event entity.
 *
 * @since  1.0
 */
class RedeventEntitySession extends RedeventEntityBase
{
	/**
	 * Associated event
	 *
	 * @var RedeventEntityAttendee[]
	 */
	private $attendees;

	/**
	 * Associated event
	 *
	 * @var RedeventEntityEvent
	 */
	private $event;

	/**
	 * @var array
	 */
	private $pricegroups;

	/**
	 * Associated venue
	 *
	 * @var RedeventEntityVenue
	 */
	private $venue;

	/**
	 * Return associated attendees
	 *
	 * @return RedeventEntityAttendee[]
	 */
	public function getAttendees()
	{
		if (is_null($this->attendees))
		{
			$item = $this->getItem();

			if (!empty($item))
			{
				$db = JFactory::getDbo();
				$query = $db->getQuery(true)
					->select('*')
					->from('#__redevent_register')
					->where('xref = ' . $this->id);

				$db->setQuery($query);
				$res = $db->loadObjectList();

				$this->attendees = $res ? array_map(
					function($row)
					{
						return RedeventEntityAttendee::getInstance($row->id)->bind($row);
					}, $res
				) : false;
			}
		}

		return $this->attendees;
	}

	/**
	 * Check if user can register to session
	 *
	 * @param   JUser  $user  user
	 *
	 * @return boolean
	 */
	public function canRegister($user = null)
	{
		$user = $user ?: JFactory::getUser();
		$status = RedeventHelper::canRegister($this->id, $user->get('id'));

		return $status->canregister;
	}

	/**
	 * Return associated event
	 *
	 * @return RedeventEntityEvent
	 */
	public function getEvent()
	{
		if (!$this->event)
		{
			$item = $this->getItem();

			if (!empty($item))
			{
				$this->event = RedeventEntityEvent::load($item->eventid);
			}
		}

		return $this->event;
	}

	/**
	 * Return formatted start date
	 *
	 * @param   string  $dateFormat  php date() format
	 * @param   string  $timeFormat  php date() format
	 *
	 * @return string
	 */
	public function getFormattedStartDate($dateFormat = null, $timeFormat = null)
	{
		$item = $this->loadItem();

		if (!RedeventHelperDate::isValidDate($item->dates))
		{
			return JText::_('LIB_REDEVENT_OPEN_DATE');
		}

		if (RedeventHelperDate::isValidTime($item->times))
		{
			if (!is_null($dateFormat))
			{
				$format = $dateFormat . (is_null($timeFormat) ? '' : $timeFormat);
			}
			else
			{
				$format = null;
			}

			return RedeventHelperDate::formatdatetime($item->dates . ' ' . $item->times, $format);
		}

		return RedeventHelperDate::formatdate($item);
	}

	/**
	 * Return formatted end date
	 *
	 * @param   string  $dateFormat  php date() format
	 * @param   string  $timeFormat  php date() format
	 *
	 * @return string
	 */
	public function getFormattedEndDate($dateFormat = null, $timeFormat = null)
	{
		$item = $this->loadItem();

		if (!RedeventHelperDate::isValidDate($item->dates))
		{
			return JText::_('LIB_REDEVENT_OPEN_DATE');
		}

		if (RedeventHelperDate::isValidTime($item->times))
		{
			if (!is_null($dateFormat))
			{
				$format = $dateFormat . (is_null($timeFormat) ? '' : $timeFormat);
			}
			else
			{
				$format = null;
			}

			return RedeventHelperDate::formatdatetime($item->dates . ' ' . $item->times, $format);
		}

		return RedeventHelperDate::formatdate($item);
	}

	/**
	 * Get number of signup left for user
	 *
	 * @param   int  $userId  user id
	 *
	 * @return int
	 */
	public function getUserNumberOfSignupLeft($userId)
	{
		// Multiple signup ?
		$max = $this->getEvent()->max_multi_signup;
		$user = JUser::getInstance($userId);

		if ($max && $user->id)
		{
			// We must substract current registrations of this user !
			$nbregs = $this->getUserActiveRegistrationsCount($user->id);
			$allowed = $max - $nbregs;

			return $allowed > 0 ? $allowed : 0;
		}

		// No max, or user not registered, always allow one place.
		return 1;
	}

	/**
	 * Return initialized RedeventRfieldSessionprice
	 *
	 * @return RedeventRfieldSessionprice
	 */
	public function getPricefield()
	{
		$field = new RedeventRfieldSessionprice;
		$field->setOptions($this->getPricegroups());
		$title = $this->getEvent()->title . ($this->title ? ' - ' . $this->title : '');
		$field->setPaymentRequestItemLabel(JText::sprintf('COM_REDEVENT_REGISTRATION_PRICE_ITEM_LABEL_S', $title));

		return $field;
	}

	/**
	 * Return RedeventEntitySessionpricegroups
	 *
	 * @return   RedeventEntitySessionpricegroup[]
	 */
	public function getPricegroups()
	{
		if (!$this->pricegroups)
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('sp.*');
			$query->from('#__redevent_sessions_pricegroups AS sp');
			$query->where('sp.xref = ' . $db->Quote($this->id));

			$db->setQuery($query);
			$items = $db->loadObjectList();

			$this->pricegroups = array_map(
				function($item)
				{
					$pricegroup = RedeventEntitySessionpricegroup::getInstance();
					$pricegroup->bind($item);

					return $pricegroup;
				},
				$items
			);
		}

		return $this->pricegroups;
	}

	/**
	 * Return associated venue
	 *
	 * @return RedeventEntityVenue
	 */
	public function getVenue()
	{
		if (!$this->venue)
		{
			$item = $this->getItem();

			if (!empty($item))
			{
				$this->venue = RedeventEntityVenue::getInstance($item->venueid)->loadItem();
			}
		}

		return $this->venue;
	}

	/**
	 * Check if session is full
	 *
	 * @return boolean
	 */
	public function isFull()
	{
		// Check the max registrations and waiting list
		if ($this->getEvent()->maxattendees)
		{
			if (!$attendees = $this->getAttendees())
			{
				return false;
			}

			$registered = 0;
			$waiting = 0;

			foreach ($attendees as $attendee)
			{
				if ((!$attendee->confirmed) || $attendee->cancelled)
				{
					continue;
				}

				if ($attendee->waitinglist)
				{
					$waiting++;
				}
				else
				{
					$registered++;
				}
			}

			if ($this->getEvent()->maxattendees <= $registered
				&& $this->getEvent()->maxwaitinglist <= $waiting)
			{
				$this->setResultError(JText::_('COM_REDEVENT_EVENT_FULL'), static::ERROR_IS_FULL);

				return true;
			}
		}

		return false;
	}

	/**
	 * return current number of registrations for current user to this event
	 *
	 * @param   int  $userId  user id
	 *
	 * @return int
	 */
	private function getUserActiveRegistrationsCount($userId)
	{
		if (!$attendees = $this->getAttendees())
		{
			return false;
		}

		$count = 0;

		foreach ($attendees as $attendee)
		{
			if ($attendee->uid == $userId && $attendee->cancelled == 0)
			{
				$count++;
			}
		}

		return $count;
	}
}
