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

		if ($params->get( 'type', '0' ) == 0) {
			$where = ' WHERE x.published = 1';
			$order = ' ORDER BY x.dates, x.times';
		} else {
			$where = ' WHERE x.published = -1';
			$order = ' ORDER BY x.dates DESC, x.times DESC';
		}

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

		//get $params->get( 'count', '2' ) nr of datasets
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
				. ' GROUP BY x.id '
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
			$length = strlen(htmlspecialchars( $row->title ));
			if ($title_length && $length > $title_length) {
				$rows[$k]->title_short = htmlspecialchars(substr($row->title, 0, $title_length).'...', ENT_COMPAT, 'UTF-8');
			}
			else {
				$rows[$k]->title_short = htmlspecialchars($row->title, ENT_COMPAT, 'UTF-8');
			}
			// cut venue name
      $length = strlen(htmlspecialchars( $row->venue ));
      if ($title_length && $length > $title_length) {
        $rows[$k]->venue_short = htmlspecialchars(substr($row->venue, 0, $title_length).'...', ENT_COMPAT, 'UTF-8');
      }
      else {
        $rows[$k]->venue_short = htmlspecialchars($row->venue, ENT_COMPAT, 'UTF-8');
      }
      
			$rows[$k]->link		= JRoute::_(RedeventHelperRoute::getDetailsRoute($row->slug, $row->xref));
//			$rows[$k]->link		= JRoute::_('index.php?option=com_redevent&view=details&id='. $row->slug .'&xref='.$row->xref);
			$rows[$k]->dateinfo 	= modRedEventHelper::_builddateinfo($row, $params);
			$rows[$k]->text		= ($params->get('showtitloc', 0 )) ? $rows[$k]->title_short : $rows[$k]->venue_short;
			$rows[$k]->city		= htmlspecialchars( $row->city, ENT_COMPAT, 'UTF-8' );
			$rows[$k]->venueurl 	= !empty( $row->url ) ? modRedEventHelper::_format_url($row->url) : JRoute::_('index.php?option=com_redevent&view=venueevents&id='. $row->venueslug , false);
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
		$date 		= modRedEventHelper::_format_date($row->dates, $row->times, $params->get('formatdate', '%d.%m.%Y'));
		$enddate 	= ($row->enddates && $row->enddates != '0000-00-00') ? modRedEventHelper::_format_date($row->enddates, $row->endtimes, $params->get('formatdate', '%d.%m.%Y')) : null;
		$time		= ($row->times && $row->times != '00:00:00') ? modRedEventHelper::_format_date($row->dates, $row->times, $params->get('formattime', '%H:%M')) : null;
		$dateinfo	= $date;

		if ( isset($enddate) && $params->get('show_enddate', 1)) {
			$dateinfo .= ' - '.$enddate;
		}

		if ( isset($time) && $params->get('show_time', 1) ) {
			$dateinfo .= ' | '.$time;
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