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
	private $user;

	private $session;

	private $result;

	/**
	 * Can the user register
	 *
	 * @param   int  $xref    session id
	 * @param   int  $userId  id of user trying to register
	 *
	 * @return object object with properties canregister / status / error
	 */
	public function canRegister($xref, $userId = 0)
	{
		$this->setSession($xref);
		$this->setUser($userId);
		$this->initResult();

		if (!($this->session->published === 1))
		{
			$this->setResultError(JText::_('COM_REDEVENT_REGISTRATION_SESSION_NOT_PUBLISHED'), 'isover');

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

		if ($this->sessionIsFull())
		{
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
		$this->user = JFactory::getUser($userId);

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
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('x.id AS xref, x.published')
			->select('x.dates, x.times, x.enddates, x.endtimes, x.maxattendees, x.maxwaitinglist, x.registrationend')
			->select('e.registra, e.max_multi_signup')
			->from('#__redevent_event_venue_xref AS x')
			->join('INNER', '#__redevent_events AS e ON x.eventid = e.id')
			->where('x.id=' . $db->Quote($xref));

		$db->setQuery($query);
		$this->session = $db->loadObject();

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
		$now = JFactory::getDate();
		$now->setOffset($app->getCfg('offset'));
		$now_unix = $now->toUnix('true');

		if (RedeventHelper::isValidDate($this->session->registrationend)
			&& strtotime($this->session->registrationend) < $now_unix)
		{
			$this->setResultError(JText::_('COM_REDEVENT_REGISTRATION_IS_OVER'), 'isover');

			return true;
		}
		elseif (RedeventHelper::isValidDate($this->session->dates)
			&& strtotime($this->session->dates . ' ' . $this->session->times) < $now_unix)
		{
			$this->setResultError(JText::_('COM_REDEVENT_REGISTRATION_IS_OVER'), 'isover');

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
		if (!$this->session->registra)
		{
			$this->setResultError(JText::_('COM_REDEVENT_NO_REGISTRATION_FOR_THIS_EVENT'), 'noregistration');

			return true;
		}

		return false;
	}

	/**
	 * Check if session is full
	 *
	 * @return boolean
	 */
	private function sessionIsFull()
	{
		// Check the max registrations and waiting list
		if ($this->session->maxattendees)
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('waitinglist, COUNT(id) AS total')
				->from('#__redevent_register')
				->where('xref = ' . $this->session->xref)
				->where('confirmed = 1')
				->where('cancelled = 0')
				->group('waitinglist');

			$db->setQuery($query);
			$res = $db->loadObjectList('waitinglist');

			// Returned index waitinglist will be 0 for registered, 1 for waiting
			$this->session->registered = (isset($res[0]) ? $res[0]->total : 0);
			$this->session->waiting = (isset($res[1]) ? $res[1]->total : 0);

			if ($this->session->maxattendees <= $this->session->registered
				&& $this->session->maxwaitinglist <= $this->session->waiting)
			{
				$this->setResultError(JText::_('COM_REDEVENT_EVENT_FULL'), 'isfull');

				return true;
			}
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
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('COUNT(r.id) AS total');
			$query->from('#__redevent_register AS r');
			$query->where('r.xref = ' . $this->session->xref);
			$query->where('r.confirmed = 0');
			$query->where('r.uid = ' . $this->user->get('id'));

			$db->setQuery($query);
			$res = $db->loadResult();

			if ($res)
			{
				$this->setResultError(JText::_('COM_REDEVENT_REGISTRATION_NOT_ALLOWED_PENDING_UNCONFIRM_REGISTRATION'), 'haspending');

				return true;
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

		$app = JFactory::getApplication();
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('COUNT(r.id) AS total');
		$query->from('#__redevent_register AS r');
		$query->where('r.xref = ' . $this->session->xref);
		$query->where('r.confirmed = 1');
		$query->where('r.cancelled = 0');
		$query->where('r.uid = ' . $this->user->get('id'));

		$db->setQuery($query);
		$this->session->userregistered = $db->loadResult();

		if ($this->session->userregistered >= ($this->session->max_multi_signup ? $this->session->max_multi_signup : 1))
		{
			$this->setResultError(JText::_('COM_REDEVENT_USER_MAX_REGISTRATION_REACHED'), 'usermax');

			return true;
		}
	}
}
