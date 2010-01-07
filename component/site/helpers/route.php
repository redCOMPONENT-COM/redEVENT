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
	function getDetailsRoute($id = 0, $xref = 0)
	{
		$parts = array( "option" => "com_redevent",
		                "view"   => "details" );
		if ($id) {
			$parts['id'] = $id;
		}
		if ($xref) {
			$parts['xref'] = $xref;
		}
		return RedEventHelperRoute::buildUrl( $parts );
	}
	
	/**
	 * return link to day view
	 * @param mixed date
	 * @return url
	 */
	function getDayRoute($id = 0)
	{
		$parts = array( "option" => "com_redevent",
		                "view"   => "day",
		                "id"     => $id );
		return RedEventHelperRoute::buildUrl( $parts );
	}
	
	
	function getVenueEventsRoute($id)
	{
		$parts = array( "option" => "com_redevent",
		                "view"   => "venueevents",
		                "id"     => $id );
		return RedEventHelperRoute::buildUrl( $parts );
	}

	function getCategoryEventsRoute($id)
	{
		$parts = array( "option" => "com_redevent",
		                "view"   => "categoryevents",
		                "id"     => $id );
		return RedEventHelperRoute::buildUrl( $parts );
	}
	
	function buildUrl($parts)
	{		
		if($item = RedEventHelperRoute::_findItem($parts)) {
			$parts['Itemid'] = $item->id;
		};
		
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
	function _findItem($query)
	{
		$component =& JComponentHelper::getComponent('com_redevent');
		$menus	= & JSite::getMenu();
		$items	= $menus->getItems('componentid', $component->id);
		$user 	= & JFactory::getUser();
		$access = (int)$user->get('aid');
		
		if ($items) 
		{
			foreach($items as $item)
			{	
				if ((@$item->query['view'] == $query['view']) && ($item->published == 1) && ($item->access <= $access)) 
				{					
					switch ($query['view'])
					{
						case 'details':
							if (isset($query['xref']) && (int) $query['xref'] == (int) @$item->query['xref']) {
								return $item;
							}
							// needs a second round to check just for 'id'
							break;
						default:
							if (isset($query['id']) && (int) @$item->query['id'] == (int) @$query['id']) {
								return $item;
							}
					}
				}
			}

			// second round for view with optional params
			foreach($items as $item)
			{	
				if ((@$item->query['view'] == $query['view']) && ($item->published == 1) && ($item->access <= $access)) 
				{					
					switch ($query['view'])
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
			
			//still no menuitem exists -> return first possible match (any link to the component)
			foreach($items as $item)
			{
				if ($item->published == 1 && $item->access <= $access) {
					return $item;
				}
			}
		}

		return false;
	}
}
?>