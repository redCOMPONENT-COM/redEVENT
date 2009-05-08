<?php
/**
 * @version 1.0 $Id: admin.class.php 662 2008-05-09 22:28:53Z schlu $
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

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

/**
 * EventList Component Details Model
 *
 * @package Joomla
 * @subpackage EventList
 * @since		0.9
 */
class RedeventModelCalendar extends JModel {
	/**
	 * Constructor
	 *
	 * @since 0.9
	 */
	function __construct() {
		parent::__construct();
	}
	
	function getdays ($greq_year, $greq_month, &$params)
	{
		$db			=& JFactory::getDBO();
		$user		=& JFactory::getUser();
		
		$catid 				= trim( $params->get('catid') );
		$venid 				= trim( $params->get('venid') );
		
		//Get eventdates
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
		
		$query = 'SELECT x.dates, x.times, x.enddates,a.title, DAYOFMONTH(x.dates) AS created_day, YEAR(x.dates) AS created_year, MONTH(x.dates) AS created_month'
		. ' FROM #__redevent_events AS a'
		. ' LEFT JOIN #__redevent_event_venue_xref AS x ON a.id = x.eventid'
		. ' LEFT JOIN #__redevent_categories AS c ON c.id = a.catsid'
		. ' LEFT JOIN #__redevent_venues AS l ON l.id = x.venueid'
		. ' WHERE a.published = 1'
		. ' AND c.access <= '.(int)$user->aid
		.($catid ? $categories : '')
		.($venid ? $venues : '')	
		;
		
		$db->setQuery( $query );
		$events = $db->loadObjectList();
		
		$days = array();
		foreach ( $events as $event )
		{
		   // Cope with no end date set i.e. set it to same as start date
			if  (($event->enddates == '0000-00-00') or (is_null($event->enddates)))
			{
				$eyear = $event->created_year;
				$emonth = $event->created_month;
				$eday = $event->created_day;
		
			}
			else
			{
				list($eyear, $emonth, $eday) = explode('-', $event->enddates);
			}
			// The two cases for roll over the year end with an event that goes across the year boundary.
			if ($greq_year < $eyear) 
			{
				$emonth = $emonth + 12; 
			}
					
			if ($event->created_year < $greq_year) 
			{
				$event->created_month = $event->created_month - 12;
			}

			if (  ($greq_year >= $event->created_year) && ($greq_year <= $eyear) 
			   && ($greq_month >= $event->created_month) && ($greq_month <= $emonth) )
		   {
			// Set end day for current month

				if ($emonth > $greq_month)
				{
					$emonth = $greq_month;

		//			$eday = cal_days_in_month(CAL_GREGORIAN, $greq_month,$greq_year);
					$eday = date('t', mktime(0,0,0, $greq_month, 1, $greq_year));
				}

			// Set start day for current month
				if ($event->created_month < $greq_month)
				{
					$event->created_month = $greq_month;
					$event->created_day = 1;
				}	
			
				for ($count = $event->created_day; $count <= $eday; $count++)
				{
		
				$uxdate = mktime(0,0,0,$event->created_month,$count,$event->created_year); // Toni change
				$tdate = strftime('%Y%m%d',$uxdate);// Toni change Joomla 1.5
				$created_day = $count;
		
	//			$tt = $days[$count][1];
		
	//			if (strlen($tt) == 0)
				if (empty($days[$count][1]))
				{
					$title = htmlspecialchars($event->title);
				}
				else
				{
					$tt = $days[$count][1];
					$title = $tt . '&#013 +' . htmlspecialchars($event->title);
				}			
				$link			= RedeventHelperRoute::getRoute( $tdate, 'day') ;		
				$days[$count] = array($link,$title);
				}
		}
	// End of Toni modification	
	}
	return $days;
	} //End of function getdays
}
?>