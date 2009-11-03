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

/**
 *
 * Holds some usefull functions to keep the code a bit cleaner
 *
 * @package Joomla
 * @subpackage EventList
 */
class redEVENTHelper {

	/**
	 * Pulls settings from database and stores in an static object
	 *
	 * @return object
	 * @since 0.9
	 */
	function &config()
	{
		static $config;

		if (!is_object($config))
		{
			$db 	= & JFactory::getDBO();
			$sql 	= 'SELECT * FROM #__redevent_settings WHERE id = 1';
			$db->setQuery($sql);
			$config = $db->loadObject();
		}

		return $config;
	}

	/**
   	* Performs dayly scheduled cleanups
   	*
   	* Currently it archives and removes outdated events
   	* and takes care of the recurrence of events
   	*
 	  * @since 0.9
   	*/
	function cleanup($forced = 0)
	{
		$elsettings = & redEVENTHelper::config();

		$now 		= time();
		$lastupdate = $elsettings->lastupdate;

		//last update later then 24h?
		//$difference = $now - $lastupdate;

		//if ( $difference > 86400 ) {

		//better: new day since last update?
		$nrdaysnow = floor($now / 86400);
		$nrdaysupdate = floor($lastupdate / 86400);

		if ( $nrdaysnow > $nrdaysupdate || $forced) 
		{
			$db			= & JFactory::getDBO();

			$nulldate = '0000-00-00';
			$limit_date = strftime('%Y-%m-%d', time() - $elsettings->minus * 3600 * 24);
			
			redEVENTHelper::generaterecurrences();
			
			//delete outdated events
			if ($elsettings->oldevent == 1) 
			{
				// lists event_id for which we are going to delete xrefs
				$query = ' SELECT x.eventid FROM #__redevent_event_venue_xref AS x '
               . ' WHERE x.dates IS NOT NULL AND DATEDIFF('. $db->Quote($limit_date) .', (IF (x.enddates <> '. $db->Quote($nulldate) .', x.enddates, x.dates))) >= 0 '
               ;
        $db->SetQuery( $query );
        $event_ids = $db->loadResultArray();
        
        if (!count($event_ids)) {
        	return true;
        }
        
				$query = ' DELETE x FROM #__redevent_event_venue_xref AS x '
               . ' WHERE x.dates IS NOT NULL AND DATEDIFF('. $db->Quote($limit_date) .', (IF (x.enddates <> '. $db->Quote($nulldate) .', x.enddates, x.dates))) >= 0 '
				       ;
				$db->SetQuery( $query );
				if (!$db->Query()) {
					RedeventHelperLog::simpleLog('CLEANUP Error while deleting old xrefs: '. $db->getErrorMsg());
				}
				
				
				// now delete the events with no more xref
        $query = ' DELETE e FROM #__redevent_events AS e '
               . ' LEFT JOIN #__redevent_event_venue_xref AS x ON x.eventid = e.id '
               . ' WHERE x.id IS NULL '
               . '   AND e.id IN (' . implode(', ', $event_ids) . ')'
               ;
        $db->SetQuery( $query );
				if (!$db->Query()) {
					RedeventHelperLog::simpleLog('CLEANUP Error while deleting old events with no more xrefs: '. $db->getErrorMsg());
				}
			}

			//Set state archived of outdated events
			if ($elsettings->oldevent == 2) 
			{
        // lists xref_id and associated event_id for which we are going to be archived
        $query = ' SELECT x.id, x.eventid '
               . ' FROM #__redevent_event_venue_xref AS x '
               . ' WHERE x.dates IS NOT NULL AND DATEDIFF('. $db->Quote($limit_date) .', (IF (x.enddates <> '. $db->Quote($nulldate) .', x.enddates, x.dates))) >= 0 '
               . ' AND x.published = 1 '
               ;
        $db->SetQuery( $query );
        $xrefs = $db->loadObjectList();
        
        if (empty($xrefs)) {
          return true;
        }
        
        // build list of xref and corresponding events
        $event_ids = array();
        $xref_ids  = array();
        foreach ($xrefs AS $xref) 
        {
        	$event_ids[] = $db->Quote($xref->eventid);
        	$xref_ids[]  = $db->Quote($xref->id);
        }
        // filter duplicates
        $event_ids = array_unique($event_ids);
        
        // update xref to archive
				$query = ' UPDATE #__redevent_event_venue_xref AS x '
				       . ' SET x.published = -1 '
               . ' WHERE x.id IN ('. implode(', ', $xref_ids) .')'
				       ;
				$db->SetQuery( $query );
				if (!$db->Query()) {
					RedeventHelperLog::simpleLog('CLEANUP Error while archiving old xrefs: '. $db->getErrorMsg());
				}
								
        // update events to archive (if no more published xref)
        $query = ' UPDATE #__redevent_events AS e '
               . ' LEFT JOIN #__redevent_event_venue_xref AS x ON x.eventid = e.id AND x.published <> -1 '
               . ' SET e.published = -1 '
               . ' WHERE x.id IS NULL '
               . '   AND e.id IN (' . implode(', ', $event_ids) . ')'
               ;
        $db->SetQuery( $query );
				if (!$db->Query()) {
					RedeventHelperLog::simpleLog('CLEANUP Error while archiving events with only archived xrefs: '. $db->getErrorMsg());
				}
			}

			//Set timestamp of last cleanup
			$query = 'UPDATE #__redevent_settings SET lastupdate = '.time().' WHERE id = 1';
			$db->SetQuery( $query );
			$db->Query();
		}
	}

