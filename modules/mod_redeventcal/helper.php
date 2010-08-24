<?php
/**
 * Helper class for mod_redeventcal module
 * 
 * @package    Eventlist CalModuleQ for Joomla 1.5
 * @subpackage Modules
 * @link http://extensions.qivva.com
 * @license        http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @copyright (C) 2008 Toni Smillie www.qivva.com
 * 
 * Original Eventlist calendar from Christoph Lukes www.schlu.net
 * PHP Calendar (version 2.3), written by Keith Devens
 * http://keithdevens.com/software/php_calendar
 * see example at http://keithdevens.com/weblog
 * License: http://keithdevens.com/software/license
*/

class modredeventcalhelper
{

	function getdays ($greq_year, $greq_month, &$params)
	{
		$db			=& JFactory::getDBO();
		$user		=& JFactory::getUser();
		
		$catid 				= trim( $params->get('catid') );
		$venid 				= trim( $params->get('venid') );
		
		$monthstart = date('Y-m-d', mktime(0,0,0, $greq_month, 1, $greq_year));
		$monthend = date('Y-m-d', mktime(0,0,0, $greq_month+1, 1, $greq_year));
		
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
				. ' FROM #__redevent_event_venue_xref AS x'
				. ' LEFT JOIN #__redevent_events AS a ON a.id = x.eventid'
        . ' LEFT JOIN #__redevent_event_category_xref AS xcat ON xcat.event_id = a.id'
        . ' LEFT JOIN #__redevent_categories AS c ON c.id = xcat.category_id'
				. ' LEFT JOIN #__redevent_venues AS l ON l.id = x.venueid'
				. ' WHERE x.published = 1'
				. '   AND c.access <= '.(int)$user->aid
  	    . '   AND ( x.dates BETWEEN ' .$db->Quote($monthstart). ' AND ' .$db->Quote($monthend)
  	    . '         OR (x.enddates IS NOT NULL and x.enddates > "0000-00-00" AND x.enddates BETWEEN ' .$db->Quote($monthstart). ' AND ' .$db->Quote($monthend).') ) '
				.($catid ? $categories : '')
				.($venid ? $venues : '')
				. ' GROUP BY x.id '
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
			
					if (empty($days[$count][1]))
					{
						$title = htmlspecialchars($event->title);
					}
					else
					{
						$tt = $days[$count][1];
						$title = $tt . '&#013 +' . htmlspecialchars($event->title);
					}			
					$link			= RedeventHelperRoute::getDayRoute( $tdate, 'day') ;		
					$days[$count] = array($link,$title);
				}
		}
	}
	return $days;
	} //End of function getdays
} //End class

?> 