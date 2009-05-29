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
			$where = ' WHERE a.published = 1';
			$order = ' ORDER BY x.dates, x.times';
		} else {
			$where = ' WHERE a.published = -1';
			$order = ' ORDER BY x.dates DESC, x.times DESC';
		}

		$catid 	= trim( $params->get('catid') );
		$venid 	= trim( $params->get('venid') );

		if ($catid)
		{
			$ids = explode( ',', $catid );
			JArrayHelper::toInteger( $ids );
			$categories = ' AND (c.id=' . implode( ' OR c.id=', $ids ) . ')';
		}
		if ($venid)
		{
			$ids = explode( ',', $venid );
			JArrayHelper::toInteger( $ids );
			$venues = ' AND (l.id=' . implode( ' OR l.id=', $ids ) . ')';
		}

		//get $params->get( 'count', '2' ) nr of datasets
		$query = 'SELECT a.*, x.id AS xref, x.dates, x.enddates, x.times, x.endtimes, l.venue, l.city, l.url'
				.' FROM #__redevent_event_venue_xref AS x'
				.' LEFT JOIN #__redevent_events AS a ON a.id = x.eventid'
				.' LEFT JOIN #__redevent_venues AS l ON l.id = x.venueid'
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
		foreach ( $rows as $row )
		{
			//cut titel
			$length = strlen(htmlspecialchars( $row->title ));

			if ($length > $params->get('cuttitle', '18')) {
				$row->titel = substr($row->title, 0, $params->get('cuttitle', '18'));
				$row->titel = htmlspecialchars( $row->title.'...', ENT_COMPAT, 'UTF-8');
			}
			
			$lists[$i]->link		= JRoute::_('index.php?option=com_redevent&view=details&xref='.$row->xref);
			$lists[$i]->dateinfo 	= modRedEventHelper::_builddateinfo($row, $params);
			$lists[$i]->text		= $params->get('showtitloc', 0 ) ? $row->title : htmlspecialchars( $row->venue, ENT_COMPAT, 'UTF-8' );
			$lists[$i]->city		= htmlspecialchars( $row->city, ENT_COMPAT, 'UTF-8' );
			$lists[$i]->venueurl 	= !empty( $row->url ) ? modRedEventHelper::_format_url($row->url) : null;
			$i++;
		}

		return $lists;
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

		if ( isset($enddate)) {
			$dateinfo .= ' - '.$enddate;
		}

		if ( isset($time) ) {
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