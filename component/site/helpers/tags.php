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


class redEVENT_tags {
	
	private $_xref;
	private $_eventid;
	private $_venueid;
	private $_maxattendees;
	private $_maxwaitinglist;
	protected $_eventlinks = null;
	private $_data = false;
	
	public function __construct() {
		$xref = JRequest::getVar('xref', false);
		if ($xref) {
			$this->_xref = $xref;
			$db = JFactory::getDBO();
			$q = "SELECT eventid, venueid, maxattendees, maxwaitinglist FROM #__redevent_event_venue_xref WHERE id = ".$xref;
			$db->setQuery($q);
			list($this->_eventid, $this->_venueid, $this->_maxattendees, $this->_maxwaitinglist) = $db->loadRow();
		}
		else $this->_xref = false;
	}
	
	/**
	 * Substitute tags with the correct info
	 *
	 * Supported tags are:
	 * [event_description]
	 * [event_title]
	 * [price]
	 * [credits]
	 * [code]
	 * [redform]
	 * [inputname] Writes an input box for a name
	 * [inputemail] Writes an input box for an e-mail address
	 * [submit] Writes a submit button
	 * [event_info_text]
	 * [time]
	 * [date]
	 * [duration]
	 * [venue]
	 * [city]
	 * [username]
	 * [useremail]
	 * [regurl]
	 * [eventplaces]
	 * [waitinglistplaces]
	 * [eventplacesleft]
	 * [waitinglistplacesleft] 
	 */
	public function ReplaceTags($page) {
		if ($this->_xref) {
			/* Load the event links */
			if (is_null($this->_eventlinks)) $this->getEventLinks();
			if (count($this->_eventlinks) == 0) return '';
			$this->getData();
			if ($this->_data) {
				/* Load the signup links */
				$venues_html = $this->SignUpLinks();
				
				/* Load custom tags */
				$customtags = array();
				preg_match_all("/\[(.+?)\]/", $page, $customtags);
				$customdata = $this->getCustomData($customtags);
				
				/* Only do the event description if it is in on the page */
				$event_description = '';
				/* Fix the tags of the event description */
				$findcourse = array('[venues]','[price]','[credits]', '[code]');
				$replacecourse = array($venues_html, 
								ELOutput::formatprice($this->_data->course_price), 
								$this->_data->course_credit,
								$this->_data->course_code);
				$event_description = str_replace($findcourse, $replacecourse, $this->_data->datdescription); 
				
				/* Only do the course description if it is in on the page */
				$event_info_description = '';
				/* Create event description without venue links */
				$findcourse = array('[venues]','[price]','[credits]', '[code]');
				$replacecourse = array('', 
								ELOutput::formatprice($this->_data->course_price), 
								$this->_data->course_credit,
								$this->_data->course_code);
				$event_info_description = str_replace($findcourse, $replacecourse, $this->_data->datdescription);
				
				/* Get waitinglist information */
				$waitinglist = $this->getWaitingList();
				if (isset($waitinglist[0])) $eventplacesleft = $this->_maxattendees - $waitinglist[0]->total;
				else $eventplacesleft = $this->_maxattendees;
				if (isset($waitinglist[1])) $waitinglistplacesleft = $this->_maxwaitinglist - $waitinglist[1]->total;
				else $waitinglistplacesleft = $this->_maxwaitinglist;
				/* Create the redFORM */
				/* Include redFORM */
				$redform = '';
				JPluginHelper::importPlugin( 'content' );
				$dispatcher = JDispatcher::getInstance();
				$form = new stdClass();
				$form->text = '{redform}'.$this->_data->redform_id.','.$this->_data->max_multi_signup.'{/redform}';
				$form->eventid = $this->_eventid;
				$tpl = JRequest::getVar('page', false);
				switch ($tpl) {
					case 'confirmation':
						$form->task = 'review';
						break;
					default:
						$form->task = 'userregister';
						break;
				}
				
				$results = $dispatcher->trigger('PrepareEvent', array($form));
				if (!isset($results[1])) {
					$redform = JText::_('REGISTRATION_NOT_POSSIBLE');
				}
				else $redform = $results[1];
				
				/* Form fields */
				$name = '<div id="divsubemailname"><div class="divsubemailnametext">'.JText::_('NAME').'</div><div class="divsubemailnameinput"><input type="text" name="subemailname" /></div></div>';
				$email = '<div id="divsubemailaddress"><div class="divsubemailaddresstext">'.JText::_('EMAIL').'</div><div class="divsubemailaddressinput"><input type="text" name="subemailaddress" /></div></div>';
				$submit = '<div id="disubemailsubmit"><input type="submit" value="'.JText::_('SUBMIT').'" /></div>';
				$time = ELOutput::formattime($this->_data->dates, $this->_data->times).' - '.ELOutput::formattime($this->_data->enddates, $this->_data->endtimes);
				$date = ELOutput::formatdate($this->_data->dates, $this->_data->times);
				$price = ELOutput::formatprice($this->_data->course_price);
				$duration = $this->_data->duration;
				if ($this->_data->duration == 1) $duration .= JText::_('DAY');
				else if ($this->_data->duration > 1) $duration .= JText::_('DAYS');
				$username = JRequest::getVar('subemailname', '');
				$useremail = JRequest::getVar('subemailaddress', '');
				$uri = JURI::getInstance();
				$regurl = JRoute::_($uri->toString());
				
				/* Clean up some tags */
				$findoffer = array('[event_description]', '[event_title]', '[price]', '[credits]', '[code]', '[redform]', '[inputname]', '[inputemail]', '[submit]',
									'[event_info_text]', '[time]', '[date]', '[duration]', '[venue]', '[city]', '[username]', '[useremail]', '[venues]','[regurl]',
									'[eventplaces]', '[waitinglistplaces]', '[eventplacesleft]', '[waitinglistplacesleft]');
				$replaceoffer = array($event_description, $this->_data->title, $price, $this->_data->course_credit, $this->_data->course_code, $redform,
									$name, $email, $submit, $event_info_description, $time, $date, $duration, $this->_data->venue, $this->_data->location,
									$username, $useremail, $venues_html, $regurl, $this->_maxattendees, $this->_maxwaitinglist, $eventplacesleft, $waitinglistplacesleft);
				/* First tag replacement */
				$message = str_replace($findoffer, $replaceoffer, $page);
				foreach ($customdata as $tag => $data) {
					$data->text_field = str_replace($findoffer, $replaceoffer, $data->text_field);
					$message = str_ireplace('['.$tag.']', $data->text_field, $message);
				}
				return ELOutput::ImgRelAbs($message);
			}
			else return '';
		}
		else return '';
	}
	
