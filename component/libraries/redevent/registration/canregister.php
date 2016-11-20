<?php
/**
 * @package    Redevent.Library
 * @copyright  Copyright (C) 2008 - 2014 redCOMPONENT.com. All rights reserved.
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

	/**
	 * @var JUser
	 */
	private $user;

	/**
	 * @var RedeventEntitySession
	 */
	private $session;

	/**
	 * @var bool
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
	 * @return void;
	 */
	private function initResult()
	{
		$result = new stdclass;
		$result->canregister = 1;
		$result->status = null;
		$result->error = null;

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

		return $this->result;
	}

	/**
	 * Is registration over ?
	 *
	 * @return bool
	 */
	private function isRegistrationOver()
	{
		$app = JFactory::getApplication();

		// We need to take into account the server offset into account for the registration dates
		$now = JFactory::getDate('now', $app->getCfg('offset'));
		$now_unix = $now->toUnix('true');

		if (RedeventHelperDate::isValidDate($this->session->registrationend)
			&& strtotime($this->session->registrationend) < $now_unix)
		{
			$this->setResultError(JText::_('COM_REDEVENT_REGISTRATION_IS_OVER'), static::ERROR_IS_OVER);

			return true;
		}
		elseif (RedeventHelperDate::isValidDate($this->session->dates)
			&& strtotime($this->session->dates . ' ' . $this->session->times) < $now_unix)
		{
			$this->setResultError(JText::_('COM_REDEVENT_REGISTRATION_IS_OVER'), static::ERROR_IS_OVER);

			return true;
		}

		return false;
	}

	/**
	 * Is registration disabled ?
	 *
	 * @return bool
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
					$this->setResultError(JText::_('COM_REDEVENT_REGISTRATION_NOT_ALLOWED_PENDING_UNCONFIRM_REGISTRATION'), static::ERROR_HAS_PENDING);

					return true;
				}
			}
		}

		return false;
	}

	/**
	 * User Reached Max Registrations ?
	 *
	 * @return bool
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
	}
}
