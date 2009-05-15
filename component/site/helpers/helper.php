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
				$query = 'DELETE FROM #__redevent_event_venue_xref WHERE DATE_SUB(NOW(), INTERVAL '.$elsettings->minus.' DAY) > (IF (enddates <> '.$nulldate.', enddates, dates))';
				$db->SetQuery( $query );
				$db->Query();
			}

			//Set state archived of outdated events
			if ($elsettings->oldevent == 2) {
				$query = 'UPDATE #__redevent_event_venue_xref SET published = -1 WHERE DATE_SUB(NOW(), INTERVAL '.$elsettings->minus.' DAY) > (IF (enddates <> '.$nulldate.', enddates, dates))';
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
			return floor($duration / (3600 * 24)) . ' ' . JText::_('Days');
		}
		else if ($duration == 3600 * 24) {
      return '1' . ' ' . JText::_('Day');			
		}
		else {
			return floor($duration / 3600) . JText::_('LOC_H') . sprintf('%02d', floor(($duration % 3600) / 60));
		}
	}
}
?>