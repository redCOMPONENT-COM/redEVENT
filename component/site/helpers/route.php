<?php
/**
 * @version 1.0 $Id$
 * @package Joomla
 * @subpackage redEVENT
 * @copyright redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license GNU/GPL, see LICENSE.php
 * redEVENT is based on EventList made by Christoph Lukes from schlu.net
 * redEVENT can be downloaded from www.redcomponent.com
 * redEVENT is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * redEVENT is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with redEVENT; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

defined('_JEXEC') or die('Restricted access');

// Component Helper
jimport('joomla.application.component.helper');

/**
 * EventList Component Route Helper
 * based on Joomla ContentHelperRoute
 *
 * @static
 * @package		Joomla
 * @subpackage	EventList
 * @since 0.9
 */
class RedeventHelperRoute
{
	/**
	 * return link to details view of specified event
	 * @param int $id
	 * @param int $xref
	 * @return url
	 */
	public static function getDetailsRoute($id = 0, $xref = 0, $task = null)
	{
		$parts = array( "option" => "com_redevent",
		                "view"   => "details" );
		if ($id) {
			$parts['id'] = $id;
		}
		if ($xref) {
			$parts['xref'] = $xref;
		}
		if ($task) {
			$parts['task'] = $task;
		}
		return self::buildUrl( $parts );
	}


	/**
	 * returns link to moreinfo view
	 *
	 * @param int or slug $xref
	 * @return string url
	 */
	public static function getMoreInfoRoute($xref, $options = null)
	{
		$parts = array( "option" => "com_redevent",
		                "view"   => "moreinfo",
		                "xref"   => $xref,
		              );
		if ($options) {
			$parts = array_merge($parts, $options);
		}
		return self::buildUrl( $parts );
	}

	/**
	 * return link to day view
	 * @param mixed date
	 * @return url
	 */
	public static function getDayRoute($id = 0)
	{
		$parts = array( "option" => "com_redevent",
		                "view"   => "day",
		                "id"     => $id );
		return self::buildUrl( $parts );
	}


	public static function getVenueEventsRoute($id, $task = null, $layout = null)
	{
		$parts = array( "option" => "com_redevent",
		                "view"   => "venueevents",
		                "id"     => $id );
		if ($task) {
			$parts['task'] = $task;
		}
		if ($layout) {
			$parts['layout'] = $layout;
		}
		return self::buildUrl( $parts );
	}

	public static function getVenueCategoryRoute($id)
	{
		$parts = array( "option" => "com_redevent",
		                "view"   => "venuecategory",
		                "id"     => $id );
		return self::buildUrl( $parts );
	}

	public static function getUpcomingVenueEventsRoute($id, $task = null)
	{
		$parts = array( "option" => "com_redevent",
		                "view"   => "upcomingvenueevents",
		                "id"     => $id );
		if ($task) {
			$parts['task'] = $task;
		}
		return self::buildUrl( $parts );
	}

	public static function getCategoryEventsRoute($id, $task = null, $layout = null)
	{
		$parts = array( "option" => "com_redevent",
		                "view"   => "categoryevents",
		                "id"     => $id );
		if ($task) {
			$parts['task'] = $task;
		}
		if ($layout) {
			$parts['layout'] = $layout;
		}
		return self::buildUrl( $parts );
	}

	/**
	 * return route to categories view
	 * @param int top category id, 0 or null for all categories
	 * @param string $task
	 * @return string
	 */
	public static function getCategoriesRoute($id = null, $task = null)
	{
		$parts = array( "option" => "com_redevent",
		                "view"   => "categories" );
		if ($id) {
			$parts['id'] = $id;
		}
		if ($task) {
			$parts['task'] = $task;
		}
		return self::buildUrl( $parts );
	}

	/**
	 * return route to categories view
	 * @param int top category id, 0 or null for all categories
	 * @param string $task
	 * @return string
	 */
	public static function getCategoriesDetailedRoute($id = null, $task = null)
	{
		$parts = array( "option" => "com_redevent",
		                "view"   => "categoriesdetailed" );
		if ($id) {
			$parts['id'] = $id;
		}
		if ($task) {
			$parts['task'] = $task;
		}
		return self::buildUrl( $parts );
	}

	/**
	 * return route to simple list view
	 * @param string $task
	 * @return string
	 */
	public static function getSimpleListRoute($task = null, $layout = null)
	{
		$parts = array( "option" => "com_redevent",
		                "view"   => "simplelist" );
		if ($task) {
			$parts['task'] = $task;
		}
		if ($layout) {
			$parts['layout'] = $layout;
		}
		return self::buildUrl( $parts );
	}

	/**
	 * return route to simple list view
	 * @param string $task
	 * @return string
	 */
	public static function getArchiveRoute()
	{
		$parts = array( "option" => "com_redevent",
		                "view"   => "archive" );
		return self::buildUrl( $parts );
	}

