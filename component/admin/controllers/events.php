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
class RedEventControllerEvents extends RedEventController
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
	}

	/**
	 * Logic to publish events
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

		$model = $this->getModel('events');
		if(!$model->publish($cid, 1)) {
			echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
		}

		$total = count( $cid );
		$msg 	= $total.' '.JText::_('EVENT PUBLISHED');

		$this->setRedirect( 'index.php?option=com_redevent&view=events', $msg );
	}

	/**
	 * Logic to unpublish events
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

		$model = $this->getModel('events');
		if(!$model->publish($cid, 0)) {
			echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
		}

		$total = count( $cid );
		$msg 	= $total.' '.JText::_('EVENT UNPUBLISHED');

		$this->setRedirect( 'index.php?option=com_redevent&view=events', $msg );
	}

	/**
	 * Logic to archive events
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

		$model = $this->getModel('events');
		if(!$model->publish($cid, -1)) {
			echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
		}

		$total = count( $cid );
		$msg 	= $total.' '.JText::_('EVENT ARCHIVED');

		$this->setRedirect( 'index.php?option=com_redevent&view=events', $msg );
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
		
		$group = & JTable::getInstance('redevent_events', '');
		$group->bind(JRequest::get('post'));
		$group->checkin();

		$this->setRedirect( 'index.php?option=com_redevent&view=events' );
	}

	/**
	 * logic to create the new event screen
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
	function add( )
	{
		global $option;

		$this->setRedirect( 'index.php?option='. $option .'&view=event' );
	}

	/**
	 * logic to create the edit event screen
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
	function edit( )
	{
		JRequest::setVar( 'view', 'event' );
		JRequest::setVar( 'hidemainmenu', 1 );

		$model 	= $this->getModel('event');
		$task 	= JRequest::getVar('task');

		if ($task == 'copy') {
			JRequest::setVar( 'task', $task );
		} else {
			
			$user	=& JFactory::getUser();
			// Error if checkedout by another administrator
			if ($model->isCheckedOut( $user->get('id') )) {
				$this->setRedirect( 'index.php?option=com_redevent&view=events', JText::_( 'EDITED BY ANOTHER ADMIN' ) );
			}
			$model->checkout();
		}
		parent::display();
	}

	/**
	 * logic to save an event
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
	function save() {
		// Check for request forgeries
		JRequest::checkToken() or die( 'Invalid Token' );
		$db = JFactory::getDBO();
		$task		= JRequest::getVar('task');
		
		$post = JRequest::get( 'post', 4 );
		
		/* Get the form fields to display */
		$showfields = '';
		foreach ($post as $field => $value) {
			if (substr($field, 0, 9) == 'showfield' && $value == "1") {
				$showfields .= substr($field, 9).",";
			}
		}
		
		$post['showfields'] = substr($showfields, 0, -1);
		if (!isset($post['checked_out'])) $post['checked_out'] = 0;
		
		/* Fix the submission types */
		$post['submission_types'] = implode(',', $post['submission_types']);
		
		$model = $this->getModel('event');
		$model_wait = $this->getModel('waitinglist');
		if ($returnid = $model->store($post)) {

			switch ($task)
			{
				case 'apply' :
					$link = 'index.php?option=com_redevent&controller=events&view=event&hidemainmenu=1&cid[]='.$returnid;
					break;

				default :
					$link = 'index.php?option=com_redevent&view=events';
					break;
			}
			$msg	= JText::_( 'EVENT SAVED');
			
			/* Get all the xref values for this particular event */
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
						$q = "INSERT INTO #__redevent_event_venue_xref (eventid, venueid, dates, enddates, times, endtimes, maxattendees, maxwaitinglist, course_price, course_credit, published) VALUES ";
						$q .= "(".$returnid.", ".$locid.", ".$db->Quote($dates).", ".$db->Quote($enddates).", ".$db->Quote($times).", ".$db->Quote($endtimes)."
								, ".$db->Quote($maxattendees).", ".$db->Quote($maxwaitinglist).", ".$db->Quote($course_price).", ".$db->Quote($course_credit).", 1)";
						$db->setQuery($q);
						$db->query();
					}
				}
			}
			$remove_xrefs = array();
			foreach ($existing_xrefs as $xid => $xref) {
				$remove_xrefs[] = $xid;
			}
			$q = "DELETE FROM #__redevent_event_venue_xref
				WHERE id IN (".implode(',', $remove_xrefs).")";
			$db->setQuery($q);
			$db->query();
			
			$cache = &JFactory::getCache('com_redevent');
			$cache->clean();

		} else {
			$msg 	= $model->getError();
			$link = 'index.php?option=com_redevent&view=events';

		}

		$model->checkin();
		
		/* Check if people need to be moved on or off the waitinglist */
		$model_wait->setEventId($post['id']);
		$model_wait->UpdateWaitingList();
		
		$this->setRedirect( $link, $msg );
 	}

	/**
	 * logic to remove an event
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

		$model = $this->getModel('events');
		if(!$model->delete($cid)) {
			echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
		}

		$msg = $total.' '.JText::_( 'EVENTS DELETED');

		$cache = &JFactory::getCache('com_redevent');
		$cache->clean();

		$this->setRedirect( 'index.php?option=com_redevent&view=events', $msg );
	}
}
?>