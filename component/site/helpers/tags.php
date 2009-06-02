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
      $elsettings = redEVENTHelper::config();

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
				/* Include redFORM */
				$redform = '';
				if ($this->_data->redform_id > 0) {
					JPluginHelper::importPlugin( 'content' );
					$dispatcher = JDispatcher::getInstance();
					$form = new stdClass();
					$form->text = '{redform}'.$this->_data->redform_id.','.($this->_data->max_multi_signup ? $this->_data->max_multi_signup : 1).'{/redform}';
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
					// params for plugin
					$params = array();
					$params['show_submission_type_webform_formal_offer'] = $this->_data->show_submission_type_webform_formal_offer;		
									
					$results = $dispatcher->trigger('onPrepareEvent', array(& $form, $params));
          $redform = $form->text;
				}
				
				/* Form fields */
				$name = '<div id="divsubemailname"><div class="divsubemailnametext">'.JText::_('NAME').'</div><div class="divsubemailnameinput"><input type="text" name="subemailname" /></div></div>';
				$email = '<div id="divsubemailaddress"><div class="divsubemailaddresstext">'.JText::_('EMAIL').'</div><div class="divsubemailaddressinput"><input type="text" name="subemailaddress" /></div></div>';
				$submit = '<div id="disubemailsubmit"><input type="submit" value="'.JText::_('SUBMIT').'" /></div>';
				$time = ELOutput::formattime($this->_data->dates, $this->_data->times).' - '.ELOutput::formattime($this->_data->enddates, $this->_data->endtimes);
				$date = ELOutput::formatdate($this->_data->dates, $this->_data->times);
				$price = ELOutput::formatprice($this->_data->course_price);
				$duration = redEVENTHelper::getEventDuration($this->_data);
				$username = JRequest::getVar('subemailname', '');
				$useremail = JRequest::getVar('subemailaddress', '');
				$uri = JURI::getInstance();
				$regurl = JRoute::_($uri->toString());
				
				// signup links
        $imagepath = JURI::base() . '/administrator/components/com_redevent/assets/images/';
				$webformsignup = '<span class="vlink webform">'.JHTML::_('link', JRoute::_('index.php?option=com_redevent&view=signup&subtype=webform&task=signup&xref='.$this->_xref.'&id='.$this->_data->id), JHTML::_('image', $imagepath.$elsettings->signup_webform_img,  JText::_($elsettings->signup_webform_text), 'width="24px" height="24px"')).'</span> ';
				$emailsignup = '<span class="vlink email">'.JHTML::_('link', JRoute::_('index.php?option=com_redevent&view=signup&task=signup&subtype=email&xref='.$this->_xref.'&id='.$this->_data->id), JHTML::_('image', $imagepath.$elsettings->signup_email_img,  JText::_($elsettings->signup_email_text), 'width="24px" height="24px"')).'</span> ';
				$formalsignup = '<span class="vlink formaloffer">'.JHTML::_('link', JRoute::_('index.php?option=com_redevent&view=signup&subtype=formaloffer&task=signup&xref='.$this->_xref.'&id='.$this->_data->id), JHTML::_('image', $imagepath.$elsettings->signup_formal_offer_img,  JText::_($elsettings->signup_formal_offer_text), 'width="24px" height="24px"')).'</span> ';
				$externalsignup = '<span class="vlink external">'.JHTML::_('link', $this->_data->submission_type_external, JHTML::_('image', $imagepath.$elsettings->signup_external_img,  $elsettings->signup_external_text), 'target="_blank"').'</span> ';
				$phonesignup = '<span class="vlink phone">'.JHTML::_('link', JRoute::_('index.php?option=com_redevent&view=signup&task=signup&subtype=phone&xref='.$this->_xref.'&id='.$this->_data->id), JHTML::_('image', $imagepath.$elsettings->signup_phone_img,  JText::_($elsettings->signup_phone_text), 'width="24px" height="24px"')).'</span> ';
				
				/* Clean up some tags */
				$findoffer = array('[event_description]', '[event_title]', '[price]', '[credits]', '[code]', '[inputname]', '[inputemail]', '[submit]',
									'[event_info_text]', '[time]', '[date]', '[duration]', '[venue]', '[city]', '[username]', '[useremail]', '[venues]','[regurl]',
									'[eventplaces]', '[waitinglistplaces]', '[eventplacesleft]', '[waitinglistplacesleft]'
				          , '[webformsignup]', '[emailsignup]', '[formalsignup]', '[externalsignup]', '[phonesignup]');
				$replaceoffer = array($event_description, $this->_data->title, $price, $this->_data->course_credit, $this->_data->course_code, 
									$name, $email, $submit, $event_info_description, $time, $date, $duration, $this->_data->venue, $this->_data->location,
									$username, $useremail, $venues_html, $regurl, $this->_maxattendees, $this->_maxwaitinglist, $eventplacesleft, $waitinglistplacesleft, 
									$webformsignup, $emailsignup, $formalsignup, $externalsignup, $phonesignup);
				/* First tag replacement */
				$message = str_replace($findoffer, $replaceoffer, $page);
			  /* second replacement, add the form */
				/* if done in first one, username in the form javascript is replaced too... */
				$message = str_replace('[redform]', $redform, $message); 
				foreach ($customdata as $tag => $data) {
					$data->text_field = str_replace($findoffer, $replaceoffer, $data->text_field);
					/* Do a redFORM replacement here too for when used in the text library */
					$data->text_field = str_replace('[redform]', $redform, $data->text_field);
					$message = str_ireplace('['.$tag.']', $data->text_field, $message);
				}
				
				// FIXME: I don't see the point of this relative to abs for pictures, only causing problems... I'll comment it for now.
				// FEEDBACK: relative to absolute images is necessary for e-mail messages that contain relative image links. The images won't show up in the e-mail.
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
		$app = & JFactory::getApplication();
		$template_path = JPATH_BASE.DS.'templates'.DS.$app->getTemplate().DS.'html'.DS.'com_redevent';
		ob_start();
		if (JRequest::getVar('format') == 'raw') {
			if (file_exists($template_path.DS.'details'.DS.'courseinfo_pdf.php')) {
  			include($template_path.DS.'details'.DS.'courseinfo_pdf.php');				
			}
			else {
        include(JPATH_COMPONENT.DS.'views'.DS.'details'.DS.'tmpl'.DS.'courseinfo_pdf.php'); 				
			}
		}
		else {
      if (file_exists($template_path.DS.'details'.DS.'courseinfo.php')) {
        include($template_path.DS.'details'.DS.'courseinfo.php');       
      }
      else {
        include(JPATH_COMPONENT.DS.'views'.DS.'details'.DS.'tmpl'.DS.'courseinfo.php');         
      }
		}
		$contents = ob_get_contents();
		ob_end_clean();
		return $contents;
	}
	
	/**
	 * Load all venues and their signup links
	 */
	private function getEventLinks() {
		$db = JFactory::getDBO();
		$q = " SELECT e.*, IF (x.course_credit = 0, '', x.course_credit) AS course_credit, x.course_price, "
		    . " x.id AS xref, x.dates, x.enddates, x.times, x.endtimes, v.venue, x.venueid,
					v.city AS location,
					v.country, 
					UNIX_TIMESTAMP(x.dates) AS unixdates,
          CASE WHEN CHAR_LENGTH(v.alias) THEN CONCAT_WS(':', v.id, v.alias) ELSE v.id END as venueslug
			FROM #__redevent_events AS e
			INNER JOIN #__redevent_event_venue_xref AS x ON x.eventid = e.id
			INNER JOIN #__redevent_venues AS v ON x.venueid = v.id
			WHERE x.published = 1
			AND e.id IN (".$this->_eventid.")
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