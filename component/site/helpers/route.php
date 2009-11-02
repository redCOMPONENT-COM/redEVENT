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
	 * Determines an EventList Link
	 *
	 * @param int The id of an EventList item
	 * @param string The view
	 * @param int The xref for the details view
	 * @since 0.9
	 *
	 * @return string determined Link
	 */
	function getRoute($id, $view = 'details', $xref = null)
	{
		//Create the link
		switch ($view) 
		{
			case 'details':
				$needles = array(
					$view  => (int) $xref
				);
				$link = 'index.php?option=com_redevent&view='.$view.'&id='. $id.'&xref='. $xref;
				break;
			default:
				$needles = array(
					$view  => (int) $id
				);
				$link = 'index.php?option=com_redevent&view='.$view.'&id='. $id;
				break;
		}

		if($item = RedEventHelperRoute::_findItem($needles)) {
			$link .= '&Itemid='.$item->id;
		};

		return $link;
	}

	/**
	 * Determines the Itemid
	 *
	 * searches if a menuitem for this item exists
	 * if not the first match will be returned
	 *
	 * @param array The id and view
	 * @since 0.9
	 *
	 * @return int Itemid
	 */
	function _findItem($needles)
	{
		$component =& JComponentHelper::getComponent('com_redevent');
		$menus	= & JSite::getMenu();
		$items	= $menus->getItems('componentid', $component->id);
		$user 	= & JFactory::getUser();
		$access = (int)$user->get('aid');
		
		//Not needed currently but kept because of a possible hierarchic link structure in future
		foreach($needles as $needle => $id)
		{
			if ($items) {
				foreach($items as $item)
				{	
					if ((@$item->query['view'] == $needle) && (@$item->query['id'] == $id || @$item->query['xref'] == $id) && ($item->published == 1) && ($item->access <= $access)) {
						return $item;
					}
				}
	
				//no menuitem exists -> return first possible match
				foreach($items as $item)
				{
					if ($item->published == 1 && $item->access <= $access) {
						return $item;
					}
				}
			}
		}

		return false;
	}
}
?>