	/**
	 * adds xref repeats to the database.
	 * 
	 * @return bool true on success
	 */
	function generaterecurrences($recurrence_id = null)
	{
	   $db = & JFactory::getDBO();

	   $nulldate = '0000-00-00';
	   
	   // generate until limit
	   $params = & JComponentHelper::getParams('com_redevent');
	   $limit = $params->get('recurrence_limit', 30);
	   $limit_date_int = time() + $limit*3600*24;

	   // get active recurrences
	   $query = ' SELECT MAX(rp.xref_id) as xref_id, r.rrule, r.id as recurrence_id '
        	   . ' FROM #__redevent_repeats AS rp '
        	   . ' INNER JOIN #__redevent_recurrences AS r on r.id = rp.recurrence_id '
             . ' INNER JOIN #__redevent_event_venue_xref AS x on x.id = rp.xref_id ' // make sure there are still events associated...
        	   . ' WHERE r.ended = 0 '
        	   ;
     if ($recurrence_id) {
       $query .= ' AND r.id = '. $db->Quote($recurrence_id);
     }
     $query .= ' GROUP BY recurrence_id ';
	   $db->setQuery($query);
	   $recurrences = $db->loadObjectList();

	   if (empty($recurrences)) {
	     return true;
	   }
	        
	   // get corresponding xrefs
	   $rids = array();
	   foreach ($recurrences as $r) {
	     $rids[] = $r->xref_id;
	   }
	   $query = ' SELECT x.*, rp.count '
        	   . ' FROM #__redevent_event_venue_xref AS x '
             . ' INNER JOIN #__redevent_repeats AS rp ON rp.xref_id = x.id '
        	   . ' WHERE id IN ('. implode(",", $rids) .')'
  	         ;
	   $db->setQuery($query);
	   $xrefs = $db->loadObjectList('id');
	   	   
	   // now, do the job...
	   foreach ($recurrences as $r) 
	   {
	     $next = RedeventHelperRecurrence::getnext($r->rrule, $xrefs[$r->xref_id]);
	     while ($next)
	     {
	       if (strtotime($next->dates) > $limit_date_int) {
	         break;
	       }
	       
         //record xref
         $object = & JTable::getInstance('RedEvent_eventvenuexref', '');
         $object->bind($next);
         if ($object->store()) 
         {
           // update repeats table          
           $query = ' INSERT INTO #__redevent_repeats '
                  . ' SET xref_id = '. $db->Quote($object->id)
                  . '   , recurrence_id = '. $db->Quote($r->recurrence_id)
                  . '   , count = '. $db->Quote($next->count)
                  ;
           $db->setQuery($query);
           if (!$db->query()) {
             RedeventHelperLog::simpleLog('saving repeat error: '.$db->getErrorMsg());
           }
//           echo "added xref $object->id / count $next->count";           
//           echo '<br>';
         }
         else {
           RedeventHelperLog::simpleLog('saving recurrence xref error: '.$db->getErrorMsg());
         }
         $next = RedeventHelperRecurrence::getnext($r->rrule, $next);
	     }
	     if (!$next) 
	     {
	       // no more events to generate, we can disable the rule    	       
         $query = ' UPDATE #__redevent_recurrences SET ended = 1 WHERE id = '. $db->Quote($r->recurrence_id);
         $db->setQuery($query);
         $db->query();
	     }	     
	   }
	   return true;
	}
	
	/**
	 * transforms <br /> and <br> back to \r\n
	 *
	 * @param string $string
	 * @return string
	 */
	function br2break($string)
	{
		return preg_replace("=<br(>|([\s/][^>]*)>)\r?\n?=i", "\r\n", $string);
	}
	
