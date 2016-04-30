<?php
/**
 * @package    Redeventb2b.Library
 * @copyright  Copyright (C) 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redeventb2b Component Route Helper
 *
 * @package  Redeventb2b.Library
 * @since    3.0
 */
class Redeventb2bHelperRoute
{
	/**
	 * B2b route
	 *
	 * @return string
	 */
	public static function getFrontadminRoute()
	{
		$parts = array( "option" => "com_redeventb2b",
			"view"   => 'frontadmin'
		);

		return self::buildUrl($parts);
	}

	/**
	 * B2B login route
	 *
	 * @return string
	 */
	public static function getFrontadminloginRoute()
	{
		$parts = array( "option" => "com_redeventb2b",
			"view"   => 'frontadminlogin'
		);

		return self::buildUrl($parts);
	}

	/**
	 * build url from parts
	 *
	 * @param   array  $parts  parts
	 *
	 * @return string
	 */
	protected static function buildUrl($parts)
	{
		if ($item = self::_findItem($parts))
		{
			$parts['Itemid'] = $item->id;
		}
		else
		{
			$params = JComponentHelper::getParams('com_redeventb2b');

			if ($params->get('default_itemid'))
			{
				$parts['Itemid'] = intval($params->get('default_itemid'));
			}
		}

		// Language filter ?
		return 'index.php?' . JURI::buildQuery($parts);
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
	protected static function _findItem($query)
	{
		$app = JFactory::getApplication();
		$component = JComponentHelper::getComponent('com_redeventb2b');
		$menus	= $app->getMenu('site');
		$items	= $menus->getItems('component_id', $component->id);
		$user 	= JFactory::getUser();

		$view = isset($query['view']) ? $query['view'] : null;

		if ($items)
		{
			foreach ($items as $item)
			{
				if ($view && (@$item->query['view'] == $view))
				{
					switch ($view)
					{
						default:
							if (!isset($query['id']) || (int) @$item->query['id'] == (int) @$query['id'])
							{
								return $item;
							}
					}
				}
			}

			// Second round for view with optional params
			foreach ($items as $item)
			{
				if (isset($view) && (@$item->query['view'] == $view))
				{
					switch ($view)
					{
						case 'details':
							if (isset($query['id']) && (int) $query['id'] == (int) @$item->query['id'])
							{
								return $item;
							}
							break;
					}
				}
			}
		}

		// Still here..
		$active = $menus->getActive();

		if ($active && $active->component == 'com_redeventb2b')
		{
			return $active;
		}

		return null;
	}

	/**
	 * Add language filter
	 *
	 * @param   array  $parts  parts
	 *
	 * @return mixed
	 */
	public static function addLanguageFilter($parts)
	{
		static $filter;

		if ($filter === null)
		{
			if (!JLanguageMultilang::isEnabled())
			{
				$filter = false;
			}
		}

		if ($filter)
		{
			$parts['lang'] = $filter;
		}

		return $parts;
	}
}
