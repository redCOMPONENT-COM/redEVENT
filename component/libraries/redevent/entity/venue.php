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
 * Venue entity.
 *
 * @since  1.0
 */
class RedeventEntityVenue extends RedeventEntityBase
{
	/**
	 * @var JUser
	 */
	private $creator;

	/**
	 * @var RedeventEntitySession[]
	 */
	private $sessions;

	/**
	 * @var RedeventEntityTwigVenuecategory[]
	 */
	private $categories;

	/**
	 * Proxy item properties
	 *
	 * @param   string  $property  Property tried to access
	 *
	 * @return  mixed   $this->item->property if it exists
	 */
	public function __get($property)
	{
		if ($property == 'name' || $property == 'title')
		{
			$property = 'venue';
		}

		if ('categories' == $property)
		{
			return $this->getCategories();
		}

		return parent::__get($property);
	}

	/**
	 * Proxy item properties isset. This needs to be implemented for proper result when doing empty() check
	 *
	 * @param   string  $property  Property tried to access
	 *
	 * @return  mixed   $this->item->property if it exists
	 */
	public function __isset($property)
	{
		if ($property == 'name' || $property == 'title')
		{
			$property = 'venue';
		}

		return parent::__isset($property);
	}

	/**
	 * Get bundles that have sessions on this venue
	 *
	 * @return RedeventEntityBundle[]
	 */
	public function getBundles()
	{
		$db = JFactory::getDbo();

		// First get from 'all_dates' bundle events
		$query = $db->getQuery(true)
			->select('b.*')
			->from('#__redevent_bundle AS b')
			->join('INNER', '#__redevent_bundle_event AS be ON be.bundle_id = b.id')
			->join('INNER', '#__redevent_event_venue_xref AS x ON x.eventid = be.event_id')
			->where('x.venueid = ' . $this->id)
			->where('x.published = 1')
			->where('be.all_dates = 1')
			->where('b.published = 1');

		$db->setQuery($query);
		$res_all = $db->loadObjectList() ?: array();

		// Then from the 'selected sessions' bundle events
		$query = $db->getQuery(true)
			->select('b.*')
			->from('#__redevent_bundle AS b')
			->join('INNER', '#__redevent_bundle_event AS be ON be.bundle_id = b.id')
			->join('INNER', '#__redevent_bundle_event_session AS bes ON bes.bundle_event_id = be.id')
			->join('INNER', '#__redevent_event_venue_xref AS x ON x.id = bes.session_id')
			->where('x.venueid = ' . $this->id)
			->where('x.published = 1')
			->where('b.published = 1');

		$db->setQuery($query);
		$res_selected = $db->loadObjectList() ?: array();

		$merged = array_merge($res_all, $res_selected);

		$bundles = array();

		foreach ($merged as $row)
		{
			if (empty($bundles[$row->id]))
			{
				$bundles[$row->id] = RedeventEntityBundle::getInstance($row->id)->bind($row);
			}
		}

		return $bundles;
	}

	/**
	 * Get venue categories
	 *
	 * @return RedeventEntityVenuescategory[]
	 */
	public function getCategories()
	{
		if ($this->categories)
		{
			return $this->categories;
		}

		if (!$this->isValid())
		{
			throw new RuntimeException('Invalid venue entity');
		}

		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('c.*')
			->from('#__redevent_venues_categories AS c')
			->join('INNER', '#__redevent_venue_category_xref AS x ON x.category_id = c.id')
			->where('x.venue_id = ' . $this->id);

		$db->setQuery($query);

		if (!$res = $db->loadObjectList())
		{
			return false;
		}

		$this->categories = RedeventEntityVenuescategory::loadArray($res);

		return $this->categories;
	}
	
	/**
	 * Get events that have sessions on this venue
	 *
	 * @return RedeventEntityEvent[]
	 */
	public function getEvents()
	{
		$db = JFactory::getDbo();

		// First get from 'all_dates' bundle events
		$query = $db->getQuery(true)
			->select('DISTINCT e.*')
			->from('#__redevent_events AS e')
			->join('INNER', '#__redevent_event_venue_xref AS x ON x.eventid = e.id')
			->where('x.venueid = ' . $this->id)
			->where('x.published = 1')
			->where('e.published = 1');

		$db->setQuery($query);
		$res = $db->loadObjectList() ?: array();

		return RedeventEntityEvent::loadArray($res);
	}

	/**
	 * Get sessions on this venue
	 *
	 * @return RedeventEntitySession[]
	 */
	public function getSessions()
	{
		if (is_null($this->sessions))
		{
			$db = JFactory::getDbo();

			// First get from 'all_dates' bundle events
			$query = $db->getQuery(true)
				->select('x.*')
				->from('#__redevent_event_venue_xref AS x')
				->join('INNER', '#__redevent_events AS e ON x.eventid = e.id')
				->where('x.venueid = ' . $this->id)
				->where('x.published = 1')
				->where('e.published = 1')
				->order('x.dates ASC');

			$db->setQuery($query);
			$res = $db->loadObjectList() ?: array();

			$this->sessions = RedeventEntitySession::loadArray($res);
		}

		return $this->sessions;
	}

	/**
	 * Get upcoming sessions on this venue
	 *
	 * @return RedeventEntitySession[]
	 */
	public function getUpcomings()
	{
		return array_filter(
			$this->getSessions(),
			function($session)
			{
				return $session->isUpcoming();
			}
		);
	}

	/**
	 * Return creator
	 *
	 * @return JUser
	 */
	public function getCreator()
	{
		if (!$this->creator)
		{
			$item = $this->getItem();

			if (!empty($item))
			{
				$this->creator = JFactory::getUser($item->created_by);
			}
		}

		return $this->creator;
	}
}