	/**
	 * returns formated event duration.
	 *
	 * @param $event object having properties dates, enddates, times, endtimes 
	 */
	function getEventDuration($event)
	{
		if (!$event->dates || $event->dates == '0000-00-00') {
			return '-';
		}
		
		// start time in seconds
		if (empty($event->times) || $event->times == '00:00:00') {
      $start = strtotime($event->dates);			
		}
		else {
			$start = strtotime($event->dates. ' ' . $event->times);
		}
		
		// end time in seconds
    if (empty($event->enddates) || $event->enddates == '0000-00-00') {
    	// same day
      if (empty($event->endtimes) || $event->endtimes == '00:00:00') {
        // we set it to end of the day, user should set it anyway
        $end = strtotime($event->dates. ' 00:00:00') + 3600 * 24;         
      }
      else {
        $end = strtotime($event->dates . ' ' . $event->endtimes);      	
      }
    }
    else {
      if (empty($event->endtimes) || $event->endtimes == '00:00:00') {
        // we set it to end of the day
        $end = strtotime($event->enddates. ' 00:00:00') + 3600 * 24;
      }
      else {
        $end = strtotime($event->enddates .' '. $event->endtimes);
      }
    }
    
    $duration = $end - $start;
    
		if ($duration > 3600 * 24) {
			$day = floor($duration / (3600 * 24)) + 1 ;
			if ($day == 1) return $day . ' ' . JText::_('Day');
			else return $day . ' ' . JText::_('Days');
		}
		else if ($duration == 3600 * 24) {
			return '1' . ' ' . JText::_('Day');			
		}
		else {
			return floor($duration / 3600) . JText::_('LOC_H') . sprintf('%02d', floor(($duration % 3600) / 60));
		}
	}
	

  /**
   * return country options from the database
   *
   * @return unknown
   */
  function getCountryOptions()
  {
    $db   = & JFactory::getDBO();
    $sql  = 'SELECT iso2 AS value, name AS text FROM #__redevent_countries ORDER BY name';
    $db->setQuery($sql);
    return $db->loadObjectList();
  }
  

  /**
   * returns indented event category options
   *
   * @param boolean show categories with no publish xref associated
   * @param boolean show unpublished categories
   * @return array
   */
  function getEventsCatOptions($show_empty = true, $show_unpublished = false) 
  {
    $db   = & JFactory::getDBO();

    if ($show_empty == false)
    {
    	// select categories with events first
    	$query = ' SELECT c.id '
    	       . ' FROM #__redevent_categories AS c '
    	       . ' INNER JOIN #__redevent_categories AS child ON child.lft BETWEEN c.lft AND c.rgt '
    	       . ' INNER JOIN #__redevent_event_category_xref AS xcat ON xcat.category_id = child.id '
    	       . ' INNER JOIN #__redevent_event_venue_xref AS x ON x.eventid = xcat.event_id '
    	       . ' WHERE x.published = 1 '
    	       . ' GROUP BY c.id '
    	       ;
	    $db->setQuery($query);
	
	    $notempty = $db->loadResultArray();
	    if (empty($notempty)) {
	    	return array();
	    }
    }
  	
    $query =  ' SELECT c.id, c.catname, (COUNT(parent.catname) - 1) AS depth '
            . ' FROM #__redevent_categories AS c '
            . ' INNER JOIN #__redevent_categories AS parent ON c.lft BETWEEN parent.lft AND parent.rgt '
            ;    

    $where = array();    
    if ($show_empty == false)
    {
      $where[] = ' c.id IN (' . implode(', ', $notempty) . ')';
    }            
    if (!$show_unpublished) {
    	$where[] = ' c.published = 1 ';
    }
    if (count($where)) {
    	$query .= ' WHERE ' . implode(' AND ', $where);
    }
    
    $query .= ' GROUP BY c.id ';       
    $query .= ' ORDER BY c.lft ';
    
    $db->setQuery($query);

    $results = $db->loadObjectList();

    $options = array();
    foreach((array) $results as $cat)
    {
      $options[] = JHTML::_('select.option', $cat->id, str_repeat('>', $cat->depth) . ' ' . $cat->catname);
    }
    return $options;
  }
  
