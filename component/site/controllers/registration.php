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
		
		$this->registerTask( 'userregister_noreview', 'userregister' );
	}
	
	/**
	 * finalize registration
	 * 
	 * @param string $action
	 */
	function finalize($action = null)
	{		
		$model = $this->getModel('confirmation');
		/* Check submission key */
		$key_ok = $model->getCheckSubmitKey();
		
		/* Get the action */
		$action = $action ? $action : JRequest::getVar('action');
				
		if (!$key_ok) 
		{
			echo JText::_('NO_VALID_REGISTRATION');
			return;
		}
			
		if ($action == 'confirmreg') 
		{
			/* Save the confirmation */
			$result = $model->getMailConfirmation();
			if ($result) 
			{
			  /** 
			   * Check if redFORM wants control again
			   * in case of a VirtueMart redirect
			   */
			  if (JRequest::getBool('redformback', false)) {
					$this->setRedirect('index.php?option=com_redform&task=redeventvm&controller=redform&form_id='.JRequest::getInt('form_id'));
					return;     
			  }
			  
			  /* Create the view object */
				$view = $this->getView('confirmation', 'html');
				
				/* Standard model */
				$view->setModel( $this->getModel( 'details' ), false );
				$view->setLayout( 'confirmed' );
				
				/* Now display the view. */
				$view->display();
			}
		}
		else if ($action == 'cancelreg') 
		{
			$model->getCancelConfirmation();
			$url = JRoute::_(RedeventHelperRoute::getDetailsRoute(null, JRequest::getVar('xref')));
			$this->setRedirect($url, JText::_('CANCEL_CONFIRMATION'));
			return;
		}
		else {
			echo 'missing action';
		}
	}
	
	/**
	 * prepare registration review page
	 * 
	 */
	function review()
	{
	  /* Create the view object */
		$view = $this->getView('confirmation', 'html');
		
		/* Standard model */
		$view->setModel( $this->getModel( 'confirmation' ), true );
		$view->setModel( $this->getModel( 'details' ), false );
		$view->setLayout( 'review' );
		
		/* Now display the view. */
		$view->display();		
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
		
		$mail = $model->notifyManagers();
		
		$this->addModelPath( JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_redevent' . DS . 'models' );
		$model_wait = $this->getModel('Waitinglist', 'RedEventModel');
		$model_wait->setXrefId($xref);
		$model_wait->UpdateWaitingList();
		
		JPluginHelper::importPlugin( 'redevent' );
		$dispatcher =& JDispatcher::getInstance();
		$res = $dispatcher->trigger( 'onEventUserRegistered', array( $xref ) );
      
		$cache = JFactory::getCache('com_redevent');
		$cache->clean();
		
		if (JRequest::getVar('task') == 'userregister_noreview')
		{
			// go to registration confirmation screen
			
			return $this->finalize('confirmreg');
		}
		else
		{
			switch (JRequest::getVar('step'))
			{
				case 'review':
					return $this->review();
					break;
					
				case 'final':
				default:
					return $this->finalize('confirmreg');
					break;
			}
		}
	}
	
	
	/***************** NEW LIB **********************/
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
  	
  	$submit_key = JRequest::getVar('submit_key');
  	
  	if (!$xref) {
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
  	if (!$result) {
  		$msg = JTEXT::_('REDEVENT_REGISTRATION_REDFORM_SAVE_FAILED').' - '.$rfcore->getError();
  		$this->setRedirect(JRoute::_(RedeventHelperRoute::getDetailsRoute(null, $xref)), $msg, 'error');
  		return;
  	}
  	JRequest::setVar('submit_key', $result->submit_key);
  	
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
									
			$mail = $model->sendNotificationEmail();
			$mail = $model->notifyManagers();
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
}
