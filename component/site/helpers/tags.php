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
	private $_submitkey;
	private $_maxattendees;
	private $_maxwaitinglist;
  private $_published;
	protected $_eventlinks = null;
	private $_libraryTags = null;
	private $_xrefcustomfields = null;
	private $_answers = null;
	private $_options = null;
	
	/**
	 * event model
	 * @var object
	 */
	private $_event = null;
	
	
	public function __construct($options = null) 
	{
		if (is_array($options))
		{
			$this->_addOptions($options);
		}		
		
		$this->_xref = JRequest::getVar('xref', false);
		
		// if no xref specified. try to get one associated to the event id
		if (!$this->_xref)
		{
			$eventid = JRequest::getVar('id', 0, 'request', 'int');
			if ($eventid)
			{
  			$db = & JFactory::getDBO();
				$query = ' SELECT x.id FROM #__redevent_event_venue_xref AS x '
				       . ' INNER JOIN #__redevent_events AS e ON e.id = x.eventid '
				       . ' WHERE x.published = 1 '
				       . '   AND x.eventid = '. $db->Quote($eventid)
				       . ' ORDER BY x.dates ASC '
				       ;
				$db->setQuery($query);
				$res = $db->loadResult();
				if ($res) {
					$this->_xref = $res;
				}
			}
		}
		
		if ($this->_xref) {
      $db = & JFactory::getDBO();
			$q = "SELECT eventid, venueid, maxattendees, maxwaitinglist, published FROM #__redevent_event_venue_xref WHERE id = ".$this->_xref;
			$db->setQuery($q);
			list($this->_eventid, $this->_venueid, $this->_maxattendees, $this->_maxwaitinglist, $this->_published) = $db->loadRow();
      if (!$this->_published) {
        JError::raiseError(404, JText::_('This event is not published'), 'this xref is not published, can\'t be displayed in venues');
      }
		}
	}
	
	function setEventId($id)
	{
		$this->_eventid = intval($id);
	}
	
	function setEventObject($object)
	{
		$this->_event = $object;
	}
	
	function setXref($xref)
	{
		$this->_xref = intval($xref);
	}
	
	function setSubmitkey($string)
	{
		$this->_submitkey = $string;
	}
	
	function _addOptions($options)
	{
		if (is_array($options)) 
		{
			if (!empty($this->_options)) {
				$this->_options = array_merge($this->_options, $options);
			}
			else {
				$this->_options = $options;
			}
		}
	}
	
	function getOption($name, $default = null)
	{		
		if (isset($this->_options) && isset($this->_options[$name])) {
			return $this->_options[$name];
		}
		else {
			return $default;
		}
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
	 * [paymentrequest]
	 * [paymentrequestlink]
	 */
	public function ReplaceTags($text, $options = null) 
	{
		$mainframe = &JFactory::getApplication();
		$base_url = $mainframe->isAdmin() ? $mainframe->getSiteURL() : JURI::base();
		$iconspath = $base_url.'administrator/components/com_redevent/assets/images/';
		if ($options) {
			$this->_addOptions($options);
		}
		
		$elsettings = redEVENTHelper::config();
		$this->_submitkey = $this->_submitkey ? $this->_submitkey : JRequest::getVar('submit_key');

				/* Load the signup links */
//				$venues_html = $this->SignUpLinks();
				
		// first, let's do the library tags replacement
		$text = $this->_replaceLibraryTags($text);

		// now get the list of all remaining tags
		preg_match_all("/\[(.+?)\]/", $text, $alltags);
				
				
				$search = array();
				$replace = array();
				// now, lets get the tags replacements
				foreach ($alltags[1] as $tag)
				{
				  switch($tag)
				  {
				  	/**************  event general tags ******************/
				  	
				  	//TODO: still used ?
				    case 'event_description': 
				    case 'event_info_text':
				      $search[] = '['.$tag.']';
      				/* Fix the tags of the event description */
      				$findcourse = array('[venues]','[price]','[credits]', '[code]');
      				$venues_html = $this->SignUpLinks();
      				$replacecourse = array($venues_html, 
      								ELOutput::formatprice($this->getEvent()->getData()->course_price), 
      								$this->getEvent()->getData()->course_credit,
      								$this->getEvent()->getData()->course_code);
      				$replace[] = str_replace($findcourse, $replacecourse, $this->getEvent()->getData()->datdescription);
      				break;
      				
				    case 'event_title':
				      $search[]  = '['.$tag.']';
      				$replace[] = $this->getEvent()->getData()->title;
      				break;

				    case 'price':
				      $search[]  = '['.$tag.']';
      				$replace[] = ELOutput::formatprice($this->getEvent()->getData()->course_price);
      				break;
      				
				    case 'credits':
				      $search[]  = '['.$tag.']';
      				$replace[] = $this->getEvent()->getData()->course_credit;
      				break;
      				
				    case 'code':
				      $search[]  = '['.$tag.']';
      				$replace[] = $this->getEvent()->getData()->course_code;
      				break;
      				
				    case 'date':
				      $search[]  = '['.$tag.']';
      				$replace[] = ELOutput::formatdate($this->getEvent()->getData()->dates, $this->getEvent()->getData()->times);
      				break;
      				
				    case 'enddate':
				      $search[]  = '['.$tag.']';
      				$replace[] = ELOutput::formatdate($this->getEvent()->getData()->enddates, $this->getEvent()->getData()->endtimes);
      				break;
      				
				    case 'time':
				      $search[]  = '['.$tag.']';
				  		$tmp = "";
				      if (!empty($this->getEvent()->getData()->times) && strcasecmp('00:00:00', $this->getEvent()->getData()->times)) 
				  		{
				      	$tmp = ELOutput::formattime($this->getEvent()->getData()->dates, $this->getEvent()->getData()->times);
				      					  		
					      if (!empty($this->getEvent()->getData()->endtimes) && strcasecmp('00:00:00', $this->getEvent()->getData()->endtimes)) {
					      	$tmp .= ' - ' .ELOutput::formattime($this->getEvent()->getData()->enddates, $this->getEvent()->getData()->endtimes);				      	
				      	}
				      }
      				$replace[] = $tmp;
      				break;      				
      				
				    case 'startenddatetime':
				      $search[]  = '['.$tag.']';
				      $tmp = ELOutput::formatdate($this->getEvent()->getData()->dates, $this->getEvent()->getData()->times);
				      if (!empty($this->getEvent()->getData()->times) && strcasecmp('00:00:00', $this->getEvent()->getData()->times)) {
				      	$tmp .= ' ' .ELOutput::formattime($this->getEvent()->getData()->dates, $this->getEvent()->getData()->times);	
				      }
				      if (!empty($this->getEvent()->getData()->enddates) && $this->getEvent()->getData()->enddates != $this->getEvent()->getData()->dates)
				      {
				      	$tmp .= ' - ' .ELOutput::formatdate($this->getEvent()->getData()->enddates, $this->getEvent()->getData()->endtimes);
				      }
				      if (!empty($this->getEvent()->getData()->endtimes) && strcasecmp('00:00:00', $this->getEvent()->getData()->endtimes)) {
				      	$tmp .= ' ' .ELOutput::formattime($this->getEvent()->getData()->dates, $this->getEvent()->getData()->endtimes);				      	
				      }
      				$replace[] = $tmp;
      				break;
      				
				    case 'duration':
				      $search[]  = '['.$tag.']';
      				$replace[] = redEVENTHelper::getEventDuration($this->getEvent()->getData());
      				break;
      				
				    case 'eventimage':
				    case 'event_image':
				      $search[]  = '['.$tag.']';
              $eventimage = redEVENTImage::flyercreator($this->getEvent()->getData()->datimage, 'event');
              $eventimage = JHTML::image(JURI::root().'/'.$eventimage['original'], $this->getEvent()->getData()->title, array('title' => $this->getEvent()->getData()->title));
      				$replace[] = $eventimage;
      				break;
      				
				    case 'event_thumb':
				      $search[]  = '['.$tag.']';
              $eventimage = redEVENTImage::modalimage('events', basename($this->getEvent()->getData()->datimage), $this->getEvent()->getData()->title);
      				$replace[] = $eventimage;
      				break;
      				
				    case 'categoryimage':
				    case 'category_image':
				      $search[]  = '['.$tag.']';
				      
      				$cats_images = array();
      				foreach ($this->getEvent()->getData()->categories as $c){
      				  $cats_images[] = redEVENTImage::getCategoryImage($c, false);
      				}
      				$categoryimage = '<span class="details-categories-images"><span class="details-categories-image">'.implode('</span><span class="details-categories-image">', $cats_images).'</span></span>';

      				$replace[] = $categoryimage;
      				break;
      				
				    case 'category_thumb':
				      $search[]  = '['.$tag.']';
				      
      				$cats_images = array();
      				foreach ($this->getEvent()->getData()->categories as $c){
      				  $cats_images[] = redEVENTImage::getCategoryImage($c);
      				}
      				$categoryimage = '<span class="details-categories-images"><span class="details-categories-image">'.implode('</span><span class="details-categories-image">', $cats_images).'</span></span>';

      				$replace[] = $categoryimage;
      				break;
      				
				    case 'info':
				      $search[]  = '['.$tag.']';
				      // check that there is no loop with the tag inclusion
              if (strpos($this->getEvent()->getData()->details, '[info]') === false) {
                $info = $this->ReplaceTags($this->getEvent()->getData()->details);
              }
              else {
                JError::raiseNotice(0, JText::_('ERROR TAG LOOP XREF DETAILS'));
                $info = '';
              }
              $replace[] = $info;
      				break;
      				
				    case 'category':
				      $search[]  = '['.$tag.']';
              // categories
              $cats = array();
              foreach ($this->getEvent()->getData()->categories as $c){
              	$cats[] = JHTML::link($this->absoluteUrls(RedeventHelperRoute::getCategoryEventsRoute($c->slug)), $c->catname);
              }
              $replace[] = '<span class="details-categories">'.implode(', ', $cats).'</span>';
      				break;
      				
				    case 'eventcomments':
				      $search[]  = '['.$tag.']';
              $replace[] = $this->_getComments($this->getEvent()->getData());
      				break;      
      				      				
				    case 'permanentlink':
				      $search[]  = '['.$tag.']';
              $replace[] = JHTML::link($this->absoluteUrls(RedeventHelperRoute::getDetailsRoute($this->getEvent()->getData()->slug), false), JText::_('Permanent link'), 'class="permalink"');
      				break;
      				      				
				    case 'datelink':
				      $search[]  = '['.$tag.']';
              $replace[] = JHTML::link($this->absoluteUrls(RedeventHelperRoute::getDetailsRoute($this->getEvent()->getData()->slug, $this->_xref), false), JText::_('Event details'), 'class="datelink"');
      				break;		

				    case 'answers':				    	
				      $search[]  = '['.$tag.']';
              $replace[] = $this->_answersToHtml($this->_submitkey);
				    	break;
      				
				    case 'ical':
				      $search[]  = '['.$tag.']';
				      $ttext = JText::_('COM_REDEVENT_EXPORT_ICS');
				      $replace[] = JHTML::link($this->absoluteUrls(RedeventHelperRoute::getDetailsRoute($this->getEvent()->getData()->slug, $this->_xref).'&format=raw&layout=ics', false), $ttext, array('class' => 'event-ics'));
				    	break;
				  	/**************  venue tags ******************/	
				    case 'venue':
				      $search[]  = '['.$tag.']';
      				$replace[] = $this->getEvent()->getData()->venue;
      				break;
      				
				    case 'city':
				      $search[]  = '['.$tag.']';
      				$replace[] = $this->getEvent()->getData()->location;
      				break;
      				
				    case 'venues':
				      $search[]  = '['.$tag.']';
      				$replace[] = $this->SignUpLinks();
      				break;
      				
				    case 'venue_title':
				      $search[]  = '['.$tag.']';
              $replace[] = $this->getEvent()->getData()->venue;
      				break;
      				
				    case 'venue_city':
				      $search[]  = '['.$tag.']';
              $replace[] = $this->getEvent()->getData()->location;
      				break;
      				
				    case 'venue_street':
				      $search[]  = '['.$tag.']';
              $replace[] = $this->getEvent()->getData()->street;
      				break;
      				
				    case 'venue_zip':
				      $search[]  = '['.$tag.']';
              $replace[] = $this->getEvent()->getData()->plz;
      				break;
      				
				    case 'venue_state':
				      $search[]  = '['.$tag.']';
              $replace[] = $this->getEvent()->getData()->state;
      				break;
      				
				    case 'venue_link':
				      $search[]  = '['.$tag.']';
      				$replace[] = JHTML::link($this->absoluteUrls(RedeventHelperRoute::getVenueEventsRoute($this->getEvent()->getData()->venueslug)), $this->getEvent()->getData()->venue);
      				break;
      				
				    case 'venue_website':
				      $search[]  = '['.$tag.']';
				      if (!empty($this->getEvent()->getData()->venueurl)) {
      					$replace[] = JHTML::link($this->absoluteUrls(($this->getEvent()->getData()->venueurl)), JText::_('Venue website'));		      	
				      }
				      else {
				      	$replace[] = '';
				      }
      				break;
      				
				    case 'venueimage':
				    case 'venue_image':
				      $search[]  = '['.$tag.']';
      				$venueimage = redEVENTImage::flyercreator($this->getEvent()->getData()->locimage);
      				$venueimage = JHTML::image(JURI::root().'/'.$venueimage['original'], $this->getEvent()->getData()->venue, array('title' => $this->getEvent()->getData()->venue));
      				$venueimage = JHTML::link($this->absoluteUrls(RedeventHelperRoute::getVenueEventsRoute($this->getEvent()->getData()->venueslug)), $venueimage);
      				$replace[] = $venueimage;
      				break;
      				
				    case 'venue_thumb':
				      $search[]  = '['.$tag.']';
              $venueimage = redEVENTImage::modalimage('venues', basename($this->getEvent()->getData()->locimage), $this->getEvent()->getData()->venue);
      				$replace[] = $venueimage;
      				break;
      				
				    case 'venue_description':
				      $search[]  = '['.$tag.']';
      				$replace[] = $this->getEvent()->getData()->venue_description;
      				break;
      				
				    case 'venue_country':
				      $search[]  = '['.$tag.']';
      				$replace[] = redEVENTHelperCountries::getCountryName($this->getEvent()->getData()->country);
      				break;
      				
				    case 'venue_countryflag':
				      $search[]  = '['.$tag.']';
      				$replace[] = redEVENTHelperCountries::getCountryFlag($this->getEvent()->getData()->country);
      				break;
      				
				    case 'venue_mapicon':
				      $search[]  = '['.$tag.']';
      				$replace[] = ELOutput::mapicon($this->getEvent()->getData(), array('class' => 'event-map'));
      				break;
      				
				    case 'venue_map':
				      $search[]  = '['.$tag.']';
      				$replace[] = ELOutput::map($this->getEvent()->getData(), array('class' => 'event-full-map'));
      				break;
				    	
      				
				  	/**************  registration tags ******************/
      				
				    case 'redform_title':
				      $search[]  = '['.$tag.']';
      				$replace[] = $this->getEvent()->getData()->formname;
      				break;      				
      				
				    case 'inputname': // for mail signup
				      $search[]  = '['.$tag.']';
      				$replace[] = '<div id="divsubemailname"><div class="divsubemailnametext">'.JText::_('NAME').'</div><div class="divsubemailnameinput"><input type="text" name="subemailname" /></div></div>';
      				break;
      				
				    case 'inputemail':
				      $search[]  = '['.$tag.']';
      				$replace[] = '<div id="divsubemailaddress"><div class="divsubemailaddresstext">'.JText::_('EMAIL').'</div><div class="divsubemailaddressinput"><input type="text" name="subemailaddress" /></div></div>';
      				break;
      				
				    case 'submit':
				      $search[]  = '['.$tag.']';
      				$replace[] = '<div id="disubemailsubmit"><input type="submit" value="'.JText::_('SUBMIT').'" /></div>';
      				break;      				
      				
				    case 'registrationend':
				      $search[]  = '['.$tag.']';
				      if (strtotime($this->getEvent()->getData()->registrationend)) 
				      {
				      	$replace[] = strftime( $elsettings->formatdate . ' '. $elsettings->formattime, strtotime($this->getEvent()->getData()->registrationend));
				  		}
				  		else {
				  			$replace[] = '';
				  		}
      				break;      	      			
      				
				    case 'username':
				      $search[]  = '['.$tag.']';
      				$replace[] = JRequest::getVar('subemailname', '');
      				break;
      				
				    case 'useremail':
				      $search[]  = '['.$tag.']';
      				$replace[] = JRequest::getVar('subemailaddress', '');
      				break;
      				
				    case 'regurl':
				      $search[]  = '['.$tag.']';
      				$replace[] = $this->absoluteUrls($uri->toString());
      				break;
      				
				    case 'eventplaces':
				      $search[]  = '['.$tag.']';
      				$replace[] = $this->_maxattendees;
      				break;
      				
				    case 'waitinglistplaces':
				      $search[]  = '['.$tag.']';
      				$replace[] = $this->_maxwaitinglist;
      				break;
      				
				    case 'eventplacesleft':
				      $search[]  = '['.$tag.']';
      				$replace[] = $this->getEvent()->getPlacesLeft();
      				break;
      				
				    case 'waitinglistplacesleft':
				      $search[]  = '['.$tag.']';
      				$replace[] = $this->getEvent()->getWaitingPlacesLeft();
      				break;
      				
				    case 'webformsignup':
				      $search[]  = '['.$tag.']';
              $replace[] = '<span class="vlink webform">'
                           . JHTML::_('link', 
                                      $this->absoluteUrls(RedeventHelperRoute::getSignupRoute('webform', $this->getEvent()->getData()->id, $this->_xref)), 
                                      JHTML::_('image', $iconspath.$elsettings->signup_webform_img,  
                                      JText::_($elsettings->signup_webform_text), 
                                      'width="24px" height="24px"'))
                           .'</span> ';
              break;      				
      				
				    case 'emailsignup':
				      $search[]  = '['.$tag.']';
				      $replace[] = '<span class="vlink email">'
				                      .JHTML::_('link', 
                                        $this->absoluteUrls(RedeventHelperRoute::getSignupRoute('email', $this->getEvent()->getData()->id, $this->_xref)), 
				                                JHTML::_('image', $iconspath.$elsettings->signup_email_img,  
				                                JText::_($elsettings->signup_email_text), 
				                                'width="24px" height="24px"'))
				                      .'</span> ';
              break;
      				
				    case 'formalsignup':
				      $search[]  = '['.$tag.']';
				      $replace[] = '<span class="vlink formaloffer">'
				                    .JHTML::_('link', 
                                      $this->absoluteUrls(RedeventHelperRoute::getSignupRoute('formaloffer', $this->getEvent()->getData()->id, $this->_xref)), 
				                              JHTML::_('image', $iconspath.$elsettings->signup_formal_offer_img,  
				                              JText::_($elsettings->signup_formal_offer_text), 
				                              'width="24px" height="24px"'))
				                   .'</span> ';
              break;
      				
				    case 'externalsignup':
				      $search[]  = '['.$tag.']';
				      if (!empty($this->getEvent()->getData()->external_registration_url)) {
				      	$link = $this->getEvent()->getData()->external_registration_url;
				      }
				      else {
				      	$link = $this->getEvent()->getData()->submission_type_external;
				      }
				      $replace[] = '<span class="vlink external">'
				                    .JHTML::_('link', 
				                              $link, 
				                              JHTML::_('image', $iconspath.$elsettings->signup_external_img,  
				                              $elsettings->signup_external_text), 
				                              'target="_blank"')
				                    .'</span> ';				      
              break;
      				
				    case 'phonesignup':
				      $search[]  = '['.$tag.']';
				      $replace[] = '<span class="vlink phone">'
				                     .JHTML::_('link', 
                                       $this->absoluteUrls(RedeventHelperRoute::getSignupRoute('phone', $this->getEvent()->getData()->id, $this->_xref)), 
				                               JHTML::_('image', $iconspath.$elsettings->signup_phone_img,  
				                               JText::_($elsettings->signup_phone_text), 
				                               'width="24px" height="24px"'))
				                     .'</span> ';
				      break;
              
				    case 'webformsignuppage':
				      $search[]  = '['.$tag.']';
              // check that there is no loop with the tag inclusion
              if (preg_match('/\[[a-z]*signuppage\]/', $this->getEvent()->getData()->submission_type_webform) == 0) {
                $replace[] = $this->ReplaceTags($this->getEvent()->getData()->submission_type_webform);
              }
              else {
                JError::raiseNotice(0, JText::_('ERROR TAG LOOP XXXXSIGNUPPAGE'));
                $replace[] = '';
              }
      				break;
      				
				    case 'formalsignuppage':
				      $search[]  = '['.$tag.']';
              // check that there is no loop with the tag inclusion
              if (preg_match('/\[[a-z]*signuppage\]/', $this->getEvent()->getData()->submission_type_formal_offer) == 0) {
                $replace[] = $this->_getFormalOffer($this->getEvent()->getData());
              }
              else {
                JError::raiseNotice(0, JText::_('ERROR TAG LOOP XXXXSIGNUPPAGE'));
                $replace[] = '';
              }
      				break;
      				
				    case 'phonesignuppage':
				      $search[]  = '['.$tag.']';
    					// check that there is no loop with the tag inclusion
    					if (preg_match('/\[[a-z]*signuppage\]/', $this->getEvent()->getData()->submission_type_phone) == 0) {
    					  $replace[] = $this->ReplaceTags($this->getEvent()->getData()->submission_type_phone);
    					}
    					else {
    						JError::raiseNotice(0, JText::_('ERROR TAG LOOP XXXXSIGNUPPAGE'));
    						$replace[] = '';
    					}
      				break;
      				
				    case 'emailsignuppage':
				      $search[]  = '['.$tag.']';
              // check that there is no loop with the tag inclusion
              if (preg_match('/\[[a-z]*signuppage\]/', $this->getEvent()->getData()->submission_type_email) == 0) {
                $replace[] = $this->_getEmailSubmission($this->getEvent()->getData());
              }
              else {
                JError::raiseNotice(0, JText::_('ERROR TAG LOOP XXXXSIGNUPPAGE'));
                $replace[] = '';
              }
      				break;
      				
      				
				    case 'paymentrequest':
				      $search[]  = '['.$tag.']';
				      if (!empty($this->_submitkey)) {
				      	$title = urlencode($this->getEvent()->getData()->title.' '.ELOutput::formatdate($this->getEvent()->getData()->dates, $this->getEvent()->getData()->times));				      
              	$replace[] = JHTML::link($this->absoluteUrls('index.php?option=com_redform&controller=payment&task=select&source=redevent&key='.$this->_submitkey.'&paymenttitle='.$title, false), JText::_('Checkout'), '');
				      }
				      else {
				      	$replace[] = '';
				      }
				    	break;
				    	
				    case 'paymentrequestlink':
				      $search[]  = '['.$tag.']';
				      if (!empty($this->_submitkey)) {
				      	$title = urlencode($this->getEvent()->getData()->title.' '.ELOutput::formatdate($this->getEvent()->getData()->dates, $this->getEvent()->getData()->times));				      
              	$replace[] = $this->absoluteUrls('index.php?option=com_redform&controller=payment&task=select&source=redevent&key='.$this->_submitkey.'&paymenttitle='.$title, false);
				      }
				      else {
				      	$replace[] = '';
				      }
				    	break;
				    	
				    case 'registrationid':
				      $search[]  = '['.$tag.']';
				      if (!empty($this->_submitkey)) {
				      	$replace[] = $this->getAttendeeUniqueId($this->_submitkey);
				      }
				      else {
				      	$replace[] = '';
				      }
				    	break;
				  }
				    
				}
				// do the replace
				$message = str_replace($search, $replace, $text);
								
				// then the custom tags
				$search = array();
				$replace = array();
				
				/* Load custom fields */
				$customfields = $this->getCustomFields();
        foreach ($customfields as $tag => $data) 
        {
          $search[] = '['.$data->text_name.']';
          $replace[] = $data->text_field;
        }
        $message = str_ireplace($search, $replace, $message);
				
				
				/* Include redFORM */
				if (in_array('[redform]', $alltags[0]) && $this->getEvent()->getData()->redform_id > 0) 
				{
				  $status = redEVENTHelper::canRegister($this->_xref);
				  if ($status->canregister) 
				  {
            $redform = $this->getForm($this->getEvent()->getData()->redform_id);
				  }
				  else {
				    $redform = '<span class="registration_error">'.$status->status.'</span>';
				  }
				  
				  /* second replacement, add the form */
					/* if done in first one, username in the form javascript is replaced too... */
				  $message = str_replace('[redform]', $redform, $message); 
				}	 				
				
				// FIXME: I don't see the point of this relative to abs for pictures, only causing problems... I'll comment it for now.
				// FEEDBACK: relative to absolute images is necessary for e-mail messages that contain relative image links. The images won't show up in the e-mail.
				// FIXME: this function doesn't work when website is not at domain root... So it has to be fixed !
				return ELOutput::ImgRelAbs($message);
	}
	
	/**
	 * return event helper model object
	 * 
	 * @return object
	 */
	private function getEvent()
	{
		if (empty($this->_event)) 
		{
			$this->_event = &JModel::getInstance('Eventhelper', 'RedeventModel');
			$this->_event->setId($this->_eventid);
			$this->_event->setXref($this->_xref);
		}
		return $this->_event;
	}
		
	/**
	 * Load the HTML table with signup links
	 */
	private function SignUpLinks() 
	{
		$app = & JFactory::getApplication();
		$this->getEventLinks();
		$template_path = JPATH_BASE.DS.'templates'.DS.$app->getTemplate().DS.'html'.DS.'com_redevent';
		
		$lists['order_Dir'] 	= JRequest::getWord('filter_order_Dir', 'ASC');
		$lists['order'] 		= JRequest::getCmd('filter_order', 'x.dates');
		$this->lists = $lists;
		
    $uri    = &JFactory::getURI();
    $this->action = $uri->toString();
    
    $this->customs = $this->getXrefCustomFields();
    
		ob_start();
		if (JRequest::getVar('format') == 'pdf') {
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
	private function getEventLinks() 
	{		
		if (empty($this->_eventlinks))
		{
			$xcustoms = $this->getXrefCustomFields();
			
			$order_Dir = JRequest::getWord('filter_order_Dir', 'ASC');
			$order 		 = JRequest::getCmd('filter_order', 'x.dates');
			
			$db = JFactory::getDBO();
			$query = ' SELECT e.*, IF (x.course_credit = 0, "", x.course_credit) AS course_credit, x.course_price, '
			   . ' x.id AS xref, x.dates, x.enddates, x.times, x.endtimes, x.maxattendees, x.maxwaitinglist, v.venue, x.venueid, x.details, x.registrationend, '
			   . ' x.external_registration_url, '
			   . ' v.city AS location, v.state, v.url as venueurl, v.locdescription as venue_description, '
			   . ' v.country, v.locimage, v.street, v.plz, v.map, '
			   . ' f.formname, '
			   . ' UNIX_TIMESTAMP(x.dates) AS unixdates, '
			   . ' CASE WHEN CHAR_LENGTH(e.alias) THEN CONCAT_WS(":", e.id, e.alias) ELSE e.id END as slug, '
			   . ' CASE WHEN CHAR_LENGTH(v.alias) THEN CONCAT_WS(":", v.id, v.alias) ELSE v.id END as venueslug '
			   ;
		
			// add the custom fields
			foreach ((array) $xcustoms as $c)
			{
				$query .= ', c'. $c->id .'.value AS custom'. $c->id;
			}
			
			$query .= ' FROM #__redevent_events AS e '
			   . ' INNER JOIN #__redevent_event_venue_xref AS x ON x.eventid = e.id '
			   . ' INNER JOIN #__redevent_venues AS v ON x.venueid = v.id '
			   . ' LEFT JOIN #__rwf_forms AS f ON f.id = e.redform_id '
			   . ' LEFT JOIN #__redevent_event_category_xref AS xcat ON xcat.event_id = e.id '
			   . ' LEFT JOIN #__redevent_categories AS c ON xcat.category_id = c.id '
			   . ' LEFT JOIN #__users AS u ON u.id = e.created_by '
			   ;		   
		
			// add the custom fields tables
			foreach ((array) $xcustoms as $c)
			{
				$query .= ' LEFT JOIN #__redevent_fields_values AS c'. $c->id .' ON c'. $c->id .'.object_id = x.id AND c'. $c->id .'.field_id = '. $c->id;
			}
			
			$query .= ' WHERE x.published = '. $db->Quote($this->getEvent()->getData()->published)
			   . ' AND e.id = '.$this->_eventid
			   . ' GROUP BY x.id '
			   . ' ORDER BY '.$order.' '.$order_Dir.', x.dates, x.times '
			   ;
			$db->setQuery($query);
			$this->_eventlinks = $db->loadObjectList();
	    $this->_eventlinks = $this->_getPlacesLeft($this->_eventlinks);
			$this->_eventlinks = $this->_getCategories($this->_eventlinks);
	    $this->_eventlinks = $this->_getUserRegistrations($this->_eventlinks);
		}
		return $this->_eventlinks;
	}
	
	private function _getCategories($rows)
	{		
    $db = JFactory::getDBO();
		foreach ($rows as $k => $r) {
			$query = ' SELECT c.id, c.catname, c.image, '
             . ' CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(":", c.id, c.alias) ELSE c.id END as slug '
			       . ' FROM #__redevent_categories AS c '
			       . ' INNER JOIN #__redevent_event_category_xref AS xcat ON xcat.category_id = c.id '
			       . ' WHERE xcat.event_id = ' . $db->Quote($r->id)
			       . ' ORDER BY c.lft '
			       ;
			$db->setQuery($query);
			$rows[$k]->categories = $db->loadObjectList();
		}
		return ($rows);
	}
    
  /**
   * adds registered (int) and waiting (int) properties to rows.
   * 
   * @return array 
   */
  private function _getPlacesLeft($rows) 
  {
    $db = JFactory::getDBO();
    foreach ($rows as $k => $r) 
    {
			$q = ' SELECT r.waitinglist, COUNT(r.id) AS total '
			   . ' FROM #__redevent_register AS r '
			   . ' WHERE r.xref = '. $db->Quote($r->xref)
			   . ' AND r.confirmed = 1 '
			   . ' GROUP BY r.waitinglist '
			   ;
	    $db->setQuery($q);
	    $res = $db->loadObjectList('waitinglist');
      $rows[$k]->registered = (isset($res[0]) ? $res[0]->total : 0) ;
      $rows[$k]->waiting = (isset($res[1]) ? $res[1]->total : 0) ;
    }
    return $rows;
  }

  /**
   * adds property userregistered to rows: the number of time this user is already registered for each xref
   * 
   * @return array 
   */
  private function _getUserRegistrations($rows) 
  {
    $db = JFactory::getDBO();
    $user = & JFactory::getUser();
    
    foreach ($rows as $k => $r) 
    {
	    if ($user->get('id'))
	    {
	      $q = "SELECT COUNT(r.id) AS total
	        FROM #__redevent_register AS r 
	        WHERE r.xref = ". $db->Quote($r->xref) ."
	        AND r.confirmed = 1
	        AND r.uid = ". $db->Quote($user->get('id')) ."
	        ";
	      $db->setQuery($q);
	      $rows[$k]->userregistered = $db->loadResult();
	    }
	    else 
	    {
        $rows[$k]->userregistered = 0;	    	
	    }
    }
    return $rows;
  }
  		
	/**
	 * recursively replaces all the library tags from the text
	 * 
	 * @param string
	 * @return string
	 */
	private function _replaceLibraryTags($text) 
	{
	  $tags = &$this->_getLibraryTags();
	  
	  $search = array();
	  $replace = array();
	  foreach ($tags as $tag => $data) 
	  {
	    $search[] = '['.$data->text_name.']';
	    $replace[] = $data->text_field;
	  }
	  // first replacement
	  $text = str_ireplace($search, $replace, $text, $count);
	  
	  // now, the problem that there could have been libray tags embedded into one another, so we keep replacing if $count is > 0
	  if ($count) {
	    $text = $this->_replaceLibraryTags($text);
	  }
	  return $text;
	}

	/**
	 * gets list of tags belonging to the text library
   * 
   * @param array
   * @return array (objects: text_name, text_field)
   */
	private function &_getLibraryTags() 
	{
	  if (empty($this->_libraryTags)) 
	  {
  		$db = JFactory::getDBO();
  		$q = "SELECT text_name, text_field
  			FROM #__redevent_textlibrary WHERE CHAR_LENGTH(text_name) > 0";
  		$db->setQuery($q);
  		
  		$this->_libraryTags = $db->loadObjectList('text_name');
	  }
	  return $this->_libraryTags;
	}
	
	/**
	 * Returns the content of comments
	 *
	 * @param object $event
	 * @return html
	 */
	private function _getComments($event)
	{
		$app = & JFactory::getApplication();
    $template_path = JPATH_BASE.DS.'templates'.DS.$app->getTemplate().DS.'html'.DS.'com_redevent';
    $contents = '';
    $this->row = $event;
    $this->row->did = $event->id;
    $this->elsettings = & redEVENTHelper::config();
    if (JRequest::getVar('format') != 'raw') {
      ob_start();
      if (file_exists($template_path.DS.'details'.DS.'default_comments.php')) {
        include($template_path.DS.'details'.DS.'default_comments.php');       
      }
      else {
        include(JPATH_COMPONENT.DS.'views'.DS.'details'.DS.'tmpl'.DS.'default_comments.php');         
      }
	    $contents = ob_get_contents();
	    ob_end_clean();
    }
    return $contents;
	}
	
	private function _getFormalOffer($event)
	{
		ob_start();
		?>
		<form name="subemail" action="<?php echo JRoute::_('index.php'); ?>" method="post">
		  <?php echo $this->ReplaceTags($event->submission_type_formal_offer); ?>
		  <input type="hidden" name="task" value="signup" />
		  <input type="hidden" name="option" value="com_redevent" />
		  <input type="hidden" name="view" value="signup" />
		  <input type="hidden" name="subtype" value="formaloffer" />
		  <input type="hidden" name="sendmail" value="1" />
		  <input type="hidden" name="xref" value="<?php echo $event->xref; ?>" />
		  <input type="hidden" name="id" value="<?php echo $event->id; ?>" />
		</form>
		<?php
    $contents = ob_get_contents();
    ob_end_clean();
    return $contents;    
	}

  private function _getEmailSubmission($event)
  {
    ob_start();
    ?>
		<form name="subemail" action="<?php echo JRoute::_('index.php'); ?>" method="post">
		  <?php echo $this->ReplaceTags($event->submission_type_email); ?>
		  <input type="hidden" name="task" value="signup" />
		  <input type="hidden" name="option" value="com_redevent" />
		  <input type="hidden" name="view" value="signup" />
		  <input type="hidden" name="subtype" value="email" />
		  <input type="hidden" name="sendmail" value="1" />
      <input type="hidden" name="xref" value="<?php echo $event->xref; ?>" />
      <input type="hidden" name="id" value="<?php echo $event->id; ?>" />
		</form>
    <?php  
    $contents = ob_get_contents();
    ob_end_clean();
    return $contents;    
  }
  
  /**
   * get custom fields and their value
   *
   * @return array tag => field
   */
  function getCustomfields()
  {
  	$xref = $this->_xref;
  	$db = & JFactory::getDBO();
    $query = ' SELECT f.*, fv.value '
           . ' FROM #__redevent_event_venue_xref AS xref '
           . ' INNER JOIN #__redevent_fields_values AS fv ON fv.object_id = xref.eventid '
           . ' INNER JOIN #__redevent_fields AS f ON fv.field_id = f.id '
           . ' WHERE f.published = 1 '
           . ' AND CHAR_LENGTH(f.tag) > 0 '
           . ' AND f.object_key = '. $db->Quote("redevent.event")
           . ' AND xref.id = '. $db->Quote($xref)
           ;
    $db->setQuery($query);
    $fields = $db->loadObjectList();
    
    $query = ' SELECT f.*, fv.value '
           . ' FROM #__redevent_event_venue_xref AS xref '
           . ' INNER JOIN #__redevent_fields_values AS fv ON fv.object_id = xref.id '
           . ' INNER JOIN #__redevent_fields AS f ON fv.field_id = f.id '
           . ' WHERE f.published = 1 '
           . ' AND CHAR_LENGTH(f.tag) > 0 '
           . ' AND f.object_key = '. $db->Quote("redevent.xref")
           . ' AND xref.id = '. $db->Quote($xref)
           ;
    $db->setQuery($query);
    $fields = array_merge($fields, $db->loadObjectList());
        
    $have_values = array();
    $replace = array();
    foreach ((array) $fields as $field)
    {
    	$have_values[] = $field->id;
    	$obj = new stdclass();
    	$obj->text_name = $field->tag;
      $obj->text_field = redEVENTHelper::renderFieldValue($field);
      $replace[$field->tag] = $obj;
    }
    
    //there might be some empty ones if the tag were added after event/xref creations    
    $query = ' SELECT f.*, null '
           . ' FROM #__redevent_fields_values AS fv '
           . ' INNER JOIN #__redevent_fields AS f ON fv.field_id = f.id '
           . ' WHERE f.published = 1 '
           . ' AND CHAR_LENGTH(f.tag) > 0 '
           . ' AND (f.object_key = '. $db->Quote("redevent.xref"). ' OR f.object_key = '. $db->Quote("redevent.event"). ')'
           ;
    if (count($have_values)) {
    	$query .= ' AND f.id NOT IN ('.implode(',', $have_values).')';
    }
    $db->setQuery($query);
    $empty = $db->loadObjectList();
    
    foreach ((array) $empty as $field)
    {
    	$obj = new stdclass();
    	$obj->text_name = $field->tag;
      $obj->text_field = null;
      $replace[$field->tag] = $obj;
    }
    
    return $replace;
  }
  
  /**
   * returns all custom fields for xrefs
   * 
   * @return array
   */
  function getXrefCustomFields()
  {
  	if (empty($this->_xrefcustomfields))
  	{
  		$db = & JFactory::getDBO();
	  	$query = ' SELECT f.id, f.name, f.in_lists, f.searchable, f.ordering '
	  	       . ' FROM #__redevent_fields AS f'
	  	       . ' WHERE f.published = 1'
	  	       . '   AND f.object_key = '. $db->Quote('redevent.xref')
	  	       . '   AND f.in_lists = 1 '
	  	       . ' ORDER BY f.ordering ASC '
	  	       ;
	  	$db->setQuery($query);
	  	$this->_xrefcustomfields = $db->loadObjectList();
  	}
  	return $this->_xrefcustomfields;
  }
  
  function getAttendeeUniqueId($submit_key)
  {
  	$db = & JFactory::getDBO();
  	$query = ' SELECT e.title, e.alias, e.course_code, r.xref, r.id '
  	       . ' FROM #__redevent_register AS r '
  	       . ' INNER JOIN #__redevent_event_venue_xref AS x ON x.id = r.xref '
  	       . ' INNER JOIN #__redevent_events AS e ON e.id = x.eventid '
  	       . ' WHERE r.submit_key = '. $db->Quote($submit_key)
  	       ;
  	$db->setQuery($query, 0, 1);
  	$obj = $db->loadObject();
  	if ($obj) {
  		$code = $obj->course_code .'-'. $obj->xref .'-'. $obj->id;
  	}
  	else {
  		$code = '';
  	}
  	return $code;
  }
  	
  /**
   * return answers as html text
   * 
   * @param string $submit_key
   * @return string html
   */
  private function _answersToHtml($submit_key)
  {
  	if (empty($submit_key)) {
  		return '';
  	}
  	$answers = $this->_getAnswers($submit_key);
  	$res = '';
  	
  	foreach ($answers as $a)
  	{
  		$res .= '<table class="formanswers">';
			foreach ($a as $field)
			{
				$res .= '<tr>';
				$res .= '<th>'.$field->field.'</th>';
				$res .= '<td>'.str_replace('~~~','<br/>', $field->answer).'</td>';
				$res .= '</tr>';
			}
  		$res .= '</table>';  		
  	}
  	return $res;
  }
  
  /**
   * returns answers as array of row arrays
   * 
   * @param string $submit_key
   * @return array
   */
  private function _getAnswers($submit_key)
  { 
  	if (empty($this->_answers))
  	{
	  	if (!$this->getEvent()->getData()) {
	  		JError::raiseWarning(0, JText::_('Error: missing data'));
	  		return false;
	  	}
	  	if (!$submit_key) {
	  		JError::raiseWarning(0, JText::_('Error: missing key'));
	  		return false;
	  	}
	  	
	  	$db = & JFactory::getDBO();
	  	$query = ' SELECT r.sid '
	  	       . ' FROM #__redevent_register AS r '
	  	       . ' WHERE r.submit_key = '.$db->quote($submit_key);
			$db->setQuery($query);
			$sids = $db->loadResultArray();
						
			$rfcore = new RedFormCore();
			$this->_answers = $rfcore->getSidsFieldsAnswers($sids);
  	}
  	return $this->_answers;
  }

  /**
   * returns form
   * 
   * @return string
   */
  function getForm()
  {
  	$submit_key = JRequest::getVar('submit_key');
  	$options = array('booking' => $this->getEvent()->getData());  	
 		$rfcore = new RedFormCore();
 		if (!$rfcore->getFormStatus($this->getEvent()->getData()->redform_id)) {
			$error = $rfcore->getError();
			return '<span class="redform_error">'.$error.'</span>';
 		}
  	$action = JRoute::_(RedeventHelperRoute::getRegistrationRoute($this->_xref, 'register'));
  	
		$html = '<form action="'.$action.'" method="post" name="redform" enctype="multipart/form-data" onsubmit="return CheckSubmit(this);">';
  	$html .= $rfcore->getFormFields($this->getEvent()->getData()->redform_id, $submit_key, ($this->getEvent()->getData()->max_multi_signup ? $this->getEvent()->getData()->max_multi_signup : 1), $options);
  	$html .= '<input type="hidden" name="xref" value="'.$this->_xref.'"/>';
  	if ($this->getOption('hasreview')) {
  		$html .= '<input type="hidden" name="hasreview" value="1"/>';
  	}
		$html .= '<div id="submit_button" style="display: block;">';
		if (empty($submit_key)) {
			$html .= '<input type="submit" id="regularsubmit" name="submit" value="'.JText::_('Submit').'" />';
		}
		else {
			$html .= '<input type="submit" id="redformsubmit" name="submit" value="'.JText::_('Confirm').'" />';
			$html .= '<input type="submit" id="redformcancel" name="cancel" value="'.JText::_('Cancel').'" />';
		}			
		$html .= '</div>';
  	$html .= '</form>';
  	return $html;
  	
  	// TODO: remove this part of code, kept for debugging
  	JPluginHelper::importPlugin( 'content' );
  	$dispatcher = JDispatcher::getInstance();
  	$form = new stdClass();
  	$form->text = '{redform}'.$this->getEvent()->getData()->redform_id.','.($this->getEvent()->getData()->max_multi_signup ? $this->getEvent()->getData()->max_multi_signup : 1).'{/redform}';
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
  	$params['show_submission_type_webform_formal_offer'] = $this->getEvent()->getData()->show_submission_type_webform_formal_offer;	
  	$params['eventdetails'] = $this->getEvent()->getData();		
  					
  	$results = $dispatcher->trigger('onPrepareEvent', array(& $form, $params, 0));
    $redform = $form->text;
  }
    	
  function absoluteUrls($url, $xhtml = true, $ssl = null)
	{
		// Get the router
		$app	= &JFactory::getApplication();
		$router = &$app->getRouter();

		// Make sure that we have our router
		if (! $router) {
			return null;
		}

		if ( (strpos($url, '&') !== 0 ) && (strpos($url, 'index.php') !== 0) ) {
            return $url;
 		}

		// Build route
		$uri = &$router->build($url);
		$url = $uri->toString(array('path', 'query', 'fragment'));

		// Replace spaces
		$url = preg_replace('/\s/u', '%20', $url);

		/*
		 * Get the secure/unsecure URLs.

		 * If the first 5 characters of the BASE are 'https', then we are on an ssl connection over
		 * https and need to set our secure URL to the current request URL, if not, and the scheme is
		 * 'http', then we need to do a quick string manipulation to switch schemes.
		 */
		$ssl	= (int) $ssl;
		if ( $ssl || 1 )
		{
			$uri	         =& JURI::getInstance();

			// Get additional parts
			static $prefix;
			if ( ! $prefix ) {
				$prefix = $uri->toString( array('host', 'port'));
				//$prefix .= JURI::base(true);
			}

			// Determine which scheme we want
			$scheme	= ( $ssl === 1 ) ? 'https' : 'http';

			// Make sure our url path begins with a slash
			if ( ! preg_match('#^/#', $url) ) {
				$url	= '/' . $url;
			}

			// Build the URL
			$url	= $scheme . '://' . $prefix . $url;
		}

		if($xhtml) {
			$url = str_replace( '&', '&amp;', $url );
		}

		return $url;
	}
}
?>