  /**
   * returns indented venues category options
   *
   * @param boolean show venues categories with no published venue associated
   * @param boolean show unpublished venues categories
   * @return array
   */
  function getVenuesCatOptions($show_empty = true, $show_unpublished = false) 
  {
    $db   = & JFactory::getDBO();
  
    if ($show_empty == false)
    {
      // select only categories with published venues
      $query = ' SELECT c.id '
             . ' FROM #__redevent_venues_categories AS c '
             . ' INNER JOIN #__redevent_venues_categories AS child ON child.lft BETWEEN c.lft AND c.rgt '
             . ' INNER JOIN #__redevent_venue_category_xref AS xcat ON xcat.category_id = child.id '
             . ' INNER JOIN #__redevent_venues AS v ON v.id = xcat.venue_id '
             . ' WHERE v.published = 1 '
             . ' GROUP BY c.id '
             ;
      $db->setQuery($query);
  
      $notempty = $db->loadResultArray();
      if (empty($notempty)) {
        return array();
      }
    }
    
    $query =  ' SELECT c.id, c.name, (COUNT(parent.name) - 1) AS depth '
				    . ' FROM #__redevent_venues_categories AS c, '
				    . ' #__redevent_venues_categories AS parent '
				    . ' WHERE c.lft BETWEEN parent.lft AND parent.rgt '
				    ;

    $where = array();    
    if ($show_empty == false)
    {
      $where[] = ' c.id IN (' . implode(', ', $notempty) . ')';
    }            
    if (!$show_unpublished) {
      $where[] = ' c.published = 1 ';
    }
    if (count($where)) {
      $query .= 'AND ' . implode(' AND ', $where);
    }
    
    $query .= ' GROUP BY c.id '
				    . ' ORDER BY c.lft;'
				    ;
    $db->setQuery($query);

    $results = $db->loadObjectList();

    $options = array();
    foreach((array) $results as $cat)
    {
    	$options[] = JHTML::_('select.option', $cat->id, str_repeat('>', $cat->depth) . ' ' . $cat->name);
    }
    return $options;
  }
  

