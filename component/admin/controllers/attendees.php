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
 * EventList Component Attendees Controller
 *
 * @package Joomla
 * @subpackage EventList
 * @since 0.9
 */
class RedEventControllerAttendees extends RedEventController
{
	/**
	 * Constructor
	 *
	 *@since 0.9
	 */
	function __construct() 
	{
		parent::__construct();
		$this->registerTask( 'addattendee', 'attendees' );
		$this->registerTask( 'add',       'edit' );
		$this->registerTask( 'apply',     'save' );
		$this->registerTask( 'emailall',  'email' );
		$this->registerTask( 'applymove', 'move' );
	}
	
	public function Attendees() 
	{
		/* Create the view object */
		$view = $this->getView('attendees', 'html');
		
		/* Standard model */
		$view->setModel( $this->getModel( 'attendees', 'RedeventModel' ), true );
		$view->setModel( $this->getModel( 'waitinglist', 'RedeventModel' ) );
		$view->setLayout('default');
		
		/* Now display the view */
		$view->display();
	}
	
	public function Submitters() 
	{
		global $mainframe;
		$mainframe->redirect('index.php?option=com_redform&controller=submitters&task=submitters&integration=redevent&xref='.JRequest::getInt('xref').'&form_id='.JRequest::getInt('form_id').'&filter='.JRequest::getInt('filter'));
		
		/* Create the view object */
		$view = $this->getView('submitters', 'html');
		
		/* Standard model */
		JController::addModelPath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_redform'.DS.'models');
		$view->setModel( $this->getModel( 'submitters', 'RedformModel' ), true);
		$view->setModel( $this->getModel( 'redform', 'RedformModel' ));
		$view->setLayout('submitters');
		
		/* Now display the view */
		$view->display();
	}
	
	/**
	 * Delete attendees
	 *
	 * @return true on sucess
	 * @access private
	 * @since 0.9
	 */
	function remove($cid = array())
	{		
		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		$xref 	= JRequest::getInt('xref');
		$total 	= count( $cid );
		$formid = JRequest::getInt('form_id');
		
		/* Check if anything is selected */
		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to delete' ) );
		}
		
		/* Get all submitter ID's */
		$model = $this->getModel('attendees');
				
		if(!$model->remove($cid)) {
      RedEventError::raiseWarning(0, JText::_( "CANT DELETE REGISTRATIONS" ) . ': ' . $model->getError() );
			echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
		}
		
		/* Check if we have space on the waiting list */
		$model_wait = $this->getModel('waitinglist');
		$model_wait->setXrefId($xref);
		$model_wait->UpdateWaitingList();
		
		$cache = JFactory::getCache('com_redevent');
		$cache->clean();

		$msg = $total.' '.JText::_( 'REGISTERED USERS DELETED');

