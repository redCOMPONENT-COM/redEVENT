<?php
/**
 * @package    Redevent.Library
 * @copyright  Copyright (C) 2008 - 2020 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevent Component Route Helper for details view
 *
 * @package  Redevent.Library
 * @since    3.0
 */
class RedeventRouteDetails
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
		if (empty($needles['id']) && empty($needles['xref']))
		{
			throw new RuntimeException('Details router: missing event and session ids');
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
		if ($item = $this->matchSession())
		{
			return $item;
		}

		if ($item = $this->matchEvent())
		{
			return $item;
		}

		if ($item = $this->getDetailsDefault())
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
	private function getDetailsDefault()
	{
		$params = JComponentHelper::getParams('com_redevent');
		$default = (int) $params->get('default_details_itemid');

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
	 * @return RedeventEntityEvent
	 */
	private function getEvent()
	{
		if (!empty($this->needles['id']))
		{
			$event = RedeventEntityEvent::load($this->needles['id']);
		}
		else
		{
			$event = RedeventEntitySession::getInstance($this->needles['xref'])->getEvent();
		}

		$event->load();

		return $event;
	}

	/**
	 * try to match event
	 *
	 * @return object
	 */
	private function matchCategory()
	{
		$categories = (array) $this->getEvent()->categories;

		foreach ($this->menuItems as $item)
		{
			if (@$item->query['view'] !== 'categoryevents')
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
	private function matchEvent()
	{
		if (empty($this->needles['id']))
		{
			return false;
		}

		foreach ($this->menuItems as $item)
		{
			if (@$item->query['view'] !== 'details')
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

	/**
	 * try to match session
	 *
	 * @return object
	 */
	private function matchSession()
	{
		if (empty($this->needles['xref']))
		{
			return false;
		}

		foreach ($this->menuItems as $item)
		{
			if (@$item->query['view'] !== 'details')
			{
				continue;
			}

			if (!empty($item->query['xref']) && (int) $item->query['xref'] == (int) $this->needles['xref'])
			{
				return $item;
			}
		}

		return false;
	}
}