  function getCountrycoordArray()
  {
    $countrycoord = array();
    $countrycoord['AD']= array(42.5  , 1.5);
    $countrycoord['AE']= array(24  , 54);
    $countrycoord['AF']= array(33  , 65);
    $countrycoord['AG']= array(17.05 , -61.8);
    $countrycoord['AI']= array(18.25 , -63.17);
    $countrycoord['AL']= array(41  , 20);
    $countrycoord['AM']= array(40  , 45);
    $countrycoord['AN']= array(12.25 , -68.75);
    $countrycoord['AO']= array(-12.5 , 18.5);
    $countrycoord['AP']= array(35  , 105);
    $countrycoord['AQ']= array(-90 , 0);
    $countrycoord['AR']= array(-34 , -64);
    $countrycoord['AS']= array(-14.33  , -170);
    $countrycoord['AT']= array(47.33 , 13.33);
    $countrycoord['AU']= array(-27 , 133);
    $countrycoord['AW']= array(12.5  , -69.97);
    $countrycoord['AZ']= array(40.5  , 47.5);
    $countrycoord['BA']= array(44  , 18);
    $countrycoord['BB']= array(13.17 , -59.53);
    $countrycoord['BD']= array(24  , 90);
    $countrycoord['BE']= array(50.83 , 4);
    $countrycoord['BF']= array(13  , -2);
    $countrycoord['BG']= array(43  , 25);
    $countrycoord['BH']= array(26  , 50.55);
    $countrycoord['BI']= array(-3.5  , 30);
    $countrycoord['BJ']= array(9.5 , 2.25);
    $countrycoord['BM']= array(32.33 , -64.75);
    $countrycoord['BN']= array(4.5 , 114.67);
    $countrycoord['BO']= array(-17 , -65);
    $countrycoord['BR']= array(-10 , -55);
    $countrycoord['BS']= array(24.25 , -76);
    $countrycoord['BT']= array(27.5  , 90.5);
    $countrycoord['BV']= array(-54.43  , 3.4);
    $countrycoord['BW']= array(-22 , 24);
    $countrycoord['BY']= array(53  , 28);
    $countrycoord['BZ']= array(17.25 , -88.75);
    $countrycoord['CA']= array(60  , -95);
    $countrycoord['CC']= array(-12.5 , 96.83);
    $countrycoord['CD']= array(0 , 25);
    $countrycoord['CF']= array(7 , 21);
    $countrycoord['CG']= array(-1  , 15);
    $countrycoord['CH']= array(47  , 8);
    $countrycoord['CI']= array(8 , -5);
    $countrycoord['CK']= array(-21.23  , -159.77);
    $countrycoord['CL']= array(-30 , -71);
    $countrycoord['CM']= array(6 , 12);
    $countrycoord['CN']= array(35  , 105);
    $countrycoord['CO']= array(4 , -72);
    $countrycoord['CR']= array(10  , -84);
    $countrycoord['CU']= array(21.5  , -80);
    $countrycoord['CV']= array(16  , -24);
    $countrycoord['CX']= array(-10.5 , 105.67);
    $countrycoord['CY']= array(35  , 33);
    $countrycoord['CZ']= array(49.75 , 15.5);
    $countrycoord['DE']= array(51  , 9);
    $countrycoord['DJ']= array(11.5  , 43);
    $countrycoord['DK']= array(56  , 10);
    $countrycoord['DM']= array(15.42 , -61.33);
    $countrycoord['DO']= array(19  , -70.67);
    $countrycoord['DZ']= array(28  , 3);
    $countrycoord['EC']= array(-2  , -77.5);
    $countrycoord['EE']= array(59  , 26);
    $countrycoord['EG']= array(27  , 30);
    $countrycoord['EH']= array(24.5  , -13);
    $countrycoord['ER']= array(15  , 39);
    $countrycoord['ES']= array(40  , -4);
    $countrycoord['ET']= array(8 , 38);
    $countrycoord['EU']= array(47  , 8);
    $countrycoord['FI']= array(64  , 26);
    $countrycoord['FJ']= array(-18 , 175);
    $countrycoord['FK']= array(-51.75  , -59);
    $countrycoord['FM']= array(6.92  , 158.25);
    $countrycoord['FO']= array(62  , -7);
    $countrycoord['FR']= array(46  , 2);
    $countrycoord['GA']= array(-1  , 11.75);
    $countrycoord['GB']= array(54  , -2);
    $countrycoord['GD']= array(12.12 , -61.67);
    $countrycoord['GE']= array(42  , 43.5);
    $countrycoord['GF']= array(4 , -53);
    $countrycoord['GH']= array(8 , -2);
    $countrycoord['GI']= array(36.18 , -5.37);
    $countrycoord['GL']= array(72  , -40);
    $countrycoord['GM']= array(13.47 , -16.57);
    $countrycoord['GN']= array(11  , -10);
    $countrycoord['GP']= array(16.25 , -61.58);
    $countrycoord['GQ']= array(2 , 10);
    $countrycoord['GR']= array(39  , 22);
    $countrycoord['GS']= array(-54.5 , -37);
    $countrycoord['GT']= array(15.5  , -90.25);
    $countrycoord['GU']= array(13.47 , 144.78);
    $countrycoord['GW']= array(12  , -15);
    $countrycoord['GY']= array(5 , -59);
    $countrycoord['HK']= array(22.25 , 114.17);
    $countrycoord['HM']= array(-53.1 , 72.52);
    $countrycoord['HN']= array(15  , -86.5);
    $countrycoord['HR']= array(45.17 , 15.5);
    $countrycoord['HT']= array(19  , -72.42);
    $countrycoord['HU']= array(47  , 20);
    $countrycoord['ID']= array(-5  , 120);
    $countrycoord['IE']= array(53  , -8);
    $countrycoord['IL']= array(31.5  , 34.75);
    $countrycoord['IN']= array(20  , 77);
    $countrycoord['IO']= array(-6  , 71.5);
    $countrycoord['IQ']= array(33  , 44);
    $countrycoord['IR']= array(32  , 53);
    $countrycoord['IS']= array(65  , -18);
    $countrycoord['IT']= array(42.83 , 12.83);
    $countrycoord['JM']= array(18.25 , -77.5);
    $countrycoord['JO']= array(31  , 36);
    $countrycoord['JP']= array(36  , 138);
    $countrycoord['KE']= array(1 , 38);
    $countrycoord['KG']= array(41  , 75);
    $countrycoord['KH']= array(13  , 105);
    $countrycoord['KI']= array(1.42  , 173);
    $countrycoord['KM']= array(-12.17  , 44.25);
    $countrycoord['KN']= array(17.33 , -62.75);
    $countrycoord['KP']= array(40  , 127);
    $countrycoord['KR']= array(37  , 127.5);
    $countrycoord['KW']= array(29.34 , 47.66);
    $countrycoord['KY']= array(19.5  , -80.5);
    $countrycoord['KZ']= array(48  , 68);
    $countrycoord['LA']= array(18  , 105);
    $countrycoord['LB']= array(33.83 , 35.83);
    $countrycoord['LC']= array(13.88 , -61.13);
    $countrycoord['LI']= array(47.17 , 9.53);
    $countrycoord['LK']= array(7 , 81);
    $countrycoord['LR']= array(6.5 , -9.5);
    $countrycoord['LS']= array(-29.5 , 28.5);
    $countrycoord['LT']= array(56  , 24);
    $countrycoord['LU']= array(49.75 , 6.17);
    $countrycoord['LV']= array(57  , 25);
    $countrycoord['LY']= array(25  , 17);
    $countrycoord['MA']= array(32  , -5);
    $countrycoord['MC']= array(43.73 , 7.4);
    $countrycoord['MD']= array(47  , 29);
    $countrycoord['ME']= array(42  , 19);
    $countrycoord['MG']= array(-20 , 47);
    $countrycoord['MH']= array(9 , 168);
    $countrycoord['MK']= array(41.83 , 22);
    $countrycoord['ML']= array(17  , -4);
    $countrycoord['MM']= array(22  , 98);
    $countrycoord['MN']= array(46  , 105);
    $countrycoord['MO']= array(22.17 , 113.55);
    $countrycoord['MP']= array(15.2  , 145.75);
    $countrycoord['MQ']= array(14.67 , -61);
    $countrycoord['MR']= array(20  , -12);
    $countrycoord['MS']= array(16.75 , -62.2);
    $countrycoord['MT']= array(35.83 , 14.58);
    $countrycoord['MU']= array(-20.28  , 57.55);
    $countrycoord['MV']= array(3.25  , 73);
    $countrycoord['MW']= array(-13.5 , 34);
    $countrycoord['MX']= array(23  , -102);
    $countrycoord['MY']= array(2.5 , 112.5);
    $countrycoord['MZ']= array(-18.25  , 35);
    $countrycoord['NA']= array(-22 , 17);
    $countrycoord['NC']= array(-21.5 , 165.5);
    $countrycoord['NE']= array(16  , 8);
    $countrycoord['NF']= array(-29.03  , 167.95);
    $countrycoord['NG']= array(10  , 8);
    $countrycoord['NI']= array(13  , -85);
    $countrycoord['NL']= array(52.5  , 5.75);
    $countrycoord['NO']= array(62  , 10);
    $countrycoord['NP']= array(28  , 84);
    $countrycoord['NR']= array(-0.53 , 166.92);
    $countrycoord['NU']= array(-19.03  , -169.87);
    $countrycoord['NZ']= array(-41 , 174);
    $countrycoord['OM']= array(21  , 57);
    $countrycoord['PA']= array(9 , -80);
    $countrycoord['PE']= array(-10 , -76);
    $countrycoord['PF']= array(-15 , -140);
    $countrycoord['PG']= array(-6  , 147);
    $countrycoord['PH']= array(13  , 122);
    $countrycoord['PK']= array(30  , 70);
    $countrycoord['PL']= array(52  , 20);
    $countrycoord['PM']= array(46.83 , -56.33);
    $countrycoord['PR']= array(18.25 , -66.5);
    $countrycoord['PS']= array(32  , 35.25);
    $countrycoord['PT']= array(39.5  , -8);
    $countrycoord['PW']= array(7.5 , 134.5);
    $countrycoord['PY']= array(-23 , -58);
    $countrycoord['QA']= array(25.5  , 51.25);
    $countrycoord['RE']= array(-21.1 , 55.6);
    $countrycoord['RO']= array(46  , 25);
    $countrycoord['RS']= array(44  , 21);
    $countrycoord['RU']= array(60  , 100);
    $countrycoord['RW']= array(-2  , 30);
    $countrycoord['SA']= array(25  , 45);
    $countrycoord['SB']= array(-8  , 159);
    $countrycoord['SC']= array(-4.58 , 55.67);
    $countrycoord['SD']= array(15  , 30);
    $countrycoord['SE']= array(62  , 15);
    $countrycoord['SG']= array(1.37  , 103.8);
    $countrycoord['SH']= array(-15.93  , -5.7);
    $countrycoord['SI']= array(46  , 15);
    $countrycoord['SJ']= array(78  , 20);
    $countrycoord['SK']= array(48.67 , 19.5);
    $countrycoord['SL']= array(8.5 , -11.5);
    $countrycoord['SM']= array(43.77 , 12.42);
    $countrycoord['SN']= array(14  , -14);
    $countrycoord['SO']= array(10  , 49);
    $countrycoord['SR']= array(4 , -56);
    $countrycoord['ST']= array(1 , 7);
    $countrycoord['SV']= array(13.83 , -88.92);
    $countrycoord['SY']= array(35  , 38);
    $countrycoord['SZ']= array(-26.5 , 31.5);
    $countrycoord['TC']= array(21.75 , -71.58);
    $countrycoord['TD']= array(15  , 19);
    $countrycoord['TF']= array(-43 , 67);
    $countrycoord['TG']= array(8 , 1.17);
    $countrycoord['TH']= array(15  , 100);
    $countrycoord['TJ']= array(39  , 71);
    $countrycoord['TK']= array(-9  , -172);
    $countrycoord['TM']= array(40  , 60);
    $countrycoord['TN']= array(34  , 9);
    $countrycoord['TO']= array(-20 , -175);
    $countrycoord['TR']= array(39  , 35);
    $countrycoord['TT']= array(11  , -61);
    $countrycoord['TV']= array(-8  , 178);
    $countrycoord['TW']= array(23.5  , 121);
    $countrycoord['TZ']= array(-6  , 35);
    $countrycoord['UA']= array(49  , 32);
    $countrycoord['UG']= array(1 , 32);
    $countrycoord['UM']= array(19.28 , 166.6);
    $countrycoord['US']= array(38  , -97);
    $countrycoord['UY']= array(-33 , -56);
    $countrycoord['UZ']= array(41  , 64);
    $countrycoord['VA']= array(41.9  , 12.45);
    $countrycoord['VC']= array(13.25 , -61.2);
    $countrycoord['VE']= array(8 , -66);
    $countrycoord['VG']= array(18.5  , -64.5);
    $countrycoord['VI']= array(18.33 , -64.83);
    $countrycoord['VN']= array(16  , 106);
    $countrycoord['VU']= array(-16 , 167);
    $countrycoord['WF']= array(-13.3 , -176.2);
    $countrycoord['WS']= array(-13.58  , -172.33);
    $countrycoord['YE']= array(15  , 48);
    $countrycoord['YT']= array(-12.83  , 45.17);
    $countrycoord['ZA']= array(-29 , 24);
    $countrycoord['ZM']= array(-15 , 30);
    $countrycoord['ZW']= array(-20 , 30);
    return $countrycoord;
  }
  

