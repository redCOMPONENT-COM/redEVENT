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
	 * Check if user can register to session
	 *
	 * @param   JUser  $user  user
	 *
	 * @return boolean
	 */
	public function getCanRegisterStatus($user = null)
	{
		$user = $user ?: JFactory::getUser();

		return RedeventHelper::canRegister($this->id, $user->get('id'));
	}

	/**
	 * Get start date/time
	 *
	 * @param   bool  $dateOnly  only take day into account
	 *
	 * @return JDate
	 */
	public function getDateStart($dateOnly = false)
	{
		$item = $this->getItem(true);

		if ($this->isOpenDate())
		{
			return false;
		}

		return JFactory::getDate($item->dates . ($this->isAllDay() || $dateOnly ? '' : ' ' . $item->times));
	}

	/**
	 * Get end date/time
	 *
	 * @param   bool  $dateOnly  only take day into account
	 *
	 * @return JDate
	 */
	public function getDateEnd($dateOnly = false)
	{
		$item = $this->getItem(true);

		if ($this->isOpenDate())
		{
			return false;
		}

		if (RedeventHelperDate::isValidDate($item->enddates))
		{
			$endDate = $item->enddates;
		}
		else
		{
			$endDate = $item->dates;
		}

		return JFactory::getDate($endDate . ($this->isAllDay() || $dateOnly ? '' : ' ' . $item->endtimes));
	}

	/**
	 * Get session duration in days (On how many days it spans)
	 *
	 * @return int
	 */
	public function getDurationDays()
	{
		if ($this->isOpenDate())
		{
			return false;
		}

		if ($this->getDateStart(true) == $this->getDateEnd(true))
		{
			return 1;
		}

		return $this->getDateEnd(true)->diff($this->getDateStart(true))->format('%a') + 1;
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
	 * Return formatted dates
	 *
	 * @param   string  $dateFormat  php date() format
	 * @param   string  $timeFormat  php date() format
	 *
	 * @return array
	 */
	public function getFormattedDates($dateFormat = null, $timeFormat = null)
	{
		$item = $this->loadItem();

		if (!RedeventHelperDate::isValidDate($item->dates))
		{
			return array(JText::_('LIB_REDEVENT_OPEN_DATE'));
		}

		if (!is_null($dateFormat))
		{
			$format = $dateFormat . (!is_null($timeFormat) && $item->allday ? '' : ' ' . $timeFormat);
		}
		else
		{
			$format = null;
		}

		$res = array();

		$res[] = RedeventHelperDate::formatdatetime(
			$item->allday ? $item->dates : $item->dates . ' ' . $item->times,
			$format
		);

		if (RedeventHelperDate::isValidDate($item->enddates))
		{
			$res[] = RedeventHelperDate::formatdatetime(
				$item->allday ? $item->enddates : $item->enddates . ' ' . $item->endtimes,
				$format
			);
		}

		return $res;
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

		$format = $dateFormat ?: RedeventHelper::config()->get('formatdate');

		if (!is_null($timeFormat) && !$item->all_day)
		{
			$format .= ' ' . $timeFormat;
		}

		return RedeventHelperDate::formatdatetime(
			$item->dates . ($item->all_day ? '' : ' ' . $item->times),
			$format
		);
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

		$format = $dateFormat ?: RedeventHelper::config()->get('formatdate');

		if (!$item->all_day)
		{
			if (!is_null($timeFormat))
			{
				$format .= ' ' . $timeFormat;
			}

			return RedeventHelperDate::formatdatetime($item->enddates . ' ' . $item->endtimes, $format);
		}

		return RedeventHelperDate::formatdatetime($item->enddates, $format);
	}

	/**
	 * Return full title, including event title
	 *
	 * @return string
	 */
	public function getFullTitle()
	{
		$config = RedeventHelper::config();

		if ($config->get('disable_frontend_session_title', 0))
		{
			return $this->getEvent()->title;
		}

		if (!empty($this->title))
		{
			return $this->getEvent()->title . ' - ' . $this->title;
		}

		return $this->getEvent()->title;
	}

	/**
	 * Check if session as a max attendees number set
	 *
	 * @return bool
	 *
	 * @since 3.2.1
	 */
	public function hasMaxAttendees()
	{
		$item = $this->getItem(true);

		return $item->maxattendees > 0;
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
	 * Return number of booked places
	 *
	 * @return int
	 */
	public function getNumberAttending()
	{
		$attendees = $this->getAttendees();

		return empty($attendees) ? 0 :
			array_reduce(
				$attendees,
				function($count, $attendee)
				{
					if ($attendee->isAttending())
					{
						$count++;
					}

					return $count;
				}
			);
	}

	/**
	 * Return number of persons on waiting list
	 *
	 * @return int
	 */
	public function getNumberLeft()
	{
		$item = $this->getItem();

		if (!$item->maxattendees)
		{
			throw new LogicException('Places left cannot be checked when no max attendees');
		}

		return $item->maxattendees - $this->getNumberAttending();
	}

	/**
	 * Return number of persons on waiting list
	 *
	 * @return int
	 */
	public function getNumberWaiting()
	{
		$attendees = $this->getAttendees();

		return empty($attendees) ? 0 :
			array_reduce(
				$attendees,
				function($count, $attendee)
				{
					if ($attendee->isWaiting())
					{
						$count++;
					}

					return $count;
				}
			);
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
		$field->setPaymentRequestItemLabel(JText::sprintf('LIB_REDEVENT_REGISTRATION_PRICE_ITEM_LABEL_S', $title));

		return $field;
	}

	/**
	 * Return RedeventEntitySessionpricegroups
	 *
	 * @param   bool   $filterAcl  filter by price group acl
	 * @param   JUser  $user       user to filter against
	 *
	 * @return   RedeventEntitySessionpricegroup[]
	 */
	public function getPricegroups($filterAcl = false, $user = null)
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

			$this->pricegroups = RedeventEntitySessionpricegroup::loadArray($items);
		}

		if ($filterAcl)
		{
			$user = $user ?: JFactory::getUser();
			$access = $user->getAuthorisedViewLevels();

			return array_filter(
				$this->pricegroups,
				function ($sessionpricegroup) use ($access)
				{
					return in_array($sessionpricegroup->getPricegroup()->access, $access);
				}
			);
		}

		return $this->pricegroups;
	}

	/**
	 * Get registration end
	 *
	 * @return JDate
	 */
	public function getRegistrationEnd()
	{
		$item = $this->getItem();

		if (!RedeventHelperDate::isValidDate($item->registrationend))
		{
			return null;
		}

		$date = JDate::getInstance($item->registrationend);

		// Put it in the user tz as default
		$date->setTimezone(new DateTimeZone(JFactory::getUser()->getParam('timezone', JFactory::getConfig()->get('offset'))));

		return $date;
	}

	/**
	 * Get registrations links as icons
	 *
	 * @return array
	 *
	 * @since 3.2.1
	 */
	public function getRegistrationIconLinks()
	{
		if (!$this->canRegister())
		{
			return false;
		}

		$icons = array();
		$imageFolder = JURI::base() . 'media/com_redevent/images/';
		$settings = RedeventHelper::config();

		/* Get the different submission types */
		$submissiontypes = explode(',', $this->getEvent()->getEventtemplate()->submission_types);

		foreach ($submissiontypes as $key => $subtype)
		{
			switch ($subtype)
			{
				case 'email':
					$image = JHTML::image(
						$imageFolder . $settings->get('signup_email_img', 'email_icon.gif'),
						JText::_($settings->get('signup_email_text')), 'class="registration-icon"'
					);
					$url = RedeventHelperRoute::getSignupRoute('email', $this->getEvent()->getSlug(), $this->getSlug());
					$icons[] = RHtml::tooltip(
						'',
						JText::_('LIB_REDEVENT_REGISTRATION_ICONS_TOOLTIP_TITLE'), null, $image, $url
					);
					break;

				case 'phone':
					$image = JHTML::image(
						$imageFolder . $settings->get('signup_phone_img', 'phone_icon.gif'),
						JText::_($settings->get('signup_phone_text')), 'class="registration-icon"'
					);
					$url = RedeventHelperRoute::getSignupRoute('phone', $this->getEvent()->getSlug(), $this->getSlug());
					$icons[] = RHtml::tooltip(
						'',
						JText::_('LIB_REDEVENT_REGISTRATION_ICONS_TOOLTIP_TITLE'), null, $image, $url
					);
					break;

				case 'external':
					if (!empty($this->getEvent()->external_registration_url))
					{
						$url = $this->getEvent()->external_registration_url;
					}
					else
					{
						$url = $this->getEvent()->submission_type_external;
					}

					$image = JHTML::image(
						$imageFolder . $settings->get('signup_external_img', 'external_icon.gif'),
						JText::_($settings->get('signup_external_text')), 'class="registration-icon"'
					);
					$icons[] = RHtml::tooltip(
						'',
						JText::_('LIB_REDEVENT_REGISTRATION_ICONS_TOOLTIP_TITLE'), null, $image, $url
					);
					break;

				case 'webform':
					if ($pgs = $this->getPricegroups())
					{
						foreach ($pgs as $p)
						{
							if (empty($p->getPricegroup()->image))
							{
								$image = JHTML::image(
									$imageFolder . $settings->get('signup_webform_img', 'form_icon.gif'), JText::_($p->getPricegroup()->name),
									'class="registration-icon"'
								);
							}
							else
							{
								$image = JHTML::image(
									JURI::base() . $p->getPricegroup()->image, JText::_($p->getPricegroup()->name),
									'class="registration-icon"'
								);
							}

							$url = RedeventHelperRoute::getSignupRoute('webform', $this->getEvent()->getSlug(), $this->getSlug(), $p->getSlug());
							$icons[] = RHtml::tooltip(
								JText::_($p->getPricegroup()->name),
								JText::_('LIB_REDEVENT_REGISTRATION_ICONS_TOOLTIP_TITLE'), null, $image, $url
							);
						}
					}
					else
					{
						$image = JHTML::image(
							$imageFolder . $settings->get('signup_webform_img', 'form_icon.gif'),
							JText::_($settings->get('signup_webform_text')), 'class="registration-icon"'
						);
						$url = RedeventHelperRoute::getSignupRoute('webform', $this->getEvent()->getSlug(), $this->getSlug());
						$icons[] = RHtml::tooltip(
							'',
							JText::_('LIB_REDEVENT_REGISTRATION_ICONS_TOOLTIP_TITLE'), null, $image, $url
						);
					}
					break;

				case 'formaloffer':
					$image = JHTML::image(
						$imageFolder . $settings->get('signup_formal_offer_img', 'formal_icon.gif'),
						JText::_($settings->get('signup_formal_offer_text')), 'class="registration-icon"'
					);
					$url = RedeventHelperRoute::getSignupRoute('formaloffer', $this->getEvent()->getSlug(), $this->getSlug());
					$icons[] = RHtml::tooltip(
						'',
						JText::_('LIB_REDEVENT_REGISTRATION_ICONS_TOOLTIP_TITLE'), null, $image, $url
					);
					break;
			}
		}

		return $icons;
	}

	/**
	 * Get unix start date/time from db
	 *
	 * @return string
	 */
	public function getUnixStart()
	{
		$item = $this->getItem();

		if (!RedeventHelperDate::isValidDate($item->dates))
		{
			return null;
		}

		return strtotime($item->dates . ($this->isAllDay() ? '' : ' ' . $item->times));
	}

	/**
	 * Get unix start date/time from db
	 *
	 * @return string
	 */
	public function getUnixEnd()
	{
		$item = $this->getItem();

		if (!RedeventHelperDate::isValidDate($item->enddates))
		{
			return null;
		}

		return strtotime($item->enddates . ($this->isAllDay() ? '' : ' ' . $item->endtimes));
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
	 * Return true if it's a full day session
	 *
	 * @return bool
	 */
	public function isAllDay()
	{
		return $this->getItem(true)->allday > 0;
	}

	/**
	 * Check if session is full
	 *
	 * @return boolean
	 */
	public function isFull()
	{
		$item = $this->getItem();

		// Check the max registrations and waiting list
		if ($item->maxattendees)
		{
			if (!$attendees = $this->getAttendees())
			{
				return false;
			}

			$registered = 0;
			$waiting = 0;

			foreach ($attendees as $attendee)
			{
				if (!$attendee->isConfirmed())
				{
					continue;
				}

				if ($attendee->isWaiting())
				{
					$waiting++;
				}
				else
				{
					$registered++;
				}
			}

			if ($item->maxattendees <= $registered
				&& $item->maxwaitinglist <= $waiting)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Return true if it's an open date
	 *
	 * @return bool
	 */
	public function isOpenDate()
	{
		return !RedeventHelperDate::isValidDate($this->getItem(true)->dates);
	}

	/**
	 * Is this an upcoming event
	 *
	 * @return bool
	 */
	public function isUpcoming()
	{
		$item = $this->getItem(true);

		if (!RedeventHelperDate::isValidDate($item->dates))
		{
			// Open date
			return false;
		}

		return $this->getUnixStart() > time();
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
