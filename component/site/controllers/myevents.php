<?php
/**
 * @package    Redevent.Site
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * redEVENT Component Myevents Controller
 *
 * @package  Redevent.Site
 * @since    2.5
 */
class RedeventControllerMyevents extends RedeventControllerFront
{
	/**
	 * ajax publish/unpublish a session
	 *
	 * @return void
	 */
	public function publishxref()
	{
		$input = $this->input;
		$xref  = $input->get('xref', 0, 'int');
		$state = $input->get('state', 0, 'int');

		$useracl = RedeventUserAcl::getInstance();

		$resp = new stdclass;

		if (!$useracl->canPublishXref($xref))
		{
			$resp->status = 0;
			$resp->error = JText::_('COM_REDEVENT_USER_ACTION_NOT_ALLOWED');
		}
		else
		{
			$model = $this->getModel('frontadmin');
			$res = $model->publishXref($xref, $state);

			if ($res)
			{
				$resp->status = 1;
			}
			else
			{
				$resp->status = 0;
				$resp->error = $model->getError();
			}
		}

		echo json_encode($resp);
		JFactory::getApplication()->close();
	}

	/**
	 * cancel a registration
	 *
	 * @return void
	 */
	public function cancelreg()
	{
		$app = JFactory::getApplication();
		$rid = $app->input->get('rid');
		$model = $this->getModel('registration');

		$resp = new stdClass;

		if ($res = $model->cancelregistration($rid))
		{
			$resp->status = 1;

			JPluginHelper::importPlugin('redevent');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('onAttendeeCancelled', array($rid));
		}
		else
		{
			$resp->status = 0;
			$resp->error = $model->getError();
		}

		echo json_encode($resp);
		JFactory::getApplication()->close();
	}

	/**
	 * Publish an event
	 *
	 * @return void
	 */
	public function publishevent()
	{
		$input = $this->input;
		$id  = $input->get('id', 0, 'int');

		$this->setEventPublishState($id, 1);
	}

	/**
	 * Unpublish an event
	 *
	 * @return void
	 */
	public function unpublishevent()
	{
		$input = $this->input;
		$id  = $input->get('id', 0, 'int');

		$this->setEventPublishState($id, 0);
	}

	/**
	 * Publish / Unpublish a venue
	 *
	 * @param   integer  $id     venue id
	 * @param   integer  $state  new state
	 *
	 * @return void
	 *
	 * @since 3.2.4
	 */
	private function setEventPublishState($id, $state)
	{
		$useracl = RedeventUserAcl::getInstance();

		$msgType = 'message';

		if (!$useracl->canPublishEvent($id))
		{
			$msg = JText::_('COM_REDEVENT_USER_ACTION_NOT_ALLOWED');
			$msgType = 'error';
		}
		else
		{
			$model = $this->getModel('editevent');

			$ids = array($id);
			$res = $model->publish($ids, $state);

			if ($res)
			{
				$msg = JText::_('COM_REDEVENT_PUBLISHED_STATE_UPDATED');
			}
			else
			{
				$msg = $model->getError();
				$msgType = 'error';
			}
		}

		$this->setRedirect(JRoute::_(RedeventHelperRoute::getMyEventsRoute(), false), $msg, $msgType);
	}

	/**
	 * Publish a venue
	 *
	 * @return void
	 */
	public function deletevenue()
	{
		$input = $this->input;
		$id  = $input->get('id', 0, 'int');

		$useracl = RedeventUserAcl::getInstance();

		$msgType = 'message';

		if (!$useracl->canEditVenue($id))
		{
			$msg = JText::_('COM_REDEVENT_USER_ACTION_NOT_ALLOWED');
			$msgType = 'error';

			$this->setRedirect(JRoute::_(RedeventHelperRoute::getMyEventsRoute(), false), $msg, $msgType);

			return;
		}

		$model = $this->getModel('editvenue');
		$ids = array($id);

		if (!$model->delete($ids))
		{
			$msg = $model->getError();
			$msgType = 'error';
		}
		else
		{
			$msg = JText::_('COM_REDEVENT_MYEVENTS_VENUE_DELETED');
		}

		$this->setRedirect(JRoute::_(RedeventHelperRoute::getMyEventsRoute(), false), $msg, $msgType);
	}

	/**
	 * Publish a venue
	 *
	 * @return void
	 */
	public function publishvenue()
	{
		$input = $this->input;
		$id  = $input->get('id', 0, 'int');

		$this->setVenuePublishState($id, 1);
	}

	/**
	 * Unpublish a venue
	 *
	 * @return void
	 */
	public function unpublishvenue()
	{
		$input = $this->input;
		$id  = $input->get('id', 0, 'int');

		$this->setVenuePublishState($id, 0);
	}

	/**
	 * Publish / Unpublish a venue
	 *
	 * @param   integer  $id     venue id
	 * @param   integer  $state  new state
	 *
	 * @return void
	 *
	 * @since 3.2.4
	 */
	private function setVenuePublishState($id, $state)
	{
		$useracl = RedeventUserAcl::getInstance();

		$msgType = 'message';

		if (!$useracl->canPublishVenue($id))
		{
			$msg = JText::_('COM_REDEVENT_USER_ACTION_NOT_ALLOWED');
			$msgType = 'error';
		}
		else
		{
			$model = $this->getModel('editvenue');

			$ids = array($id);
			$res = $model->publish($ids, $state);

			if ($res)
			{
				$msg = JText::_('COM_REDEVENT_PUBLISHED_STATE_UPDATED');
			}
			else
			{
				$msg = $model->getError();
				$msgType = 'error';
			}
		}

		$this->setRedirect(JRoute::_(RedeventHelperRoute::getMyEventsRoute(), false), $msg, $msgType);
	}
}
