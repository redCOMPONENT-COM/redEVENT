<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevent Component Attendee Controller
 *
 * @package  Redevent.admin
 * @since    3.0
 */
class RedeventControllerAttendee extends RControllerForm
{
	/**
	 * Logic to save an attendee
	 *
	 * @return void
	 */
	public function save()
	{
		// Check for request forgeries
		JSession::checkToken() or die('Invalid Token');

		$xref = $this->input->getInt('sessionId', 0) or die( 'Missing session id' );
		$task = $this->input->getCmd('task');

		$model = $this->getModel('attendee');

		$data = array();

		$data['id'] = $this->input->getInt('id');
		$data['xref'] = $xref;
		$data['sessionpricegroup_id'] = $this->input->getInt('sessionpricegroup_id', 0);
		$data['uid'] = $this->input->getInt('uid');

		if ($returnid = $model->store($data))
		{
			$model_wait = $this->getModel('Waitinglist');
			$model_wait->setXrefId($xref);
			$model_wait->UpdateWaitingList();

			$cache = JFactory::getCache('com_redevent');
			$cache->clean();

			JPluginHelper::importPlugin('redevent');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('onAttendeeModified', array($returnid));

			switch ($task)
			{
				case 'apply' :
					$link = 'index.php?option=com_redevent&task=attendee.edit&id=' . $returnid;
					break;

				default :
					$link = $this->getRedirectToListRoute();
			}

			$this->setMessage(JText::_('COM_REDEVENT_REGISTRATION_SAVED'));
		}
		else
		{
			$link = $this->getRedirectToListRoute();
			$this->setMessage($model->getError(), 'error');
		}

		$model->checkin();
		$this->setRedirect($link);
	}
}
