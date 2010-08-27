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
 * ICS CategoryEvents View class of the redEVENT component
 *
 * @package Joomla
 * @subpackage redEVENT
 * @since 2.0
 */
class RedeventViewCategoryEvents extends JView
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
		$model = $this->getModel();
		$model->setLimit($settings->params->get('ical_max_items', 100));
		$model->setLimitstart(0);
		$rows = & $model->getData();
		
		$catid = JRequest::getInt('id');
		
		$vcal = new vcalendar();                          // initiate new CALENDAR
		$vcal->setProperty('unique_id', 'category'.$catid.'@'.$mainframe->getCfg('sitename'));
		$vcal->setProperty( "calscale", "GREGORIAN" ); 
    $vcal->setProperty( 'method', 'PUBLISH' );
    if ($timezone_name) {
    	$vcal->setProperty( "X-WR-TIMEZONE", $timezone_name ); 
    }
		$vcal->setConfig( "filename", "category".$catid.".ics" );
		
		foreach ( $rows as $row )
		{					
			// get categories names
			$categories = array();
			foreach ($row->categories as $c) {
				$categories[] = $c->catname;
			}
			      		
			// format date
			$date = ELOutput::getIcalDateArray($row->dates, $row->times);
			$dateparam = array();
			if (isset($date['hour'])) {
				$date['tz'] = $utcoffset;
				$dateparam['VALUE'] = 'DATE-TIME';
			}
			else {
				$dateparam['VALUE'] = 'DATE';				
			}
				
			// end date
			$date_end = ELOutput::getIcalDateArray($row->enddates, $row->endtimes);
			$dateendparam = array();
			if (isset($date_end['hour'])) {
				$date_end['tz'] = $utcoffset;
				$dateendparam['VALUE'] = 'DATE-TIME';
			}
			else {
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
			$e->setProperty( 'summary', utf8_encode($row->title) );           // title
			$e->setProperty( 'categories', utf8_encode( implode(', ', $categories)) );           // categorize
			$e->setProperty( 'dtstart', $date, $dateparam );
			if (count($date_end)) {
				$e->setProperty( 'dtend', $date_end, $dateendparam );
			}
			$e->setProperty( 'description', utf8_encode($description) );    // describe the event
			$e->setProperty( 'location', utf8_encode($row->venue.' / '.$row->city) ); // locate the event
			$e->setProperty( 'url', $link );
			$e->setProperty( 'uid', 'session'.$row->xref.'@'.$mainframe->getCfg('sitename') );
			$vcal->addComponent( $e );                    // add component to calendar
		}
		$vcal->returnCalendar();                       // generate and redirect output to user browser
		echo $vcal->createCalendar(); // debug
	}
}
?>