	/**
	 * return route to featured sessions view
	 * @param string $task
	 * @return string
	 */
	public static function getFeaturedRoute($task = null, $layout = null)
	{
		$parts = array( "option" => "com_redevent",
		                "view"   => "featured" );
		if ($task) {
			$parts['task'] = $task;
		}
		if ($layout) {
			$parts['layout'] = $layout;
		}
		return self::buildUrl( $parts );
	}

	public static function getSignupRoute($type, $id, $xref, $pricegroup = null)
	{
		$parts = array( "option" => "com_redevent",
		                "view"   => "signup",
		                "subtype"   => $type,
		                "task"   => "signup",
		                "id"   => $id,
		                "xref"   => $xref);
		if ($pricegroup) {
			$parts['pg'] = $pricegroup;
		}
		return self::buildUrl( $parts );
	}

	public static function getMyeventsRoute()
	{
		$parts = array( "option" => "com_redevent",
		                "view"   => "myevents" );
		return self::buildUrl( $parts );
	}

	public static function getSearchRoute()
	{
		$parts = array( "option" => "com_redevent",
		                "view"   => "search" );
		return self::buildUrl( $parts );
	}

	public static function getEditEventRoute($id = null, $xref = 0)
	{
		$parts = array( "option" => "com_redevent",
		                "view"   => "editevent" );
		if ($id) {
			$parts['id'] = $id;
		}
		if ($xref) {
			$parts['xref'] = $xref;
		}
		return self::buildUrl( $parts );
	}

	public static function getEditXrefRoute($id = null, $xref = 0)
	{
		$parts = array( "option" => "com_redevent",
		                "view"   => "editevent",
		                "layout" => "eventdate");
		if (!empty($id)) {
			$parts['id'] = $id;
		}
		if ($xref) {
			$parts['xref'] = $xref;
		}
		return self::buildUrl( $parts );
	}

	public static function getEditVenueRoute($id = null)
	{
		$parts = array( "option" => "com_redevent",
		                "view"   => "editvenue" );
		if (!empty($id)) {
			$parts['id'] = $id;
		}
		return self::buildUrl( $parts );
	}

	public static function getRegistrationRoute($xref, $task, $submit_key = null)
	{
		$parts = array( "option" => "com_redevent",
		                "controller" => "registration",
		                "xref" => $xref,
		                "task"   => $task, );
		if (!empty($submit_key)) {
			$parts['submit_key'] = $submit_key;
		}
		return self::buildUrl( $parts );
	}

	public static function getManageAttendees($xref, $task = 'manageattendees')
	{
		$parts = array( "option" => "com_redevent",
		                "controller" => "registration",
		                "view"   => 'attendees',
		                "xref"   => $xref,
									);
		if (!empty($task)) {
			$parts['task'] = $task;
		}
		return self::buildUrl( $parts );
	}

	public static function getWeekRoute($week)
	{
		$parts = array( "option" => "com_redevent",
		                "view"   => 'week',
		                "week"   => $week,
									);
		return self::buildUrl( $parts );
	}

	protected static function buildUrl($parts)
	{
		if($item = self::_findItem($parts)) {
			$parts['Itemid'] = $item->id;
		}
		else {
			$params = JComponentHelper::getParams('com_redevent');
			if ($params->get('default_itemid')) {
				$parts['Itemid'] = intval($params->get('default_itemid'));
			}
		}

		return 'index.php?'.JURI::buildQuery( $parts );
	}

	/**
	 * Determines the Itemid
	 *
	 * searches if a menuitem for this item exists
	 * if not the first match will be returned
	 *
	 * @param array url parameters
	 * @since 0.9
	 *
	 * @return int Itemid
	 */
	protected static function _findItem($query)
	{
		$component =& JComponentHelper::getComponent('com_redevent');
		$menus	= & JApplication::getMenu('site');
		$items	= $menus->getItems('component_id', $component->id);
		$user 	= & JFactory::getUser();

		$view = isset($query['view']) ? $query['view'] : null;
		if (!$view && isset($query['controller']) && $query['controller'] == 'registration') {
			$view = 'details';
		}

		if ($items)
		{
			foreach($items as $item)
			{
				if ($view && (@$item->query['view'] == $view))
				{
					switch ($view)
					{
						case 'details':
							if (isset($query['xref']) && (int) $query['xref'] == (int) @$item->query['xref']) {
								return $item;
							}
							// needs a second round to check just for 'id'
							break;
						default:
							if (!isset($query['id']) || (int) @$item->query['id'] == (int) @$query['id']) {
								return $item;
							}
					}
				}
			}

			// second round for view with optional params
			foreach($items as $item)
			{
				if (isset($view) && (@$item->query['view'] == $view))
				{
					switch ($view)
					{
						case 'details':
							if (isset($query['id']) && (int) $query['id'] == (int) @$item->query['id']) {
								return $item;
							}
							// needs a second round to check just for 'id'
							break;
					}
				}
			}
		}

		return false;
	}
}
