<?php
/**
 * THIS FILE IS BASED mod_eventlist_teaser from ezuri.de, BASED ON MOD_EVENTLIST_WIDE FROM SCHLU.NET
 * @version 0.9 $Id$
 * @package Joomla
 * @subpackage RedEvent
 * @copyright (C) 2008 - 2011 redComponent
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
 * Redevent Moduleteaser helper
 *
 * @package Joomla
 * @subpackage Redevent Teaser Module
 * @since		1.0
 */
class modRedeventTeaserHelper
{

	/**
	 * Method to get the events
	 *
	 * @access public
	 * @return array
	 */
	function getList(&$params)
	{
		$mainframe = &Jfactory::getApplication();

		$db			=& JFactory::getDBO();
		$user		=& JFactory::getUser();
		$user_gid	= (int) max($user->getAuthorisedViewLevels());

		$where = array();
		
		//all upcoming events
		if ($params->get( 'type', 1 ) == 1) {
			$where[]  = ' x.dates >= CURDATE()';
			$where[]  = ' x.published = 1';
			$order  = ' ORDER BY x.dates, x.times';
		}
		
		//archived events only
		if ($params->get( 'type', 1 ) == 2) {
			$where[]  = ' x.published = -1';
			$order = ' ORDER BY x.dates DESC, x.times DESC';
		}
		
		//currently running events only
		if ($params->get( 'type', 1 ) == 3) {
			$where[]  = ' x.published = 1 ';			
			$where[]  = ' ( x.dates = CURDATE() OR ( x.enddates >= CURDATE() AND x.dates <= CURDATE() ))';
			$order  = ' ORDER BY x.dates, x.times';
		}

		//clean parameter data
		$catid 	= trim( $params->get('catid') );
		$venid 	= trim( $params->get('venid') );
		$state	= JString::strtolower(trim( $params->get('stateloc') ) );

		//Build category selection query statement
		if ($catid)
		{
			$ids = explode( ',', $catid );
			JArrayHelper::toInteger( $ids );
			$where[]  = ' (c.id=' . implode( ' OR c.id=', $ids ) . ')';
		}
		
		//Build venue selection query statement
		if ($venid)
		{
			$ids = explode( ',', $venid );
			JArrayHelper::toInteger( $ids );
			$where[]  = ' (l.id=' . implode( ' OR l.id=', $ids ) . ')';
		}
		
		//Build state selection query statement
		if ($state)
		{
			$rawstate = explode( ',', $state );
			
			foreach ($rawstate as $val)
			{
				if ($val) {
					$states[] = '"'.trim($db->getEscaped($val)).'"';
				}
			}
	
			JArrayHelper::toString( $states );
			$where[]  = ' (LOWER(l.state)='.implode(' OR LOWER(l.state)=',$states).')';
		}
		
		//query
		$query = 'SELECT a.*, x.eventid, x.id AS xref, x.dates, x.enddates, x.times, x.endtimes, l.venue, l.city, l.url , l.locimage, l.state, '
		    . ' CONCAT_WS(",", c.image) AS categories_images,'
        . ' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug, '
		    . ' CASE WHEN CHAR_LENGTH(l.alias) THEN CONCAT_WS(\':\', l.id, l.alias) ELSE l.id END as venueslug '
				. ' FROM #__redevent_event_venue_xref AS x'
				. ' INNER JOIN #__redevent_events AS a ON a.id = x.eventid'
				. ' LEFT JOIN #__redevent_venues AS l ON l.id = x.venueid'
        . ' LEFT JOIN #__redevent_event_category_xref AS xcat ON xcat.event_id = a.id'
        . ' LEFT JOIN #__redevent_categories AS c ON c.id = xcat.category_id'
        . ' LEFT JOIN #__redevent_repeats AS r ON r.xref_id = x.id '
				. ' WHERE '.implode(' AND ', $where)
				. ' GROUP BY x.id '
				. $order
				.' LIMIT '.(int)$params->get( 'count', '2' )
				;

		$db->setQuery($query);
		$rows = $db->loadObjectList();
		$rows = self::_categories($rows);
		
		//Loop through the result rows and prepare data
		$i		= 0;
		$lists	= array();
		foreach ( $rows as $k => $row )
		{
			//create thumbnails if needed and receive imagedata
			$dimage = redEVENTImage::modalimage($row->datimage, $row->title, intval($params->get('picture_size', 30)));
			$limage = redEVENTImage::modalimage($row->locimage, $row->venue, intval($params->get('picture_size', 30)));
						
			//cut title
			$length = mb_strlen( $row->title, 'UTF-8' );
			$title_length = $params->get('cuttitle', 35);
			if ($title_length && $length > $title_length) {
				$rows[$k]->title = mb_substr($row->title, 0, $title_length, 'UTF-8').'...';
			}
			else {
				$rows[$k]->title = $row->title;
			}			

			$lists[$i]->title			= htmlspecialchars( $row->title, ENT_COMPAT, 'UTF-8' );
			$lists[$i]->venue			= htmlspecialchars( $row->venue, ENT_COMPAT, 'UTF-8' );
//			$lists[$i]->catname		= htmlspecialchars( $row->catname, ENT_COMPAT, 'UTF-8' );
			$lists[$i]->state			= htmlspecialchars( $row->state, ENT_COMPAT, 'UTF-8' );		
			$lists[$i]->city	  	= htmlspecialchars( $row->city, ENT_COMPAT, 'UTF-8' );		      	
			$lists[$i]->eventlink	= $params->get('linkevent', 1) ? JRoute::_( RedeventHelperRoute::getDetailsRoute($row->slug, $row->xref) ) : '';
			$lists[$i]->venuelink	= $params->get('linkvenue', 1) ? JRoute::_( RedeventHelperRoute::getVenueEventsRoute($row->venueslug) ) : '';
			$lists[$i]->categorylink = $params->get('linkcategory', 1) ? self::_getCatLinks($row) : '';
			$lists[$i]->date 			= self::_format_date($row, $params);
			$lists[$i]->day 			= self::_format_day($row, $params);
			$lists[$i]->dayname		= self::_format_dayname($row);
			$lists[$i]->daynum 		= self::_format_daynum($row);
			$lists[$i]->month 		= self::_format_month($row);
			$lists[$i]->year 			= self::_format_year($row);

			$lists[$i]->time 			= $row->times ? self::_format_time($row->dates, $row->times, $params) : '' ;
			$lists[$i]->eventimage		= $dimage;
			$lists[$i]->venueimage		= $limage;
			
			// Hint: Thanks for checking the code. If you want to display the event description in the module use the following command in your layout:
			// echo $item->eventdescription; 
			// Note that all html elements will be removed
		
			 
			$length = $params->get( 'descriptionlength' );
			$etc = '...';

			//strip html tags but leave <br /> tags
			//entferne html tags bis auf Zeilenumbr�che
			$description = strip_tags($row->summary, "<br>");
			
			//switch <br /> tags to space character
			//wandle zeilenumbr�che in leerzeichen um
			if ($params->get( 'br' ) == 0) {
			 $description = str_replace('<br />',' ',$description);
			}
			//
			if (strlen($description) > $length) {
       			$length -= strlen($etc);
        		$description = preg_replace('/\s+?(\S+)?$/', '', substr($description, 0, $length+1));
				$lists[$i]->eventdescription = substr($description, 0, $length).$etc;
			} else {
				$lists[$i]->eventdescription	= $description;
			}
			
			$i++;
		}     
		
		return $lists;
	}
	
