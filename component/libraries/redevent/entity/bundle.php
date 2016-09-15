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
class RedeventEntityBundle extends RedeventEntityBase
{
	/**
	 * @var  RedeventEntityBundleevent[]
	 */
	private $bundleEvents;

	/**
	 * Get next session
	 *
	 * @return RedeventEntitySession
	 */
	public function getNextSession()
	{
		$upcoming = $this->getUpcomingSessions();

		return $upcoming ? reset($upcoming) : false;
	}

	/**
	 * Get events
	 *
	 * @return RedeventEntityBundleevent[]
	 */
	public function getBundleevents()
	{
		if (is_null($this->bundleEvents))
		{
			$item = $this->getItem(true);

			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('*')
				->from('#__redevent_bundle_event')
				->where('bundle_id = ' . $item->id);

			$db->setQuery($query);

			if (!$res = $db->loadObjectList())
			{
				$this->bundleEvents = false;

				return false;
			}

			$this->bundleEvents = RedeventEntityBundleevent::loadArray($res);
		}

		return $this->bundleEvents;
	}

	/**
	 * Get upcoming sessions
	 *
	 * @return RedeventEntitySession
	 */
	public function getUpcomingSessions()
	{
		if (!$bundleEvents = $this->getBundleevents())
		{
			return false;
		}

		// Merge upcoming sessions from all events
		$upcoming = array();

		foreach ($bundleEvents as $bundleEvent)
		{
			foreach ($bundleEvent->getSessions() AS $session)
			{
				if ($session->isUpcoming())
				{
					$upcoming[] = $session;
				}
			}
		}

		// Sort by date
		usort(
			$upcoming,
			function($a, $b)
			{
				return $a->getUnixStart() - $b->getUnixStart();
			}
		);

		return $upcoming;
	}

	/**
	 * Get all venues
	 *
	 * @return RedeventEntityVenue[]
	 */
	public function getVenues()
	{
		if (!$bundleEvents = $this->getBundleevents())
		{
			return false;
		}

		// Merge venues from all events sessions
		$venues = array();

		foreach ($bundleEvents as $bundleEvent)
		{
			foreach ($bundleEvent->getSessions() AS $session)
			{
				if (!isset($venues[$session->getVenue()->id]))
				{
					$venues[$session->getVenue()->id] = $session->getVenue();
				}
			}
		}

		return $venues;
	}
}
