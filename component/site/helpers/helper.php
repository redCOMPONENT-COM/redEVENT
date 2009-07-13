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
	function cleanup()
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

		if ( $nrdaysnow > $nrdaysupdate ) {

			$db			= & JFactory::getDBO();

			$nulldate = '0000-00-00';
			/* Turn off recurrence */
			if (0) {
				$query = 'SELECT * FROM #__redevent_events WHERE DATE_SUB(NOW(), INTERVAL '.$elsettings->minus.' DAY) > (IF (enddates <> '.$nulldate.', enddates, dates)) AND recurrence_number <> "0" AND recurrence_type <> "0" AND `published` = 1';
				$db->SetQuery( $query );
				$recurrence_array = $db->loadAssocList();
	
				foreach($recurrence_array as $recurrence_row) {
					$insert_keys = '';
					$insert_values = '';
					$wherequery = '';
	
					// get the recurrence information
					$recurrence_number = $recurrence_row['recurrence_number'];
					$recurrence_type = $recurrence_row['recurrence_type'];
	
					$recurrence_row = redEVENTHelper::calculate_recurrence($recurrence_row);
					if (($recurrence_row['dates'] <= $recurrence_row['recurrence_counter']) || ($recurrence_row['recurrence_counter'] == "0000-00-00")) {
	
						// create the INSERT query
						foreach ($recurrence_row as $key => $result) {
							if ($key != 'id') {
								if ($insert_keys != '') {
									if (redEVENTHelper::where_table_rows($key)) {
										$wherequery .= ' AND ';
									}
									$insert_keys .= ', ';
									$insert_values .= ', ';
								}
								$insert_keys .= $key;
								if (($key == "enddates" || $key == "times" || $key == "endtimes") && $result == "") {
									$insert_values .= "NULL";
									$wherequery .= '`'.$key.'` IS NULL';
								} else {
									$insert_values .= "'".$result."'";
									if (redEVENTHelper::where_table_rows($key)) {
										$wherequery .= '`'.$key.'` = "'.$result.'"';
									}
	
								}
							}
						}
	
						$query = 'SELECT id FROM #__redevent_events WHERE '.$wherequery.';';
						$db->SetQuery( $query );
	
						if (count($db->loadAssocList()) == 0) {
							$query = 'INSERT INTO #__redevent_events ('.$insert_keys.') VALUES ('.$insert_values.');';
							$db->SetQuery( $query );
							$db->Query();
						}
					}
				}
			}

			//delete outdated events
			if ($elsettings->oldevent == 1) {
				// lists event_id for which we are going to delete xrefs
				$query = ' SELECT x.eventid FROM #__redevent_event_venue_xref AS x '
               . ' WHERE x.dates IS NOT NULL AND DATE_SUB(NOW(), INTERVAL '.$elsettings->minus.' DAY) > (IF (x.enddates <> '.$nulldate.', x.enddates, x.dates)) '
               ;
        $db->SetQuery( $query );
        $event_ids = $db->loadResultArray();
        
        if (!count($event_ids)) {
        	return true;
        }
        
				$query = ' DELETE x FROM #__redevent_event_venue_xref AS x '
				       . ' WHERE x.dates IS NOT NULL AND DATE_SUB(NOW(), INTERVAL '.$elsettings->minus.' DAY) > (IF (x.enddates <> '.$nulldate.', x.enddates, x.dates)) '
				       ;
				$db->SetQuery( $query );
				$db->Query();
				
				// now delete the events with no more xref
        $query = ' DELETE e FROM #__redevent_events AS e '
               . ' LEFT JOIN #__redevent_event_venue_xref AS x ON x.eventid = e.id '
               . ' WHERE x.id IS NULL '
               . '   AND e.id IN (' . implode(', ', $event_ids) . ')'
               ;
        $db->SetQuery( $query );
        $db->Query();			
				
			}

			//Set state archived of outdated events
			if ($elsettings->oldevent == 2) {
        // lists event_id for which we are going to archive xrefs
        $query = ' SELECT x.eventid FROM #__redevent_event_venue_xref AS x '
               . ' WHERE x.dates IS NOT NULL AND DATE_SUB(NOW(), INTERVAL '.$elsettings->minus.' DAY) > (IF (x.enddates <> '.$nulldate.', x.enddates, x.dates)) '
               ;
        $db->SetQuery( $query );
        $event_ids = $db->loadResultArray();
        
        if (!count($event_ids)) {
          return true;
        }
        
        // update xref to archive
				$query = ' UPDATE #__redevent_event_venue_xref AS x '
				       . ' SET x.published = -1 '
				       . ' WHERE x.dates IS NOT NULL AND DATE_SUB(NOW(), INTERVAL '.$elsettings->minus.' DAY) > (IF (x.enddates <> '.$nulldate.', x.enddates, x.dates))';
				$db->SetQuery( $query );
				$db->Query();
								
        // update events to archive (if no more published xref)
        $query = ' UPDATE #__redevent_events AS e '
               . ' LEFT JOIN #__redevent_event_venue_xref AS x ON x.eventid = e.id AND x.published <> -1 '
               . ' SET e.published = -1 '
               . ' WHERE x.id IS NULL '
               . '   AND e.id IN (' . implode(', ', $event_ids) . ')'
               ;
        $db->SetQuery( $query );
        $db->Query();
			}

			//Set timestamp of last cleanup
			$query = 'UPDATE #__redevent_settings SET lastupdate = '.time().' WHERE id = 1';
			$db->SetQuery( $query );
			$db->Query();
		}
	}

	/**
	 * this methode calculate the next date
	 */
	function calculate_recurrence($recurrence_row) {
		// get the recurrence information
		$recurrence_number = $recurrence_row['recurrence_number'];
		$recurrence_type = $recurrence_row['recurrence_type'];

		$day_time = 86400;	// 60 sec. * 60 min. * 24 h
		$week_time = 604800;// $day_time * 7days
		$date_array = redEVENTHelper::generate_date($recurrence_row['dates'], $recurrence_row['enddates']);


		switch($recurrence_type) {
			case "1":
				// +1 hour for the Summer to Winter clock change
				$start_day = mktime(1,0,0,$date_array["month"],$date_array["day"],$date_array["year"]);
				$start_day = $start_day + ($recurrence_number * $day_time);
				break;
			case "2":
				// +1 hour for the Summer to Winter clock change
				$start_day = mktime(1,0,0,$date_array["month"],$date_array["day"],$date_array["year"]);
				$start_day = $start_day + ($recurrence_number * $week_time);
				break;
			case "3":
				$start_day = mktime(1,0,0,($date_array["month"] + $recurrence_number),$date_array["day"],$date_array["year"]);;
				break;
			default:
				$weekday_must = ($recurrence_row['recurrence_type'] - 3);	// the 'must' weekday
				if ($recurrence_number < 5) {	// 1. - 4. week in a month
					// the first day in the new month
					$start_day = mktime(1,0,0,($date_array["month"] + 1),1,$date_array["year"]);
					$weekday_is = date("w",$start_day);							// get the weekday of the first day in this month

					// calculate the day difference between these days
					if ($weekday_is <= $weekday_must) {
						$day_diff = $weekday_must - $weekday_is;
					} else {
						$day_diff = ($weekday_must + 7) - $weekday_is;
					}
					$start_day = ($start_day + ($day_diff * $day_time)) + ($week_time * ($recurrence_number - 1));
				} else {	// the last or the before last week in a month
					// the last day in the new month
					$start_day = mktime(1,0,0,($date_array["month"] + 2),1,$date_array["year"]) - $day_time;
					$weekday_is = date("w",$start_day);
					// calculate the day difference between these days
					if ($weekday_is >= $weekday_must) {
						$day_diff = $weekday_is - $weekday_must;
					} else {
						$day_diff = ($weekday_is - $weekday_must) + 7;
					}
					$start_day = ($start_day - ($day_diff * $day_time));
					if ($recurrence_number == 6) {	// before last?
						$start_day = $start_day - $week_time;
					}
				}
				break;
		}
		$recurrence_row['dates'] = date("Y-m-d", $start_day);
		if ($recurrence_row['enddates']) {
			$recurrence_row['enddates'] = date("Y-m-d", $start_day + $date_array["day_diff"]);
		}
		return $recurrence_row;
	}

	/**
	 * this method generate the date string to a date array
	 *
	 * @var string the date string
	 * @return array the date informations
	 * @access public
	 */
	function generate_date($startdate, $enddate) {
		$startdate = explode("-",$startdate);
		$date_array = array("year" => $startdate[0],
							"month" => $startdate[1],
							"day" => $startdate[2],
							"weekday" => date("w",mktime(1,0,0,$startdate[1],$startdate[2],$startdate[0])));
		if ($enddate) {
			$enddate = explode("-", $enddate);
			$day_diff = (mktime(1,0,0,$enddate[1],$enddate[2],$enddate[0]) - mktime(1,0,0,$startdate[1],$startdate[2],$startdate[0]));
			$date_array["day_diff"] = $day_diff;
		}
		return $date_array;
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
	 * use only some importent keys of the redevent_events - database table for the where query
	 *
	 * @param string $key
	 * @return boolean
	 */
	function where_table_rows($key) {
		if ($key == 'locid' ||
			$key == 'catsid' ||
			$key == 'dates' ||
			$key == 'enddates' ||
			$key == 'times' ||
			$key == 'endtimes' ||
			$key == 'alias' ||
			$key == 'created_by') {
			return true;
		} else {
			return false;
		}
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
		if (!$event->times || $event->times == '00:00:00') {
			$start = strtotime($event->dates. ' ' . $event->times);
		}
		else {
      $start = strtotime($event->dates. ' ' . $event->times);			
		}
		
		// end time in seconds
    if (!$event->enddates || $event->enddates == '0000-00-00') {
    	// same day
      if (!$event->endtimes || $event->endtimes == '00:00:00') {
        // we set it to end of the day, user should set it anyway
        $end = strtotime($event->dates. ' 00:00') + 3600 * 24;         
      }
      else {
        $end = strtotime($event->dates . ' ' . $event->endtimes);      	
      }
    }
    else {
      if (!$event->endtimes || $event->endtimes == '00:00:00') {
        // we set it to end of the day
        $end = strtotime($event->enddates. ' 00:00') + 3600 * 24;
      }
      else {
        $end = strtotime($event->enddates . ' ' . $event->endtimes);
      }    	
    }
    
    $duration = $end - $start;
		if ($duration > 3600 * 24) {
			$day = floor($duration / (3600 * 24));
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
}
?>