	/**
   * adds categories property to event rows
   *
   * @param array $rows of events
   * @return array
   */
  function _categories($rows)
  {
		$db =& JFactory::getDBO();
		
		$acl = &UserAcl::getInstance();		
		$gids = $acl->getUserGroupsIds();
		if (!is_array($gids) || !count($gids)) {
			$gids = array(0);
		}
		$gids = implode(',', $gids);
		
		$events = array();
		foreach ($rows as $k => $r) {
			$events[] = $r->eventid;
		}
		$events = array_unique($events);
		
		if (!count($events)) {
			return $rows;
		}
		
		$query = ' SELECT c.id, c.catname, c.image, x.event_id, '
		       . ' CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as slug '
		       . ' FROM #__redevent_categories as c '
		       . ' INNER JOIN #__redevent_event_category_xref as x ON x.category_id = c.id '
		       . '  LEFT JOIN #__redevent_groups_categories AS gc ON gc.category_id = c.id '
		       . ' WHERE c.published = 1 '
		       . '   AND x.event_id IN (' . implode(", ", $events) .')'
		       . '   AND (c.private = 0 OR gc.group_id IN ('.$gids.')) '
		       . ' ORDER BY c.ordering'
		       ;
		$db->setQuery( $query );
		$res = $db->loadObjectList();
		
		// get categories per events
		$evcats = array();
		foreach ($res as $r)
		{
			if (!isset($evcats[$r->event_id])) {
				$evcats[$r->event_id] = array();
			}
			$evcats[$r->event_id][] = $r;
		}
		
		foreach ($rows as $k => $r) {
			if (isset($evcats[$r->id])) {
				$rows[$k]->categories = $evcats[$r->id];
			}
			else {
				$rows[$k]->categories = array();
			}
		}

    return $rows;   
  }
	
