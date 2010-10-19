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
		$this->registerTask( 'managedelreguser', 'delreguser' );
		$this->registerTask( 'unpublishxref', 'publishxref' );
		$this->registerTask( 'archivexref', 'publishxref' );
		
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
		
		$view = JRequest::getVar('view', '');
		
		$method = '_display'.ucfirst($view);
		if (method_exists($this, $method)) {
			return $this->$method();
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
        $customs = $app->getUserStateFromRequest('com_redevent.filter.customs', 'filtercustom', array(), 'array');
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

			$this->setRedirect( JRoute::_('index.php?option=com_redevent&view=venueevents&id='.$id) );

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
		$acl        = UserAcl::getInstance();

		//Sanitize
		$post = JRequest::get( 'post' );
		$post['locdescription'] = JRequest::getVar( 'locdescription', '', 'post', 'string', JREQUEST_ALLOWRAW );

    $isNew = ($post['id']) ? false : true;    
	
		if (!$isNew && !$acl->canEditVenue($post['id'])) {
			$msg = JText::_('REDEVENT_USER_NOT_ALLOWED_TO_EDIT_THIS_VENUE');
			$this->setRedirect(JRoute::_(RedeventHelperRoute::getVenueEventsRoute($post['id'])), $msg, 'error' );
			return;
		}
		else if ($isNew && !$acl->canAddVenue()) {
			$msg =  JText::_('REDEVENT_USER_NOT_ALLOWED_TO_ADD_VENUE');
			$link = JRequest::getString('referer', JURI::base(), 'post');
			$this->setRedirect($link, $msg, 'error' );
			return;			
		}
    
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

			RedeventError::raiseWarning('REDEVENT_GENERIC_ERROR', $model->getError() );
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
				
    $isNew = ($post['id']) ? false : true;
		
		$model = $this->getModel('editevent');
		$this->addModelPath(JPATH_BASE.DS.'administrator'.DS.'components'.DS.'com_redevent'.DS.'models');
		$model_wait = $this->getModel('waitinglist');
		
		if ($returnid = $model->store($post, $file)) 
		{		
      JPluginHelper::importPlugin( 'redevent' );
      $dispatcher =& JDispatcher::getInstance();
      $res = $dispatcher->trigger( 'onEventEdited', array( $returnid, $isNew ) );   
      
			$cache = &JFactory::getCache('com_redevent');
			$cache->clean();
			$msg 		= 'saved';
//			$link = JRequest::getString('referer', RedeventHelperRoute::getMyeventsRoute(), 'post');
		} 
		else 
		{
			$msg 		= $model->getError();
//			$link = JRequest::getString('referer', RedeventHelperRoute::getMyeventsRoute(), 'post');

			RedeventError::raiseWarning(0, $model->getError() );
		}

		$model->checkin();
		
		$link = RedeventHelperRoute::getMyeventsRoute();
		$this->setRedirect($link, $msg );
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
		
		$task = JRequest::getVar('task');
		
		if ($task == 'managedelreguser')
		{
			$msg = JText::_( 'REGISTRATION REMOVAL SUCCESSFULL' );			
	    $this->setRedirect( JRoute::_(RedeventHelperRoute::getManageAttendees($xref, 'manageattendees'), false), $msg );
		}
		else
		{
			$msg = JText::_( 'UNREGISTERED SUCCESSFULL' );
			
			if ($params->get('details_attendees_layout', 0)) {
			  $this->setRedirect( JRoute::_('index.php?option=com_redevent&view=details&id='.$id.'&tpl=attendees&xref=' . $xref, false), $msg );
			}
			else {
	      $this->setRedirect( JRoute::_('index.php?option=com_redevent&view=details&id='.$id.'&tpl=attendees_table&xref=' . $xref, false), $msg );
	    }
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
	
	
	function savexref()
	{
		// Check for request forgeries
		JRequest::checkToken() or die( 'Invalid Token' );
		
		//get image
		$post 		= JRequest::get('post');
		$xref 		= JRequest::getInt('id');
		
		$post['details'] = JRequest::getVar( 'details', '', 'post', 'string', JREQUEST_ALLOWRAW );
		
		$model = $this->getModel('editevent');
		$this->addModelPath(JPATH_BASE.DS.'administrator'.DS.'components'.DS.'com_redevent'.DS.'models');
		$model_wait = $this->getModel('waitinglist');
		
		if ($returnid = $model->storeXref($post)) 
		{
			/* Check if people need to be moved on or off the waitinglist */
			if ($xref) 
			{
				$model_wait->setXrefId($xref);
				$model_wait->UpdateWaitingList();
			}
		
			$msg = JText::_('EVENT DATE SAVED');
			$this->setRedirect(JRoute::_(RedeventHelperRoute::getMyEventsRoute(), false), $msg);				
		}
		else {
			$msg = JText::_('SUBMIT XREF ERROR').$model->getError();
			$this->setRedirect(JRoute::_(RedeventHelperRoute::getMyEventsRoute(), false), $msg, 'error');			
		}		
	}
	
	function publishxref()
	{
		$acl = new UserAcl();
		$xref = JRequest::getInt('xref');
				
		if (!$acl->canPublishXref($xref)) {
			$msg = JText::_('MYEVENTS_CHANGE_PUBLISHED_STATE_NOTE_ALLOWED');
			$this->setRedirect(JRoute::_(RedeventHelperRoute::getMyEventsRoute(), false), $msg, 'error');		
			return;
		}
		
		$model = $this->getModel('editevent');
		$task = JRequest::getVar('task');
		switch (JRequest::getVar('task'))
		{
			case 'publishxref':
				$newstate = 1;
				break;
			case 'unpublishxref':
				$newstate = 0;
				break;
			case 'archivexref':
				$newstate = -1;
				break;
		}
		
		if ($model->publishxref($xref, $newstate)) {
			$msg = JText::_('PUBLISHED STATE UPDATED');
			$this->setRedirect(JRoute::_(RedeventHelperRoute::getMyEventsRoute(), false), $msg);				
		}
		else {
			$msg = JText::_('PUBLISHED STATE UPDATE ERROR').'<br>'.$model->getError();
			$this->setRedirect(JRoute::_(RedeventHelperRoute::getMyEventsRoute(), false), $msg, 'error');			
		}		
	}

	function deletexref()
	{		
		$acl = new UserAcl();
		$xref = JRequest::getInt('xref');
	
		if (!$acl->canEditXref($xref)) {
			$msg = JText::_('MYEVENTS_DELETE_XREF_NOTE_ALLOWED');
			$this->setRedirect(JRoute::_(RedeventHelperRoute::getMyEventsRoute(), false), $msg, 'error');		
			return;
		}
		
		$model = $this->getModel('editevent');
		
		if ($model->deletexref($xref)) {
			$msg = JText::_('EVENT DATE DELETED');
			$this->setRedirect(JRoute::_(RedeventHelperRoute::getMyEventsRoute(), false), $msg);				
		}
		else {
			$msg = JText::_('EVENT DATE DELETION ERROR').'<br>'.$model->getError();
			$this->setRedirect(JRoute::_(RedeventHelperRoute::getMyEventsRoute(), false), $msg, 'error');			
		}		
	}
	
  function insertevent()
  {
		JRequest::setVar( 'view', 'simplelist' );
		JRequest::setVar( 'layout', 'editors-xtd'  );
		JRequest::setVar( 'filter_state', 'P'  );

		parent::display();  	
  }
  


	/**
	 * Display the details view
	 * 
	 * @since 2.0
	 */
	function _displayDetails() 
	{		
		if (JRequest::getVar('format', 'html') == 'html')
		{
			/* Create the view object */
			$view = $this->getView('details', 'html');
			$this->addModelPath(JPATH_BASE.DS.'administrator'.DS.'components'.DS.'com_redevent'.DS.'models');
			
			/* Standard model */
			$view->setModel( $this->getModel( 'details', 'RedeventModel' ), true );
			$view->setModel( $this->getModel( 'waitinglist', 'RedeventModel' ));
			$view->setModel( $this->getModel( 'event', 'RedeventModel' ));
			$view->setLayout( JRequest::getCmd( 'layout', 'default' ));
			
			/* Now display the view. */
			$view->display();
		}
		else {
			parent::display();
		}
	}
	
	/**
	 * Load custom models for venue upcoming events view
	 */
	function _displayUpcomingvenueevents() 
	{
		/* Create the view object */
		if (JRequest::getVar('format') == 'feed') {
			$view = $this->getView('upcomingvenueevents', 'feed');
		}
		else {
			$view = $this->getView('upcomingvenueevents', 'html');
		}
		
		/* Standard model */
		$view->setModel( $this->getModel( 'upcomingvenueevents', 'RedeventModel' ), true );
		$view->setModel( $this->getModel( 'venueevents', 'RedeventModel' ));
		
		/* Now display the view. */
		$view->display();
	}
	


	/**
	 * Display the signup view
	 * 
	 * @since 2.0
	 */
	function _displaySignup() 
	{	  
		if (JRequest::getVar('format', 'html') == 'html')
		{
  		/* Create the view object */
  		$view = $this->getView('signup', 'html');
  		$this->addModelPath(JPATH_BASE.DS.'administrator'.DS.'components'.DS.'com_redevent'.DS.'models');
  		
  		/* Standard model */
  		$view->setModel( $this->getModel( 'signup', 'RedeventModel' ), true );
  		$view->setModel( $this->getModel( 'details', 'RedeventModel' ) );
  		$view->setLayout('default');
  		
  		/* Now display the view. */
  		$view->display();
		}
		else
		{
		  parent::display();
		}
	}
	

	/**
	 * send reminder emails
	 */
	function reminder()
	{
		jimport('joomla.filesystem.file');
		$app = &JFactory::getApplication();
		$params = $app->getParams('com_redevent');
		
		$file = JPATH_COMPONENT_SITE.DS.'reminder.txt';
		if (JFile::exists($file))
		{
			$date = (int) JFile::read($file);
		}
		else
		{
			$date = 0;
		}
		
		// only run this once a day
		echo sprintf("last update on %s<br/>", strftime('%Y-%m-%d %H:%M', $date));
		if (time() - $date < 3600*23.9 && !JRequest::getVar('force', 0)) {
			echo "reminder sent less the 24 hours ago<br/>";
			return;
		}
		
		$model = $this->getModel('attendees');
		
		$events = $model->getReminderEvents($params->get('reminder_days', 14));
		
		if ($events && count($events))
		{
			$mailer   = &JFactory::getMailer();
			$MailFrom = $app->getCfg('mailfrom');
			$FromName = $app->getCfg('fromname');
			$mailer->setSender( array( $MailFrom, $FromName ) );
			$mailer->IsHTML(true);
			
			$subject = $params->get('reminder_subject');
			$body = $params->get('reminder_body');
									
			foreach ($events as $event)
			{
				echo "sending reminder for event: ".$event->title."<br>";				
				
				$tags = new redEVENT_tags();
				$tags->setXref($event->id);
								                
				// get attendees
				$attendees = $model->getAttendeesEmails($event->id);
				if (!$attendees) {
					continue;
				}
				foreach ($attendees as $sid => $a)
				{
					$msubject = $tags->ReplaceTags($subject, array('sids' => array($sid)));
					$mbody    = '<html><body>'.$tags->ReplaceTags($body).'</body></html>';
					$mailer->setSubject($msubject);
					$mailer->setBody($mbody);
					
					$mailer->clearAllRecipients();
					$mailer->addRecipient( $a );
//					echo '<pre>';print_r($mailer); echo '</pre>';
//					dump($mailer);
					$sent = $mailer->Send();
				}
			}
		}
		else
		{
			echo 'No events for this reminder interval<br/>';
		}
		
		// update file
		JFile::write($file, time());
	}
}
?>