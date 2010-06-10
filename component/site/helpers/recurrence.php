<?php
/**
 * @version 1.0 $Id: helper.php 270 2009-06-17 12:37:35Z julien $
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
 * Helper class for recurrence calculations
 * @package    Notes
 * @subpackage com_notes
 */
class RedeventHelperRecurrence
{
  
  /**
   * parses the data from editxref form, and returns the corresponding rrule
   * 
   * @param array posted data 
   * @return string rrule
   */
  function parsePost($data)
  {
    $rrule = '';
    switch($data['recurrence_type'])
    {
      case 'DAILY':
        $rrule = RedeventHelperRecurrence::_parseDaily($data);
        break;
      case 'WEEKLY':
        $rrule = RedeventHelperRecurrence::_parseWeekly($data);
        break;
      case 'MONTHLY':
        $rrule = RedeventHelperRecurrence::_parseMonthly($data);
        break;
      case 'YEARLY':
        $rrule = RedeventHelperRecurrence::_parseYearly($data);
        break;
      case 'NONE':
      default:
        $rrule = '';
        break;
    } 
    
    return $rrule;
  }
  
  /**
   * returns daily parsed rule
   * 
   * @param array posted data
   * @return string rrule
   */
  function _parseDaily($data)
  {
    $rrule = "RRULE:FREQ=DAILY;INTERVAL=" .$data['recurrence_interval'].';';
    
    if ($data['rutype'] == 'count')
    {
      $rrule .= "COUNT=". $data['recurrence_repeat_count'];
    }
    else 
    {
      $rrule .= "UNTIL=". RedeventHelperRecurrence::convertDate($data['recurrence_repeat_until']);      
    }
    return $rrule;
  }

  /**
   * returns weekly parsed rule
   * 
   * @param array posted data
   * @return string rrule
   */
  function _parseWeekly($data)
  {
    $params   = & JComponentHelper::getParams('com_redevent');
    
    $rrule = "RRULE:FREQ=WEEKLY;INTERVAL=" .$data['recurrence_interval'].';';
    
    // limit
    if ($data['rutype'] == 'count')
    {
      $rrule .= "COUNT=". $data['recurrence_repeat_count'].';';
    }
    else 
    {
      $rrule .= "UNTIL=". RedeventHelperRecurrence::convertDate($data['recurrence_repeat_until']).';';    
    }
    // week start
    $rrule .= "WKST=". $params->get('week_start', 'MO').';';
    // selected days
    $rrule .= "BYDAY=". implode(',', $data['wweekdays']).';';
    
    return $rrule;
  }

  /**
   * returns monthly parsed rule
   * 
   * @param array posted data
   * @return string rrule
   */
  function _parseMonthly($data)
  {
    $params   = & JComponentHelper::getParams('com_redevent');
    
    $rrule = "RRULE:FREQ=MONTHLY;INTERVAL=" .$data['recurrence_interval'].';';
    
    // limit
    if ($data['rutype'] == 'count')
    {
      $rrule .= "COUNT=". $data['recurrence_repeat_count'].';';
    }
    else 
    {
      $rrule .= "UNTIL=". RedeventHelperRecurrence::convertDate($data['recurrence_repeat_until']).';';    
    }
    
    if ($data['monthtype'] == 'byday')
    {
      // week start
      $rrule .= "WKST=". $params->get('week_start', 'MO').';';
      // selected weeks, normal order
      $days = array();
      foreach($data['mweeks'] as $week)
      {
        foreach ($data['mweekdays'] as $day)
        {
          $days[] = $week.$day;
        }
      }
      foreach($data['mrweeks'] as $week)
      {
        foreach ($data['mrweekdays'] as $day)
        {
          $days[] = '-'.$week.$day;
        }
      }
      $rrule .= "BYDAY=". implode(',', $days).';';
    }
  
    if ($data['monthtype'] == 'bymonthday')
    {      
      $days = array();
      $reverse = (isset($data['reverse_bymonthday'])) ? true : false;
      foreach(explode(',', $data['bymonthdays']) as $day)
      {
          $days[] = ($reverse ? '-' : '').((int) $day);
      }
      $rrule .= "BYDAY=". implode(',', $days).';';
    }
    
    return $rrule;
  }
  
/**
   * returns monthly parsed rule
   * 
   * @param array posted data
   * @return string rrule
   */
  function _parseYearly($data)
  {
    $params   = & JComponentHelper::getParams('com_redevent');
    
    $rrule = "RRULE:FREQ=YEARLY;INTERVAL=" .$data['recurrence_interval'].';';
    
    // limit
    if ($data['rutype'] == 'count')
    {
      $rrule .= "COUNT=". $data['recurrence_repeat_count'].';';
    }
    else 
    {
      $rrule .= "UNTIL=". RedeventHelperRecurrence::convertDate($data['recurrence_repeat_until']).';';    
    }
         
      $days = array();
      $reverse = (isset($data['reverse_byyearday'])) ? true : false;
      foreach(explode(',', $data['byyeardays']) as $day)
      {
          $days[] = ($reverse ? '-' : '').((int) $day);
      }
      $rrule .= "BYDAY=". implode(',', $days).';';
    
    return $rrule;
  }
  
