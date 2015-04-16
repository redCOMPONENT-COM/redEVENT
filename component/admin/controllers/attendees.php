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
			JError::raiseError(500, JText::_('COM_REDEVENT_Select_an_attendee_to_move' ) );
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

		foreach($cid as $attendee_id)
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

		$msg = $total.' '.JText::_( 'COM_REDEVENT_ATTENDEES_MOVED');

		$this->setRedirect( 'index.php?option=com_redevent&view=attendees&xref='.$dest, $msg );
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

	/* Obsolete */
	function export()
	{
		$mainframe = &JFactory::getApplication();

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
    		$col[] = str_replace("\"", "\"\"", JHTML::Date( $data->uregdate, JText::_('COM_REDEVENT_JDATE_FORMAT_DATETIME' ) ));

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

	protected function getRedirectToList()
	{
		$app = JFactory::getApplication();
		$sessionId = $app->input->getInt('session', 0) or die( 'Missing session Id' );

		if ($app->input->get('return'))
		{
			$link = base64_decode($app->input->get('return'));
		}
		else
		{
			$link = 'index.php?option=com_redevent&view=attendees&session=' . $sessionId;
		}

		return $link;
	}
}