	/**
	 * This function loads the basic data needed to replace tags
	 */
	private function getData() {
		foreach ($this->_eventlinks AS $key => $eventlink) {
			if ($eventlink->xref == $this->_xref) {
				$this->_data = $eventlink;
			}
		}
	}
	
	/**
	 * Load the HTML table with signup links
	 */
	private function SignUpLinks() {
		ob_start();
		if (JRequest::getVar('format') == 'raw') include('courseinfo_pdf.php');
		else include('courseinfo.php');
		$contents = ob_get_contents();
		ob_end_clean();
		return $contents;
	}
	
	/**
	 * Load all venues and their signup links
	 */
	private function getEventLinks() {
		$db = JFactory::getDBO();
		$q = "SELECT e.*, IF (x.course_credit = 0, '', x.course_credit) AS course_credit, x.course_price, x.id AS xref, x.dates, x.enddates, x.times, x.endtimes, v.venue, x.venueid,
					v.city AS location,
					v.country, DATEDIFF(x.enddates, x.dates)+1 AS duration,
					UNIX_TIMESTAMP(x.dates) AS unixdates
			FROM #__redevent_venues v
			LEFT JOIN #__redevent_event_venue_xref x
			ON x.venueid = v.id
			LEFT JOIN #__redevent_events e
			ON x.eventid = e.id
			WHERE x.published = 1
			AND x.eventid IN (".$this->_eventid.")
			";
		$db->setQuery($q);
		$this->_eventlinks = $db->loadObjectList();
	}
	
	/**
	 * Load the number of people that are confirmed and if they are on or off
	 * the waitinglist
	 */
	private function getWaitingList() {
		$db = JFactory::getDBO();
		$q = "SELECT waitinglist, COUNT(id) AS total
			FROM #__rwf_submitters
			WHERE xref = ".$this->_xref."
			AND confirmed = 1
			GROUP BY waitinglist";
		$db->setQuery($q);
		return $db->loadObjectList('waitinglist');
	}
	
	/**
	 * Load the number of people that are confirmed and if they are on or off
	 * the waitinglist
	 */
	private function getCustomData($customtags) {
		$db = JFactory::getDBO();
		$q = "SELECT text_name, text_field
			FROM #__redevent_textlibrary
			WHERE text_name ='". implode( "' OR text_name ='", $customtags[1] )."'";
		$db->setQuery($q);
		return $db->loadObjectList('text_name');
	}

}
?>