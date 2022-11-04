<?php
/**
 * @package    Redevent.Library
 * @copyright  Copyright (C) 2008 - 2020 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Class for can register evaluation
 *
 * @package  Redevent.Library
 * @since    2.5
 */
class RedeventRegistrationCanregister
{
	const ERROR_IS_OVER = 'isover';
	const ERROR_IS_FULL = 'isfull';
	const ERROR_NO_REGISTRATION = 'noregistration';
	const ERROR_HAS_PENDING = 'haspending';
	const ERROR_USER_MAX = 'usermax';
	const ERROR_NO_PRICE_AVAILABLE = 'nopriceavailable';

	/**
	 * @var JUser
	 */
	private $user;

	/**
	 * @var RedeventEntitySession
	 */
	private $session;

	/**
	 * @var object
	 */
	private $result;

	/**
	 * Constructor
	 *
	 * @param   int  $xref  session id
	 */
	public function __construct($xref)
	{
		$this->setSession($xref);
		$this->initResult();
	}

	/**
	 * Can the user register
	 *
	 * @param   int  $userId  id of user trying to register
	 *
	 * @return object object with properties canregister / status / error
	 */
	public function canRegister($userId = 0)
	{
		$this->setUser($userId);

		if (!($this->session->published == 1))
		{
			$this->setResultError(JText::_('COM_REDEVENT_REGISTRATION_SESSION_NOT_PUBLISHED'), static::ERROR_IS_OVER);

			return $this->result;
		}

		if ($this->isRegistrationDisabled())
		{
			return $this->result;
		}

		if ($this->isRegistrationOver())
		{
			return $this->result;
		}

		if ($this->session->isFull())
		{
			$this->setResultError(JText::_('COM_REDEVENT_EVENT_FULL'), static::ERROR_IS_FULL);

			return $this->result;
		}

		if ($this->userHasPendingRegistration())
		{
			return $this->result;
		}

		if ($this->userReachedMaxRegistrations())
		{
			return $this->result;
		}

		if (!$this->checkPrices())
		{
			return $this->result;
		}

		return $this->result;
	}

	/**
	 * Set user
	 *
	 * @param   int  $userId  user id
	 *
	 * @return JUser
	 */
	private function setUser($userId)
	{
		$this->user = JUser::getInstance($userId);

		return $this->user;
	}

	/**
	 * Set session
	 *
	 * @param   int  $xref  session id
	 *
	 * @return mixed
	 */
	private function setSession($xref)
	{
		$this->session = RedeventEntitySession::load($xref);

		return $this->session;
	}

	/**
	 * Init result object
	 *
	 * @return void
	 */
	private function initResult()
	{
		$result = new stdclass;
		$result->canregister = 1;
		$result->status = null;
		$result->error = null;
		$result->icon = null;

		$this->result = $result;
	}

	/**
	 * Set the result as an error
	 *
	 * @param   string  $status  status to be displayed
	 * @param   string  $error   short error code
	 *
	 * @return object
	 */
	private function setResultError($status, $error)
	{
		$this->result->canregister = 0;
		$this->result->status = $status;
		$this->result->error = $error;

		$imgpath = 'media/com_redevent/images/' . $error . '.png';
		$this->result->icon = JHTML::_('image', JURI::base() . $imgpath, $status, array('class' => 'hasTooltip', 'title' => $status));

		return $this->result;
	}

	/**
	 * Is registration over ?
	 *
	 * @return boolean
	 */
	private function isRegistrationOver()
	{
		$app = JFactory::getApplication();

		// We need to take into account the server offset into account for the registration dates
		$now = JFactory::getDate('now', $app->getCfg('offset'));
		$now_unix = $now->toUnix('true');

		if (RedeventHelperDate::isValidDate($this->session->registrationend))
		{
			$registrationEnd = JFactory::getDate($this->session->registrationend, new DateTimeZone("UTC"));

			if ($registrationEnd < $now)
			{
				$this->setResultError(JText::_('COM_REDEVENT_REGISTRATION_IS_OVER'), static::ERROR_IS_OVER);

				return true;
			}
		}
		elseif (RedeventHelperDate::isValidDate($this->session->dates))
		{
			if (strtotime($this->session->dates . ' ' . $this->session->times) < $now_unix)
			{
				$this->setResultError(JText::_('COM_REDEVENT_REGISTRATION_IS_OVER'), static::ERROR_IS_OVER);

				return true;
			}
		}

		return false;
	}

	/**
	 * Is registration disabled ?
	 *
	 * @return boolean
	 */
	private function isRegistrationDisabled()
	{
		if (!$this->session->getEvent()->registra)
		{
			$this->setResultError(JText::_('COM_REDEVENT_NO_REGISTRATION_FOR_THIS_EVENT'), static::ERROR_NO_REGISTRATION);

			return true;
		}

		return false;
	}

	/**
	 * check if the user has pending unconfirm registration for the session
	 *
	 * @return boolean
	 */
	private function userHasPendingRegistration()
	{
		// Check if the user has pending unconfirm registration for the session
		if ($this->user->get('id'))
		{
			if (!$attendees = $this->session->getAttendees())
			{
				return false;
			}

			foreach ($attendees as $attendee)
			{
				if ($attendee->uid == $this->user->get('id') && $attendee->confirmed == 0 && $attendee->cancelled == 0)
				{
					$this->setResultError(
						JText::_('COM_REDEVENT_REGISTRATION_NOT_ALLOWED_PENDING_UNCONFIRM_REGISTRATION'), static::ERROR_HAS_PENDING
					);

					return true;
				}
			}
		}

		return false;
	}

	/**
	 * User Reached Max Registrations ?
	 *
	 * @return boolean
	 */
	private function userReachedMaxRegistrations()
	{
		if (!$this->user->get('id'))
		{
			return false;
		}

		if (!$this->session->getUserNumberOfSignupLeft($this->user->get('id')))
		{
			$this->setResultError(JText::_('COM_REDEVENT_USER_MAX_REGISTRATION_REACHED'), static::ERROR_USER_MAX);

			return true;
		}

		return false;
	}

	/**
	 * Check if there are available prices for user, or free for all
	 *
	 * @return boolean true if allowed
	 */
	private function checkPrices()
	{
		$hasPrices = $this->session->getActivePricegroups(false);

		if (empty($hasPrices))
		{
			// Session is free for all
			return true;
		}

		$availablePrices = $this->session->getActivePricegroups(true, $this->user);

		if (!empty($availablePrices))
		{
			return true;
		}

		$this->setResultError(
			$this->user->guest
				? JText::_('COM_REDEVENT_REGISTRATION_NO_ALLOWED_PRICE_LOGIN_FIRST')
				: JText::_('COM_REDEVENT_REGISTRATION_NO_ALLOWED_PRICE'),
			static::ERROR_NO_PRICE_AVAILABLE
		);

		return false;
	}
}
