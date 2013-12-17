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
 * redEVENT Component Registrations Controller
 *
 * @package Joomla
 * @subpackage redEVENT
 * @since 2.0
 */
class RedEventControllerRegistrations extends RedEventController
{
	/**
	 * Constructor
	 *
	 *@since 0.9
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * set cancelled status to an attendee registration
	 *
	 * @return boolean true on success
	 */
	public function cancelreg()
	{
		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );

		$model = $this->getModel('attendees');

		if ($model->cancelreg($cid))
		{
			$msg = JText::_( 'COM_REDEVENT_ATTENDEES_REGISTRATION_CANCELLED');
			$this->setRedirect( 'index.php?option=com_redevent&view=registrations&filter_cancelled=1', $msg );

			foreach($cid as $attendee_id)
			{
				JPluginHelper::importPlugin('redevent');
				$dispatcher = JDispatcher::getInstance();
				$res = $dispatcher->trigger('onAttendeeModified', array($attendee_id));
			}
		}
		else
		{
			$msg = JText::_( 'COM_REDEVENT_ATTENDEES_REGISTRATION_CANCELLED_ERROR') . ': ' . $model->getError();
			$this->setRedirect( 'index.php?option=com_redevent&view=registrations', $msg, 'error' );
		}
		return true;
	}

	/**
	 * remove cancelled status from an attendee registration
	 *
	 * @return boolean true on success
	 */
	public function uncancelreg()
	{
		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );

		$model = $this->getModel('attendees');

		if ($model->uncancelreg($cid))
		{
			$msg = JText::_( 'COM_REDEVENT_ATTENDEES_REGISTRATION_UNCANCELLED');
			$this->setRedirect( 'index.php?option=com_redevent&view=registrations&filter_cancelled=0', $msg );

			foreach($cid as $attendee_id)
			{
				JPluginHelper::importPlugin('redevent');
				$dispatcher =& JDispatcher::getInstance();
				$res = $dispatcher->trigger('onAttendeeModified', array($attendee_id));
			}
		}
		else
		{
			$msg = JText::_( 'COM_REDEVENT_ATTENDEES_REGISTRATION_UNCANCELLED_ERROR') . ': ' . $model->getError();
			$this->setRedirect( 'index.php?option=com_redevent&view=registrations', $msg, 'error' );
		}
		return true;
	}


	/**
	 * Delete attendees
	 *
	 * @return true on sucess
	 * @access private
	 * @since 2.5
	 */
	public function remove($cid = array())
	{
		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );

		/* Check if anything is selected */
		if (!is_array( $cid ) || count( $cid ) < 1)
		{
			JError::raiseError(500, JText::_('COM_REDEVENT_Select_an_item_to_delete' ) );
		}
		$total 	= count( $cid );

		/* Get all submitter ID's */
		$model = $this->getModel('registrations');

		if (!$model->remove($cid))
		{
			RedEventError::raiseWarning(0, JText::_( "COM_REDEVENT_CANT_DELETE_REGISTRATIONS" ) . ': ' . $model->getError() );

			echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
		}

		foreach($cid as $attendee_id)
		{
			JPluginHelper::importPlugin('redevent');
			$dispatcher =& JDispatcher::getInstance();
			$res = $dispatcher->trigger('onAttendeeDeleted', array($attendee_id));
		}

		$cache = JFactory::getCache('com_redevent');
		$cache->clean();

		$msg = $total.' '.JText::_('COM_REDEVENT_REGISTERED_USERS_DELETED');

		$this->setRedirect( 'index.php?option=com_redevent&view=registrations', $msg );
	}

	/**
	 * confirm an attendee registration
	 *
	 * @return boolean true on success
	 */
	public function confirmattendees()
	{
		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );

		$model = $this->getModel('attendees');

		if ($model->confirmattendees($cid))
		{
			$msg = JText::_('COM_REDEVENT_REGISTRATION_CONFIRMED');
			$this->setRedirect( 'index.php?option=com_redevent&view=registrations', $msg );

			foreach($cid as $attendee_id)
			{
				JPluginHelper::importPlugin('redevent');
				$dispatcher = JDispatcher::getInstance();
				$res = $dispatcher->trigger('onAttendeeModified', array($attendee_id));
			}
		}
		else
		{
			$msg = JText::_('COM_REDEVENT_ERROR_REGISTRATION_CONFIRM') . ': ' . $model->getError();
			$this->setRedirect( 'index.php?option=com_redevent&view=registrations', $msg, 'error' );
		}
		return true;
	}

	/**
	 * remove confirm status from an attendee registration
	 *
	 * @return boolean true on success
	 */
	public function unconfirmattendees()
	{
		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );

		$model = $this->getModel('attendees');

		if ($model->unconfirmattendees($cid))
		{
			$msg = JText::_('COM_REDEVENT_REGISTRATION_UNCONFIRMED');
			$this->setRedirect( 'index.php?option=com_redevent&view=registrations', $msg );

			foreach($cid as $attendee_id)
			{
				JPluginHelper::importPlugin('redevent');
				$dispatcher =& JDispatcher::getInstance();
				$res = $dispatcher->trigger('onAttendeeModified', array($attendee_id));
			}
		}
		else
		{
			$msg = JText::_('COM_REDEVENT_ERROR_REGISTRATION_UNCONFIRM') . ': ' . $model->getError();
			$this->setRedirect( 'index.php?option=com_redevent&view=registrations', $msg, 'error' );
		}
		return true;
	}

	/**
	 * puts attendees on the waiting list of the session
	 *
	 * @return boolean true on success
	 */
	public function onwaiting()
	{
		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		$count = count($cid);

		$model = $this->getModel('registrations');

		if ($model->togglewaiting($cid, 1))
		{
			$msg = $count.' '.JText::_('COM_REDEVENT_PUT_ON_WAITING_SUCCESS');
			$this->setRedirect( 'index.php?option=com_redevent&view=registrations', $msg );

			foreach($cid as $attendee_id)
			{
				JPluginHelper::importPlugin('redevent');
				$dispatcher = JDispatcher::getInstance();
				$res = $dispatcher->trigger('onAttendeeModified', array($attendee_id));
			}
		}
		else
		{
			$msg = JText::_('COM_REDEVENT_PUT_ON_WAITING_FAILURE') . ': ' . $model->getError();
			$this->setRedirect( 'index.php?option=com_redevent&view=registrations', $msg, 'error' );
		}
		return true;
	}

	/**
	 * puts attendees off the waiting list of the session
	 *
	 * @return boolean true on success
	 */
	function offwaiting()
	{
		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		$count = count($cid);

		$model = $this->getModel('registrations');

		if ($model->togglewaiting($cid, 0))
		{
			$msg = $count.' '.JText::_('COM_REDEVENT_PUT_OFF_WAITING_SUCCESS');
			$this->setRedirect( 'index.php?option=com_redevent&view=registrations', $msg );

			foreach($cid as $attendee_id)
			{
				JPluginHelper::importPlugin('redevent');
				$dispatcher = JDispatcher::getInstance();
				$res = $dispatcher->trigger('onAttendeeModified', array($attendee_id));
			}
		}
		else
		{
			$msg = JText::_('COM_REDEVENT_PUT_OFF_WAITING_FAILURE') . ': ' . $model->getError();
			$this->setRedirect( 'index.php?option=com_redevent&view=registrations', $msg, 'error' );
		}
		return true;
	}
}
