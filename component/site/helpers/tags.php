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
        JError::raiseError(404, 'This event is not published', 'test');
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
	 */
	public function ReplaceTags($page) 
	{
		//exit($page);
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
				preg_match_all("/\[(.+?)\]/", $page, $alltags);
				$customdata = $this->getCustomData($alltags);
				
				$customfields = $this->getCustomFields($this->_data->id);
				
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
				$eventplacesleft = $this->_maxattendees - $this->_data->registered;
				$waitinglistplacesleft = $this->_maxwaitinglist - $this->_data->waiting;
				
				/* Include redFORM */
				$redform = '';
				if ($this->_data->redform_id > 0) {
				  $status = redEVENTHelper::canRegister($this->_xref);
				  if ($status->canregister) {
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
  									
  					$results = $dispatcher->trigger('onPrepareContent', array(& $form, $params, 0));
            $redform = $form->text;
				  }
				  else {
				    $redform = $status->status;
				  }
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
				
				//signup pages
				if (in_array('[phonesignuppage]', $alltags[0])) {
					// check that there is no loop with the tag inclusion
					if (preg_match('/\[[a-z]*signuppage\]/', $this->_data->submission_type_phone) == 0) {
					  $phonesignuppage = $this->ReplaceTags($this->_data->submission_type_phone);
					}
					else {
						JError::raiseNotice(0, JText::_('ERROR TAG LOOP XXXXSIGNUPPAGE'));
						$phonesignuppage = '';
					}
				}
				else {
					$phonesignuppage = '';
				}
				
        if (in_array('[webformsignuppage]', $alltags[0])) {
          // check that there is no loop with the tag inclusion
          if (preg_match('/\[[a-z]*signuppage\]/', $this->_data->submission_type_webform) == 0) {
            $webformsignuppage = $this->ReplaceTags($this->_data->submission_type_webform);
          }
          else {
            JError::raiseNotice(0, JText::_('ERROR TAG LOOP XXXXSIGNUPPAGE'));
            $webformsignuppage = '';
          }
        }
        else {
          $webformsignuppage = '';
        }
        
        if (in_array('[formalsignuppage]', $alltags[0])) {
          // check that there is no loop with the tag inclusion
          if (preg_match('/\[[a-z]*signuppage\]/', $this->_data->submission_type_formal_offer) == 0) {
            $formalsignuppage = $this->_getFormalOffer($this->_data);
          }
          else {
            JError::raiseNotice(0, JText::_('ERROR TAG LOOP XXXXSIGNUPPAGE'));
            $formalsignuppage = '';
          }
        }
        else {
          $formalsignuppage = '';
        }
        
        if (in_array('[emailsignuppage]', $alltags[0])) {
          // check that there is no loop with the tag inclusion
          if (preg_match('/\[[a-z]*signuppage\]/', $this->_data->submission_type_email) == 0) {
            $emailsignuppage = $this->_getEmailSubmission($this->_data);
          }
          else {
            JError::raiseNotice(0, JText::_('ERROR TAG LOOP XXXXSIGNUPPAGE'));
            $emailsignuppage = '';
          }
        }
        else {
          $emailsignuppage = '';
        }
        
			  // xref details
        if (in_array('[info]', $alltags[0])) {
          // check that there is no loop with the tag inclusion
          if (strpos($this->_data->details, '[info]') === false) {
            $info = $this->ReplaceTags($this->_data->details);
          }
          else {
            JError::raiseNotice(0, JText::_('ERROR TAG LOOP XREF DETAILS'));
            $info = '';
          }
        }
        else {
          $info = '';
        }
				
				//images
				$venueimage = redEVENTImage::flyercreator($this->_data->locimage);
				$venueimage = JHTML::image(JURI::root().'/'.$venueimage['original'], $this->_data->venue, array('title' => $this->_data->venue));
        $eventimage = redEVENTImage::flyercreator($this->_data->datimage, 'event');
        $eventimage = JHTML::image(JURI::root().'/'.$eventimage['original'], $this->_data->title, array('title' => $this->_data->title));
				
        // categories
        $cats = array();
        $cats_images = array();
        foreach ($this->_data->categories as $c){
        	$cats[] = JHTML::link(JRoute::_('index.php?option=com_redevent&view=categoryevents&id=' . $c->slug), $c->catname);
          $cats_images[] = redEVENTImage::getCategoryImage($c);
        }
        $category = '<span class="details-categories">'.implode(', ', $cats).'</span>';
        $categoryimage = '<span class="details-categories-images"><span class="details-categories-image">'.implode('</span><span class="details-categories-image">', $cats_images).'</span></span>';
        
        //comments
        $eventcomments = $this->_getComments($this->_data);
        
				/* tags  replacements array */
				$findoffer = array('[event_description]', '[event_title]', '[price]', '[credits]', '[code]', '[inputname]', '[inputemail]', '[submit]',
									'[event_info_text]', '[time]', '[date]', '[duration]', '[venue]', '[city]', '[username]', '[useremail]', '[venues]','[regurl]',
									'[eventplaces]', '[waitinglistplaces]', '[eventplacesleft]', '[waitinglistplacesleft]'
				          , '[webformsignup]', '[emailsignup]', '[formalsignup]', '[externalsignup]', '[phonesignup]'
				          , '[phonesignuppage]', '[webformsignuppage]', '[formalsignuppage]', '[emailsignuppage]'
				          , '[venueimage]', '[eventimage]', '[categoryimage]'
                  , '[info]'
				          , '[category]'
				          , '[eventcomments]'
				          );
				$replaceoffer = array($event_description, $this->_data->title, $price, $this->_data->course_credit, $this->_data->course_code, 
									$name, $email, $submit, $event_info_description, $time, $date, $duration, $this->_data->venue, $this->_data->location,
									$username, $useremail, $venues_html, $regurl, $this->_maxattendees, $this->_maxwaitinglist, $eventplacesleft, $waitinglistplacesleft, 
									$webformsignup, $emailsignup, $formalsignup, $externalsignup, $phonesignup
									, $phonesignuppage, $webformsignuppage, $formalsignuppage, $emailsignuppage
                  , $venueimage, $eventimage, $categoryimage
                  , $info                  
                  , $category
                  , $eventcomments
                  );
				/* First tag replacement */
				$message = str_replace($findoffer, $replaceoffer, $page);
				
			  /* second replacement, add the form */
				/* if done in first one, username in the form javascript is replaced too... */
				$message = str_replace('[redform]', $redform, $message); 
				
				// then the tags from the custom library
				foreach ($customdata as $tag => $data) {
					$data->text_field = str_replace($findoffer, $replaceoffer, $data->text_field);
					/* Do a redFORM replacement here too for when used in the text library */
					$data->text_field = str_replace('[redform]', $redform, $data->text_field);
					$message = str_ireplace('['.$tag.']', $data->text_field, $message);
				}
				
        // then the tags from the custom library
        foreach ($customfields as $tag => $data) 
        {
//          // in case tags are used in custom fields...
//          $data->text_field = str_replace($findoffer, $replaceoffer, $data->text_field);
//          /* Do a redFORM replacement here too for when used in the text library */
//          $data->text_field = str_replace('[redform]', $redform, $data->text_field);
          $message = str_ireplace('['.$tag.']', $data->text_field, $message);
        }
				
				// then the custom tags
				
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
	private function getEventLinks() 
	{
		$db = JFactory::getDBO();
		$q = " SELECT e.*, IF (x.course_credit = 0, '', x.course_credit) AS course_credit, x.course_price, "
		    . " x.id AS xref, x.dates, x.enddates, x.times, x.endtimes, x.maxattendees, x.maxwaitinglist, v.venue, x.venueid, x.details, x.registrationend,
					v.city AS location,
					v.country, v.locimage,
					UNIX_TIMESTAMP(x.dates) AS unixdates,
          CASE WHEN CHAR_LENGTH(v.alias) THEN CONCAT_WS(':', v.id, v.alias) ELSE v.id END as venueslug
			FROM #__redevent_events AS e
			INNER JOIN #__redevent_event_venue_xref AS x ON x.eventid = e.id
			INNER JOIN #__redevent_venues AS v ON x.venueid = v.id
      LEFT JOIN #__redevent_event_category_xref AS xcat ON xcat.event_id = e.id
      LEFT JOIN #__redevent_categories AS c ON xcat.category_id = c.id
			WHERE x.published = ". $db->Quote($this->_published) ."
			AND e.id IN (".$this->_eventid.")
      GROUP BY x.id
      ORDER BY x.dates, x.times
			";
		$db->setQuery($q);
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
	 *
	 */
	private function getCustomData($customtags) {
		$db = JFactory::getDBO();
		$q = "SELECT text_name, text_field
			FROM #__redevent_textlibrary
			WHERE text_name ='". implode( "' OR text_name ='", $customtags[1] )."'";
		$db->setQuery($q);
		return $db->loadObjectList('text_name');
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
   * @return unknown
   */
  function getCustomfields($event_id)
  {
  	$db = & JFactory::getDBO();
    $query = ' SELECT f.*, fv.value '
           . ' FROM #__redevent_fields AS f '
           . ' INNER JOIN #__redevent_fields_values AS fv ON fv.field_id = f.id AND fv.object_id = '.(int) $event_id
           . ' WHERE f.published = 1 AND f.object_key = '. $db->Quote("redevent.event")
           . ' ORDER BY f.ordering ASC '
           ;
    $db->setQuery($query);
    $fields = $db->loadObjectList();
    
    $replace = array();
    foreach ($fields as $field)
    {
    		$obj = new stdclass();
    		$obj->text_name = $field->tag;
        $obj->text_field = redEVENTHelper::renderFieldValue($field);
        $replace[$field->tag] = $obj;
    }
    
    return $replace;
  }
}
?>