  function getCustomField($type)
  {
    require_once (JPATH_COMPONENT_ADMINISTRATOR.DS.'customfield'.DS.'customfield.php');
    switch ($type)
    {
      case 'select':
        return new TCustomfieldSelect();
        break;
        
      case 'select_multiple':
        return new TCustomfieldSelectmultiple();
        break;
        
      case 'date':
        return new TCustomfieldDate();
        break;
        
      case 'radio':
        return new TCustomfieldRadio();
        break;
        
      case 'checkbox':
        return new TCustomfieldCheckbox();
        break;
        
      case 'textarea':
        return new TCustomfieldTextarea();
        break;
        
      case 'textbox':
      default:
        return new TCustomfieldTextbox();
        break;
    }
  }
  
  function renderFieldValue($field)
  {
    switch ($field->type)
    {
      case 'select_multiple':
      case 'checkbox':
        return str_replace("\n", "<br/>", $field->value);
      case 'textarea':
        return str_replace("\n", "<br/>", htmlspecialchars($field->value));
      case 'date':
        return strftime(($field->options ? $field->options : '%Y-%m-%d'), strtotime($field->value));
      case 'textbox':
      default:
        return htmlspecialchars($field->value);
    }
  }
  
  /**
   * Check if the user can register to the specified xref. 
   *  
   * Returns an object with properties canregister and status
   * 
   * @param $xref_id
   * @param $user_id
   * @return object (canregister, status)
   */
  function canRegister($xref_id, $user_id = null)
  {
    $db = & JFactory::getDBO();
    $user = & JFactory::getUser($user_id);
    $result = new stdclass();
    $result->canregister = 1;
    
    $query = ' SELECT x.dates, x.times, x.enddates, x.endtimes, x.maxattendees, x.maxwaitinglist, x.registrationend, e.registra, e.max_multi_signup '
           . ' FROM #__redevent_event_venue_xref AS x '
           . ' INNER JOIN #__redevent_events AS e ON x.eventid = e.id '
           . ' WHERE x.id='. $db->Quote($xref_id)
            ;
    $db->setQuery($query);
    $event = & $db->loadObject();
    
    // first, let's check the thing that don't need database queries
    if (!$event->registra)
    {
      $result->canregister = 0;
      $result->status = JTEXT::_('NO REGISTRATION FOR THIS EVENT');
      return $result;
    }
    else if (!empty($event->registrationend) && $event->registrationend != '0000-00-00 00:00:00')
    {
      if ( strtotime($event->registrationend) < time() )
      {
        $result->canregister = 0;
        $result->status = JTEXT::_('REGISTRATION IS OVER');
        return $result;
      }
    }
    else if (!empty($event->dates) && strtotime($event->dates .' '. $event->times) < time())
    {
      // it's separated from previous case so that it is not checked if a registration end was set
      $result->canregister = 0;
      $result->status = JTEXT::_('REGISTRATION IS OVER');
      return $result;
    }

    // now check the max registrations and waiting list
    if ($event->maxattendees)
    {
      // get places taken
      $q = "SELECT waitinglist, COUNT(id) AS total
          FROM #__rwf_submitters
          WHERE xref = ". $db->Quote($xref_id)."
          AND confirmed = 1
          GROUP BY waitinglist";
      $db->setQuery($q);
      $res = $db->loadObjectList('waitinglist');
      $event->registered = (isset($res[0]) ? $res[0]->total : 0) ;
      $event->waiting = (isset($res[1]) ? $res[1]->total : 0) ;
      
      if ($event->maxattendees <= $event->registered && $event->maxwaitinglist <= $event->waiting)
      {
        $result->canregister = 0;
        $result->status = JTEXT::_('EVENT FULL');
        return $result;
      }
    }
    
    // then the max registration per user
    if ($user->get('id'))
    {
      $q = "SELECT COUNT(s.id) AS total
          FROM #__rwf_submitters AS s
          INNER JOIN #__redevent_register AS r USING(submit_key)
          WHERE s.xref = ". $db->Quote($xref_id) ."
          AND s.confirmed = 1
          AND r.uid = ". $db->Quote($user->get('id')) ."
          ";
      // if there is a submit key set, it means we are reviewing, so we need to discard this submit_key from the count.
      if (JRequest::getVar('submit_key')) {
        $q .= '  AND s.submit_key <> '. $db->Quote(JRequest::getVar('submit_key', ''));
      }
      $db->setQuery($q);
      $event->userregistered = $db->loadResult();
      
      // in case this is a review, user has already registered... but not finished yet.
      if ($event->userregistered && JRequest::getVar('event_task') == 'review') {
        $event->userregistered--;
      }
    }
    else
    {
      $event->userregistered = 0;
    }
    
    if ($event->userregistered >= ($event->max_multi_signup ? $event->max_multi_signup : 1) )
    {
      $result->canregister = 0;
      $result->status = JTEXT::_('USER MAX REGISTRATION REACHED');
      return $result;
    }
        
    return $result;
  }
  
