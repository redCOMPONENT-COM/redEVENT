<?php
/** 
 * @copyright Copyright (C) 2008 redCOMPONENT.com. All rights reserved. 
 * @license can be read in this package of software in the file license.txt or 
 * read on http://redcomponent.com/license.txt  
 * Developed by email@recomponent.com - redCOMPONENT.com 
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');

/**
 * EventList Component Controller
 *
 * @package Joomla
 * @subpackage EventList
 * @since 0.9
 */
class RedeventController extends JController
{
	/**
	 * Constructor
	 *
	 * @since 0.9
	 */
	function __construct()
	{
		parent::__construct();
		
		//register extratasks
		$this->registerTask( 'ical', 'vcal' );
		
		// prevent issues with view name change in 2.0 beta 6.2
		if (JRequest::getVar('view') == 'eventlist') {
			JRequest::setVar('view', 'simplelist');
		}
	}

	/**
	 * Display the view
	 * 
	 * @since 0.9
	 */
	function display() 
	{
		// if filter is set, put the filter values as get variable so that the user can go back without warning
		if ($this->_checkfilter()) { // a redirect was set in the filter function
			return;
		}
		parent::display();
	}

	function _checkfilter()
	{
		$app = & JFactory::getApplication();
		
		if (!JRequest::getVar('filter', 0, 'post'))
		{
			return false;
		}
		
		switch (JRequest::getVar('view', ''))
		{
			case 'venuesmap':
				$url = 'index.php?option=com_redevent&view=venuesmap';
				$cat = JRequest::getVar('cat', '');
				if (!empty($cat)) {
					$url .= '&cat=' . $cat;
				}
        $vcat = JRequest::getVar('vcat', '');
        if (!empty($vcat)) {
          $url .= '&vcat=' . $vcat;
        }        
        $customs = $app->getUserStateFromRequest('com_redevent.venuesmap.customs', 'filtercustom', array(), 'array');
				$this->setRedirect(JRoute::_($url, false));
				break;
		}
	}
	
	/**
	 * Logic for canceling an event edit task
	 * 
	 * @since 0.9
	 */
	function cancelevent()
	{
		$user	= & JFactory::getUser();
		$id		= JRequest::getInt( 'id');

		// Must be logged in
		if ($user->get('id') < 1) {
			JError::raiseError( 403, JText::_('ALERTNOTAUTH') );
			return;
		}

		if ($id) {
			// Create and load a events table
			$row =& JTable::getInstance('redevent_events', '');

			$row->load($id);
			$row->checkin();

			$this->setRedirect( JRoute::_('index.php?option=com_redevent&view=details&xref='.JRequest::getInt('returnid'), false ) );

		} else {
			$link = JRequest::getString('referer', JURI::base(), 'post');
			$this->setRedirect($link);
		}
	}

	/**
	 * Logic for canceling an event and proceed to add a venue
	 * 
	 * @since 0.9
	 */
	function addvenue()
	{
		$user	= & JFactory::getUser();
		$id		= JRequest::getInt( 'id');

		// Must be logged in
		if ($user->get('id') < 1) {
			JError::raiseError( 403, JText::_('ALERTNOTAUTH') );
			return;
		}

		if ($id) {
			// Create and load a events table
			$row =& JTable::getInstance('redevent_events', '');

			$row->load($id);
			$row->checkin();
		}

		$this->setRedirect( JRoute::_('index.php?option=com_redevent&view=editvenue', false ) );
	}

	/**
	 * Logic for canceling a venue edit task
	 *
	 * @since 0.9
	 */
	function cancelvenue()
	{
		$user	= & JFactory::getUser();
		$id		= JRequest::getInt( 'id' );

		// Must be logged in
		if ($user->get('id') < 1) {
			JError::raiseError( 403, JText::_('ALERTNOTAUTH') );
			return;
		}

		if ($id) {
			// Create and load a venues table
			$row =& JTable::getInstance('redevent_venues', '');

			$row->load($id);
			$row->checkin();

			$this->setRedirect( JRoute::_('index.php?option=com_redevent&view=venueevents&id='.$id, false) );

		} else {
			$link = JRequest::getString('referer', JURI::base(), 'post');
			$this->setRedirect($link);
		}
	}