  /**
   * return rrule as fields to be put in form
   * 
   * @param string $rrule
   * @return array fields
   */
  function getRule($rrule = null)
  {
    $rules = new rruleFields();
    
    if (!$rrule) {
      return $rules;
    }
    
    $parts = explode(';', $rrule);
    foreach ($parts as $p)
    {
      if (!strpos($p, '=')) {
        continue;
      }
      list($element, $value) = explode('=', $p);
      switch ($element)
      {
        case 'RRULE:FREQ':
          $rules->type = $value;
          break;
        case 'INTERVAL':
          $rules->interval = $value;
          break;
        case 'COUNT':
          $rules->until_type = 'count';
          $rules->count = $value;
          break;
        case 'UNTIL':
          $rules->until_type = 'until';
          $rules->until = RedeventHelperRecurrence::icalDatetotime($value);
          break;
        case 'BYDAY':
          $days = explode(',', $value);
          foreach ($days as $d) 
          {
            ereg('([-]*)([0-9]*)([A-Z]*)', $d, $res);
            $revert = ($res[1] == '-');
            if ($res[2] && $res[3]) {
              if ($revert) {
                if (!in_array($res[2], $rules->rweeks)) {
                  $rules->rweeks[] = $res[2];
                }
                if (!in_array($res[3], $rules->rweekdays)) {
                  $rules->rweekdays[] = $res[3];
                }
              }
              else {
                if (!in_array($res[2], $rules->weeks)) {
                  $rules->weeks[] = $res[2];
                }
                if (!in_array($res[3], $rules->weekdays)) {
                  $rules->weekdays[] = $res[3];
                }              
              }
            }
            else if ($res[2]) {
              $rules->bydays[] = $res[2];
              if ($rules->type == 'MONTHLY') {
                $rules->monthtype = 'bymonthdays';
              }
              if ($revert) {
                $rules->reverse_bydays = 1;
              }
            }
            else if ($res[3]) {
              if ($revert) {
                if (!in_array($res[3], $rules->rweekdays)) {
                  $rules->rweekdays[] = $res[3];
                }
              }
              else {
                if (!in_array($res[3], $rules->weekdays)) {
                  $rules->weekdays[] = $res[3];
                }                  
              }             
            }
          }
          break;
        default:
          break;
      }
    }
    
    return $rules;
  }
  
  function convertDate($date)
  {
    $convert = strftime('%Y%m%dT%H%M%S', strtotime($date));
    return $convert;
  }
  
  function icalDatetotime($date)
  {
    if (ereg('([0-9]{4})([0-9]{2})([0-9]{2})T([0-9]{2})([0-9]{2})([0-9]{2})(Z?)', $date, $res))
    {
      $res = mktime($res[4], $res[5], $res[6], $res[2], $res[3], $res[1]);
      return strftime('%Y-%m-%d %H:%M:%S', $res);
    }
    else {
      return false;
    }
  }
  
