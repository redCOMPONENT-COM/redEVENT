<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevent Component sessions Controller
 *
 * @package  Redevent.admin
 * @since    0.9
 */
class RedeventControllerSessions extends RControllerAdmin
{
  /**
   * save the event session
   */
	public function save()
	{
    // Check for request forgeries
    JRequest::checkToken() or die( 'Invalid Token' );

    $post = JRequest::get( 'post' );

		$isNew = JFactory::getApplication()->input->getInt('id', 0) == 0;

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

			if (JRequest::getVar('task') == 'saveAndTwit')
			{
				JPluginHelper::importPlugin('system', 'autotweetredevent');
				$dispatcher =& JDispatcher::getInstance();
				$res = $dispatcher->trigger('onAfterRedeventSessionSave', array($returnid));
			}

			// Trigger event
			JPluginHelper::importPlugin('redevent');
			$dispatcher =& JDispatcher::getInstance();
			$res = $dispatcher->trigger('onAfterSessionSave', array($returnid, $isNew));

      $msg = 'saved session';
			if (JRequest::getVar('task') == 'apply')
			{
				$this->setRedirect('index.php?option=com_redevent&view=session&cid[]=' . $returnid, $msg);
      }
      else {
      	$this->setRedirect('index.php?option=com_redevent&view=sessions', $msg);
      }
    }
    else {
    	$msg = 'error saving: '. $model->getError() ;
      $this->setRedirect('index.php?option=com_redevent&view=sessions',  $msg, 'error');
    }
    return true;
	}

	public function cancel()
	{
		$eventid = JRequest::getInt('eventid');
		$this->setRedirect('index.php?option=com_redevent&view=sessions');
	}

	/**
	 * Logic to publish
	 *
	 * @access public
	 * @return void
	 * @since  0.9
	 */
	public function publish()
	{
		$cid = JRequest::getVar('cid', array(0), 'post', 'array');

		if (!is_array($cid) || count($cid) < 1)
		{
			JError::raiseError(500, JText::_('COM_REDEVENT_Select_an_item_to_publish'));
		}

		$model = $this->getModel('sessions');

		if (!$model->publish($cid, 1))
		{
			echo "<script> alert('" . $model->getError() . "'); window.history.go(-1); </script>\n";
		}

		// Trigger plugins
		foreach ($cid as $id)
		{
			// Trigger event for plugins
			JPluginHelper::importPlugin('redevent');
			$dispatcher =& JDispatcher::getInstance();
			$res = $dispatcher->trigger('onAfterSessionSaved', array($id));
		}

		$total = count( $cid );
		$msg 	= $total.' '.JText::_('COM_REDEVENT_SESSIONS_PUBLISHED');

		$eventid = JRequest::getInt('eventid');
		$this->setRedirect('index.php?option=com_redevent&view=sessions', $msg);
	}

	/**
	 * Logic to unpublish
	 *
	 * @access public
	 * @return void
	 * @since  0.9
	 */
	public function unpublish()
	{
		$cid = JRequest::getVar('cid', array(0), 'post', 'array');

		if (!is_array($cid) || count($cid) < 1)
		{
			JError::raiseError(500, JText::_('COM_REDEVENT_Select_an_item_to_unpublish'));
		}

		$model = $this->getModel('sessions');

		if (!$model->publish($cid, 0))
		{
			echo "<script> alert('" . $model->getError() . "'); window.history.go(-1); </script>\n";
		}

		// Trigger plugins
		foreach ($cid as $id)
		{
			// Trigger event for plugins
			JPluginHelper::importPlugin('redevent');
			$dispatcher =& JDispatcher::getInstance();
			$res = $dispatcher->trigger('onAfterSessionSaved', array($id));
		}

		$total = count( $cid );
		$msg 	= $total.' '.JText::_('COM_REDEVENT_SESSIONS_UNPUBLISHED');

		$eventid = JRequest::getInt('eventid');
		$this->setRedirect('index.php?option=com_redevent&view=sessions', $msg);
	}

	/**
	 * Logic to archive
	 *
	 * @access public
	 * @return void
	 * @since  0.9
	 */
	public function archive()
	{
		$cid = JRequest::getVar('cid', array(0), 'post', 'array');

		if (!is_array($cid) || count($cid) < 1)
		{
			JError::raiseError(500, JText::_('COM_REDEVENT_Select_an_item_to_archive'));
		}

		$model = $this->getModel('sessions');

		if (!$model->publish($cid, -1))
		{
			echo "<script> alert('" . $model->getError() . "'); window.history.go(-1); </script>\n";
		}

		// Trigger plugins
		foreach ($cid as $id)
		{
			// Trigger event for plugins
			JPluginHelper::importPlugin('redevent');
			$dispatcher =& JDispatcher::getInstance();
			$res = $dispatcher->trigger('onAfterSessionSaved', array($id));
		}

		$total = count( $cid );
		$msg 	= $total.' '.JText::_('COM_REDEVENT_SESSIONS_ARCHIVED');

		$eventid = JRequest::getInt('eventid');
		$this->setRedirect('index.php?option=com_redevent&view=sessions', $msg);
	}

	/**
	 * logic to remove a session
	 *
	 * @access public
	 * @return void
	 * @since  0.9
	 */
	public function remove()
	{
		$cid = JRequest::getVar('cid', array(0), 'post', 'array');

		$total = count($cid);

		if (!is_array($cid) || count($cid) < 1)
		{
			JError::raiseError(500, JText::_('COM_REDEVENT_Select_an_item_to_delete'));
		}

		$model = $this->getModel('session');
		foreach ($cid as $xref)
		{
			// Get Data before deletion for event plugins
			$model->setId($xref);
			$session = $model->getXref();
			$session_code = $session->session_code;

			if(!$model->removexref($xref))
			{
				$msg = $model->getError();
				$this->setRedirect('index.php?option=com_redevent&view=sessions', $msg, 'error');
				return;
			}

			// Trigger event
			JPluginHelper::importPlugin('redevent');
			$dispatcher = JDispatcher::getInstance();
			$res = $dispatcher->trigger('onAfterSessionDelete', array($session_code));
		}

		$msg = $total . ' ' . JText::_('COM_REDEVENT_SESSIONS_DELETED');

		$cache = & JFactory::getCache('com_redevent');
		$cache->clean();

		$eventid = JRequest::getInt('eventid');
		$this->setRedirect('index.php?option=com_redevent&view=sessions', $msg);
	}

	/**
	 * Logic to set as featured
	 *
	 * @access public
	 * @return void
	 * @since  0.9
	 */
	public function featured()
	{
		$cid = JRequest::getVar('cid', array(0), 'post', 'array');

		if (!is_array($cid) || count($cid) < 1)
		{
			JError::raiseError(500, JText::_('COM_REDEVENT_Select_an_item_to_publish'));
		}

		$model = $this->getModel('sessions');

		if (!$model->featured($cid, 1))
		{
			echo "<script> alert('" . $model->getError() . "'); window.history.go(-1); </script>\n";
		}

		// Trigger plugins
		foreach ($cid as $id)
		{
			// Trigger event for plugins
			JPluginHelper::importPlugin('redevent');
			$dispatcher =& JDispatcher::getInstance();
			$res = $dispatcher->trigger('onAfterSessionSaved', array($id));
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
	 * @since  0.9
	 */
	public function unfeatured()
	{
		$cid = JRequest::getVar('cid', array(0), 'post', 'array');

		if (!is_array($cid) || count($cid) < 1)
		{
			JError::raiseError(500, JText::_('COM_REDEVENT_Select_an_item_to_publish'));
		}

		$model = $this->getModel('sessions');

		if(!$model->featured($cid, 0)) {
			echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
		}

		// Trigger plugins
		foreach ($cid as $id)
		{
			// Trigger event for plugins
			JPluginHelper::importPlugin('redevent');
			$dispatcher =& JDispatcher::getInstance();
			$res = $dispatcher->trigger('onAfterSessionSaved', array($id));
		}

		$total = count($cid);
		$msg = JText::sprintf('COM_REDEVENT_SESSIONS_SET_AS_NOT_FEATURED', $total);

		$eventid = JRequest::getInt('eventid');
		$this->setRedirect('index.php?option=com_redevent&view=sessions', $msg);
	}
}
