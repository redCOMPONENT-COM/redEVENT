<?php
/**
 * @version 0.9 $Id$
 * @package Joomla
 * @subpackage RedEvent
 * @copyright (C) 2005 - 2008 Christoph Lukes
 * @license GNU/GPL, see LICENCE.php
 * RedEvent is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * RedEvent is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with RedEvent; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

require_once (JPATH_SITE.DS.'components'.DS.'com_redevent'.DS.'helpers'.DS.'route.php');

/**
 * RedEvent Module helper
 *
 * @package Joomla
 * @subpackage RedEvent Module
 * @since		0.9
 */
class modRedEventAllEventsHelper
{

	/**
	 * Method to get the events
	 *
	 * @access public
	 * @return array
	 */
	function getList(&$params)
	{
		$mainframe = &JFactory::getApplication();

		$db			=& JFactory::getDBO();
		$user		=& JFactory::getUser();
		$user_gid	= (int) max($user->getAuthorisedViewLevels());

		$where = ' WHERE a.published = 1 ';
		$order = ' ORDER BY a.title ASC ';

		$catid 	= trim( $params->get('catid') );
		$venid 	= trim( $params->get('venid') );

		if ($catid)
		{
			$ids = explode( ',', $catid );
			JArrayHelper::toInteger( $ids );
			$categories = ' AND c.id IN (' . implode( ',', $ids ) . ')';
		}
		if ($venid)
		{
			$ids = explode( ',', $venid );
			JArrayHelper::toInteger( $ids );
			$venues = ' AND l.id IN (' . implode( ',', $ids ) . ')';
		}

		$query = 'SELECT a.*, x.id AS xref, x.dates, x.enddates, x.times, x.endtimes, l.venue, l.city, l.url ,'
        . ' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug, '
		    . ' CASE WHEN CHAR_LENGTH(l.alias) THEN CONCAT_WS(\':\', l.id, l.alias) ELSE l.id END as venueslug '
				. ' FROM #__redevent_event_venue_xref AS x'
				. ' LEFT JOIN #__redevent_events AS a ON a.id = x.eventid'
				. ' LEFT JOIN #__redevent_venues AS l ON l.id = x.venueid'
        . ' LEFT JOIN #__redevent_event_category_xref AS xcat ON xcat.event_id = a.id'
        . ' LEFT JOIN #__redevent_categories AS c ON c.id = xcat.category_id'
				. $where
				.' AND c.access <= '.$user_gid
				.($catid ? $categories : '')
				.($venid ? $venues : '')
				. ' GROUP BY a.id '
				. $order
//				.' LIMIT '.(int)$params->get( 'count', '2' )
				;

		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$i		= 0;
		$lists	= array();
		$title_length = $params->get('cuttitle', '18');
		foreach ( $rows as $k => $row )
		{
			//cut title
			$length = strlen(htmlspecialchars( $row->title ));
			if ($title_length && $length > $title_length) {
				$rows[$k]->title_short = '<span class="hasTip" title="'.$row->title.'">'.htmlspecialchars(substr($row->title, 0, $title_length).'...', ENT_COMPAT, 'UTF-8').'</span>';
			}
			else {
				$rows[$k]->title_short = htmlspecialchars($row->title, ENT_COMPAT, 'UTF-8');
			}      
			$rows[$k]->link		= JRoute::_(RedeventHelperRoute::getDetailsRoute($row->slug));
			$rows[$k]->text		= $rows[$k]->title_short;
		}

		return $rows;
	}

	/**
	 * Method to get a valid url
	 *
	 * @access public
	 * @return string
	 */
	function _format_url($url)
	{
		if(!empty($url) && strtolower(substr($url, 0, 7)) != "http://") {
        	$url = 'http://'.$url;
        }
		return $url;
	}
}