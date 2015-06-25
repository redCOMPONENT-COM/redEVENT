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
 * @since    0.9
 */
class RedeventHelperRoute
{
	/**
	 * return link to details view of specified event
	 *
	 * @param   int     $id    event id
	 * @param   int     $xref  session id
	 * @param   string  $task  task
	 *
	 * @return url
	 */
	public static function getDetailsRoute($id = 0, $xref = 0, $task = null)
	{
		$parts = array("option" => "com_redevent",
			"view"   => "details"
		);

		if ($id)
		{
			$parts['id'] = $id;
		}

		if ($xref)
		{
			$parts['xref'] = $xref;
		}

		if ($task)
		{
			$parts['task'] = $task;
		}

		return self::buildUrl($parts);
	}

	/**
	 * returns link to moreinfo view
	 *
	 * @param   int    $xref     int or slug
	 * @param   array  $options  options array
	 *
	 * @return string url
	 */
	public static function getMoreInfoRoute($xref, $options = null)
	{
		$parts = array( "option" => "com_redevent",
			"view"   => "moreinfo",
			"xref"   => $xref,
		);

		if ($options)
		{
			$parts = array_merge($parts, $options);
		}

		return self::buildUrl($parts);
	}

	/**
	 * return link to day view
	 *
	 * @param   mixed  $id  date
	 *
	 * @return url
	 */
	public static function getDayRoute($id = 0)
	{
		$parts = array(
			"option" => "com_redevent",
			"view"   => "day",
			"id"     => $id
		);

		return self::buildUrl($parts);
	}

	/**
	 * returns link to Venue Events view
	 *
	 * @param   int     $id      int or slug
	 * @param   string  $task    task
	 * @param   string  $layout  layout
	 *
	 * @return string url
	 */
	public static function getVenueEventsRoute($id, $task = null, $layout = null)
	{
		$parts = array(
			"option" => "com_redevent",
			"view"   => "venueevents",
			"id"     => $id
		);

		if ($task)
		{
			$parts['task'] = $task;
		}

		if ($layout)
		{
			$parts['layout'] = $layout;
		}

		return self::buildUrl($parts);
	}

	/**
	 * Route to venue category
	 *
	 * @param   int  $id  venue category id
	 *
	 * @return string
	 */
	public static function getVenueCategoryRoute($id)
	{
		$parts = array(
			"option" => "com_redevent",
			"view"   => "venuecategory",
			"id"     => $id
		);

		return self::buildUrl($parts);
	}

	/**
	 * getUpcomingVenueEventsRoute
	 *
	 * @param   int     $id    venue id
	 * @param   string  $task  task
	 *
	 * @return string
	 */
	public static function getUpcomingVenueEventsRoute($id, $task = null)
	{
		$parts = array(
			"option" => "com_redevent",
			"view"   => "upcomingvenueevents",
			"id"     => $id
		);

		if ($task)
		{
			$parts['task'] = $task;
		}

		return self::buildUrl($parts);
	}

	/**
	 * Route to venues view
	 *
	 * @return string
	 */
	public static function getVenuesRoute()
	{
		$parts = array( "option" => "com_redevent",
			"view"   => "venues");

		return self::buildUrl($parts);
	}

	/**
	 * getCategoryEventsRoute
	 *
	 * @param   int     $id      category id
	 * @param   string  $task    task
	 * @param   string  $layout  layout
	 *
	 * @return string
	 */
	public static function getCategoryEventsRoute($id, $task = null, $layout = null)
	{
		$parts = array(
			"option" => "com_redevent",
			"view"   => "categoryevents",
			"id"     => $id
		);

		if ($task)
		{
			$parts['task'] = $task;
		}

		if ($layout)
		{
			$parts['layout'] = $layout;
		}

		return self::buildUrl($parts);
	}

	/**
	 * return route to categories view
	 *
	 * @param   int     $id    top category id, 0 or null for all categories
	 * @param   string  $task  task
	 *
	 * @return string
	 */
	public static function getCategoriesRoute($id = null, $task = null)
	{
		$parts = array(
			"option" => "com_redevent",
			"view"   => "categories"
		);

		if ($id)
		{
			$parts['id'] = $id;
		}

		if ($task)
		{
			$parts['task'] = $task;
		}

		return self::buildUrl($parts);
	}

	/**
	 * return route to categories view
	 *
	 * @param   int     $id    top category id, 0 or null for all categories
	 * @param   string  $task  task
	 *
	 * @return string
	 */
	public static function getCategoriesDetailedRoute($id = null, $task = null)
	{
		$parts = array(
			"option" => "com_redevent",
			"view"   => "categoriesdetailed"
		);

		if ($id)
		{
			$parts['id'] = $id;
		}

		if ($task)
		{
			$parts['task'] = $task;
		}

		return self::buildUrl($parts);
	}

	/**
	 * return route to simple list view
	 *
	 * @param   string  $task    task
	 * @param   string  $layout  layout
	 *
	 * @return string
	 */
	public static function getSimpleListRoute($task = null, $layout = null)
	{
		$parts = array(
			"option" => "com_redevent",
			"view"   => "simplelist"
		);

		if ($task)
		{
			$parts['task'] = $task;
		}

		if ($layout)
		{
			$parts['layout'] = $layout;
		}

		return self::buildUrl($parts);
	}

	/**
	 * return route to simple list view
	 *
	 * @return string
	 */
	public static function getArchiveRoute()
	{
		$parts = array(
			"option" => "com_redevent",
			"view"   => "archive"
		);

		return self::buildUrl($parts);
	}