  function getnext($recurrence, $last_xref)
  {
    $rule = RedeventHelperRecurrence::getRule($recurrence);
    
    $params = & JComponentHelper::getParams('com_redevent');
    $week_start = $params->get('week_start', 'SU');

//    print_r($rule); 
//    print_r($last_xref);
        
    $new = false;
    
    // check the count
    if ($rule->until_type == 'count' && $last_xref->count >= $rule->count) {
      return false;          
    }
    
    $days_name = array('SU' => 'sunday', 'MO' => 'monday', 'TU' => 'tuesday', 'WE' => 'wednesday', 'TH' => 'thursday', 'FR' => 'friday', 'SA' => 'saturday');
    if ($week_start == 'SU') {
      $days_number = array('SU' => 0, 'MO' => 1, 'TU' => 2, 'WE' => 3, 'TH' => 4, 'FR' => 5, 'SA' => 6);
    }
    else {
      $days_number = array('SU' => 7, 'MO' => 1, 'TU' => 2, 'WE' => 3, 'TH' => 4, 'FR' => 5, 'SA' => 6);
    }
    $xref_start = strtotime($last_xref->dates);
    
    // get the next start timestamp
    switch ($rule->type)
    {
      case 'DAILY':
        $next_start = strtotime($last_xref->dates." +". $rule->interval ." day");
        break;
        
      case 'WEEKLY':
        // calculate next dates for all set weekdays
        $next = array();
        foreach ($rule->weekdays as $d) 
        {
          if ($week_start == 'SU') {
            $current = strftime('%w', $xref_start);
          }
          else {
            $current = strftime('%u', $xref_start);
          }
          if ($days_number[$d] > $current) {
            $next[] = strtotime('+1 '. $days_name[$d], strtotime($last_xref->dates));
          }
          else if ($days_number[$d] == $current) { // same day, look in next intervall, after this day
            $next[] = strtotime('+'. $rule->interval .' '. $days_name[$d], strtotime($last_xref->dates)+3600*24); 
          }  
          else { // in next intervall
            $next[] = strtotime('+'. $rule->interval .' '. $days_name[$d], strtotime($last_xref->dates));   
          }          
        }
        // the next one is the lowest value
        $next_start = min($next);               
        break;
        
      case 'MONTHLY':
        if ($rule->monthtype == 'bymonthdays') 
        {
          $current = strftime('%d', strtotime($last_xref->dates));
                    
          if (!$rule->reverse_bydays)
          {
            sort($rule->bydays);
            $next_day = null;
            foreach ($rule->bydays as $day)
            {
              if ($day > $current) {
                $next_day = $day;
                break;
              }
            }
            if ($next_day == null) // not this month => this month + interval month!
            {
              $year_month = strftime('%Y-%m', strtotime(date("F", strtotime($last_xref->dates)) .' 1 +'. $rule->interval ." month"));
              $next_start = strtotime($year_month.'-'.$rule->bydays[0]);
            }
            else {
              $year_month = strftime('%Y-%m', strtotime($last_xref->dates));
              $next_start = strtotime($year_month.'-'.$next_day);            
            }     
          }
          else 
          {
            $current_sec = strtotime($last_xref->dates);
            $next = array();
            foreach ($rule->bydays as $day) 
            {
              // we need to check the dates for this month, and the +interval month
              $dd = strtotime(date("F", strtotime($last_xref->dates)) .' 1 +1 month -'.$day. ' day');
              if ($dd > $current_sec) {
                $next[] = $dd;
              }
              $dd = strtotime(date("F", strtotime($last_xref->dates)) .' 1 +'.(1 + $rule->interval).' month -'.$day. ' day', strtotime($last_xref->dates));
              if ($dd > $current_sec) {
                $next[] = $dd;
              }
            }
            // the next is the closest, lower value
            $next_start = min($next);            
          }       
        }
        else 
        {
          // first day of this month
          $first_this = mktime(0, 0, 0, strftime('%m', $xref_start), 1, strftime('%Y', $xref_start));
          // last day of this month
          $last_this = mktime(0, 0, 0, strftime('%m', $xref_start)+1, 0, strftime('%Y', $xref_start));
          // first day of +interval month
          $first_next_interval = mktime(0, 0, 0, strftime('%m', $xref_start) + $rule->interval, 1, strftime('%Y', $xref_start));
          // last day of this month
          $last_next_interval = mktime(0, 0, 0, strftime('%m', $xref_start)+1 + $rule->interval, 0, strftime('%Y', $xref_start));
          
          $days = array();
//          print_r($rule);
          foreach ($rule->weeks as $week)
          {
            foreach ($rule->weekdays as $day)
            {
              $int_day = strtotime($week. ' ' . $days_name[$day], $first_this);
              if ($int_day > $xref_start && $int_day <= $last_this) {
                $days[] = $int_day;                
              }
              $int_day = strtotime($week. ' ' . $days_name[$day], $first_next_interval);
              if ($int_day > $xref_start && $int_day <= $last_next_interval) {
                $days[] = $int_day;                
              }
            }            
          }
          foreach ($rule->rweeks as $week)
          {
            foreach ($rule->rweekdays as $day)
            {
              $int_day = strtotime('-'.$week. ' ' . $days_name[$day], $last_this + 24*3600);
              if ($int_day > $xref_start && $int_day >= $first_this) {
                $days[] = $int_day;                
              }
              $int_day = strtotime('-'.$week. ' ' . $days_name[$day], $last_next_interval + 24*3600);
              if ($int_day > $xref_start && $int_day >= $first_next_interval) {
                $days[] = $int_day;                
              }
            }            
          }
          $next_start = min($days);
        }
        break;
        
      case 'YEARLY':
        $current = strtotime($last_xref->dates);
        
        if (empty($rule->bydays)) // in that case, use current date, plus a year
        {
          $next_start = mktime(0, 0, 0, strftime('%m', $current), strftime('%d', $current), strftime('%Y',  $current) + $rule->interval);        	
        }
        else
        {
	        if (!$rule->reverse_bydays)
	        {
	          sort($rule->bydays);
	          $next_day = $rule->bydays[0];
	          foreach ($rule->bydays as $day)
	          {
	            if ($day > $current) {
	              $next_day = $day;
	              break;
	            }
	          }
	          if ($next_day == $rule->bydays[0]) // not this year => this year + interval year!
	          {
	            $next_start = mktime(0, 0, 0, 1, $next_day, strftime('%Y', strtotime($last_xref->dates)) + 1);
	          }
	          else {
	            $next_start = mktime(0, 0, 0, 1, $next_day, strftime('%Y', strtotime($last_xref->dates)));
	          }
	        }
	        else
	        {
	          // total days in this year          
	          $total = strftime('%j', mktime(0, 0, 0, 1, 0, strftime('%Y', strtotime($last_xref->dates)) + 1));
	          $rev_days = array();
	          // get number in proper order
	          rsort($rule->bydays);
	          foreach ($rule->bydays as $day) {
	            $rev_days[] = $total - $day + 1;
	          }
	          
	          $next_day = null;
	          foreach ($rev_days as $day)
	          {
	            if ($day > $current) {
	              $next_day = $day;
	              break;
	            }
	          }
	          
	          if ($next_day == null) // not this year => this year + interval year!
	          {
	            $next_start = mktime(0, 0, 0, 1, -$rule->bydays[0], strftime('%Y', strtotime($last_xref->dates)) + 1 + $rule->interval);
	          }
	          else {
	            $next_start = mktime(0, 0, 0, 1, $next_day, strftime('%Y', strtotime($last_xref->dates)));
	          }
	        }
        }
        break;
        
      case 'NONE':
      default:
        break;
    }

    if (!isset($next_start) || !$next_start) {
      return false;
    }
    
    // check the until rule
    if ($rule->until_type == 'until' && strtotime(strftime('%Y-%m-%d', $next_start).' '.$last_xref->times) > strtotime($rule->until)) {
      return false;
    }
    
    // return the new occurence
    $new = clone $last_xref;
    
    unset($new->id);
    $delta = $next_start - strtotime($last_xref->dates);
    $new->dates = strftime('%Y-%m-%d', $next_start);
    if (strtotime($last_xref->enddates)) {
      $new->enddates = strftime('%Y-%m-%d', strtotime($last_xref->enddates) + $delta);
    }
    if (strtotime($last_xref->registrationend)) {
      $new->registrationend = strftime('%Y-%m-%d', strtotime($last_xref->registrationend) + $delta);
    }
    $new->count++;

//    print_r($new); 
//    exit;
    return $new;
  }
  
}