		$this->setRedirect( 'index.php?option=com_redevent&view=attendees&xref='.$xref, $msg );
	}

	/**
	 * Delete attendees
	 *
	 * @return true on sucess
	 * @access private
	 * @since 0.9
	 */
	function move($cid = array())
	{		
		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		$xref 	= JRequest::getInt('xref');
		$dest 	= JRequest::getInt('dest');
		$total 	= count( $cid );
		$formid = JRequest::getInt('form_id');
		
		/* Check if anything is selected */
		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select an attendee to move' ) );
		}
		
		
		if (!$dest) // display the form to chose destination 
		{			
			/* Create the view object */
			$view = $this->getView('attendees', 'html');
			
			/* Standard model */
			$view->setModel( $this->getModel( 'attendees', 'RedeventModel' ), true );
			/* set layout */
			$view->setLayout('move');
			
			/* Now display the view */
			$view->display();
			return;
		}
		
		/* Get all submitter ID's */
		$model = $this->getModel('attendees');
				
		if(!$model->move($cid, $dest)) {
      RedEventError::raiseWarning(0, JText::_( "COM_REDEVENT_ATTENDEES_CANT_MOVE_REGISTRATIONS" ) . ': ' . $model->getError() );
			echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
		}
		/* Check if we have space on the waiting list */
		$model_wait = $this->getModel('waitinglist');
		$model_wait->setXrefId($xref);
		$model_wait->UpdateWaitingList();
		
		$model_wait->setXrefId($dest);
		$model_wait->UpdateWaitingList();
		
		$cache = JFactory::getCache('com_redevent');
		$cache->clean();

		$msg = $total.' '.JText::_( 'COM_REDEVENT_ATTENDEES_MOVED');

		$this->setRedirect( 'index.php?option=com_redevent&view=attendees&xref='.$dest, $msg );
	}
	
	function selectXref()
	{
		JRequest::setVar('view', 'xrefelement');
		JRequest::setVar('form_id', JRequest::getVar('form_id'));
		parent::display();
	}
	
	/**
	 * confirm an attendee registration
	 * 
	 * @return unknown_type
	 */
	function confirmattendees()
	{
    $cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
    $xref   = JRequest::getInt('xref');
    
    $model = $this->getModel('attendees');
    
    if ($model->confirmattendees($cid))
    {
  	  $msg = JText::_( 'REGISTRATION CONFIRMED');
      $this->setRedirect( 'index.php?option=com_redevent&view=attendees&xref='.$xref, $msg );
  	}
  	else
  	{
      $msg = JText::_( 'ERROR REGISTRATION CONFIRM') . ': ' . $model->getError();
      $this->setRedirect( 'index.php?option=com_redevent&view=attendees&xref='.$xref, $msg, 'error' );  	  
  	}
    return true;
	}
	
  /**
   * remove confirm status from an attendee registration
   * 
   * @return unknown_type
   */
  function unconfirmattendees()
  {
    $cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
    $xref   = JRequest::getInt('xref');
    
    $model = $this->getModel('attendees');
    
    if ($model->unconfirmattendees($cid))
    {
      $msg = JText::_( 'REGISTRATION UNCONFIRMED');
      $this->setRedirect( 'index.php?option=com_redevent&view=attendees&xref='.$xref, $msg );
    }
    else
    {
      $msg = JText::_( 'ERROR REGISTRATION UNCONFIRM') . ': ' . $model->getError();
      $this->setRedirect( 'index.php?option=com_redevent&view=attendees&xref='.$xref, $msg, 'error' );      
    }
    return true;
  }
  
  function onwaiting()
  {
    $cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
    $xref   = JRequest::getInt('xref');
    
    $model = $this->getModel('waitinglist');
    $model->setXrefId($xref);
    
    if ($model->putOnWaitingList($cid))
    {
      $msg = JText::_( 'PUT ON WAITING SUCCESS');
      $this->setRedirect( 'index.php?option=com_redevent&view=attendees&xref='.$xref, $msg );
    }
    else
    {
      $msg = JText::_( 'PUT ON WAITING FAILURE') . ': ' . $model->getError();
      $this->setRedirect( 'index.php?option=com_redevent&view=attendees&xref='.$xref, $msg, 'error' );      
    }
    return true;    
  }

  function offwaiting()
  {
    $cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
    $xref   = JRequest::getInt('xref');
    
    $model = $this->getModel('waitinglist');
    $model->setXrefId($xref);
    
    if ($model->putOffWaitingList($cid))
    {
      $msg = JText::_( 'PUT OFF WAITING SUCCESS');
      $this->setRedirect( 'index.php?option=com_redevent&view=attendees&xref='.$xref, $msg );
    }
    else
    {
      $msg = JText::_( 'PUT OFF WAITING FAILURE') . ': ' . $model->getError();
      $this->setRedirect( 'index.php?option=com_redevent&view=attendees&xref='.$xref, $msg, 'error' );      
    }
    return true;    
  }
		
	/* Obsolete */
	function export()
	{
		global $mainframe;

		$model = $this->getModel('attendees');

		$datas = $model->getData();

		$doc =& JFactory::getDocument();
		$doc->setMimeEncoding('text/csv');
		header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		header('Content-Disposition: attachment; filename=attendees.csv');
		header('Pragma: no-cache');

		$k = 0;
		$export = '';
		$col = array();

		for($i=0, $n=count( $datas ); $i < $n; $i++)
		{
			$data = &$datas[$i];

    		$col[] = str_replace("\"", "\"\"", $data->name);
    		$col[] = str_replace("\"", "\"\"", $data->username);
    		$col[] = str_replace("\"", "\"\"", $data->email);
    		$col[] = str_replace("\"", "\"\"", JHTML::Date( $data->uregdate, JText::_( 'DATE_FORMAT_LC2' ) ));
			
   	 		for($j = 0; $j < count($col); $j++)
    		{
        		$export .= "\"" . $col[$j] . "\"";

        		if($j != count($col)-1)
       	 		{
            		$export .= ";";
        		}
    		}
    		$export .= "\r\n";
    		$col = '';

			$k = 1 - $k;
		}

		echo $export;

		$mainframe->close();
	}
	
	/**
	 * logic to create the edit screen
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
	function edit( )
	{
		JRequest::setVar( 'view', 'attendee' );
		JRequest::setVar( 'hidemainmenu', 1 );

		$model 	= $this->getModel('attendee');
		$task 	= JRequest::getVar('task');

		if ($task == 'copy' || $task == 'add') {
			JRequest::setVar( 'task', $task );
		} 
		else 
		{			
			$user	=& JFactory::getUser();
			// Error if checkedout by another administrator
			if ($model->isCheckedOut( $user->get('id') )) {
				$this->setRedirect( 'index.php?option=com_redevent&view=attendees', JText::_( 'EDITED BY ANOTHER ADMIN' ) );
			}
			$model->checkout();
		}
		parent::display();
	}

	/**
	 * logic for cancel an action
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
	function cancel()
	{
		// Check for request forgeries
		JRequest::checkToken() or die( 'Invalid Token' );
		
		$xref = JRequest::getVar('xref', 0, '', 'int') or die( 'Missing xref' );
		
		$row = & JTable::getInstance('redevent_register', '');
		$row->bind(JRequest::get('post'));
		$row->checkin();

		$this->setRedirect( 'index.php?option=com_redevent&view=attendees&xref='. $xref );
	}

	/**
	 * logic to save an attendee
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
	function save()
	{
		// Check for request forgeries
		JRequest::checkToken() or die( 'Invalid Token' );
		$xref = JRequest::getVar('xref', 0, '', 'int') or die( 'Missing xref' );
		$task		= JRequest::getVar('task');

		$post 	= JRequest::get( 'post' );
		
		$model = $this->getModel('attendee');
		
		$msg = '';
		$mtype= 'message';

		if ($returnid = $model->store($post)) 
		{		
			$model_wait = $this->getModel('Waitinglist');
			$model_wait->setXrefId($xref);
			$model_wait->UpdateWaitingList();
	      
			$cache = JFactory::getCache('com_redevent');
			$cache->clean();
			
			switch ($task)
			{
				case 'apply' :
					$link = 'index.php?option=com_redevent&controller=attendees&view=attendee&xref='. $xref.'&hidemainmenu=1&cid[]='.$returnid;
					break;

				default :
					$link 	= 'index.php?option=com_redevent&view=attendees&xref='. $xref;
					break;
			}
			$msg	= JText::_( 'REGISTRATION SAVED');
			
		} else {

			$link 	= 'index.php?option=com_redevent&view=attendees&xref='. $xref;
			$msg	= $model->getError();
			$mtype= 'error';				
		}

		$model->checkin();

		$this->setRedirect( $link, $msg, $mtype );
 	}
 	
 	function email()
 	{
		$task = JRequest::getVar('task');
		
		if ($task == 'email') {
			$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		}
		else {
			$cid = null;
		}
		$xref 	= JRequest::getInt('xref');
		
		JRequest::setVar('view', 'emailattendees');
		
		parent::display();
 	}
 	
 	function sendemail()
 	{
		// Check for request forgeries
		JRequest::checkToken() or die( 'Invalid Token' );
		$xref = JRequest::getVar('xref', 0, '', 'int') or die( 'Missing xref' );
		$task		= JRequest::getVar('task');

		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);
		
		$subject  = JRequest::getVar('subject', '', 'post', 'string');
		$from     = JRequest::getVar('from', '', 'post', 'string');
		$fromname = JRequest::getVar('fromname', '', 'post', 'string');
		$replyto  = JRequest::getVar('replyto', '', 'post', 'string');
		$body     = JRequest::getVar('body', '', 'post', 'string',  JREQUEST_ALLOWRAW );
		
		$model = $this->getModel('attendees');
		
		$msg = '';
		$mtype= 'message';
		
		if ($model->sendMail($cid, $subject, $body, $from, $fromname, $replyto))
		{
			$msg = JText::_('COM_REDEVENT_EMAIL_ATTENDEES_SENT');
		}
		else
		{
			$msg = $model->getError();
			$mtype = 'error';
		}
		
		$this->setRedirect( 'index.php?option=com_redevent&view=attendees&xref='. $xref, $msg, $mtype );
		$this->redirect();
 	}
 	
 	function cancelemail()
 	{
		$xref = JRequest::getVar('xref', 0, '', 'int') or die( 'Missing xref' );
		$this->setRedirect( 'index.php?option=com_redevent&view=attendees&xref='. $xref );
 	}
}
?>