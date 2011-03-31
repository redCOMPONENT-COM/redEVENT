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
class modRedEventHelper
{

	/**
	 * Method to get the events
	 *
	 * @access public
	 * @return array
	 */
	function getList(&$params)
	{
		global $mainframe;

		$db			=& JFactory::getDBO();
		$user		=& JFactory::getUser();
		$user_gid	= (int) $user->get('aid');
				
		$where = array();
		
		$where[] = 'c.access <= '.$user_gid;

		$type = $params->get( 'type', '0' );
		if ($type == 0) 
		{
			$where[] = 'x.published = 1';
			$order = ' ORDER BY x.dates, x.times';
		} 
		else if ($type == 1)
		{
			$offset = (int) $params->get( 'dayoffset', '0' );
			$date = $offset ? 'now +'.$offset.' days' : 'now';
			$ref = strftime('%Y-%m-%d %H:%M', strtotime($date));
			$where[] = 'x.published = 1 AND (CASE WHEN x.times THEN CONCAT(x.dates," ",x.times) ELSE x.dates END) > '.$db->Quote($ref);
			$order = ' ORDER BY x.dates, x.times ';
		} 
		else if ($type == 2)
		{
			$where[] = 'x.published = -1';
			$order = ' ORDER BY x.dates DESC, x.times DESC';
		} 
		else if ($type == 3) 
		{
			$where[] = 'x.dates = 0';
			$order = ' ORDER BY a.title ASC';
		}

		$catid 	= trim( $params->get('catid') );
		$venid 	= trim( $params->get('venid') );

		if ($catid)
		{
			$ids = explode( ',', $catid );
			JArrayHelper::toInteger( $ids );
			$where[] = ' c.id IN (' . implode( ',', $ids ) . ')';
		}
		if ($venid)
		{
			$ids = explode( ',', $venid );
			JArrayHelper::toInteger( $ids );
			$where[] = ' l.id IN (' . implode( ',', $ids ) . ')';
		}
		
		if ($params->get('showrecurring', 1) == 0) {
			$where[] = ' r.count = 0 ';			
		}
		
		if ($params->get('showsessions', 1) == 0) {
			$groupby = ' GROUP BY a.id ';		
		}
		else {
			$groupby = ' GROUP BY x.id ';		
		}

		//get $params->get( 'count', '2' ) nr of datasets
		$query = 'SELECT a.*, x.id AS xref, x.dates, x.enddates, x.times, x.endtimes, l.venue, l.city, l.url ,'
		    . ' CONCAT_WS(",", c.image) AS categories_images,'
        . ' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug, '
		    . ' CASE WHEN CHAR_LENGTH(l.alias) THEN CONCAT_WS(\':\', l.id, l.alias) ELSE l.id END as venueslug '
				. ' FROM #__redevent_event_venue_xref AS x'
				. ' LEFT JOIN #__redevent_events AS a ON a.id = x.eventid'
				. ' LEFT JOIN #__redevent_venues AS l ON l.id = x.venueid'
        . ' LEFT JOIN #__redevent_event_category_xref AS xcat ON xcat.event_id = a.id'
        . ' LEFT JOIN #__redevent_categories AS c ON c.id = xcat.category_id'
        . ' LEFT JOIN #__redevent_repeats AS r ON r.xref_id = x.id '
				. ' WHERE '.implode(' AND ', $where)
				. $groupby
				. $order
				.' LIMIT '.(int)$params->get( 'count', '2' )
				;

		$db->setQuery($query);
		$rows = $db->loadObjectList();
		
		$i		= 0;
		$lists	= array();
		$title_length = $params->get('cuttitle', '18');
		foreach ( $rows as $k => $row )
		{
			//cut title
			$length = mb_strlen( $row->title, 'UTF-8' );
			if ($title_length && $length > $title_length) {
				$rows[$k]->title_short = mb_substr($row->title, 0, $title_length, 'UTF-8').'...';
			}
			else {
				$rows[$k]->title_short = $row->title;
			}
			// cut venue name
      $length = mb_strlen($row->venue, 'UTF-8');
      if ($title_length && $length > $title_length) {
        $rows[$k]->venue_short = mb_substr($row->venue, 0, $title_length, 'UTF-8').'...';
      }
      else {
        $rows[$k]->venue_short = $row->venue;
      }
      
			$rows[$k]->link		= JRoute::_(RedeventHelperRoute::getDetailsRoute($row->slug, $row->xref));
//			$rows[$k]->link		= JRoute::_('index.php?option=com_redevent&view=details&id='. $row->slug .'&xref='.$row->xref);
			$rows[$k]->dateinfo 	= modRedEventHelper::_builddateinfo($row, $params);
			$rows[$k]->city		= htmlspecialchars( $row->city, ENT_COMPAT, 'UTF-8' );
			$rows[$k]->venueurl 	= !empty( $row->url ) ? modRedEventHelper::_format_url($row->url) : JRoute::_(RedeventHelperRoute::getVenueEventsRoute($row->venueslug) , false);
		}

		return $rows;
	}

	/**
	 * Method to a formated and structured string of date infos
	 *
	 * @access public
	 * @return string
	 */
	function _builddateinfo($row, &$params)
	{
		if (!redEVENTHelper::isValidDate($row->dates)) {
			return JText::_('MOD_REDEVENT_REDEVENT_OPEN_DATE');
		}
		$date 		= modRedEventHelper::_format_date($row->dates, $row->times, $params->get('formatdate', '%d.%m.%Y'));
		$enddate 	= redEVENTHelper::isValidDate($row->enddates) ? modRedEventHelper::_format_date($row->enddates, $row->endtimes, $params->get('formatdate', '%d.%m.%Y')) : null;
		$time		= ($row->times && $row->times != '00:00:00') ? modRedEventHelper::_format_date($row->dates, $row->times, $params->get('formattime', '%H:%M')) : null;
		$dateinfo	= '<span class="event-start">'.$date.'</span>';

		if ( isset($enddate) && $params->get('show_enddate', 1) && $row->dates != $row->enddates) {
			$dateinfo .= ' - <span class="event-end">'.$enddate.'</span>';
		}

		if ( isset($time) && $params->get('show_time', 1) ) {
			$dateinfo .= ' <span class="event-time">'.$time.'</span>';
		}

		return $dateinfo;
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

	/**
	 * Method to format date information
	 *
	 * @access public
	 * @return string
	 */
	function _format_date($date, $time, $format)
	{
		//format date
		$date = strftime($format, strtotime( $date.' '.$time ));

		return $date;
	}
}