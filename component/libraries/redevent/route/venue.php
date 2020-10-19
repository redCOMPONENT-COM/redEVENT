<?php
/**
 * @package    Redevent.Library
 * @copyright  Copyright (C) 2008 - 2020 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevent Component Route Helper for venueevents view
 *
 * @package  Redevent.Library
 * @since    3.0
 */
class RedeventRouteVenue
{
	/**
	 * Component menu items
	 *
	 * @var  array
	 */
	protected $menuItems;

	/**
	 * @var array  needles for association
	 */
	protected $needles;

	/**
	 * Constructor
	 *
	 * @param   array  $menuItems  menu items
	 * @param   array  $needles    search parts
	 *
	 * @throws RuntimeException
	 */
	public function __construct($menuItems, $needles)
	{
		if (empty($needles['id']))
		{
			throw new RuntimeException('Details router: missing venue id');
		}

		$this->menuItems = $menuItems;
		$this->needles = $needles;
	}

	/**
	 * Get associated menu item
	 *
	 * @return object
	 */
	public function getItem()
	{
		if ($item = $this->matchVenueevents())
		{
			return $item;
		}

		if ($item = $this->getVenueDefault())
		{
			return $item;
		}

		if ($item = $this->matchCategory())
		{
			return $item;
		}

		return false;
	}

	/**
	 * Get default item from settings
	 *
	 * @return object
	 */
	private function getVenueDefault()
	{
		$params = JComponentHelper::getParams('com_redevent');
		$default = (int) $params->get('default_venue_itemid');

		if (!$default)
		{
			return false;
		}

		$menus = JFactory::getApplication()->getMenu('site');

		foreach ($menus->getItems(array(), array()) as $item)
		{
			if ($item->id == $default)
			{
				return $item;
			}
		}

		return false;
	}

	/**
	 * Get associated event
	 *
	 * @return RedeventEntityVenue
	 */
	private function getVenue()
	{
		return RedeventEntityVenue::load($this->needles['id']);
	}

	/**
	 * try to match event
	 *
	 * @return object
	 */
	private function matchCategory()
	{
		$categories = (array) $this->getVenue()->categories;

		foreach ($this->menuItems as $item)
		{
			if (@$item->query['view'] !== 'venuescategory')
			{
				continue;
			}

			if (in_array((int) $item->query['id'], $categories))
			{
				return $item;
			}
		}

		return false;
	}

	/**
	 * try to match event
	 *
	 * @return object
	 */
	private function matchVenueevents()
	{
		if (empty($this->needles['id']))
		{
			return false;
		}

		foreach ($this->menuItems as $item)
		{
			if (@$item->query['view'] !== 'venueevents')
			{
				continue;
			}

			if (!empty($item->query['id']) && (int) $item->query['id'] == (int) $this->needles['id'])
			{
				return $item;
			}
		}

		return false;
	}
}