	/**
	 * Saves the submitted venue to the database
	 *
	 * @since 0.5
	 */
	function savevenue()
	{
		// Check for request forgeries
		JRequest::checkToken() or die( 'Invalid Token' );

		//Sanitize
		$post = JRequest::get( 'post' );
		$post['locdescription'] = JRequest::getVar( 'locdescription', '', 'post', 'string', JREQUEST_ALLOWRAW );

    $isNew = ($post['id']) ? false : true;
    
		$file 		= JRequest::getVar( 'userfile', '', 'files', 'array' );

		$model = $this->getModel('editvenue');

		if ($returnid = $model->store($post, $file)) {

			$msg 	= JText::_( 'VENUE SAVED' );
			$link 	= JRoute::_('index.php?option=com_redevent&view=venueevents&id='.$returnid, false) ;

				
			JPluginHelper::importPlugin( 'redevent' );
			$dispatcher =& JDispatcher::getInstance();
			$res = $dispatcher->trigger( 'onVenueEdited', array( $returnid, $isNew ) );
          
			$cache = &JFactory::getCache('com_redevent');
			$cache->clean();

		} else {

			$msg 		= '';
			$link = JRequest::getString('referer', JURI::base(), 'post');

			RedeventError::raiseWarning('SOME_ERROR_CODE', $model->getError() );
		}

		$model->checkin();

		$this->setRedirect($link, $msg );
	}

	/**
	 * Cleanes and saves the submitted event to the database
	 *
	 * TODO: Check if the user is allowed to post events assigned to this category/venue
	 *
	 * @since 0.4
	 */
	function saveevent()
	{
		// Check for request forgeries
		JRequest::checkToken() or die( 'Invalid Token' );
		
		//get image
		$file 		= JRequest::getVar( 'userfile', '', 'files', 'array' );
		$post 		= JRequest::get( 'post', 4 );
		$xref 		= JRequest::getInt('returnid');
		
		/* Get the form fields to display */
		$showfields = '';
		foreach ($post as $field => $value) {
			if (substr($field, 0, 9) == 'showfield' && $value == "1") {
				$showfields .= substr($field, 9).",";
			}
		}
		$post['showfields'] = substr($showfields, 0, -1);
		
    $isNew = ($post['id']) ? false : true;
		
		$model = $this->getModel('editevent');
		$this->addModelPath(JPATH_BASE.DS.'administrator'.DS.'components'.DS.'com_redevent'.DS.'models');
		$model_wait = $this->getModel('waitinglist');
		
		if ($returnid = $model->store($post, $file)) {

			/* Get all the xref values for this particular event */
			$db = JFactory::getDBO();
			$q = "SELECT id
				FROM #__redevent_event_venue_xref 
				WHERE eventid = ".$returnid;
			$db->setQuery($q);
			$existing_xrefs = $db->loadObjectList('id');
			
			/* Compute the differences */
			/* Now add all the xref values */
			foreach ($post['locid'] as $key => $locid) {
				foreach ($post['locid'.$locid] as $random => $datetimes) {
					if (isset($datetimes['dates'])) $dates = $datetimes['dates'];
					else $dates = 'NULL';
					if (isset($datetimes['enddates'])) $enddates = $datetimes['enddates'];
					else $enddates = 'NULL';
					if (isset($datetimes['times'])) $times = $datetimes['times'];
					else $times = 'NULL';
					if (isset($datetimes['endtimes'])) $endtimes = $datetimes['endtimes'];
					else $endtimes = 'NULL';
					if (isset($datetimes['maxattendees'])) $maxattendees = $datetimes['maxattendees'];
					else $maxattendees = 'NULL';
					if (isset($datetimes['maxwaitinglist'])) $maxwaitinglist = $datetimes['maxwaitinglist'];
					else $maxwaitinglist = 'NULL';
					if (isset($datetimes['course_price'])) $course_price = $datetimes['course_price'];
					else $course_price = 'NULL';
					if (isset($datetimes['course_credit'])) $course_credit = $datetimes['course_credit'];
					else $course_credit = 'NULL';
					if (isset($existing_xrefs[$random])) {
						$q = "UPDATE #__redevent_event_venue_xref 
							SET dates = ".$db->Quote($dates).",
							enddates = ".$db->Quote($enddates).", 
							times = ".$db->Quote($times).", 
							endtimes = ".$db->Quote($endtimes).",
							maxattendees = ".$db->Quote($maxattendees).",
							maxwaitinglist = ".$db->Quote($maxwaitinglist).",
							course_price = ".$db->Quote($course_price).",
							course_credit = ".$db->Quote($course_credit)."
							WHERE id = ".$random;
						$db->setQuery($q);
						$db->query();
						unset($existing_xrefs[$random]);
					}
					else {
						$q = "INSERT INTO #__redevent_event_venue_xref (eventid, venueid, dates, enddates, times, endtimes, maxattendees, maxwaitinglist, course_price, course_credit) VALUES ";
						$q .= "(".$returnid.", ".$locid.", ".$db->Quote($dates).", ".$db->Quote($enddates).", ".$db->Quote($times).", ".$db->Quote($endtimes)."
								, ".$db->Quote($maxattendees).", ".$db->Quote($maxwaitinglist).", ".$db->Quote($course_price).", ".$db->Quote($course_credit).")";
						$db->setQuery($q);
						$db->query();
						$xref = $db->insertid();
					}
				}
				
				$msg 	= JText::_( 'EVENT SAVED' );
				$link 	= JRoute::_('index.php?option=com_redevent&view=details&xref='.$xref, false) ;
			}
			
			if (count($existing_xrefs) > 0) {
				$remove_xrefs = array();
				foreach ($existing_xrefs as $xid => $xref) {
					$remove_xrefs[] = $xid;
				}
				$q = "DELETE FROM #__redevent_event_venue_xref
					WHERE id IN (".implode(',', $remove_xrefs).")";
				$db->setQuery($q);
				$db->query();
			}

      JPluginHelper::importPlugin( 'redevent' );
      $dispatcher =& JDispatcher::getInstance();
      $res = $dispatcher->trigger( 'onEventEdited', array( $returnid, $isNew ) );   
      
			$cache = &JFactory::getCache('com_redevent');
			$cache->clean();

		} else {

			$msg 		= '';
			$link = JRequest::getString('referer', JURI::base(), 'post');

			RedeventError::raiseWarning('SOME_ERROR_CODE', $model->getError() );
		}

