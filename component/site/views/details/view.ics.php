<?php
/**
 * @version 1.0 $Id: view.html.php 1625 2009-11-18 16:54:27Z julien $
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
defined( '_JEXEC' ) or die( 'Restricted access' );

require_once JPATH_SITE.DS.'components'.DS.'com_redevent'.DS.'classes'.DS.'iCalcreator.class.php';

jimport( 'joomla.application.component.view');

/**
 * CSV Details View class of the redEVENT component
 *
 * @package Joomla
 * @subpackage redEVENT
 * @since 2.0
 */
class RedeventViewDetails extends JView
{
	/**
	 * Creates the output for the details view
	 *
 	 * @since 2.0
	 */
	function display($tpl = null)
	{		
		$mainframe = &JFactory::getApplication();
    
		$offset = (float) $mainframe->getCfg('offset');
		$timezone_name = redEVENTHelper::getTimeZone($offset);
		$hours = ($offset >= 0) ? floor($offset) : ceil($offset);
		$mins = abs($offset - $hours) * 60;
		$utcoffset = sprintf('%+03d%02d00', $hours, $mins);
		
		$settings = redEVENTHelper::config();
		
		// Get data from the model
		$row     = $this->get('Details');		
//		echo '<pre>';print_r($row); echo '</pre>';exit;
		
		// get categories names
		$categories = array();
		foreach ($row->categories as $c) {
			$categories[] = $c->catname;
		}
		
		$vcal = new vcalendar();                          // initiate new CALENDAR
		$vcal->setProperty('unique_id', 'session'.$row->xref.'@'.$mainframe->getCfg('sitename'));
		$vcal->setProperty( "calscale", "GREGORIAN" ); 
    $vcal->setProperty( 'method', 'PUBLISH' );
    if ($timezone_name) {
    	$vcal->setProperty( "X-WR-TIMEZONE", $timezone_name ); 
    }
		$vcal->setConfig( "filename", "event".$row->xref.".ics" );
                
          
		//Format date
		if (!ereg('([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})',$row->dates, $start_date)) {
			continue;
		}
		$date = array('year' => (int) $start_date[1], 'month' => (int) $start_date[2], 'day' => (int) $start_date[3]);
		$dateparam = array();
			
		//Format time
		if ($row->times && ereg('([0-9]{2}):([0-9]{2}):([0-9]{2})',$row->times, $start_time)) {
			$date['hour'] = $start_time[1];
			$date['min'] = $start_time[2];
			$date['sec'] = $start_time[3];
			$date['tz'] = $utcoffset;
			$dateparam['VALUE'] = 'DATE-TIME';
		}
		else {
			// all day event
			$dateparam['VALUE'] = 'DATE';
		}
			
		// end date
		$date_end = array();
		$dateendparam = array();
		// end date
		if ($row->enddates && ereg('([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})',$row->enddates, $end_date)) {
			$date_end = array('year' => $end_date[1], 'month' => $end_date[2], 'day' => $end_date[3]);
		}
		if ($row->endtimes && ereg('([0-9]{2}):([0-9]{2}):([0-9]{2})',$row->endtimes, $end_time))
		{
			if (!count($date_end)) {
				// no end date, so it must be the same day...
				$date_end = array('year' => $date['year'], 'month' => $date['month'], 'day' => $date['day']);
			}
			$date_end['hour'] = $end_time[1];
			$date_end['min'] = $end_time[2];
			$date_end['sec'] = $end_time[3];
			$dateendparam['VALUE'] = 'DATE-TIME';
			$date_end['tz'] = $utcoffset;
		}
		if ($row->enddates && !$row->endtimes) {
			// all day event
			$dateendparam['VALUE'] = 'DATE';
		}

		// item description text
		$description = $row->title.'\\n';
		$description .= JText::_( 'CATEGORY' ).': '.implode(', ', $categories).'\\n';

		// url link to event
		$link = JURI::base().RedeventHelperRoute::getDetailsRoute($row->slug, $row->xref);
		$link = JRoute::_( $link );
		$description .= JText::_( 'COM_REDEVENT_ICS_LINK' ).': '.$link.'\\n';
			
		$e = new vevent();              // initiate a new EVENT
		$e->setProperty( 'summary', $row->title );           // title
		$e->setProperty( 'categories', implode(', ', $categories) );           // categorize
		$e->setProperty( 'dtstart', $date, $dateparam );
		if (count($date_end)) {
			$e->setProperty( 'dtend', $date_end, $dateendparam );
		}
		$e->setProperty( 'description', $description );    // describe the event
		$e->setProperty( 'location', $row->venue.' / '.$row->city ); // locate the event
		$e->setProperty( 'url', $link );
		$e->setProperty( 'uid', 'session'.$row->xref.'@'.$mainframe->getCfg('sitename') );
		$vcal->addComponent( $e );                    // add component to calendar

		$vcal->returnCalendar();                       // generate and redirect output to user browser
		echo $vcal->createCalendar(); // debug
	}
}
?>