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
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

/**
 * HTML Details View class of the EventList component
 *
 * @package Joomla
 * @subpackage EventList
 * @since 0.9
 */
class RedeventViewSignup extends JView
{
	/**
	 * Creates the output for the details view
	 *
 	 * @since 0.9
	 */
	function display($tpl = null) 
	{
		if (JRequest::getVar('layout') == 'edit') {
			return $this->_displayEdit();
		}
		$mainframe = & JFactory::getApplication();
		
		$document 	= & JFactory::getDocument();
    $params   = & $mainframe->getParams();
    $menu   = & JSite::getMenu();
    $item     = $menu->getActive();
		
		/* Load the event details */
		$course = $this->get('Details');
		$venue = $this->get('Venue');
				
    $pagetitle = $params->set('page_title', JText::_('SIGNUP_PAGE_TITLE'));
		$document->setTitle($pagetitle);
    $mainframe->setPageTitle( $pagetitle );
    
    //Print
    $params->def( 'print', !$mainframe->getCfg( 'hidePrint' ) );
    $params->def( 'icons', $mainframe->getCfg( 'icons' ) );
		
    //add css file
    if (!$params->get('custom_css')) {
      $document->addStyleSheet($this->baseurl.'/components/com_redevent/assets/css/redevent.css');
    }
    else {
      $document->addStyleSheet($params->get('custom_css'));     
    }
    $document->addCustomTag('<!--[if IE]><style type="text/css">.floattext{zoom:1;}, * html #eventlist dd { height: 1%; }</style><![endif]-->');
		
    $canRegister = $this->get('RegistrationStatus');
    if ($canRegister->canregister == 0) {
      echo $canRegister->status;
      echo '<br/>';
      echo JHTML::_('link', JRoute::_('index.php?option=com_redevent&view=details&xref='.JRequest::getInt('xref').'&id='.JRequest::getInt('id')), JText::_('RETURN_EVENT_DETAILS'));
      return;    	
    }
    
		/* This loads the tags replacer */
		JView::loadHelper('tags');
		$tags = new redEVENT_tags;
		$this->assignRef('tags', $tags);
		
		switch (JRequest::getCmd('subtype', 'webform')) {
			case 'email':
				if (JRequest::getVar('sendmail') == '1') {
					$this->tmp_xref = JRequest::getInt('xref');
					$this->tmp_id = JRequest::getInt('id');
					$model_signup = $this->getModel('Signup');
					/* Send the user the signup email */
					$result = $model_signup->getSendSignupEmail($tags, $course->send_pdf_form);
					$this->assignRef('result', $result);
					JRequest::setVar('xref', $this->tmp_xref);
					JRequest::setVar('id', $this->tmp_id);
				}
				/* Load the view */
				$this->assignRef('page', $course->submission_type_email);
				$tpl = 'email';
				break;
			case 'formaloffer':
				if (JRequest::getVar('sendmail') == '1') {
					$this->tmp_xref = JRequest::getInt('xref');
					$this->tmp_id = JRequest::getInt('id');
					$model_details = $this->getModel('Details');
					$model_signup = $this->getModel('Signup');
					$model_details->getDetails();
					$venues = $model_details->getVenues();
					/* Send the user the formal offer email */
					$result = $model_signup->getSendFormalOfferEmail($tags);
					$this->assignRef('result', $result);
					JRequest::setVar('xref', $this->tmp_xref);
					JRequest::setVar('id', $this->tmp_id);
				}
				/* Load the view */
				$this->assignRef('page', $course->submission_type_formal_offer);
				$tpl = 'formaloffer';
				break;
			case 'phone':
				/* Load the view */
				$this->assignRef('page', $course->submission_type_phone);
				$tpl = 'phone';
				break;
			case 'webform':
			default:
          $this->tmp_xref = JRequest::getInt('xref');
          $this->tmp_id = JRequest::getInt('id');
				$this->assignRef('page', $course->submission_type_webform);
    		$print_link = JRoute::_( 'index.php?option=com_redevent&view=signup&subtype=webform&task=signup&xref='.$this->tmp_xref.'&id='.$this->tmp_id.'&pop=1&tmpl=component' );
        $this->assignRef('print_link', $print_link);
				break;
		}
		
		// The replaceTag function can sometime call the layout directly. This variable allows to make the difference with regular
		// call
		$fullpage = true;
		
		$this->assignRef('course', $course);
		$this->assignRef('venue',  $venue);
    $this->assignRef('params', $params);
    $this->assignRef('pagetitle', $pagetitle);
    $this->assignRef('fullpage', $fullpage);
		
		parent::display($tpl);
	}
	
	function _displayEdit($tpl = null)
	{
		$user = &JFactory::getUser();
		$submitter_id = JRequest::getInt('submitter_id', 0);
		if (!$submitter_id) {
			JError::raise(0,'Registration id required');
			return false;
		}
		$course = $this->get('Details');
		$model = $this->getModel();
		
		$registration = $model->getRegistration($submitter_id);
		if (!$registration) {
			JError::raise(0,$model->getError);
			return false;
		}		
		
		JPluginHelper::importPlugin('content', 'redform');
		$dispatcher = JDispatcher::getInstance();
		$form = new stdClass();
		$form->text = '{redform}'.$course->redform_id.', 1'.'{/redform}';
		$form->eventid = $course->did;
		
		if ($registration->uid == $user->get('id')) {
			$form->task = 'edit';
		}
		else if ($model->getManageAttendees($registration->xref)) {
			$form->task = 'manageredit';			
		}
		else {
			JError::raiseError(403,'NOT AUTHORIZED');
			return false;
		}
		
		// params for plugin
		$params = array();
		$params['answers'] = array($registration->answers);
		$params['submitter_id'] = $submitter_id;
			
		$results = $dispatcher->trigger('onPrepareEvent', array(& $form, $params, 0));
		$redform = $form->text;
		
		echo $form->text;
//		parent::display($tpl);
	}
}
?>