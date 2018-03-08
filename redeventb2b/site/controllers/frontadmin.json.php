<?php
/**
 * @package    Redevent.site
 * @copyright  Copyright (C) 2008 - 2014 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * redEVENT Component b2b Controller
 *
 * @package     Joomla
 * @subpackage  redEVENT
 * @since       2.5
 */
class Redeventb2bControllerFrontadmin extends JControllerLegacy
{
	/**
	 * return sessions html table
	 *
	 * @return void
	 */
	public function searchsessions()
	{
		$app = JFactory::getApplication();

		$this->input->set('view', 'frontadmin');
		$this->input->set('layout', 'searchsessions');

		$this->display();

		// No debug !
		$app->close();
	}

	/**
	 * return events options as JSON
	 *
	 * @return void
	 */
	public function eventsoptions()
	{
		$app = JFactory::getApplication();

		$model = $this->getModel('Frontadmin');
		$options = $model->getEventsOptions();

		echo json_encode($options);

		$app->close();
	}

	/**
	 * return sessions options as JSON
	 *
	 * @return void
	 */
	public function sessionsoptions()
	{
		$app = JFactory::getApplication();

		$model = $this->getModel('Frontadmin');
		$options = $model->getSessionsOptions();

		echo json_encode($options);

		$app->close();
	}

	/**
	 * return venues options as JSON
	 *
	 * @return void
	 */
	public function venuesoptions()
	{
		$app = JFactory::getApplication();

		$model = $this->getModel('Frontadmin');
		$options = $model->getVenuesOptions();

		echo json_encode($options);

		$app->close();
	}

	/**
	 * return categories options as JSON
	 *
	 * @return void
	 */
	public function categoriesoptions()
	{
		$app = JFactory::getApplication();

		$model = $this->getModel('Frontadmin');
		$options = $model->getCategoriesOptions();

		echo json_encode($options);

		$app->close();
	}

	/**
	 * return booked sessions html table
	 *
	 * @return void
	 */
	public function getbookings()
	{
		$app = JFactory::getApplication();

		$this->viewName  = 'frontadmin';
		$this->modelName = 'frontadmin';
		$this->layout    = 'bookings';

		$this->display();

		// No debug !
		$app->close();
	}

	/**
	 * return sessions details as JSON
	 *
	 * @return void
	 */
	public function getusers()
	{
		$app = JFactory::getApplication();

		$model = $this->getModel('Frontadmin');
		$options = $model->getUsersOptions();

		echo json_encode($options);

		$app->close();
	}

	/**
	 * return sessions details as JSON
	 *
	 * @return void
	 */
	public function getsession()
	{
		$app = JFactory::getApplication();
		$model = RModel::getFrontInstance('eventhelper', array('ignore_request' => true), 'com_redevent');
		$model->setXref($app->input->get('id'));
		$data  = $model->getData();

		if ($data && $data->maxattendees)
		{
			$data->placesleft = $model->getPlacesLeft();
		}
		elseif ($data)
		{
			$data->placesleft = -1;
		}

		echo json_encode($data);

		$app->close();
	}

	/**
	 * Get attendees list
	 *
	 * @return void
	 */
	public function getattendees()
	{
		$app = JFactory::getApplication();

		$this->viewName  = 'frontadmin';
		$this->modelName = 'frontadmin';
		$this->layout    = 'attendees';

		$model = $this->getModel('FrontadminMembers');

		$orgId = $app->input->get('org', 0, 'int');

		$att = $model->getAttendees($app->input->get('xref', 0, 'int'), $orgId, $app->input->get('filter_person', '', 'string'));

		$view = $this->getView('frontadmin', 'html');
		$view->assignRef('attendees', $att);
		$view->assign('orgId', $orgId);
		$view->setModel($model, false);
		$view->setLayout($this->layout);
		$this->display();

		JFactory::getApplication()->close();
	}

