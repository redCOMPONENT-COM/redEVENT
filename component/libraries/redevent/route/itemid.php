<?php
/**
 * @package    Redevent.Library
 * @copyright  Copyright (C) 2008 - 2014 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevent Component Route Helper
 *
 * @package  Redevent.Library
 * @since    3.0
 */
class RedeventRouteItemid
{
	/**
	 * Cached instances
	 *
	 * @var  array
	 */
	protected static $instances = array();

	/**
	 * Component menu items
	 *
	 * @var  array
	 */
	protected $menuItems;

	/**
	 * Create and return a cached instance
	 *
	 * @return  RedeventRouteItemid
	 */
	public static function getInstance()
	{
		$class = get_called_class();

		if (empty(static::$instances[$class]))
		{
			static::$instances[$class] = new static;
		}

		return static::$instances[$class];
	}

	/**
	 * Determines the Itemid
	 *
	 * searches if a menuitem for this item exists
	 * if not the first match will be returned
	 *
	 * @param   array  $query  url parameters
	 *
	 * @return int Itemid
	 */
	public function getItemId($query)
	{
		$menus = JFactory::getApplication()->getMenu('site');

		if (!$items	= $this->loadMenuItems())
		{
			return false;
		}

		$view = isset($query['view']) ? $query['view'] : null;

		if (!$view && $this->isRegistration($query))
		{
			$view = 'details';
		}

		if ($view == 'details')
		{
			$helper = new RedeventRouteDetails($this->menuItems, $query);

			if ($item = $helper->getItem())
			{
				return $item;
			}
		}
		else
		{
			foreach ($items as $item)
			{
				if ($view && (@$item->query['view'] == $view))
				{
					if (!isset($query['id']) || (int) @$item->query['id'] == (int) @$query['id'])
					{
						return $item;
					}
				}
			}
		}

		if ($default = $this->getSettingsDefault())
		{
			return $default;
		}

		// Still here..
		$active = $menus->getActive();

		if ($active && $active->component == 'com_redevent')
		{
			return $active;
		}

		return null;
	}

	/**
	 * Get default item from settings
	 *
	 * @return bool
	 */
	private function getSettingsDefault()
	{
		$params = JComponentHelper::getParams('com_redevent');
		$default = (int) $params->get('default_itemid');

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
	 * Check if this is a link to registration
	 *
	 * @param   array  $query  query parts
	 *
	 * @return bool|string
	 */
	private function isRegistration($query)
	{
		return empty($query['task']) ? false : strstr($query['task'], "registration.");
	}

	/**
	 * Load menu items
	 *
	 * @return array
	 */
	private function loadMenuItems()
	{
		if (empty($this->menuItems))
		{
			$app = JFactory::getApplication();
			$component = JComponentHelper::getComponent('com_redevent');
			$menus = $app->getMenu('site');
			$this->menuItems = $menus->getItems('component_id', $component->id);
		}

		return $this->menuItems;
	}
}
