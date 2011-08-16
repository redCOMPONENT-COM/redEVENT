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
 * EventList Component Events Controller
 *
 * @package Joomla
 * @subpackage EventList
 * @since 0.9
 */
class RedEventControllerSessions extends RedEventController
{	
	/**
	 * Constructor
	 *
	 * @since 0.9
	 */
	function __construct()
	{
		parent::__construct();

		// Register Extra task
		$this->registerTask( 'apply', 		'save' );
		$this->registerTask( 'copy',	 	'edit' );
		$this->registerTask( 'add',	 	'edit' );
	}
	
	function editxref()
	{
    JRequest::setVar( 'view', 'session' );
    JRequest::setVar( 'layout', 'default' );
		
    parent::display();
	}
	
	function savexref()
	{		
    // Check for request forgeries
    JRequest::checkToken() or die( 'Invalid Token' );
        
    $post = JRequest::get( 'post' );
    $post['details'] = JRequest::getVar('details', '', 'post', 'string', JREQUEST_ALLOWRAW);
    $post['icaldetails'] = JRequest::getVar('icaldetails', '', 'post', 'string', JREQUEST_ALLOWRAW);
    
    $model = $this->getModel('session');
    if ($returnid = $model->savexref($post)) 
    {
			/* Check if people need to be moved on or off the waitinglist */
			$model_wait = $this->getModel('waitinglist');
			$model_wait->setXrefId($returnid);
			$model_wait->UpdateWaitingList();
			
			$cache = &JFactory::getCache('com_redevent');
			$cache->clean();
			
      $msg = 'saved session';
      $this->setRedirect('index.php?option=com_redevent&controller=sessions&task=closexref&tmpl=component&xref='. $returnid, $msg);      
    }
    else {
    	$msg = 'error saving: '. $model->getError() ;
      $this->setRedirect('index.php?option=com_redevent&controller=sessions&task=editxref&tmpl=component&xref='. $returnid,  $msg, 'error');
    }
	}
	
  function closexref()
  {
    JRequest::setVar( 'view', 'session' );
    JRequest::setVar( 'layout', 'closexref' );
    
    parent::display();
  }
  
  function back()
  {
  	$this->setRedirect('index.php?option=com_redevent&view=events');
  }
  
  function edit()
  {
  	JRequest::setVar( 'hidemainmenu', 1 );
		JRequest::setVar( 'layout', 'default'  );
		JRequest::setVar( 'view'  , 'session');
		JRequest::setVar( 'standalone'  , true);
		if (Jrequest::getVar('task') == 'edit') {
			JRequest::setVar( 'edit', true );
		}
		else {
			JRequest::setVar( 'edit', false );
		}
		
		parent::display();
  }
	
	function save()
	{		
    // Check for request forgeries
    JRequest::checkToken() or die( 'Invalid Token' );
        
    $post = JRequest::get( 'post' );
    
    $model = $this->getModel('session');
    
    $customs = $model->getXrefCustomfields();
    foreach ($customs as $c) 
    {
    	if ($c->type == 'wysiwyg') {
    		$post['custom'.$c->id] = JRequest::getVar('custom'.$c->id, '', 'post', 'string', JREQUEST_ALLOWRAW);   		
    	}
    }
    
    $post['details'] = JRequest::getVar('details', '', 'post', 'string', JREQUEST_ALLOWRAW);
    $post['icaldetails'] = JRequest::getVar('icaldetails', '', 'post', 'string', JREQUEST_ALLOWRAW);
    
    $eventid = JRequest::getInt('eventid');
    
    $model = $this->getModel('session');
    if ($returnid = $model->savexref($post)) 
    {
			/* Check if people need to be moved on or off the waitinglist */
			$model_wait = $this->getModel('waitinglist');
			$model_wait->setXrefId($returnid);
			$model_wait->UpdateWaitingList();
			
			$cache = &JFactory::getCache('com_redevent');
			$cache->clean();
			
      $msg = 'saved session';
      if (JRequest::getVar('task') == 'apply') {
      	$this->setRedirect('index.php?option=com_redevent&controller=sessions&task=edit&cid[]='. $returnid, $msg);
      } 
      else {
      	$this->setRedirect('index.php?option=com_redevent&view=sessions', $msg);
      }    
    }
    else {
    	$msg = 'error saving: '. $model->getError() ;
      $this->setRedirect('index.php?option=com_redevent&view=sessions',  $msg, 'error');
    }
	}
	
	function cancel()
	{
    $eventid = JRequest::getInt('eventid');
    $this->setRedirect('index.php?option=com_redevent&view=sessions');
	}

	/**
	 * Logic to publish
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
	function publish()
	{
		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to publish' ) );
		}

		$model = $this->getModel('sessions');

		if(!$model->publish($cid, 1)) {
			echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
		}

		$total = count( $cid );
		$msg 	= $total.' '.JText::_( 'SESSIONS PUBLISHED');

    $eventid = JRequest::getInt('eventid');
		$this->setRedirect('index.php?option=com_redevent&view=sessions', $msg);
	}

	/**
	 * Logic to unpublish
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
	function unpublish()
	{
		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to unpublish' ) );
		}

		$model = $this->getModel('sessions');

		if(!$model->publish($cid, 0)) {
			echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
		}

		$total = count( $cid );
		$msg 	= $total.' '.JText::_( 'SESSIONS UNPUBLISHED');

    $eventid = JRequest::getInt('eventid');
		$this->setRedirect('index.php?option=com_redevent&view=sessions', $msg);
	}

	/**
	 * Logic to archive
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
	function archive()
	{
		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to archive' ) );
		}

		$model = $this->getModel('sessions');

		if(!$model->publish($cid, -1)) {
			echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
		}

		$total = count( $cid );
		$msg 	= $total.' '.JText::_( 'SESSIONS ARCHIVED');

    $eventid = JRequest::getInt('eventid');
		$this->setRedirect('index.php?option=com_redevent&view=sessions', $msg);
	}

	/**
	 * logic to remove a session
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
 	function remove()
	{
		$cid = JRequest::getVar( 'cid', array(0), 'post', 'array' );

		$total = count( $cid );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to delete' ) );
		}

		$model = $this->getModel('session');
		foreach ($cid as $xref) 
		{
			if(!$model->removexref($xref)) {
				echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
			}
		}

		$msg = $total.' '.JText::_( 'SESSIONS DELETED');

		$cache = &JFactory::getCache('com_redevent');
		$cache->clean();

    $eventid = JRequest::getInt('eventid');
		$this->setRedirect('index.php?option=com_redevent&view=sessions', $msg);
	}
	


	/**
	 * Logic to set as featured
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
	function featured()
	{
		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to publish' ) );
		}

		$model = $this->getModel('sessions');

		if(!$model->featured($cid, 1)) {
			echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
		}

		$total = count( $cid );
		$msg 	= JText::sprintf( 'COM_REDEVENT_SESSIONS_SET_AS_FEATURED', $total);

    $eventid = JRequest::getInt('eventid');
		$this->setRedirect('index.php?option=com_redevent&view=sessions', $msg);
	}

	/**
	 * Logic to set as not featured
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
	function unfeatured()
	{
		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to publish' ) );
		}

		$model = $this->getModel('sessions');

		if(!$model->featured($cid, 0)) {
			echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
		}

		$total = count( $cid );
		$msg 	= JText::sprintf( 'COM_REDEVENT_SESSIONS_SET_AS_NOT_FEATURED', $total);

    $eventid = JRequest::getInt('eventid');
		$this->setRedirect('index.php?option=com_redevent&view=sessions', $msg);
	}
}
?>