	/**
	 * Get members list
	 *
	 * @return void
	 */
	public function getmembers()
	{
		$app = JFactory::getApplication();

		$this->viewName  = 'frontadmin';
		$this->modelName = 'frontadmin';
		$this->layout    = 'members';

		$model = $this->getModel('FrontadminMembers');

		$orgId = $app->input->get('org', 0, 'int');

		$att = $model->getAttendees(0, $orgId, $app->input->get('filter_person', '', 'string'));

		$view = $this->getView('frontadmin', 'html');
		$view->assignRef('members', $att);
		$view->assign('orgId', $orgId);
		$view->setModel($model, false);
		$view->setLayout($this->layout);
		$this->display();

		JFactory::getApplication()->close();
	}

	/**
	 * Show edit member view
	 *
	 * @return void
	 */
	public function editmember()
	{
		$app = JFactory::getApplication();

		$this->input->set('view', 'frontadmin');
		$this->input->set('layout', 'editmember');

		$this->display();

		if (!$this->input->get('modal'))
		{
			JFactory::getApplication()->close();
		}
	}

	/**
	 * Close modal for create member
	 *
	 * @return void
	 */
	public function closemodalmember()
	{
		$this->viewName  = 'frontadmin';
		$this->modelName = 'frontadmin';
		$this->layout    = 'closemodalmember';

		$this->display();
	}

	/**
	 * Show booked members
	 *
	 * @return void
	 */
	public function getmemberbooked()
	{
		$app = JFactory::getApplication();

		$this->viewName  = 'frontadmin';
		$this->modelName = 'frontadmin';
		$this->layout    = 'memberbooked';

		$model = $this->getModel('frontadmin');

		$this->display();

		JFactory::getApplication()->close();
	}

	/**
	 * Show member previous bookings
	 *
	 * @return void
	 */
	public function getmemberprevious()
	{
		$app = JFactory::getApplication();

		$this->viewName  = 'frontadmin';
		$this->modelName = 'frontadmin';
		$this->layout    = 'memberprevious';

		$model = $this->getModel('frontadmin');

		$this->display();

		JFactory::getApplication()->close();
	}

	/**
	 * book users to event
	 *
	 * @return void
	 */
	public function quickbook()
	{
		// Can't display warnings before json...
		$err_reporting = error_reporting(E_ERROR | E_PARSE);

		$app = JFactory::getApplication();

		$xref = $app->input->get('xref', 0, 'int');
		$regs = $app->input->get('reg', array(), 'array');
		$orgId = $app->input->get('org', 0, 'int');
		JArrayHelper::toInteger($regs);

		$resp = new stdclass;
		$resp->status = 1;
		$resp->regs = array();

		$acl = RedeventUserAcl::getInstance();

		if (!$acl->canManageAttendees($xref))
		{
			$resp->status = 0;
			$resp->error = JText::_('COM_REDEVENT_USER_ACTION_NOT_ALLOWED');
		}
		else
		{
			$added = 0;

			foreach ($regs as $user_id)
			{
				try
				{
					$model = $this->getModel('Frontadminregistration', 'Redeventb2bModel');
					$attendee = $model->book($user_id, $xref, $orgId);
					$regresp = new stdclass;

					if ($attendee)
					{
						$regresp->status = 1;
						$regresp->details = $attendee;
						$resp->submit_key = $attendee->submit_key;
						$added++;

						JPluginHelper::importPlugin('redevent');
						$dispatcher = JDispatcher::getInstance();
						$dispatcher->trigger('onAttendeeCreated', array($attendee->id));
					}
					else
					{
						$resp->status = 0;
						$regresp->status = 0;
						$regresp->error = $model->getError();
					}

					$resp->message = JText::sprintf('COM_REDEVENT_FRONTEND_ADMIN_D_MEMBERS_BOOKED', $added);
					$resp->regs[] = $regresp;
				}
				catch (Redeventb2bExceptionNotice $e)
				{
					$resp->status = 1;
					$regresp->status = 1;
					$regresp->error = $e->getMessage();
					$resp->regs[] = $regresp;
				}
				catch (Exception $e)
				{
					$resp->status = 0;
					$regresp->status = 0;
					$regresp->error = $e->getMessage();
					$resp->regs[] = $regresp;
				}
			}
		}

		echo json_encode($resp);

		JFactory::getApplication()->close();
	}

