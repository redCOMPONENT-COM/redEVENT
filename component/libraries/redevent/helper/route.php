<?php
/**
 * @package    Redevent.Library
 * @copyright  Copyright (C) 2008 - 2020 redCOMPONENT.com. All rights reserved.
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
	 * return link to bundle view
	 *
	 * @param   int  $id  bundle id
	 *
	 * @return url
	 */
	public static function getBundleRoute($id)
	{
		$parts = array("option" => "com_redevent",
			"view"   => "bundle",
			"id" => $id
		);

		return self::buildUrl($parts);
	}

	/**
	 * return link to bundle view
	 *
	 * @return url
	 */
	public static function getBundlesRoute()
	{
		$parts = array("option" => "com_redevent",
			"view"   => "bundles"
		);

		return self::buildUrl($parts);
	}

	/**
	 * return link to details view of specified event
	 *
	 * @param   int  $yearId   year id
	 * @param   int  $monthId  month id
	 *
	 * @return url
	 */
	public static function getCalendarRoute($yearId = null, $monthId = nul)
	{
		$parts = array("option" => "com_redevent",
			"view"   => "calendar"
		);

		if ($yearId)
		{
			$parts['yearID'] = $yearId;
		}

		if ($monthId)
		{
			$parts['monthID'] = $monthId;
		}

		return self::buildUrl($parts);
	}

	/**
	 * return link to details view of specified event
	 *
	 * @param   int     $id    event id
	 * @param   int     $xref  session id
	 * @param   string  $task  task
	 *
	 * @return url
	 */
	public static function getDetailsRoute($id, $xref = 0, $task = null)
	{
		$parts = array("option" => "com_redevent",
			"view"   => "details"
		);

		$parts['id'] = $id;

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
	public static function getSignupRoute($type, $id, $xref = null, $sessionpricegroup = null)
	{
		$parts = array(
			"option" => "com_redevent",
			"view"   => "signup",
			"subtype"   => $type,
			"task"   => "signup",
			"id"   => $id
		);

		if ($xref)
		{
			$parts['xref'] = $xref;
		}

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
			"view"   => "editsession"
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
	 * Get task route to add session
	 *
	 * @param   int  $id  event id
	 *
	 * @return string
	 */
	public static function getAddSessionTaskRoute($id = null)
	{
		$parts = array(
			"option" => "com_redevent",
			"view"   => "editsession",
			"task"   => "editsession.add"
		);

		if (!empty($id))
		{
			$parts['e_id'] = $id;
		}

		return self::buildUrl($parts);
	}

	/**
	 * Get task route to edit session
	 *
	 * @param   int  $id         event id
	 * @param   int  $sessionId  session id
	 *
	 * @return string
	 */
	public static function getEditSessionTaskRoute($id = null, $sessionId = 0)
	{
		$parts = array(
			"option" => "com_redevent",
			"view"   => "editsession",
			"task"   => "editsession.edit"
		);

		if (RedeventHelperConfig::get('frontendsubmit_mode', 'simple') == 'simple')
		{
			$parts["layout"] = "easy";
		}

		$parts['s_id'] = $sessionId;

		if (!empty($id))
		{
			$parts['e_id'] = $id;
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
	 * @param   int     $xref        session id
	 * @param   string  $task        task
	 * @param   string  $submit_key  submit_key
	 *
	 * @return string
	 */
	public static function getRegistrationConfirmRoute($xref, $submit_key = null)
	{
		$parts = array(
			"option" => "com_redevent",
			"view"   => 'registration',
			"layout"   => 'confirmed',
			"xref" => $xref,
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
	 * @param   int     $xref        session id
	 * @param   string  $task        task
	 * @param   string  $submit_key  submit_key
	 *
	 * @return string
	 */
	public static function getRegistrationReviewRoute($xref, $submit_key = null)
	{
		$parts = array(
			"option" => "com_redevent",
			"view"   => 'registration',
			"layout"   => 'review',
			"xref" => $xref,
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
	 * @param   int     $registrationId  session id
	 * @param   string  $xref            xref
	 * @param   string  $task            task
	 *
	 * @return string
	 */
	public static function getCancelRegistrationRoute($registrationId, $xref, $task = 'registration.cancelreg')
	{
		$parts = array(
			"option" => "com_redevent",
			"task"   => $task,
			"rid"   => $registrationId,
			"xref" => $xref,
		);

		return 'index.php?' . JURI::buildQuery($parts);
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
	 * Return item id associated to a view
	 *
	 * @param   string  $viewName  view name
	 *
	 * @return integer|boolean false on failure
	 */
	public static function getViewItemId($viewName)
	{
		$parts = array(
			"option" => "com_redevent",
			"view"   => $viewName
		);

		if ($item = self::findItem($parts))
		{
			return $item->id;
		}

		return false;
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
		if ($item = self::findItem($parts))
		{
			$parts['Itemid'] = $item->id;
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
	 * @return integer Itemid
	 */
	protected static function findItem($query)
	{
		$finder = RedeventRouteItemid::getInstance();

		return $finder->getItemid($query);
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

	/**
	 * Url to attachement file
	 *
	 * @param   integer  $attachmentId  attachmentId
	 *
	 * @return string
	 */
	public static function getAttachment($attachmentId)
	{
		return 'index.php?option=com_redevent&task=attachments.getfile&format=raw&file=' . $attachmentId;
	}
}
