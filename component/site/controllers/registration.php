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

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');

/**
 * redEVENT Component Registration Controller
 *
 * @package Joomla
 * @subpackage redEVENT
 * @since 2.0
 */
class RedEventControllerRegistration extends RedEventController
{
	/**
	 * Constructor
	 *
	 * @since 2.0
	 */
	function __construct() {
		parent::__construct();
		$this->registerTask( 'manageredit',   'edit' );
		$this->registerTask( 'managerupdate', 'update' );
		$this->registerTask( 'review', 'confirm' );		
	}
	
		
	function register()
	{
		if (JRequest::getVar('cancel', '', 'post')) {
			return $this->cancelreg();
		}
  	$msg = 'OK';
  	
  	$xref = JRequest::getInt('xref');
  	$review = JRequest::getVar('hasreview', 0);
  	$isedit = JRequest::getVar('isedit', 0);
	  $model = $this->getModel('registration');
	  $model->setXref(JRequest::getInt('xref'));  	
  	$details = $model->getSessionDetails();
  	  	
  	if (!$xref) 
  	{
  		$msg = JText::_('REDEVENT_REGISTRATION_MISSING_XREF');
  		$this->setRedirect('index.php', $msg, 'error');
  		return;
  	}  	
  	
  	// first, ask redform to save it's fields, and return the corresponding sids.
  	$options = array('baseprice' => $details->course_price);
  	if ($review) {
  		$options['savetosession'] = 1;
  	}
		$rfcore = new redFormCore();
  	$result = $rfcore->saveAnswers('redevent', $options);
  	if (!$result) 
  	{
  		$msg = JTEXT::_('REDEVENT_REGISTRATION_REDFORM_SAVE_FAILED').' - '.$rfcore->getError();
  		$this->setRedirect(JRoute::_(RedeventHelperRoute::getDetailsRoute($details->did, $xref)), $msg, 'error');
  		return;
  	}
  	$submit_key = $result->submit_key;
  	JRequest::setVar('submit_key', $submit_key);
  	
  	if (!$isedit && !$review)
  	{
	  	// redform save fine, now save corresponding bookings
	  	foreach ($result->posts as $rfpost)
	  	{
	  		if (!$res = $model->register($rfpost['sid'], $result->submit_key)) {
	  			$msg = JTEXT::_('REDEVENT_REGISTRATION_REGISTRATION_FAILED');
		  		$this->setRedirect(JRoute::_(RedeventHelperRoute::getDetailsRoute($details->did, $xref)), $msg, 'error');
		  		return;
	  		}
	  	}
			JPluginHelper::importPlugin( 'redevent' );
			$dispatcher =& JDispatcher::getInstance();
			$res = $dispatcher->trigger( 'onEventUserRegistered', array( $xref ) );
									
			$mail = $model->sendNotificationEmail($submit_key);
			$mail = $model->notifyManagers($submit_key);
  	}
  	
  	if (!$review)
  	{  	  	
			$this->addModelPath( JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_redevent' . DS . 'models' );
			$model_wait = $this->getModel('Waitinglist', 'RedEventModel');
			$model_wait->setXrefId($xref);
			$model_wait->UpdateWaitingList();
			
			$cache = JFactory::getCache('com_redevent');
			$cache->clean();
			
			$rfredirect = $rfcore->getFormRedirect($details->redform_id);
			if ($rfredirect) {
				$link = $rfredirect;
			}
			else {
				$link = RedeventHelperRoute::getRegistrationRoute($xref, 'confirm', $submit_key);
			}
  	}
  	else
  	{
			$link = RedeventHelperRoute::getRegistrationRoute($xref, 'review', $submit_key);
  	}
  	
  	// redirect to prevent resending the form on refresh
  	$this->setRedirect(JRoute::_($link, false));
	}
	
	function cancelreg()
	{
  	$submit_key = JRequest::getVar('submit_key');
  	$xref = JRequest::getInt('xref');

  	$model = $this->getModel('registration');
  	$model->setXref($xref);
	  $model->cancel($submit_key);
		$eventdata = $model->getSessionDetails();
	  
  	$msg = JText::_('Registration cancelled');
		$this->setRedirect(JRoute::_(RedeventHelperRoute::getDetailsRoute($eventdata->did, $xref)), $msg);
	}
	
	function edit()
	{		
		JRequest::setvar('view', 'registration');
		JRequest::setvar('layout', 'edit');
		parent::display();
	}
	
	function manageattendees()
	{
		$acl = UserAcl::getInstance();
		$xref = JRequest::getInt('xref');
		if ($acl->canManageAttendees($xref)) {
			$layout = 'manageattendees';			
		}
		else if ($acl->canViewAttendees($xref)){
			$layout = 'default';
		}
		else {
			$this->setRedirect(RedeventHelperRoute::getMyEventsRoute(), JText::_('ACCESS NOT ALLOWED'), 'error');
			$this->redirect();
		}
		JRequest::setvar('view', 'attendees');
		JRequest::setvar('layout', $layout);
		parent::display();		
	}
	
	function update()
	{	
  	$xref = JRequest::getInt('xref');
  	$task = JRequest::getVar('task');
		if (JRequest::getVar('cancel', '', 'post')) 
		{
  		$msg = JText::_('Registration Edit cancelled');
  		if ($task == 'managerupdate') {
  			$this->setRedirect(JRoute::_(RedeventHelperRoute::getManageAttendees($xref)), $msg);  			
  		}
  		else {
  			$this->setRedirect(JRoute::_(RedeventHelperRoute::getDetailsRoute(null, $xref), false), $msg);
  		}
			$this->redirect();
		}
		
		$xref = JRequest::getInt('xref');
	  $model = $this->getModel('registration');
	  $model->setXref(JRequest::getInt('xref'));  	
  	$details = $model->getSessionDetails();
  	
  	$submit_key = JRequest::getVar('submit_key');
  	
  	if (!$xref) {
  		$msg = JText::_('REDEVENT_REGISTRATION_MISSING_XREF');
  		$this->setRedirect('index.php', $msg, 'error');
  		return;
  	}
  	
  	// first, ask redform to save it's fields, and return the corresponding sids.
  	$options = array('baseprice' => $details->course_price);
		$rfcore = new redFormCore();
  	$result = $rfcore->saveAnswers('redevent', $options);
  	if (!$result) {
  		$msg = JTEXT::_('REDEVENT_REGISTRATION_REDFORM_SAVE_FAILED').' - '.$rfcore->getError();
  		if ($task == 'managerupdate') {
  			$this->setRedirect(JRoute::_(RedeventHelperRoute::getManageAttendees($xref)), $msg, 'error');  			
  		}
  		else {
  			$this->setRedirect(JRoute::_(RedeventHelperRoute::getDetailsRoute(null, $xref)), $msg, 'error');
  		}
  		return;
  	}
  	JRequest::setVar('submit_key', $result->submit_key);
  	
  	// redform save fine, now save corresponding bookings
  	foreach ($result->posts as $rfpost)
  	{
  		if (!$res = $model->register($rfpost['sid'], $result->submit_key)) {
  			$msg = JTEXT::_('REDEVENT_REGISTRATION_REGISTRATION_FAILED');
	  		if ($task == 'managerupdate') {
	  			$this->setRedirect(JRoute::_(RedeventHelperRoute::getManageAttendees($xref)), $msg, 'error');  			
	  		}
	  		else {
	  			$this->setRedirect(JRoute::_(RedeventHelperRoute::getDetailsRoute(null, $xref)), $msg, 'error');
	  		}
	  		return;
  		}
  	}
									
//		$mail = $model->sendNotificationEmail();
//		$mail = $model->notifyManagers();
  				
		$cache = JFactory::getCache('com_redevent');
		$cache->clean();
		
  	$msg = JTEXT::_('COM_REDEVENT_REGISTRATION_UPDATED');
  	if ($task == 'managerupdate') {
  		$this->setRedirect(RedeventHelperRoute::getManageAttendees($xref), $msg);  			
  	}
  	else {
  		$this->setRedirect(RedeventHelperRoute::getDetailsRoute(null, $xref), $msg);
  	}
	}
	
	function confirm()
	{
		if (JRequest::getVar('task') == 'review') {
			JRequest::setVar('layout', 'review');
		}
		else {
			JRequest::setVar('layout', 'confirmed');			
		}
		JRequest::setVar('view', 'registration');
		parent::display();
	}
	

	/**
	 * Confirms the users request
	 */
	 function activate() 
	 {
		 global $mainframe;
		 	
		 /* Get the confirm ID */
		 $confirmid = JRequest::getVar('confirmid', '', 'get');
		 	
		 /* Get the details out of the confirmid */
		 list($uip, $xref, $uid, $register_id, $submit_key) = split("x", $confirmid);
		 	
		 	
		 /* Confirm sign up via mail */
		 $model_event = $this->getModel('Registration', 'RedEventModel');
		 $model_event->setXref($xref);
		 $eventdata = $model_event->getSessionDetails();
		 	
		 /* This loads the tags replacer */
		 JRequest::setVar('xref', $xref);
		 require_once(JPATH_COMPONENT_SITE.DS.'helpers'.DS.'tags.php');
		 $tags = new redEVENT_tags();
		 $tags->setXref($xref);
		 $tags->setSubmitkey($submit_key);
		 
		 /* Check the db if this entry exists */
		 $db = JFactory::getDBO();
		 $q = ' SELECT r.confirmed '
		    . ' FROM #__redevent_register r '
		    . ' WHERE r.uid = '.$db->Quote($uid)
		    . ' AND r.submit_key = '.$db->Quote($submit_key)
		    . ' AND r.xref = '.$db->Quote($xref)
		    . ' AND r.id = '.$db->Quote($register_id)
		    ;
		$db->setQuery($q);
		$regdata = $db->loadObject();
		
		if ($regdata && $regdata->confirmed == 0) 
		{
			/* User exists, confirm the entry */
			$q = "UPDATE #__redevent_register
				SET confirmed = 1,
				confirmdate = NOW()
				WHERE id = ".$register_id;
			$db->setQuery($q);
			if ($db->query()) {
				$this->setMessage(JText::_('YOUR SUBMISSION HAS BEEN CONFIRMED'));
			}
			
			/* Update the waitinglist */
			$this->addModelPath( JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_redevent' . DS . 'models' );
			$model_wait = $this->getModel('Waitinglist', 'RedEventModel');
			/* Get the event id */
			$q = "SELECT eventid FROM #__redevent_event_venue_xref WHERE id = ".$xref;
			$db->setQuery($q);
			$eventid = $db->loadResult();
			$model_wait->setXrefId($xref);
			$model_wait->UpdateWaitingList();
			
			if ($eventdata->notify) 
			{
				$this->_Mailer();				
				
				$rfcore = new RedFormCore();
				$addresses = $rfcore->getSubmissionContactEmail($submit_key);
				
				/* Check if there are any addresses to be mailed */
				if (count($addresses) > 0) 
				{
					/* Start mailing */
					foreach ($addresses as $key => $email) 
					{
						/* Send a off mailinglist mail to the submitter if set */
						/* Add the email address */
						$this->mailer->AddAddress($email['email']);
						
						/* Mail submitter */
						$htmlmsg = '<html><head><title></title></title></head><body>'.$tags->ReplaceTags($eventdata->notify_confirm_body).'</body></html>';
						// convert urls
						$htmlmsg = ELOutput::ImgRelAbs($htmlmsg);
						
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
		else if ($regdata && $regdata->confirmed == 1) {
			$msg = JText::_('YOUR SUBMISSION HAS ALREADY BEEN CONFIRMED');
		}
		else {
			$msg = JText::_('YOUR SUBMISSION CANNOT BE CONFIRMED');
		}
		
		$this->setRedirect(JRoute::_(RedeventHelperRoute::getDetailsRoute($eventdata->did, $xref)), $msg);
	}
	 

	/**
	 * Initialise the mailer object to start sending mails
	 */
	private function _Mailer() 
	{
		if (empty($this->mailer))
		{
			$mainframe = & JFactory::getApplication();
			jimport('joomla.mail.helper');
			/* Start the mailer object */
			$this->mailer = JFactory::getMailer();
			$this->mailer->isHTML(true);
			$this->mailer->From = $mainframe->getCfg('mailfrom');
			$this->mailer->FromName = $mainframe->getCfg('sitename');
			$this->mailer->AddReplyTo(array($mainframe->getCfg('mailfrom'), $mainframe->getCfg('sitename')));
		}
		return $this->mailer;
	}
}
