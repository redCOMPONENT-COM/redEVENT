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
  private $_published;
	protected $_eventlinks = null;
	private $_data = false;
	private $_libraryTags = null;
	private $_xrefcustomfields = null;
	
	
	public function __construct() {
				
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
	public function ReplaceTags($page) 
	{
		//exit($page);
		if ($this->_xref) 
		{
      $elsettings = redEVENTHelper::config();

      /* Load the event links */
			if (is_null($this->_eventlinks)) $this->getEventLinks();
			if (count($this->_eventlinks) == 0) return '';
			$this->getData();
			
			if ($this->_data) 
			{
				/* Load the signup links */
				$venues_html = $this->SignUpLinks();
				
				// first, let's do the library tags replacement
				$page = $this->_replaceLibraryTags($page);
				
				// now get the list of all remaining tags
				preg_match_all("/\[(.+?)\]/", $page, $alltags);
				
				/* Load custom fields */
				$customfields = $this->getCustomFields($this->_xref);
				
				$search = array();
				$replace = array();
				// now, lets get the tags replacements
				foreach ($alltags[1] as $tag)
				{
				  switch($tag)
				  {
				    case 'event_description':
				      $search[] = '['.$tag.']';
      				/* Fix the tags of the event description */
      				$findcourse = array('[venues]','[price]','[credits]', '[code]');
      				$replacecourse = array($venues_html, 
      								ELOutput::formatprice($this->_data->course_price), 
      								$this->_data->course_credit,
      								$this->_data->course_code);
      				$replace[] = str_replace($findcourse, $replacecourse, $this->_data->datdescription);
      				break;
      				
				    case 'event_title':
				      $search[]  = '['.$tag.']';
      				$replace[] = $this->_data->title;
      				break;

				    case 'price':
				      $search[]  = '['.$tag.']';
      				$replace[] = ELOutput::formatprice($this->_data->course_price);
      				break;
      				
				    case 'credits':
				      $search[]  = '['.$tag.']';
      				$replace[] = $this->_data->course_credit;
      				break;
      				
				    case 'code':
				      $search[]  = '['.$tag.']';
      				$replace[] = $this->_data->course_code;
      				break;
      				
				    case 'inputname':
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
      				
				    case 'event_info_text':
				      $search[]  = '['.$tag.']';
      				/* Create event description without venue links */
      				$findcourse = array('[venues]','[price]','[credits]', '[code]');
      				$replacecourse = array('', 
      								ELOutput::formatprice($this->_data->course_price), 
      								$this->_data->course_credit,
      								$this->_data->course_code);
      				$replace[] = str_replace($findcourse, $replacecourse, $this->_data->datdescription);
      				break;
      				
				    case 'time':
				      $search[]  = '['.$tag.']';
				  		$text = "";
				      if (!empty($this->_data->times) && strcasecmp('00:00:00', $this->_data->times)) 
				  		{
				      	$text = ELOutput::formattime($this->_data->dates, $this->_data->times);
				      					  		
					      if (!empty($this->_data->endtimes) && strcasecmp('00:00:00', $this->_data->endtimes)) {
					      	$text .= ' - ' .ELOutput::formattime($this->_data->enddates, $this->_data->endtimes);				      	
				      	}
				      }
      				$replace[] = $text;
      				break;
      				
				    case 'date':
				      $search[]  = '['.$tag.']';
      				$replace[] = ELOutput::formatdate($this->_data->dates, $this->_data->times);
      				break;
      				
				    case 'enddate':
				      $search[]  = '['.$tag.']';
      				$replace[] = ELOutput::formatdate($this->_data->enddates, $this->_data->endtimes);
      				break;
      				
				    case 'startenddatetime':
				      $search[]  = '['.$tag.']';
				      $text = ELOutput::formatdate($this->_data->dates, $this->_data->times);
				      if (!empty($this->_data->times) && strcasecmp('00:00:00', $this->_data->times)) {
				      	$text .= ' ' .ELOutput::formattime($this->_data->dates, $this->_data->times);	
				      }
				      if (!empty($this->_data->enddates) && $this->_data->enddates != $this->_data->dates)
				      {
				      	$text .= ' - ' .ELOutput::formatdate($this->_data->enddates, $this->_data->endtimes);
				      }
				      if (!empty($this->_data->endtimes) && strcasecmp('00:00:00', $this->_data->endtimes)) {
				      	$text .= ' ' .ELOutput::formattime($this->_data->dates, $this->_data->endtimes);				      	
				      }
      				$replace[] = $text;
      				break;
      				
				    case 'duration':
				      $search[]  = '['.$tag.']';
      				$replace[] = redEVENTHelper::getEventDuration($this->_data);
      				break;
      				
				    case 'registrationend':
				      $search[]  = '['.$tag.']';
				      if (strtotime($this->_data->registrationend)) 
				      {
				      	$replace[] = strftime( $elsettings->formatdate . ' '. $elsettings->formattime, strtotime($this->_data->registrationend));
				  		}
				  		else {
				  			$replace[] = '';
				  		}
      				break;
      				
				    case 'venue':
				      $search[]  = '['.$tag.']';
      				$replace[] = $this->_data->venue;
      				break;
      				
				    case 'city':
				      $search[]  = '['.$tag.']';
      				$replace[] = $this->_data->location;
      				break;
      				
				    case 'username':
				      $search[]  = '['.$tag.']';
      				$replace[] = JRequest::getVar('subemailname', '');
      				break;
      				
				    case 'useremail':
				      $search[]  = '['.$tag.']';
      				$replace[] = JRequest::getVar('subemailaddress', '');
      				break;
      				
				    case 'venues':
				      $search[]  = '['.$tag.']';
      				$replace[] = $venues_html;
      				break;
      				
				    case 'regurl':
				      $search[]  = '['.$tag.']';
      				$replace[] = JRoute::_($uri->toString());
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
      				$replace[] = $this->_maxattendees - $this->_data->registered;
      				break;
      				
				    case 'waitinglistplacesleft':
				      $search[]  = '['.$tag.']';
      				$replace[] = $this->_maxwaitinglist - $this->_data->waiting;
      				break;
      				
				    case 'webformsignup':
				      $search[]  = '['.$tag.']';
              $replace[] = '<span class="vlink webform">'
                           . JHTML::_('link', 
                                      JRoute::_(RedeventHelperRoute::getSignupRoute('webform', $this->_data->id, $this->_xref)), 
                                      JHTML::_('image', $imagepath.$elsettings->signup_webform_img,  
                                      JText::_($elsettings->signup_webform_text), 
                                      'width="24px" height="24px"'))
                           .'</span> ';
              break;      				
      				
				    case 'emailsignup':
				      $search[]  = '['.$tag.']';
				      $replace[] = '<span class="vlink email">'
				                      .JHTML::_('link', 
                                        JRoute::_(RedeventHelperRoute::getSignupRoute('email', $this->_data->id, $this->_xref)), 
				                                JHTML::_('image', $imagepath.$elsettings->signup_email_img,  
				                                JText::_($elsettings->signup_email_text), 
				                                'width="24px" height="24px"'))
				                      .'</span> ';
              break;
      				
				    case 'formalsignup':
				      $search[]  = '['.$tag.']';
				      $replace[] = '<span class="vlink formaloffer">'
				                    .JHTML::_('link', 
                                      JRoute::_(RedeventHelperRoute::getSignupRoute('formaloffer', $this->_data->id, $this->_xref)), 
				                              JHTML::_('image', $imagepath.$elsettings->signup_formal_offer_img,  
				                              JText::_($elsettings->signup_formal_offer_text), 
				                              'width="24px" height="24px"'))
				                   .'</span> ';
              break;
      				
				    case 'externalsignup':
				      $search[]  = '['.$tag.']';
				      $replace[] = '<span class="vlink external">'
				                    .JHTML::_('link', 
				                              $this->_data->submission_type_external, 
				                              JHTML::_('image', $imagepath.$elsettings->signup_external_img,  
				                              $elsettings->signup_external_text), 
				                              'target="_blank"')
				                    .'</span> ';				      
              break;
      				
				    case 'phonesignup':
				      $search[]  = '['.$tag.']';
				      $replace[] = '<span class="vlink phone">'
				                     .JHTML::_('link', 
                                       JRoute::_(RedeventHelperRoute::getSignupRoute('phone', $this->_data->id, $this->_xref)), 
				                               JHTML::_('image', $imagepath.$elsettings->signup_phone_img,  
				                               JText::_($elsettings->signup_phone_text), 
				                               'width="24px" height="24px"'))
				                     .'</span> ';
				      break;
              
				    case 'webformsignuppage':
				      $search[]  = '['.$tag.']';
              // check that there is no loop with the tag inclusion
              if (preg_match('/\[[a-z]*signuppage\]/', $this->_data->submission_type_webform) == 0) {
                $replace[] = $this->ReplaceTags($this->_data->submission_type_webform);
              }
              else {
                JError::raiseNotice(0, JText::_('ERROR TAG LOOP XXXXSIGNUPPAGE'));
                $replace[] = '';
              }
      				break;
      				
				    case 'formalsignuppage':
				      $search[]  = '['.$tag.']';
              // check that there is no loop with the tag inclusion
              if (preg_match('/\[[a-z]*signuppage\]/', $this->_data->submission_type_formal_offer) == 0) {
                $replace[] = $this->_getFormalOffer($this->_data);
              }
              else {
                JError::raiseNotice(0, JText::_('ERROR TAG LOOP XXXXSIGNUPPAGE'));
                $replace[] = '';
              }
      				break;
      				
				    case 'phonesignuppage':
				      $search[]  = '['.$tag.']';
    					// check that there is no loop with the tag inclusion
    					if (preg_match('/\[[a-z]*signuppage\]/', $this->_data->submission_type_phone) == 0) {
    					  $replace[] = $this->ReplaceTags($this->_data->submission_type_phone);
    					}
    					else {
    						JError::raiseNotice(0, JText::_('ERROR TAG LOOP XXXXSIGNUPPAGE'));
    						$replace[] = '';
    					}
      				break;
      				
				    case 'emailsignuppage':
				      $search[]  = '['.$tag.']';
              // check that there is no loop with the tag inclusion
              if (preg_match('/\[[a-z]*signuppage\]/', $this->_data->submission_type_email) == 0) {
                $replace[] = $this->_getEmailSubmission($this->_data);
              }
              else {
                JError::raiseNotice(0, JText::_('ERROR TAG LOOP XXXXSIGNUPPAGE'));
                $replace[] = '';
              }
      				break;
      				
				    case 'venueimage':
				      $search[]  = '['.$tag.']';
      				$venueimage = redEVENTImage::flyercreator($this->_data->locimage);
      				$venueimage = JHTML::image(JURI::root().'/'.$venueimage['original'], $this->_data->venue, array('title' => $this->_data->venue));
      				$venueimage = JHTML::link(JRoute::_(RedeventHelperRoute::getVenueEventsRoute($this->_data->venueslug)), $venueimage);
      				$replace[] = $venueimage;
      				break;
      				
				    case 'eventimage':
				      $search[]  = '['.$tag.']';
              $eventimage = redEVENTImage::flyercreator($this->_data->datimage, 'event');
              $eventimage = JHTML::image(JURI::root().'/'.$eventimage['original'], $this->_data->title, array('title' => $this->_data->title));
      				$replace[] = $eventimage;
      				break;
      				
				    case 'categoryimage':
				      $search[]  = '['.$tag.']';
				      
      				$cats_images = array();
      				foreach ($this->_data->categories as $c){
      				  $cats_images[] = redEVENTImage::getCategoryImage($c);
      				}
      				$categoryimage = '<span class="details-categories-images"><span class="details-categories-image">'.implode('</span><span class="details-categories-image">', $cats_images).'</span></span>';

      				$replace[] = $categoryimage;
      				break;
      				
				    case 'info':
				      $search[]  = '['.$tag.']';
				      // check that there is no loop with the tag inclusion
              if (strpos($this->_data->details, '[info]') === false) {
                $info = $this->ReplaceTags($this->_data->details);
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
              foreach ($this->_data->categories as $c){
              	$cats[] = JHTML::link(JRoute::_(RedeventHelperRoute::getCategoryEvents($c->slug)), $c->catname);
              }
              $replace[] = '<span class="details-categories">'.implode(', ', $cats).'</span>';
      				break;
      				
				    case 'eventcomments':
				      $search[]  = '['.$tag.']';
              $replace[] = $this->_getComments($this->_data);
      				break;
      				
				    case 'venue_title':
				      $search[]  = '['.$tag.']';
              $replace[] = $this->_data->venue;
      				break;
      				
				    case 'venue_city':
				      $search[]  = '['.$tag.']';
              $replace[] = $this->_data->location;
      				break;
      				
				    case 'venue_street':
				      $search[]  = '['.$tag.']';
              $replace[] = $this->_data->street;
      				break;
      				
				    case 'venue_zip':
				      $search[]  = '['.$tag.']';
              $replace[] = $this->_data->plz;
      				break;
      				
				    case 'venue_link':
				      $search[]  = '['.$tag.']';
      				$replace[] = JHTML::link(JRoute::_(RedeventHelperRoute::getVenueEventsRoute($this->_data->venueslug)), $this->_data->venue);
      				break;
      				
				    case 'venue_website':
				      $search[]  = '['.$tag.']';
				      if (!empty($this->_data->venueurl)) {
      					$replace[] = JHTML::link(JRoute::_(($this->_data->venueurl)), JText::_('Venue website'));		      	
				      }
				      else {
				      	$replace[] = '';
				      }
      				break;
      				      				
				    case 'permanentlink':
				      $search[]  = '['.$tag.']';
              $replace[] = JHTML::link(JRoute::_(RedeventHelperRoute::getDetailsRoute($this->_data->slug), false), JText::_('Permanent link'), 'class="permalink"');
      				break;
      				
				    case 'paymentrequest':
				      $search[]  = '['.$tag.']';
				      $submit_key = JRequest::getVar('submit_key');
				      if (!empty($submit_key)) {
              	$replace[] = JHTML::link(JRoute::_('index.php?option=com_redform&controller=payment&task=select&source=redevent&key='.$submit_key, false), JText::_('Checkout'), '');
				      }
				      else {
				      	$replace[] = '';
				      }
				    	break;
				    	
				    case 'paymentrequestlink':
				      $search[]  = '['.$tag.']';
				      $submit_key = JRequest::getVar('submit_key');
				      if (!empty($submit_key)) {
              	$replace[] = JRoute::_('index.php?option=com_redform&controller=payment&task=select&source=redevent&key='.$submit_key, false);
				      }
				      else {
				      	$replace[] = '';
				      }
				    	break;
				  }
				    
				}
				// do the replace
				$message = str_replace($search, $replace, $page);
				
				
				/* Include redFORM */
				if (in_array('[redform]', $alltags[0]) && $this->_data->redform_id > 0) 
				{
				  $status = redEVENTHelper::canRegister($this->_xref);
				  if ($status->canregister) 
				  {
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
  					$params['eventdetails'] = $this->_data;		
  									
  					$results = $dispatcher->trigger('onPrepareEvent', array(& $form, $params, 0));
            $redform = $form->text;
				  }
				  else {
				    $redform = $status->status;
				  }
				  
				  /* second replacement, add the form */
					/* if done in first one, username in the form javascript is replaced too... */
				  $message = str_replace('[redform]', $redform, $message); 
				}	 
								
				// then the custom tags
				$search = array();
				$replace = array();
        foreach ($customfields as $tag => $data) 
        {
          $search[] = '['.$data->text_name.']';
          $replace[] = $data->text_field;
        }
        $message = str_ireplace($search, $replace, $message);
				
				
				// FIXME: I don't see the point of this relative to abs for pictures, only causing problems... I'll comment it for now.
				// FEEDBACK: relative to absolute images is necessary for e-mail messages that contain relative image links. The images won't show up in the e-mail.
				// FIXME: this function doesn't work when website is not at domain root... So it has to be fixed !
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
	private function SignUpLinks() 
	{
		$app = & JFactory::getApplication();
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
		$xcustoms = $this->getXrefCustomFields();
		
		$order_Dir = JRequest::getWord('filter_order_Dir', 'ASC');
		$order 		  = JRequest::getCmd('filter_order', 'x.dates');
		
		$db = JFactory::getDBO();
		$query = ' SELECT e.*, IF (x.course_credit = 0, "", x.course_credit) AS course_credit, x.course_price, '
		   . ' x.id AS xref, x.dates, x.enddates, x.times, x.endtimes, x.maxattendees, x.maxwaitinglist, v.venue, x.venueid, x.details, x.registrationend, '
		   . ' v.city AS location, v.state, v.url as venueurl, '
		   . ' v.country, v.locimage, v.street, v.plz, '
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
		   . ' LEFT JOIN #__redevent_event_category_xref AS xcat ON xcat.event_id = e.id '
		   . ' LEFT JOIN #__redevent_categories AS c ON xcat.category_id = c.id '
		   ;		   
	
		// add the custom fields tables
		foreach ((array) $xcustoms as $c)
		{
			$query .= ' LEFT JOIN #__redevent_fields_values AS c'. $c->id .' ON c'. $c->id .'.object_id = x.id AND c'. $c->id .'.field_id = '. $c->id;
		}
		
		$query .= ' WHERE x.published = '. $db->Quote($this->_published)
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
	    $q = "SELECT waitinglist, COUNT(id) AS total
	      FROM #__rwf_submitters
	      WHERE xref = ".$r->xref."
	      AND confirmed = 1
	      GROUP BY waitinglist";
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
	      $q = "SELECT COUNT(s.id) AS total
	        FROM #__rwf_submitters AS s
	        INNER JOIN #__redevent_register AS r USING(submit_key)
	        WHERE s.xref = ". $db->Quote($r->xref) ."
	        AND s.confirmed = 1
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
    <?php  /**
   * returns all custom fields for xrefs
   * 
   * @return array
   */
  function getXrefCustomFields()
  {
  	if (empty($this->_xrefcustomfields))
  	{
	  	$query = ' SELECT f.id, f.name, f.in_lists, f.searchable, f.ordering '
	  	       . ' FROM #__redevent_fields AS f'
	  	       . ' WHERE f.published = 1'
	  	       . '   AND f.object_key = '. $this->_db->Quote('redevent.xref')
	  	       . ' ORDER BY f.ordering ASC '
	  	       ;
	  	$this->_db->setQuery($query);
	  	$this->_xrefcustomfields = $this->_db->loadObjectList();
  	}
  	return $this->_xrefcustomfields;
  }
    $contents = ob_get_contents();
    ob_end_clean();
    return $contents;    
  }
  
  /**
   * get custom fields and their value
   *
   * @return unknown
   */
  function getCustomfields($xref)
  {
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
}
?>