class rruleFields {
  
  /**
   * type of recurence: NONE, DAILY,WEEKLY, MONTHLY, YEARLY
   * @var string
   */
  var $type = 'NONE';
  
  /**
   * interval of repeatition
   * @var int
   */
  var $interval = 1;
  
  /**
   * type of repeatition limit: count (count), or until date (until)
   * @var unknown_type
   */
  var $until_type = 'count';
  
  /**
   * number of repeats
   * @var int
   */
  var $count = 10;
  
  /**
   * repeat limit date
   * @var string
   */
  var $until = null;
  
  /**
   * selected days for weekly repeat (list SU, MO, ...)
   * @var array
   */
  var $weekdays = array();
  /**
   * selected days for weekly repeat reverted (list SU, MO, ...)
   * @var array
   */
  var $rweekdays = array();
  
  /**
   * type of rule for month freq: bymonthday (int list: bymonthday), or by weekdays (byday)
   * @var string
   */
  var $monthtype = 'bymonthday';
  
  /**
   * array of days number
   * @var array
   */
  var $bydays = array();
  
  /**
   * count days from end
   * @var int
   */
  var $reverse_bydays = 0;
    
  /**
   * array of weeks numbers (1, 2, ...)
   * @var string
   */
  var $weeks = array();
  
  /**
   * array of weeks numbers (1, 2, ...), counted from end of the month
   * @var string
   */
  var $rweeks = array();
      
}