  function canUnregister($xref_id, $user_id = null)
  {
    $db = & JFactory::getDBO();
    $user = & JFactory::getUser($user_id);
    
    // if user is not logged, he can't unregister
    if (!$user->get('id')) {
      return false;
    }    
    
    $query = ' SELECT x.dates, x.times, x.enddates, x.endtimes, x.registrationend, e.unregistra '
           . ' FROM #__redevent_event_venue_xref AS x '
           . ' INNER JOIN #__redevent_events AS e ON x.eventid = e.id '
           . ' WHERE x.id='. $db->Quote($xref_id)
            ;
    $db->setQuery($query);
    $event = & $db->loadObject();    
    
    // check if unregistration is allowed
    if (!$event->unregistra) {
      return false;
    }
    
    if (!empty($event->registrationend) && $event->registrationend != '0000-00-00 00:00:00')
    {
      if ( strtotime($event->registrationend) < time() )
      {
        // REGISTRATION IS OVER
        return false;
      }
    }
    else if (!empty($event->dates) && strtotime($event->dates .' '. $event->times) < time())
    {
      // it's separated from previous case so that it is not checked if a registration end was set
      // REGISTRATION IS OVER
      return false;
    }
    
    return true;
  }
  
  /**
   * this function is used to return the number of places left in event lists
   * 
   * it requires the input object to have the properties registra, registrationend, dates, times, maxattendees, registered
   * 
   * @param object xref
   * @return string
   */ 
  function getRemainingPlaces($xref)
  {
    // only display for events were registrations still open
    if (!$xref->registra) {
      return '-';      
    }
    if (    (strtotime($xref->registrationend) && strtotime($xref->registrationend) < time())
         || strtotime($xref->dates . ' ' . $xref->times) < time() ) 
    {
      return '-';
    }
    
    // if there is no limit...
    if (!$xref->maxattendees) 
    {
      return '-';
    }
    return $xref->maxattendees - $xref->registered;
  }
}
?>