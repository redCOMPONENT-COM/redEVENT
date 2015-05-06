<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Registrations Controller
 *
 * @package  Redevent.admin
 * @since    0.9
 */
class RedeventControllerAttendees extends RControllerAdmin
{
	/**
	 * Override to trigger plugins
	 *
	 * @param   RModelAdmin  $model  The data model object.
	 * @param   array        $cid    The validated data.
	 *
	 * @return  void
	 */
	protected function postDeleteHook(RModelAdmin $model, $cid = null)
	{
		if (!(is_array($cid) && count($cid)))
		{
			return false;
		}

		foreach ($cid as $attendee_id)
		{
			JPluginHelper::importPlugin('redevent');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('onAttendeeDeleted', array($attendee_id));
		}
	}

	/**
	 * Move attendees
	 *
	 * @TODO: reimplement for 3.x
	 *
	 * @return true on sucess
	 */
	public function move()
	{
		$cid = JRequest::getVar('cid', array(), 'post', 'array');
		$xref = JRequest::getInt('xref');
		$dest = JRequest::getInt('dest');
		$total = count($cid);
		$formid = JRequest::getInt('form_id');

		/* Check if anything is selected */
		if (!is_array($cid) || count($cid) < 1)
		{
			JError::raiseError(500, JText::_('COM_REDEVENT_Select_an_attendee_to_move'));
		}

		if (!$dest)
		{
			// Display the form to chose destination
			/* Create the view object */
			$view = $this->getView('attendees', 'html');

			/* Standard model */
			$view->setModel($this->getModel('attendees', 'RedeventModel'), true);
			/* set layout */
			$view->setLayout('move');

			/* Now display the view */
			$view->display();

			return;
		}

		/* Get all submitter ID's */
		$model = $this->getModel('attendees');

		if (!$model->move($cid, $dest))
		{
			RedEventError::raiseWarning(0, JText::_("COM_REDEVENT_ATTENDEES_CANT_MOVE_REGISTRATIONS") . ': ' . $model->getError());
			echo "<script> alert('" . $model->getError() . "'); window.history.go(-1); </script>\n";
		}

		foreach ($cid as $attendee_id)
		{
			JPluginHelper::importPlugin('redevent');
			$dispatcher = JDispatcher::getInstance();
			$res = $dispatcher->trigger('onAttendeeModified', array($attendee_id));
		}

		/* Check if we have space on the waiting list */
		$model_wait = $this->getModel('waitinglist');
		$model_wait->setXrefId($xref);
		$model_wait->UpdateWaitingList();

		$model_wait->setXrefId($dest);
		$model_wait->UpdateWaitingList();

		$cache = JFactory::getCache('com_redevent');
		$cache->clean();

		$msg = $total . ' ' . JText::_('COM_REDEVENT_ATTENDEES_MOVED');

		$this->setRedirect('index.php?option=com_redevent&view=attendees&xref=' . $dest, $msg);
	}

	/**
	 * confirm an attendee registration
	 *
	 * @return boolean true on success
	 */
	public function confirm()
	{
		// Check for request forgeries
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

		// Get items to remove from the request.
		$cid = JFactory::getApplication()->input->get('cid', array(), 'array');

		$model = $this->getModel('attendees');

		if (!is_array($cid) || count($cid) < 1)
		{
			JLog::add(JText::_($this->text_prefix . '_NO_ITEM_SELECTED'), JLog::WARNING, 'jerror');
		}
		else
		{
			if ($model->confirmattendees($cid))
			{
				$msg = JText::_('COM_REDEVENT_REGISTRATION_CONFIRMED');
				$this->setMessage($msg);

				foreach ($cid as $attendee_id)
				{
					JPluginHelper::importPlugin('redevent');
					$dispatcher = JDispatcher::getInstance();
					$dispatcher->trigger('onAttendeeModified', array($attendee_id));
				}
			}
			else
			{
				$msg = JText::_('COM_REDEVENT_ERROR_REGISTRATION_CONFIRM') . ': ' . $model->getError();
				$this->setMessage($msg, 'error');
			}
		}

		// Set redirect
		$this->setRedirect($this->getRedirectToListRoute());
	}

	/**
	 * remove confirm status from an attendee registration
	 *
	 * @return boolean true on success
	 */
	public function unconfirm()
	{
		// Check for request forgeries
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

		// Get items to remove from the request.
		$cid = JFactory::getApplication()->input->get('cid', array(), 'array');

		$model = $this->getModel('attendees');

		if (!is_array($cid) || count($cid) < 1)
		{
			JLog::add(JText::_($this->text_prefix . '_NO_ITEM_SELECTED'), JLog::WARNING, 'jerror');
		}
		else
		{
			if ($model->unconfirmattendees($cid))
			{
				$msg = JText::_('COM_REDEVENT_REGISTRATION_UNCONFIRMED');
				$this->setMessage($msg);

				foreach ($cid as $attendee_id)
				{
					JPluginHelper::importPlugin('redevent');
					$dispatcher = JDispatcher::getInstance();
					$dispatcher->trigger('onAttendeeModified', array($attendee_id));
				}
			}
			else
			{
				$msg = JText::_('COM_REDEVENT_ERROR_REGISTRATION_UNCONFIRM') . ': ' . $model->getError();
				$this->setMessage($msg, 'error');
			}
		}

		// Set redirect
		$this->setRedirect($this->getRedirectToListRoute());
	}

