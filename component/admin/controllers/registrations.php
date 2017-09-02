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
 * @since    2.0
 */
class RedeventControllerRegistrations extends RControllerAdmin
{
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
			$this->setRedirect('index.php?option=com_redevent&view=registrations&cancelled=1', $msg);
		}
		else
		{
			$msg = JText::_('COM_REDEVENT_ATTENDEES_REGISTRATION_CANCELLED_ERROR') . ': ' . $model->getError();
			$this->setRedirect('index.php?option=com_redevent&view=registrations', $msg, 'error');
		}

		return true;
	}

	/**
	 * set cancelled status to an attendee registration
	 *
	 * @return void
	 */
	public function cancelmultiple()
	{
		$cid = JFactory::getApplication()->input->get('cid', array(), 'post', 'array');

		$model = $this->getModel('attendees');

		if ($model->cancelMultipleReg($cid))
		{
			$msg = JText::_('COM_REDEVENT_ATTENDEES_REGISTRATION_CANCELLED');
			$this->setRedirect($this->getRedirectToListRoute(), $msg);
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
			$this->setRedirect('index.php?option=com_redevent&view=registrations&cancelled=0', $msg);

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
			$this->setRedirect('index.php?option=com_redevent&view=registrations', $msg, 'error');
		}

		return true;
	}

	/**
	 * Removes an item.
	 *
	 * @return  void
	 */
	public function delete()
	{
		// Check for request forgeries
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

		// Get items to remove from the request.
		$cid = JFactory::getApplication()->input->get('cid', array(), 'array');

		// Get the model.
		$model = $this->getModel('attendee');

		if (!is_array($cid) || count($cid) < 1)
		{
			JLog::add(JText::_($this->text_prefix . '_NO_ITEM_SELECTED'), JLog::WARNING, 'jerror');
		}
		else
		{
			// Make sure the item ids are integers
			jimport('joomla.utilities.arrayhelper');
			JArrayHelper::toInteger($cid);

			// Remove the items.
			if ($model->delete($cid))
			{
				$this->setMessage(JText::plural($this->text_prefix . '_N_ITEMS_DELETED', count($cid)));
			}
			else
			{
				$this->setMessage($model->getError(), 'error');
			}
		}

		// Set redirect
		$this->setRedirect($this->getRedirectToListRoute());
	}

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
			return;
		}

		foreach ($cid as $attendee_id)
		{
			JPluginHelper::importPlugin('redevent');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('onAttendeeDeleted', array($attendee_id));
		}
	}

	/**
	 * confirm an attendee registration
	 *
	 * @return void
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
	 * @return void
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
	 * puts attendees on the waiting list of the session
	 *
	 * @return void
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
	 * @return void
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
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  object  The model.
	 */
	public function getModel($name = '', $prefix = '', $config = array('ignore_request' => true))
	{
		return parent::getModel($name ?: 'attendee', $prefix, $config);
	}
}