	/**
	 * return route to featured sessions view
	 *
	 * @param   string  $task    task
	 * @param   string  $layout  layout
	 *
	 * @return string
	 */
	public static function getFeaturedRoute($task = null, $layout = null)
	{
		$parts = array(
			"option" => "com_redevent",
			"view"   => "featured"
		);

		if ($task)
		{
			$parts['task'] = $task;
		}

		if ($layout)
		{
			$parts['layout'] = $layout;
		}

		return self::buildUrl($parts);
	}

	/**
	 * getSignupRoute
	 *
	 * @param   string  $type               signup type
	 * @param   int     $id                 event id
	 * @param   int     $xref               session id
	 * @param   int     $sessionpricegroup  session group id
	 *
	 * @return string
	 */
	public static function getSignupRoute($type, $id, $xref, $sessionpricegroup = null)
	{
		$parts = array(
			"option" => "com_redevent",
			"view"   => "signup",
			"subtype"   => $type,
			"task"   => "signup",
			"id"   => $id,
			"xref"   => $xref
		);

		if ($sessionpricegroup)
		{
			$parts['pg'] = $sessionpricegroup;
		}

		return self::buildUrl($parts);
	}

	/**
	 * route
	 *
	 * @return string
	 */
	public static function getMyeventsRoute()
	{
		$parts = array(
			"option" => "com_redevent",
			"view"   => "myevents",
			"controller" => "myevents"
		);

		return self::buildUrl($parts);
	}

	/**
	 * route
	 *
	 * @return string
	 */
	public static function getSearchRoute()
	{
		$parts = array(
			"option" => "com_redevent",
			"view"   => "search"
		);

		return self::buildUrl($parts);
	}

	/**
	 * edit route
	 *
	 * @param   int  $id    event id
	 * @param   int  $xref  session id
	 *
	 * @return string
	 */
	public static function getEditEventRoute($id = null, $xref = 0)
	{
		$parts = array(
			"option" => "com_redevent",
			"task"   => "editevent.edit"
		);

		if ($id)
		{
			$parts['e_id'] = $id;
		}

		if ($xref)
		{
			$parts['s_id'] = $xref;
		}

		return self::buildUrl($parts);
	}

	/**
	 * edit route
	 *
	 * @param   int  $id    event id
	 * @param   int  $xref  session id
	 *
	 * @return string
	 */
	public static function getEditXrefRoute($id = null, $xref = 0)
	{
		$parts = array(
			"option" => "com_redevent",
			"task"   => "editsession.edit"
		);

		if (!empty($id))
		{
			$parts['e_id'] = $id;
		}

		if ($xref)
		{
			$parts['s_id'] = $xref;
		}

		return self::buildUrl($parts);
	}

	/**
	 * edit venue route
	 *
	 * @param   int  $id  venue id
	 *
	 * @return string
	 */
	public static function getEditVenueRoute($id = null)
	{
		$parts = array(
			"option" => "com_redevent",
			"task"   => "editvenue.edit"
		);

		if (!empty($id))
		{
			$parts['id'] = $id;
		}

		return self::buildUrl($parts);
	}

	/**
	 * Get route
	 *
	 * @param   int     $xref        session id
	 * @param   string  $task        task
	 * @param   string  $submit_key  submit_key
	 *
	 * @return string
	 */
	public static function getRegistrationRoute($xref, $task, $submit_key = null)
	{
		$parts = array(
			"option" => "com_redevent",
			"xref" => $xref,
			"task"   => $task
		);

		if (!empty($submit_key))
		{
			$parts['submit_key'] = $submit_key;
		}

		if (JLanguageMultilang::isEnabled())
		{
			$db		= JFactory::getDBO();
			$query	= $db->getQuery(true);
			$query->select('a.sef AS sef');
			$query->select('a.lang_code AS lang_code');
			$query->from('#__redevent_event_venue_xref AS x');
			$query->join('INNER', '#__redevent_events AS e ON e.id = x.eventid');
			$query->join('INNER', '#__languages AS a ON a.lang_code = e.language');
			$query->where('x.id = ' . (int) $xref);

			$db->setQuery($query);

			if ($lang = $db->loadObject())
			{
				$parts['lang'] = $lang->sef;
			}
		}

		return self::buildUrl($parts);
	}

	/**
	 * Get route
	 *
	 * @param   int     $xref  session id
	 * @param   string  $task  task
	 *
	 * @return string
	 */
	public static function getManageAttendees($xref, $task = 'registration.manageattendees')
	{
		$parts = array(
			"option" => "com_redevent",
			"view"   => 'attendees',
			"xref"   => $xref,
		);

		if (!empty($task))
		{
			$parts['task'] = $task;
		}

		return self::buildUrl($parts);
	}

	/**
	 * get route
	 *
	 * @param   string  $week  week id
	 *
	 * @return string
	 */
	public static function getWeekRoute($week = null)
	{
		$parts = array(
			"option" => "com_redevent",
			"view"   => 'week'
		);

		if ($week && preg_match('/^([0-9]{4})([0-9]{2})$/', $week))
		{
			$parts['week'] = $week;
		}

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
			$params = JComponentHelper::getParams('com_redevent');

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
		$component = JComponentHelper::getComponent('com_redevent');
		$menus	= $app->getMenu('site');
		$items	= $menus->getItems('component_id', $component->id);
		$user 	= JFactory::getUser();

		$view = isset($query['view']) ? $query['view'] : null;

		if (!$view && isset($query['controller']) && $query['controller'] == 'registration')
		{
			$view = 'details';
		}

		if ($items)
		{
			foreach ($items as $item)
			{
				if ($view && (@$item->query['view'] == $view))
				{
					switch ($view)
					{
						case 'details':
							if (isset($query['xref']) && (int) $query['xref'] == (int) @$item->query['xref'])
							{
								return $item;
							}
							// Needs a second round to check just for 'id'
							break;
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

		if ($active && $active->component == 'com_redevent')
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