	/**
	 * set cancelled status to an attendee registration
	 *
	 * @return boolean true on success
	 */
	public function cancelreg()
	{
		$cid = JFactory::getApplication()->input->get('cid', array(), 'post', 'array');

		$model = $this->getModel('attendees');

		if ($model->cancelreg($cid))
		{
			$msg = JText::_('COM_REDEVENT_ATTENDEES_REGISTRATION_CANCELLED');
			$this->setRedirect($this->getRedirectToListRoute('&cancelled=1'), $msg);

			foreach ($cid as $attendee_id)
			{
				JPluginHelper::importPlugin('redevent');
				$dispatcher = JDispatcher::getInstance();
				$dispatcher->trigger('onAttendeeModified', array($attendee_id));
			}
		}
		else
		{
			$msg = JText::_('COM_REDEVENT_ATTENDEES_REGISTRATION_CANCELLED_ERROR') . ': ' . $model->getError();
			$this->setRedirect($this->getRedirectToListRoute(), $msg, 'error');
		}
	}

	/**
	 * remove cancelled status from an attendee registration
	 *
	 * @return boolean true on success
	 */
	public function uncancelreg()
	{
		$cid = JFactory::getApplication()->input->get('cid', array(), 'post', 'array');

		$model = $this->getModel('attendees');

		if ($model->uncancelreg($cid))
		{
			$msg = JText::_('COM_REDEVENT_ATTENDEES_REGISTRATION_UNCANCELLED');
			$this->setRedirect($this->getRedirectToListRoute('&cancelled=0'), $msg);

			foreach ($cid as $attendee_id)
			{
				JPluginHelper::importPlugin('redevent');
				$dispatcher = JDispatcher::getInstance();
				$dispatcher->trigger('onAttendeeModified', array($attendee_id));
			}
		}
		else
		{
			$msg = JText::_('COM_REDEVENT_ATTENDEES_REGISTRATION_UNCANCELLED_ERROR') . ': ' . $model->getError();
			$this->setRedirect($this->getRedirectToListRoute(), $msg, 'error');
		}
	}

	/**
	 * puts attendees on the waiting list of the session
	 *
	 * @return boolean true on success
	 */
	public function onwaiting()
	{
		// Check for request forgeries
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

		// Get items to remove from the request.
		$cid = JFactory::getApplication()->input->get('cid', array(), 'array');

		$model = $this->getModel('registrations');

		if (!is_array($cid) || count($cid) < 1)
		{
			JLog::add(JText::_($this->text_prefix . '_NO_ITEM_SELECTED'), JLog::WARNING, 'jerror');
		}
		else
		{
			if ($model->togglewaiting($cid, 1))
			{
				$msg = count($cid) . ' ' . JText::_('COM_REDEVENT_PUT_ON_WAITING_SUCCESS');
				$this->setMessage($msg);

				foreach ($cid as $attendee_id)
				{
					JPluginHelper::importPlugin('redevent');
					$dispatcher = JDispatcher::getInstance();
					$dispatcher->trigger('onAttendeeModified', array($attendee_id));
				}
			}
			else
			{
				$msg = JText::_('COM_REDEVENT_PUT_ON_WAITING_FAILURE') . ': ' . $model->getError();
				$this->setMessage($msg, 'error');
			}
		}

		// Set redirect
		$this->setRedirect($this->getRedirectToListRoute());
	}

	/**
	 * puts attendees off the waiting list of the session
	 *
	 * @return boolean true on success
	 */
	public function offwaiting()
	{
		// Check for request forgeries
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

		// Get items to remove from the request.
		$cid = JFactory::getApplication()->input->get('cid', array(), 'array');

		$model = $this->getModel('registrations');

		if (!is_array($cid) || count($cid) < 1)
		{
			JLog::add(JText::_($this->text_prefix . '_NO_ITEM_SELECTED'), JLog::WARNING, 'jerror');
		}
		else
		{
			if ($model->togglewaiting($cid, 0))
			{
				$msg = count($cid) . ' ' . JText::_('COM_REDEVENT_PUT_OFF_WAITING_SUCCESS');
				$this->setMessage($msg);

				foreach ($cid as $attendee_id)
				{
					JPluginHelper::importPlugin('redevent');
					$dispatcher = JDispatcher::getInstance();
					$dispatcher->trigger('onAttendeeModified', array($attendee_id));
				}
			}
			else
			{
				$msg = JText::_('COM_REDEVENT_PUT_OFF_WAITING_FAILURE') . ': ' . $model->getError();
				$this->setMessage($msg, 'error');
			}
		}

		// Set redirect
		$this->setRedirect($this->getRedirectToListRoute());
	}

	/**
	 * Get the JRoute object for a redirect to list.
	 *
	 * @param   string  $append  An optional string to append to the route
	 *
	 * @return  JRoute  The JRoute object
	 */
	protected function getRedirectToListRoute($append = null)
	{
		$returnUrl = $this->input->get('return');

		if ($returnUrl)
		{
			$returnUrl = base64_decode($returnUrl);

			return JRoute::_($returnUrl . $append, false);
		}
		else
		{
			if (!$sessionId = $this->input->getInt('session', 0))
			{
				$filters = $this->input->get('filter', null, 'array');
				$sessionId = isset($filters['session']) ? (int) $filters['session'] : 0;

				if (!$sessionId)
				{
					die( 'Missing session Id' );
				}
			}

			return JRoute::_('index.php?option=com_redevent&view=attendees&session=' . $sessionId . $append, false);
		}
	}
}