  /**
   * returns categories links
   * @param unknown_type $item
   */
  function _getCatLinks($item)
  {
 		$links = array();
  	foreach ((array) $item->categories as $c)
  	{
			$link = JRoute::_(RedeventHelperRoute::getCategoryEventsRoute($c->slug));
			$links[] = JHTML::link($link, $c->catname);
  	}
  	return $links;
  }
  
  /**
   *format days
   *
   */        
	function _format_day($row, &$params)
	{
		//Get needed timestamps and format
		$yesterday_stamp	= mktime(0, 0, 0, date("m") , date("d")-1, date("Y"));
		$yesterday 		  	= strftime("%Y-%m-%d", $yesterday_stamp);
		$today_stamp	  	= mktime(0, 0, 0, date("m") , date("d"), date("Y"));
		$today 			    	= date('Y-m-d');
		$tomorrow_stamp 	= mktime(0, 0, 0, date("m") , date("d")+1, date("Y"));
		$tomorrow 		  	= strftime("%Y-%m-%d", $tomorrow_stamp);
		
		$dates_stamp	  	= strtotime($row->dates);
		$enddates_stamp		= $row->enddates ? strtotime($row->enddates) : null;
    

			//check if today or tomorrow or yesterday and no current running multiday event
			if($row->dates == $today && empty($enddates_stamp)) {
				$result = JText::_( 'MOD_REDEVENT_TEASER_TODAY' );
			} elseif($row->dates == $tomorrow) {
				$result = JText::_( 'MOD_REDEVENT_TEASER_TOMORROW' );
			} elseif($row->dates == $yesterday) {
				$result = JText::_( 'MOD_REDEVENT_TEASER_YESTERDAY' );
			}
				else {
				
		    //if daymethod show day 
	    	if($params->get('daymethod', 1) == 1) {				

		     	//single day event
		     	$date = strftime('%A', strtotime( $row->dates ));
		     	$result = JText::sprintf('MOD_REDEVENT_TEASER_ON_DATE', $date);
			
		    	//Upcoming multidayevent (From 16.10.2010 Until 18.10.2010)
		    	if($dates_stamp > $tomorrow_stamp && $enddates_stamp) {
		    	$startdate = strftime('%A', strtotime( $row->dates ));
		  		$result = JText::sprintf('MOD_REDEVENT_TEASER_FROM', $startdate);
		    	}
			
		    	//current multidayevent (Until 18.08.2008)
		    	if( $row->enddates && $enddates_stamp > $today_stamp && $dates_stamp <= $today_stamp ) {
		  		//format date
		  		$result = strftime('%A', strtotime( $row->enddates ));
		  		$result = JText::sprintf('MOD_REDEVENT_TEASER_UNTIL', $result);
		    	}			 
		    	
	  		}	
	  		
	  		// show day difference
	  		 else {
	     		//the event has an enddate and it's earlier than yesterday
		  	  if ($row->enddates && $enddates_stamp < $yesterday_stamp) {
	   			$days = round( ($today_stamp - $enddates_stamp) / 86400 );
	   			$result = JText::sprintf( 'MOD_REDEVENT_TEASER_ENDED_DAYS_AGO', $days );

      		//the event has an enddate and it's later than today but the startdate is today or earlier than today
	     		//means a currently running event with startdate = today 
		    	} elseif($row->enddates && $enddates_stamp > $today_stamp && $dates_stamp <= $today_stamp) {
	   			$days = round( ($enddates_stamp - $today_stamp) / 86400 );
	   			$result = JText::sprintf( 'MOD_REDEVENT_TEASER_DAYS_LEFT', $days );
           				
    			//the events date is earlier than yesterday
	     		} elseif($dates_stamp < $yesterday_stamp) {
	   			$days = round( ($today_stamp - $dates_stamp) / 86400 );
	   			$result = JText::sprintf( 'MOD_REDEVENT_TEASER_DAYS_AGO', $days );
				
	     		//the events date is later than tomorrow
	     		} elseif($dates_stamp > $tomorrow_stamp) {
	   			$days = round( ($dates_stamp - $today_stamp) / 86400 );
	   			$result = JText::sprintf( 'MOD_REDEVENT_TEASER_DAYS_AHEAD', $days );
	     		}
         }
        }
		return $result;
	}
	/**
	 * Method to format date information
	 *
	 * @access public
	 * @return string
	 */
	function _format_date($row, &$params)
	{
  		$enddates_stamp		= $row->enddates ? strtotime($row->enddates) : null;

			//single day event
			if (empty($enddates_stamp)) {
			$date = strftime($params->get('formatdate', '%d.%m.%Y'), strtotime( $row->dates.' '.$row->times ));
			$result = JText::sprintf('MOD_REDEVENT_TEASER_ON_DATE', $date);
			}
		  	else {	
		  	//multidayevent (From 16.10.2008 Until 18.08.2008)
				$startdate = strftime($params->get('formatdate', '%d.%m.%Y'), strtotime( $row->dates ));
				$enddate = strftime($params->get('formatdate', '%d.%m.%Y'), strtotime( $row->enddates ));
				$result = JText::sprintf('MOD_REDEVENT_TEASER_FROM_UNTIL', $startdate, $enddate);			
	     }
				
		return $result;
	}
	/**
	 * Method to format time information
	 *
	 * @access public
	 * @return string
	 */
	function _format_time($date, $time, &$params)
	{
		$time = strftime( $params->get('formattime', '%H:%M'), strtotime( $date.' '.$time ));
		$result = JText::sprintf('MOD_REDEVENT_TEASER_TIME_STRING', $time);	
		return $result;
	}

/*Calendar*/

	function _format_dayname($row)
	{
	    $date	  = strtotime($row->dates);
      $result = strftime("%A", $date);
		return $result;
	}
	function _format_daynum($row)
	{
	    $date	  = strtotime($row->dates);
      $result = strftime("%d", $date);
		return $result;
	}
	function _format_year($row)
	{
	    $date	  = strtotime($row->dates);
      $result = strftime("%Y", $date);
		return $result;
	}	
	function _format_month($row)
	{
	    $date	  = strtotime($row->dates);
      $result = strftime("%B", $date);
         /*htmlentities for german month March->M�rz*/
		return htmlentities($result);
	}	
}