	/**
	 * ajax publish/unpublish a session
	 *
	 * @return void
	 */
	public function publishxref()
	{
		$input = JFactory::getApplication()->input;
		$xref  = $input->get('xref', 0, 'int');
		$state = $input->get('state', 0, 'int');

		$RedeventUserAcl = RedeventUserAcl::getInstance();

		$resp = new stdclass;

		if (!$RedeventUserAcl->canPublishXref($xref))
		{
			$resp->status = 0;
			$resp->error  = JText::_('COM_REDEVENT_USER_ACTION_NOT_ALLOWED');
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
				$resp->error  = $model->getError();
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
		$rid = $app->input->get('rid', 0, 'int');
		$orgId = $app->input->get('org', 0, 'int');

		$model = RModel::getFrontInstance('registration', array('ignore_request' => true), 'com_redevent');

		$resp = new stdClass;

		if ($res = $model->cancelregistration($rid))
		{
			$resp->status = 1;

			JPluginHelper::importPlugin('redevent');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('onAttendeeCancelled', array($rid));

			$this->sendCancellationNotifications($rid, $orgId);
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
	 * Send cancellation notifications
	 *
	 * @param   int  $rid    attendee id
	 * @param   int  $orgId  organization id
	 *
	 * @return void
	 */
	private function sendCancellationNotifications($rid, $orgId)
	{
		$model = $this->getModel('Frontadmincancellationnotification');
		$model->setAttendeeId($rid)
			->setOrganizationId($orgId);
		$model->notify();
	}

	/**
	 * ajax update ponumber
	 *
	 * @return void
	 */
	public function updateponumber()
	{
		$app = JFactory::getApplication();
		$rid = $app->input->get('rid', 0, 'int');
		$value = $app->input->get('value', '', 'string');
		$model = $this->getModel('frontadmin');

		$resp = new stdClass;

		if ($res = $model->updateponumber($rid, $value))
		{
			$resp->status = 1;

			JPluginHelper::importPlugin('redevent');
			$dispatcher = JDispatcher::getInstance();
			$res = $dispatcher->trigger('onAttendeeModified', array($rid));
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
	 * ajax update comments
	 *
	 * @return void
	 */
	public function updatecomments()
	{
		$app = JFactory::getApplication();
		$rid = $app->input->get('rid', 0, 'int');
		$value = $app->input->get('value', '', 'string');
		$model = $this->getModel('frontadmin');

		$resp = new stdClass;

		if ($res = $model->updatecomments($rid, $value))
		{
			$resp->status = 1;

			JPluginHelper::importPlugin('redevent');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('onAttendeeModified', array($rid));
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
	 * ajax update status
	 *
	 * @return void
	 */
	public function updatestatus()
	{
		$app = JFactory::getApplication();
		$rid = $app->input->get('rid', 0, 'int');
		$value = $app->input->get('value', '', 'string');
		$model = $this->getModel('frontadmin');

		$resp = new stdClass;
		$res = $model->updatestatus($rid, $value);

		if ($res !== false)
		{
			$resp->status = 1;
			$resp->html = RedeventHelper::getStatusIcon($res);

			JPluginHelper::importPlugin('redevent');
			$dispatcher = JDispatcher::getInstance();
			$res = $dispatcher->trigger('onAttendeeModified', array($rid));
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
	 * Print person suggestions as json
	 *
	 * @return void
	 */
	public function personsuggestions()
	{
		$response = false;

		$search       = $this->input->get('q', '', 'string');
		$organization = $this->input->getint('org', 0);

		if ($search && $organization)
		{
			$res = RedmemberApi::searchMember($search, $organization);

			// Check the data.
			if (!empty($res))
			{
				$response = array('suggestions' => $res);
			}
		}

		// Send the response.
		echo new JResponseJson($response);
	}
}