		$model->checkin();
		
		/* Check if people need to be moved on or off the waitinglist */
		if ($post['id'] > 0) {
			$model_wait->setEventId($post['id']);
			$model_wait->UpdateWaitingList();
		}

		$this->setRedirect($link, $msg );
	}

	/**
	 * Saves the registration to the database
	 *
	 * @since 0.7
	 */
	function userregister()
	{
		$xref 	= JRequest::getInt( 'xref', 0 );
		$venueid = JRequest::getInt( 'venueid', 0 );

		// Get the model
		$model = $this->getModel('Details', 'RedeventModel');

		$model->setXref($xref);
		/* Store the user registration */
		$result = $model->userregister();
		if (!$result) {
      RedeventHelperLog::simpleLog("Error registering new user for xref $xref" . $model->getError());			
		}
		
		$this->addModelPath( JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_redevent' . DS . 'models' );
		$model_wait = $this->getModel('Waitinglist', 'RedEventModel');
		$model_wait->setXrefId($xref);
		$model_wait->UpdateWaitingList();
		
		JPluginHelper::importPlugin( 'redevent' );
		$dispatcher =& JDispatcher::getInstance();
		$res = $dispatcher->trigger( 'onEventUserRegistered', array( $xref ) );
      
		$cache = JFactory::getCache('com_redevent');
		$cache->clean();
		
		$link = 'index.php?option=com_redevent&view=confirmation&page=confirmation&xref='.$xref.'&submit_key='.JRequest::getVar('submit_key').'&action='.JRequest::getVar('action');
		if (JRequest::getBool('redformback', 0)) {
		  $link .= '&redformback=1&form_id='. JRequest::getInt('form_id');
		}
		/* Go to the confirmation page */
		$this->setRedirect(JRoute::_($link, false));
	}

	/**
	 * Deletes a registered user
	 *
	 * @since 0.7
	 */
	function delreguser()
	{
	  $mainframe = & JFactory::getApplication();
	  
	  $params  = & $mainframe->getParams('com_redevent');
	  
		// Check for request forgeries
		//JRequest::checkToken() or die( 'Invalid Token' );

		// TODO: is $id still usefull ? xref seems to be used in delreguser...
		$id 	= JRequest::getInt( 'id', 0 );
		
    $xref   = JRequest::getInt( 'xref', 0 );
    
		// Get/Create the model
		$model = $this->getModel('Details', 'RedeventModel');

		$model->setId($id);
		$model->delreguser();
		
		/* Check if we have space on the waiting list */
		$this->addModelPath(JPATH_BASE.DS.'administrator'.DS.'components'.DS.'com_redevent'.DS.'models');
		$model_wait = $this->getModel('waitinglist');
    $model_wait->setXrefId($xref);
		$model_wait->UpdateWaitingList();
		
//		JPluginHelper::importPlugin( 'redevent' );
//		$dispatcher =& JDispatcher::getInstance();
//		$res = $dispatcher->trigger( 'onEventUserUnregistered', array( $xref ) );
      
		$cache = JFactory::getCache('com_redevent');
		$cache->clean();

		$msg = JText::_( 'UNREGISTERED SUCCESSFULL' );
		
		if ($params->get('details_attendees_layout', 0)) {
		  $this->setRedirect( JRoute::_('index.php?option=com_redevent&view=details&id='.$id.'&tpl=attendees&xref=' . $xref, false), $msg );
		}
		else {
      $this->setRedirect( JRoute::_('index.php?option=com_redevent&view=details&id='.$id.'&tpl=attendees_table&xref=' . $xref, false), $msg );
    }
	}

	/**
	 * Display the select venue modal popup
	 *
	 * @since 0.9
	 */
	function selectvenue()
	{
		JRequest::setVar('view', 'editevent');
		JRequest::setVar('layout', 'selectvenue');

		parent::display();
	}

	/**
	 * offers the vcal/ical functonality
	 * 
	 * @todo Not yet working
	 *
	 * @author Lybegard Karl-Olof
	 * @since 0.9
	 */
	function vcal()
	{
		global $mainframe;

		$task 			= JRequest::getWord( 'task' );
		$id 			= JRequest::getInt( 'id' );
		$user_offset 	= $mainframe->getCfg( 'offset_user' );

		//get Data from model
		$model = & $this->getModel('Details', 'RedEventModel');
		$model->setId((int)$id);

		$row = $model->getDetails();

		$Start = mktime(strftime('%H', strtotime($row->times)),
				strftime('%M', strtotime($row->times)),
				strftime('%S', strtotime($row->times)),
				strftime('%m', strtotime($row->dates)),
				strftime('%d', strtotime($row->dates)),
				strftime('%Y', strtotime($row->dates)),0);

		$End   = mktime(strftime('%H', strtotime($row->endtimes)),
				strftime('%M', strtotime($row->endtimes)),
				strftime('%S', strtotime($row->endtimes)),
				strftime('%m', strtotime($row->enddates)),
				strftime('%d', strtotime($row->enddates)),
				strftime('%Y', strtotime($row->enddates)),0);

		require_once (JPATH_COMPONENT_SITE.DS.'classes'.DS.'vcal.class.php');

		$v = new vCal();

		$v->setTimeZone($user_offset);
		$v->setSummary($row->venue.'-'.$row->catname.'-'.$row->title);
		$v->setDescription($row->datdescription);
		$v->setStartDate($Start);
		$v->setEndDate($End);
		$v->setLocation($row->street.', '.$row->plz.', '.$row->city.', '.$row->country);
		$v->setFilename((int)$row->did);

		if ($task == 'vcal') {
			$v->generateHTMLvCal();
		} else {
			$v->generateHTMLiCal();
		}
		
	}
	
	/**
	 * Confirms the users request
	 */
	 function Confirm() {
		 global $mainframe;
		 
		 /* Get the confirm ID */
		 $confirmid = JRequest::getVar('confirmid', '', 'get');
		 
		 /* Get the details out of the confirmid */
		 list($uip, $xref, $uid, $submit_id, $submit_key) = split("x", $confirmid);
		 
		 /* This loads the tags replacer */
		 JRequest::setVar('xref', $xref);
		 require_once('helpers'.DS.'tags.php');
		 $tags = new redEVENT_tags;
		 
		 /* Check the db if this entry exists */
		 $db = JFactory::getDBO();
		 $q = "SELECT s.confirmed
		 	FROM #__redevent_register r
			LEFT JOIN #__rwf_submitters s
			ON r.submit_key = s.submit_key
			WHERE uip = '".str_replace('_', '.', $uip)."'
			AND uid = ".$uid."
			AND s.submit_key = ".$db->Quote($submit_key)."
			AND s.xref = ".$xref."
			AND s.answer_id = ".$submit_id;
		$db->setQuery($q);
		$regdata = $db->loadObject();
		
		if ($regdata && $regdata->confirmed == 0) {
			/* User exists, confirm the entry */
			$q = "UPDATE #__rwf_submitters
				SET confirmed = 1,
				confirmdate = NOW()
				WHERE answer_id = ".$submit_id;
			$db->setQuery($q);
			if ($db->query()) $this->setMessage(JText::_('YOUR SUBMISSION HAS BEEN CONFIRMED'));
			
			/* Update the waitinglist */
			$this->addModelPath( JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_redevent' . DS . 'models' );
			$model_wait = $this->getModel('Waitinglist', 'RedEventModel');
			/* Get the event id */
			$q = "SELECT eventid FROM #__redevent_event_venue_xref WHERE id = ".$xref;
			$db->setQuery($q);
			$eventid = $db->loadResult();
			$model_wait->setXrefId($xref);
			$model_wait->UpdateWaitingList();
			
			/* Confirm sign up via mail */
			$model_event = $this->getModel('Event', 'RedEventModel');
			$model_event->setId($eventid);
			$eventdata = $model_event->getData();
			
			if ($eventdata->notify) {
				$this->Mailer();
				/* Get the details per submitter */
				$query = "SELECT form_id, answer_id
						FROM #__rwf_submitters
						WHERE submit_key = ".$db->Quote($submit_key)." 
						AND xref = ".$xref."
						LIMIT 1";
				$db->setQuery($query);
				$id_details = $db->loadObject();
				
				/* Find out what the fieldname is for the email field */
				$q = "SELECT f.id
					FROM #__rwf_fields f, #__rwf_values v
					WHERE f.id = v.field_id
					AND f.published = 1
					AND f.form_id = ".$id_details->form_id."
					AND fieldtype = 'email'
					LIMIT 1";
				$db->setQuery($q);
				$selectfield = $db->loadResult();
				
				if (!empty($selectfield)) 
				{					
					/* Inform the ids that they can attend the event */
					$query = "SELECT ". $db->nameQuote('field_'. $selectfield) . "
							FROM #__rwf_forms_".$id_details->form_id."
							WHERE ID = ".$id_details->answer_id;
					$db->setQuery($query);
					$addresses = $db->loadResultArray();
					
					/* Check if there are any addresses to be mailed */
					if (count($addresses) > 0) {
						/* Start mailing */
						$this->Mailer();
						foreach ($addresses as $key => $email) {
							/* Send a off mailinglist mail to the submitter if set */
							/* Add the email address */
							$this->mailer->AddAddress($email);
							
							/* Mail submitter */
							$htmlmsg = '<html><head><title></title></title></head><body>'.$tags->ReplaceTags($eventdata->notify_confirm_body).'</body></html>';
							$this->mailer->setBody($htmlmsg);
							$this->mailer->setSubject($tags->ReplaceTags($eventdata->notify_confirm_subject));
							
							/* Send the mail */
							if (!$this->mailer->Send()) {
								$mainframe->enqueueMessage(JText::_('THERE WAS A PROBLEM SENDING MAIL'));
	              RedeventHelperLog::simpleLog('Error sending confirm email'.': '.$this->mailer->error);
							}
							
							/* Clear the mail details */
							$this->mailer->ClearAddresses();
						}
					}
				}
			}
		}
		else if ($regdata && $regdata->confirmed == 1) {
			$this->setMessage(JText::_('YOUR SUBMISSION HAS ALREADY BEEN CONFIRMED'));
		}
		else {
			$this->setMessage(JText::_('YOUR SUBMISSION CANNOT BE CONFIRMED'));
		}
		
		$this->setRedirect(JRoute::_('index.php?option=com_redevent&view=details&xref=' . $xref, false));
	 }
	 
	 /**
	 * Initialise the mailer object to start sending mails
	 */
	private function Mailer() {
		global $mainframe;
		jimport('joomla.mail.helper');
		/* Start the mailer object */
		$this->mailer = JFactory::getMailer();
		$this->mailer->isHTML(true);
		$this->mailer->From = $mainframe->getCfg('mailfrom');
		$this->mailer->FromName = $mainframe->getCfg('sitename');
		$this->mailer->AddReplyTo(array($mainframe->getCfg('mailfrom'), $mainframe->getCfg('sitename')));
	}
}
?>