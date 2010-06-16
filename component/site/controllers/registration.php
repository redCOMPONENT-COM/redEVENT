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
  		$this->setRedirect(JRoute::_(RedeventHelperRoute::getDetailsRoute(null, $xref)), $msg, 'error');
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
		  		$this->setRedirect(JRoute::_(RedeventHelperRoute::getDetailsRoute(null, $xref)), $msg, 'error');
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
			$layout = 'confirmed';
  	}
  	else
  	{
  		$layout = 'review';
  	}
  	
  	// push models to the view
  	$view = $this->getView('registration', 'html');
  	$view->setModel($model, true);
		$view->setLayout($layout);
  	$view->display();
	}
	
	function cancelreg()
	{
  	$submit_key = JRequest::getVar('submit_key');
  	$xref = JRequest::getInt('xref');

  	$model = $this->getModel('registration');
  	$model->setXref($xref);
	  $model->cancel($submit_key);
	  
  	$msg = JText::_('Registration cancelled');
		$this->setRedirect(JRoute::_(RedeventHelperRoute::getDetailsRoute(null, $xref)), $msg);
	}
	
	function edit()
	{		
		JRequest::setvar('view', 'registration');
		JRequest::setvar('layout', 'edit');
		parent::display();
	}
	
	function manageattendees()
	{
		JRequest::setvar('view', 'details');
		JRequest::setvar('layout', 'manageattendees');
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
  			$this->setRedirect(JRoute::_(RedeventHelperRoute::getDetailsRoute(null, $xref)), $msg);
  		}
			return;
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
		
  	$msg = JTEXT::_('REDEVENT_REGISTRATION_UPDATED').' - '.$rfcore->getError();
  	if ($task == 'managerupdate') {
  		$this->setRedirect(JRoute::_(RedeventHelperRoute::getManageAttendees($xref)), $msg);  			
  	}
  	else {
  		$this->setRedirect(JRoute::_(RedeventHelperRoute::getDetailsRoute(null, $xref)), $msg);
  	}
	}
}
