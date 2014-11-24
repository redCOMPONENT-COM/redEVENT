<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevent Component session Controller
 *
 * @package  Redevent.admin
 * @since    3.0
 */
class RedeventControllerSession extends RControllerForm
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
}
