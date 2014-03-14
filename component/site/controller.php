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
 * @subpackage redEVENT
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

		$input = JFactory::getApplication()->input;

		// Default list layout for certain views
		switch ($view)
		{
			case 'categoryevents':
			case 'simplelist':
			case 'venueevents':
				if (!$input->get('layout'))
				{
					$input->def('layout', JFactory::getApplication()->getParams('com_redevent')->get('default_list_layout', 'table'));
				}
				break;

			case 'frontadmin':
				if (!$input->get('tmpl'))
				{
					$input->set('tmpl', 'component');
				}
				break;
		}

		parent::display();
	}

	function _checkfilter()
	{
		$app = & JFactory::getApplication();

		$post = JRequest::get('post');
		$uri  = Jfactory::getUri();

		$myuri = clone($uri); // do not modify it if not proper view...
		$vars = 0;
		foreach ($post as $filter => $v)
		{
			switch ($filter)
			{
				case 'filter_category':
				case 'filter_multicategory':
				case 'filter_venuecategory':
				case 'filter_order':
				case 'filter_order_Dir':
				case 'filter':
				case 'filter_type':
				case 'filter_venue':
				case 'filter_multivenue':
				case 'layout':
				case 'task':
					if ($v)
					{
						$myuri->setVar($filter, $v);
						$vars++;
					}
					break;
				case 'filtercustom':
					$filt = array();
					foreach ((array) $v as $n => $val)
					{
						if (is_array($val))
						{
							//							echo '<pre>';print_r($val); echo '</pre>';exit;
							$r = array();
							foreach ($val as $sub) {
								if ($sub) $r[] = $sub;
							}
							$myuri->setVar("filtercustom[$n]", $r);
						}
						else {
							if ($val) $filt[$n] = $val;
						}
					}
					if (count($filt)) {
						//						echo '<pre>';print_r($filt); echo '</pre>';exit;
						$myuri->setVar($filter, $filt);
						$vars++;
					}
					break;
			}
		}

		if ($vars)
		{
			switch (JRequest::getVar('view', ''))
			{
				case 'categoryevents':
				case 'venueevents':
				case 'simplelist':
				case 'venuesmap':
				case 'search':
					$this->setRedirect(JRoute::_($myuri->toString(), false));
					break;
			}
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
		$xref		= JRequest::getInt( 'xref');

		$msg = JText::_('COM_REDEVENT_ACTION_CANCELLED');

		switch (JRequest::getWord('referer'))
		{
			case 'myevents':
				$link = JRoute::_(RedeventHelperRoute::getMyeventsRoute(), false);
				break;
			default:
				if ($id) {
					$link = JRoute::_(RedeventHelperRoute::getDetailsRoute($id, $xref), false);
				}
				else {
					$link = JRoute::_(RedeventHelperRoute::getMyeventsRoute(), false);
				}
		}

		// Must be logged in
		if ($user->get('id') < 1) {
			$this->setRedirect('index.php',JText::_('COM_REDEVENT_Only_logged_users_can_access_this_page'), 'error');
			$this->redirect();
			return;
		}

		if ($id) {
			// Create and load a events table
			$row =& JTable::getInstance('redevent_events', '');

			$row->load($id);
			$row->checkin();

			$this->setRedirect($link, $msg);

		} else {
			$this->setRedirect($link, $msg);
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
			$this->setRedirect('index.php',JText::_('COM_REDEVENT_Only_logged_users_can_access_this_page'), 'error');
			$this->redirect();
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
			$this->setRedirect('index.php',JText::_('COM_REDEVENT_Only_logged_users_can_access_this_page'), 'error');
			$this->redirect();
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
			$msg = JText::_('COM_REDEVENT_USER_NOT_ALLOWED_TO_EDIT_THIS_VENUE');
			$this->setRedirect(JRoute::_(RedeventHelperRoute::getVenueEventsRoute($post['id'])), $msg, 'error' );
			return;
		}
		else if ($isNew && !$acl->canAddVenue()) {
			$msg =  JText::_('COM_REDEVENT_USER_NOT_ALLOWED_TO_ADD_VENUE');
			$link = JRequest::getString('referer', JURI::base(), 'post');
			$this->setRedirect($link, $msg, 'error' );
			return;
		}

		$file 		= JRequest::getVar( 'userfile', '', 'files', 'array' );

		$model = $this->getModel('editvenue');

		if ($returnid = $model->store($post, $file)) {

			$msg 	= JText::_('COM_REDEVENT_VENUE_SAVED' );

			JPluginHelper::importPlugin( 'redevent' );
			$dispatcher =& JDispatcher::getInstance();
			$res = $dispatcher->trigger( 'onVenueEdited', array( $returnid, $isNew ) );

			$cache = &JFactory::getCache('com_redevent');
			$cache->clean();

		} else {

			$msg 		= '';

			RedeventError::raiseWarning('REDEVENT_GENERIC_ERROR', $model->getError() );
		}

		$model->checkin();
		$link = JRequest::getString('referer', RedeventHelperRoute::getMyeventsRoute());

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
		$app = JFactory::getApplication();

		// Check for request forgeries
		JRequest::checkToken() or die( 'Invalid Token' );

		//get image
		$file 		= JRequest::getVar( 'userfile', '', 'files', 'array' );
		$post 		= JRequest::get( 'post', 4 );

		$isNew = ($post['id']) ? false : true;

		$model = $this->getModel('editevent');
		$this->addModelPath(JPATH_BASE.DS.'administrator'.DS.'components'.DS.'com_redevent'.DS.'models');
		$model_wait = $this->getModel('waitinglist');

		if ($row = $model->store($post, $file))
		{
			JPluginHelper::importPlugin( 'redevent' );
			$dispatcher =& JDispatcher::getInstance();
			$res = $dispatcher->trigger( 'onEventEdited', array( $row->id, $isNew ) );

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

		if ($app->input->get('tmpl') == 'component')
		{
			// Ajax mode, just output result and stop.
			echo $msg;
			$app->close();
		}

		switch (JRequest::getWord('referer'))
		{
			case 'myevents':
				$link = JRoute::_(RedeventHelperRoute::getMyeventsRoute(), false);
				break;
			default:
				if ($row && $row->published) {
					$link = JRoute::_(RedeventHelperRoute::getDetailsRoute($row->id, ($row->xref ? $row->xref : null) ), false);
				}
				else {
					$link = JRoute::_(RedeventHelperRoute::getMyeventsRoute(), false);
				}
		}
		$this->setRedirect($link, $msg );
	}

	/**
	 * Deletes a registered user
	 *
	 * @since 0.7
	 */
	function delreguser()
	{
		$app = JFactory::getApplication();

		$msgtype = 'message';
		$task    = $app->input->getCmd('task');
		$id      = $app->input->getInt('id', 0);
		$xref    = $app->input->getInt('xref', 0);

		$params  = $app->getParams('com_redevent');

		if ($this->cancelRegistration())
		{
			if ($task == 'managedelreguser')
			{
				$msg = JText::_('COM_REDEVENT_REGISTRATION_REMOVAL_SUCCESSFULL');
			}
			else
			{
				$msg = JText::_('COM_REDEVENT_UNREGISTERED_SUCCESSFULL');
			}
		}
		else
		{
			$msg = $this->getError();
			$msgtype = 'error';
		}

		// Redirect
		if ($task == 'managedelreguser')
		{
			$this->setRedirect(JRoute::_(RedeventHelperRoute::getManageAttendees($xref, 'manageattendees'), false), $msg, $msgtype);
		}
		else
		{
			if ($params->get('details_attendees_layout', 0))
			{
				$this->setRedirect(JRoute::_('index.php?option=com_redevent&view=details&id=' . $id . '&tpl=attendees&xref=' . $xref, false), $msg, $msgtype);
			}
			else
			{
				$this->setRedirect(JRoute::_('index.php?option=com_redevent&view=details&id=' . $id . '&tpl=attendees_table&xref=' . $xref, false), $msg, $msgtype);
			}
		}
	}

	/**
	 * Ajax cancel registration
	 *
	 * @return void
	 */
	public function ajaxcancelregistration()
	{
		$resp = new stdClass();

		if ($this->cancelRegistration())
		{
			$resp->status = 1;
		}
		else
		{
			$resp->status = 0;
			$resp->error = $this->getError();
		}

		echo json_encode($resp);
		JFactory::getApplication()->close();
	}

	/**
	 * Actually do the cancel work (with notifications)
	 *
	 * @return bool
	 */
	protected function cancelRegistration()
	{
		$app = JFactory::getApplication();

		$rid  = $app->input->getInt('rid', 0);
		$xref = $app->input->getInt('xref', 0);

		// Get/Create the model
		$model = $this->getModel('Registration', 'RedeventModel');

		if (!$model->cancelregistration($rid, $xref))
		{
			$msg = $model->getError();
			$this->setError($msg);
			return false;
		}

		/* Check if we have space on the waiting list */
		$this->addModelPath(JPATH_BASE . '/administrator/components/com_redevent/models');
		$model_wait = $this->getModel('waitinglist');
		$model_wait->setXrefId($xref);
		$model_wait->UpdateWaitingList();

		JPluginHelper::importPlugin('redevent');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onAttendeeCancelled', array($rid));

		$cache = JFactory::getCache('com_redevent');
		$cache->clean();

		// Send unreg notification email
		$key = redEVENTHelper::getAttendeeSubmitKey($rid);
		$model->notifyManagers($key, true, $rid);

		return true;
	}

	/**
	 * first step in unreg process by email
	 *
	 */
	function cancelreg()
	{
		$xref = JRequest::getInt('xref');
		if (!redEVENTHelper::canUnregister($xref)) {
			echo JText::_('COM_REDEVENT_UNREGISTRATION_NOT_ALLOWED');
			return;
		}

		// display the unreg form confirmation
		JRequest::setVar('view', 'registration');
		JRequest::setVar('layout', 'cancel');
		parent::display();
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
		$mainframe = &JFactory::getApplication();

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
		$v->setSummary($row->venue.'-'.$row->catname.'-'.redEVENTHelper::getSessionFullTitle($row));
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
		$mainframe = &JFactory::getApplication();
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
		$app = JFactory::getApplication();

		// Check for request forgeries
		JSession::checkToken() or die( 'Invalid Token' );

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

			$msg = JText::_('COM_REDEVENT_EVENT_DATE_SAVED');
			$this->setRedirect(JRoute::_(RedeventHelperRoute::getMyEventsRoute(), false), $msg);
		}
		else
		{
			$msg = JText::_('COM_REDEVENT_SUBMIT_XREF_ERROR').$model->getError();
			$this->setRedirect(JRoute::_(RedeventHelperRoute::getMyEventsRoute(), false), $msg, 'error');
		}

		if ($app->input->get('tmpl') == 'component')
		{
			// Ajax mode, just output result and stop.
			echo $msg;
			$app->close();
		}
	}

	function publishxref()
	{
		$acl  = UserAcl::getInstance();
		$xref = JRequest::getInt('xref');

		if (!$acl->canPublishXref($xref)) {
			$msg = JText::_('COM_REDEVENT_MYEVENTS_CHANGE_PUBLISHED_STATE_NOTE_ALLOWED');
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
			$msg = JText::_('COM_REDEVENT_PUBLISHED_STATE_UPDATED');
			$this->setRedirect(JRoute::_(RedeventHelperRoute::getMyEventsRoute(), false), $msg);
		}
		else {
			$msg = JText::_('COM_REDEVENT_PUBLISHED_STATE_UPDATE_ERROR').'<br>'.$model->getError();
			$this->setRedirect(JRoute::_(RedeventHelperRoute::getMyEventsRoute(), false), $msg, 'error');
		}
	}

	function deletexref()
	{
		$acl  = UserAcl::getInstance();
		$xref = JRequest::getInt('xref');

		if (!$acl->canEditXref($xref)) {
			$msg = JText::_('COM_REDEVENT_MYEVENTS_DELETE_XREF_NOTE_ALLOWED');
			$this->setRedirect(JRoute::_(RedeventHelperRoute::getMyEventsRoute(), false), $msg, 'error');
			return;
		}

		$model = $this->getModel('editevent');

		if ($model->deletexref($xref)) {
			$msg = JText::_('COM_REDEVENT_EVENT_DATE_DELETED');
			$this->setRedirect(JRoute::_(RedeventHelperRoute::getMyEventsRoute(), false), $msg);
		}
		else {
			$msg = JText::_('COM_REDEVENT_EVENT_DATE_DELETION_ERROR').'<br>'.$model->getError();
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
				echo "sending reminder for event: ".redEVENTHelper::getSessionFullTitle($event)."<br>";

				$tags = new redEVENT_tags();
				$tags->setXref($event->id);

				// get attendees
				$attendees = $model->getAttendeesEmails($event->id, $params->get('reminder_include_waiting', 1));
				if (!$attendees) {
					continue;
				}
				foreach ($attendees as $sid => $a)
				{
					$msubject = $tags->ReplaceTags($subject, array('sids' => array($sid)));
					$mbody    = '<html><body>'.$tags->ReplaceTags($body).'</body></html>';

					// convert urls
					$mbody = REOutput::ImgRelAbs($mbody);

					$mailer->setSubject($msubject);
					$mailer->setBody($mbody);

					$mailer->clearAllRecipients();
					$mailer->addRecipient( $a );

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

	/**
	 * for attachement downloads
	 *
	 * @return void
	 */
	public function getfile()
	{
		$app  = JFactory::getApplication();
		$id   = $app->input->getInt('file', 0);
		$user = JFactory::getUser();
		$path = REAttach::getAttachmentPath($id, max($user->getAuthorisedViewLevels()));

		// The header is fine tuned to work with grump ie8... if you modify a property, make sure it's still ok !
		header('Content-Description: File Transfer');

		// Mime
		$mime = redEVENTHelper::getMimeType($path);
		$doc = JFactory::getDocument();
		$doc->setMimeEncoding($mime);

		header('Content-Disposition: attachment; filename="'. basename($path) .'"');
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: no-store, no-cache');
		header('Pragma: no-cache');

		if ($fd = fopen ($path, "r"))
		{
			$fsize = filesize($path);
			header("Content-length: $fsize");

			while(!feof($fd))
			{
				$buffer = fread($fd, 2048);
				echo $buffer;
			}
		}

		fclose ($fd);
		return;
	}

	/**
	 * Delete attachment
	 *
	 * @return true on sucess
	 * @access private
	 * @since 1.1
	 */
	function ajaxattachremove()
	{
		$mainframe = & JFactory::getApplication();
		$id     = JRequest::getVar( 'id', 0, 'request', 'int' );

		$res = REAttach::remove($id);
		if (!$res) {
			echo 0;
			$mainframe->close();
		}

		$cache = &JFactory::getCache('com_redevent');
		$cache->clean();

		echo 1;
		$mainframe->close();
	}

	function debugrel()
	{
		$image = JHTML::image('components/com_redevent/assets/images/calendar_edit.png', 'blabla');
		echo ELoutput::ImgRelAbs($image);
		exit;
	}

	function registrationexpiration()
	{
		redEVENTHelper::registrationexpiration();
	}

	public function dbgajax()
	{
		echo 'test';
		JFactory::getApplication()->close();
	}
}
