<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevent Component Events Controller
 *
 * @package  Redevent.admin
 * @since    0.9
 */
class RedeventControllerEvents extends RControllerAdmin
{
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
			JError::raiseError(500, JText::_('COM_REDEVENT_Select_an_item_to_publish' ) );
		}

		$model = $this->getModel('events');
		if(!$model->publish($cid, 1)) {
			echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
		}

		// Trigger plugins
		foreach ($cid as $eventid)
		{
			// Trigger event for plugins
			JPluginHelper::importPlugin('redevent');
			$dispatcher =& JDispatcher::getInstance();
			$res = $dispatcher->trigger('onAfterEventSaved', array($eventid));
		}

		$total = count( $cid );
		$msg 	= $total.' '.JText::_('COM_REDEVENT_EVENT_PUBLISHED');

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
			JError::raiseError(500, JText::_('COM_REDEVENT_Select_an_item_to_unpublish' ) );
		}

		$model = $this->getModel('events');
		if(!$model->publish($cid, 0)) {
			echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
		}

		// Trigger plugins
		foreach ($cid as $eventid)
		{
			// Trigger event for plugins
			JPluginHelper::importPlugin('redevent');
			$dispatcher =& JDispatcher::getInstance();
			$res = $dispatcher->trigger('onAfterEventSaved', array($eventid));
		}

		$total = count( $cid );
		$msg 	= $total.' '.JText::_('COM_REDEVENT_EVENT_UNPUBLISHED');

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
			JError::raiseError(500, JText::_('COM_REDEVENT_Select_an_item_to_archive' ) );
		}

		$model = $this->getModel('events');
		if(!$model->publish($cid, -1)) {
			echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
		}

		// Trigger plugins
		foreach ($cid as $eventid)
		{
			// Trigger event for plugins
			JPluginHelper::importPlugin('redevent');
			$dispatcher =& JDispatcher::getInstance();
			$res = $dispatcher->trigger('onAfterEventSaved', array($eventid));
		}

		$total = count( $cid );
		$msg 	= $total.' '.JText::_('COM_REDEVENT_OLD_EVENT_DATE_ARCHIVED');

		$this->setRedirect( 'index.php?option=com_redevent&view=events', $msg );
	}

  /**
   * Logic to archive events
   *
   * @access public
   * @return void
   * @since 0.9
   */
  function archivepast()
  {
    $cid  = JRequest::getVar( 'cid', array(0), 'post', 'array' );

    if (!is_array( $cid ) || count( $cid ) < 1) {
      JError::raiseError(500, JText::_('COM_REDEVENT_Select_an_item_to_archive' ) );
    }

    $model = $this->getModel('events');
    if(!$model->archive($cid)) {
      echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
    }

	  // Trigger plugins
	  foreach ($cid as $eventid)
	  {
		  // Trigger event for plugins
		  JPluginHelper::importPlugin('redevent');
		  $dispatcher =& JDispatcher::getInstance();
		  $res = $dispatcher->trigger('onAfterEventSaved', array($eventid));
	  }

    $total = count( $cid );
    $msg  = $total.' '.JText::_('COM_REDEVENT_OLD_EVENT_DATE_ARCHIVED');

    $this->setRedirect( 'index.php?option=com_redevent&view=events', $msg );
  }

	/**
	 * logic to save an event
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
	function save()
	{
		// Check for request forgeries
		JSession::checkToken() or die( 'Invalid Token' );
		$db   = JFactory::getDBO();
		$app  = JFactory::getApplication();
		$task = $app->input->get('task');

		$post = JRequest::get( 'post', 4 );

		/* Get the form fields to display */
		$showfields = array();

		foreach ($post as $field => $value)
		{
			if (substr($field, 0, 9) == 'showfield' && $value == "1")
			{
				$showfields[] = substr($field, 9);
			}
		}

		$post['showfields'] = implode(',', $showfields);

		if (!isset($post['checked_out']))
		{
			$post['checked_out'] = 0;
		}

		/* Fix the submission types */
		if (!$post['submission_types'])
		{
			$post['submission_types'] = array();
		}
		else
		{
			$post['submission_types'] = implode(',', $post['submission_types']);
		}

		$model = $this->getModel('event');
		$model_wait = $this->getModel('waitinglist');

		if ($returnid = $model->store($post))
		{
			// Event saved, trigger plugins
			JPluginHelper::importPlugin('redevent');
			$dispatcher =& JDispatcher::getInstance();
			$res = $dispatcher->trigger('onAfterEventSaved', array($returnid));

			$msg	= JText::_('COM_REDEVENT_EVENT_SAVED');

			// Check if we have session info, which only happens when creating the event the first time (other sessions have to be added in sessions view)
			if (isset($post['venueid']) && $post['venueid'])
			{
				if (!$xref = $this->_saveInitialSession($returnid)) {
					$msg .= "\n".JTExt::_('COM_REDEVENT_EVENT_FAILED_SAVING_INITIAL_SESSION').': '.$this->getError();
				}

				// Session saved, trigger plugins
				JPluginHelper::importPlugin('redevent');
				$dispatcher =& JDispatcher::getInstance();
				$res = $dispatcher->trigger('onAfterSessionSaved', array($xref));

				// Specific for autotweet
				if ($task == 'saveAndTwit')
				{
					JPluginHelper::importPlugin( 'system', 'autotweetredevent');
					$dispatcher =& JDispatcher::getInstance();
					$res = $dispatcher->trigger( 'onAfterRedeventSessionSave', array( $xref ) );
				}
			}

			switch ($task)
			{
				case 'apply' :
					$link = 'index.php?option=com_redevent&controller=events&view=event&hidemainmenu=1&cid[]='.$returnid;
					break;

				default :
					$link = 'index.php?option=com_redevent&view=events';
					break;
			}

			$cache = &JFactory::getCache('com_redevent');
			$cache->clean();

		} else {
			$msg 	= $model->getError();
			$link = 'index.php?option=com_redevent&view=events';

		}

		$model->checkin();

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

		$msgtype = "message";

		if (!is_array( $cid ) || count( $cid ) < 1) {
			$msg = JText::_('COM_REDEVENT_Select_an_item_to_delete' );
			$msgtype = 'error';
		}

		$model = $this->getModel('events');
		if (!$model->delete($cid)) {
			$msg = $model->getError();
			$msgtype = 'error';
		}
		else {
			$msg = $total.' '.JText::_('COM_REDEVENT_EVENTS_DELETED');
			$cache = &JFactory::getCache('com_redevent');
			$cache->clean();
		}

		// Trigger plugins
		foreach ($cid as $id)
		{
			JPluginHelper::importPlugin('redevent');
			$dispatcher =& JDispatcher::getInstance();
			$res = $dispatcher->trigger('onAfterEventRemoved', array($id));
		}

		$this->setRedirect( 'index.php?option=com_redevent&view=events', $msg, $msgtype);
	}

	function removexref()
	{
		$id = JRequest::getVar('xref', 0, 'request', 'int');

		if (!$id) {
			echo '0' .':'. JText::_('COM_REDEVENT_NO_XREF_ID');
      return true;
		}
		else {
			$model = $this->getModel('session');
			if ($model->removexref($id)) {
				echo '1' .':'. JText::_('COM_REDEVENT_DATE_DELETED');

				JPluginHelper::importPlugin('redevent');
				$dispatcher =& JDispatcher::getInstance();
				$res = $dispatcher->trigger('onAfterSessionRemoved', array($id));

        return true;
			}
			else {
        echo '0' .':'. JText::_('COM_REDEVENT_COULDNT_DELETE_DATE') .' - '. $model->getError() ;
        return true;
			}
		}
	}

  /**
   * save data of first session associated to newly created event
   *
   * @param int $eventid
   * @return mixed xref id on success, else false
   */
  protected function _saveInitialSession($eventid)
  {
  	$model = $this->getModel('Session', 'RedeventModel');

  	$post = JRequest::get( 'post' );
  	$post['eventid'] = $eventid;
    $post['details'] = JRequest::getVar('session_details', '', 'post', 'string', JREQUEST_ALLOWRAW);
    $post['icaldetails'] = JRequest::getVar('icaldetails', '', 'post', 'string', JREQUEST_ALLOWRAW);
    foreach ($post as $key => $val)
    {
    	if (strpos($key, 'session_') === 0) {
    		$post[substr($key, 8)] = $val;
    	}
    }

    $model = $this->getModel('session');
    if (!$returnid = $model->savexref($post))
    {
    	$this->setError($model->getError());
    	return false;
    }
    return $returnid;

  }
}
