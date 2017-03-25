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
 * Bundle entity.
 *
 * @since  3.2.0
 */
class RedeventEntityBundleevent extends RedeventEntityBase
{
	/**
	 * @var  RedeventEntityEvent
	 */
	private $event;

	/**
	 * @var RedeventEntitySession[]
	 */
	private $sessions;

	/**
	 * Get maximum duration for event
	 *
	 * @return integer
	 */
	public function getDurationMax()
	{
		if (!$sessions = $this->getSessions())
		{
			return false;
		}

		return array_reduce(
			$sessions,
			function ($max, $session)
			{
				return max($max, $session->getDurationDays());
			}
		);
	}

	/**
	 * Get minium duration for event
	 *
	 * @return integer
	 */
	public function getDurationMin()
	{
		if (!$sessions = $this->getSessions())
		{
			return false;
		}

		return array_reduce(
			$sessions,
			function ($min, $session)
			{
				$duration = $session->getDurationDays();

				if (!$duration)
				{
					return $min;
				}

				return $min ? min($min, $duration) : $duration;
			}
		);
	}

	/**
	 * Get next session
	 *
	 * @return RedeventEntityEvent
	 */
	public function getEvent()
	{
		if (is_null($this->event))
		{
			$item = $this->getItem(true);

			if (!$this->event)
			{
				$this->event = RedeventEntityEvent::load($item->event_id);
			}
		}

		return $this->event;
	}

	/**
	 * Get next session in bundle
	 *
	 * @return RedeventEntitySession
	 */
	public function getNext()
	{
		if (!$sessions = $this->getSessions())
		{
			return false;
		}

		$upcomings = array_filter(
			$sessions,
			function ($session)
			{
				return $session->isUpcoming();
			}
		);

		if (!$upcomings)
		{
			return false;
		}

		// Order
		uasort(
			$upcomings,
			function ($a, $b)
			{
				return $a->getUnixStart() - $b->getUnixStart();
			}
		);

		return reset($upcomings);
	}

	/**
	 * Get associated sessions
	 *
	 * @return RedeventEntitySession[]
	 */
	public function getSessions()
	{
		if (is_null($this->sessions))
		{
			$item = $this->getItem(true);

			if ($item->all_dates)
			{
				$this->sessions = $this->getEvent()->getSessions(null, null, array('published' => 1));

				return $this->sessions;
			}
			else
			{
				$this->sessions = $this->getSelectedSessions();
			}
		}

		return $this->sessions;
	}

	/**
	 * Get selected sessions for event
	 *
	 * @return RedeventEntitySession[]
	 */
	private function getSelectedSessions()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('x.*')
			->from('#__redevent_event_venue_xref AS x')
			->join('INNER', '#__redevent_bundle_event_session AS bes On bes.session_id = x.id')
			->where('bes.bundle_event_id = ' . $this->id);

		$db->setQuery($query);

		if (!$res = $db->loadObjectList())
		{
			return false;
		}

		return RedeventEntitySession::loadArray($res);
	}
}
