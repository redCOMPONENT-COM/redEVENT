<?php
/**
 * @version     2.5
 * @package     Joomla
 * @subpackage  redEVENT
 * @copyright   redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license     GNU/GPL, see LICENSE.php
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

/**
 * redEVENT Component b2b Controller
 *
 * @package     Joomla
 * @subpackage  redEVENT
 * @since       2.5
 */
class RedeventControllerFrontadmin extends FOFController
{
	/**
	 * return sessions html table
	 *
	 * @return void
	 */
	public function searchsessions()
	{
		$app = JFactory::getApplication();

		$this->viewName  = 'frontadmin';
		$this->modelName = 'frontadmin';
		$this->layout    = 'searchsessions';

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

		$model = $this->getModel('Frontadmin', 'RedeventModel');
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

		$model = $this->getModel('Frontadmin', 'RedeventModel');
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

		$model = $this->getModel('Frontadmin', 'RedeventModel');
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

		$model = $this->getModel('Frontadmin', 'RedeventModel');
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

		$model = $this->getModel('Frontadmin', 'RedeventModel');
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
		$model = $this->getModel('eventhelper');
		$model->setXref($app->input->get('id'));
		$data  = $model->getData();

		echo json_encode($data);

		$app->close();
	}

	public function getattendees()
	{
		$app = JFactory::getApplication();

		$this->viewName  = 'frontadmin';
		$this->modelName = 'frontadmin';
		$this->layout    = 'attendees';

		$model = $this->getModel('FrontadminMembers');

		$att = $model->getAttendees($app->input->get('xref', 0, 'int'), $app->input->get('org', 0, 'int'), $app->input->get('filter_person', '', 'string'));

		$view = $this->getThisView();
		$view->assignRef('attendees', $att);
		$view->setModel($model, false);
		$this->display();

		JFactory::getApplication()->close();
	}

	public function editmember()
	{
		$app = JFactory::getApplication();

		$this->viewName  = 'frontadmin';
		$this->modelName = 'frontadmin';
		$this->layout    = 'editmember';

		$model = $this->getModel('frontadmin');

		$this->display();

		if (!$this->input->get('modal'))
		{
			JFactory::getApplication()->close();
		}
	}

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
			$model = $this->getModel('frontadmin');
			$added = 0;

			foreach ($regs as $user_id)
			{
				$attendee = $model->quickbook($user_id, $xref);
				$regresp = new stdclass;

				if ($attendee)
				{
					$regresp->status = 1;
					$regresp->details = $attendee;
					$resp->submit_key = $attendee->submit_key;
					$added++;

					JPluginHelper::importPlugin( 'redevent' );
					$dispatcher =& JDispatcher::getInstance();
					$res = $dispatcher->trigger('onAttendeeCreated', array($attendee->id));
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

		$resp = new stdclass();

		if (!$RedeventUserAcl->canPublishXref($xref)) {
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
		$rid = $app->input->get('rid');
		$model = $this->getModel('registration');

		$resp = new stdClass();

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

		$cancelled = new RedeventAttendee($rid);
		$cancelled->notifyManagers(true);

		JFactory::getApplication()->close();
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
	 * ajax update user
	 *
	 * @return void
	 */
	public function update_user()
	{
		require_once JPATH_SITE . '/components/com_redmember/lib/redmemberlib.php';
		$app = JFactory::getApplication();

		$resp = new stdClass;

		try
		{
			$user = RedmemberLib::saveUser(true, null, false, array('assign_organization' => $app->input->getInt('assign_org')));
			$resp->status = 1;
		}
		catch (Exception $e)
		{
			$resp->status = 0;
			$resp->error  = JText::_('COM_USERS_USER_SAVE_FAILED') . ': ' . $e->getMessage();
		}

		if ($this->input->get('format') == 'json')
		{
			echo json_encode($resp);
			$app->close();
		}
		else
		{
			if ($resp->status)
			{
				$app->input->set('uid', $user->get('id'));
				$app->enqueueMessage(Jtext::_('COM_REDEVENT_FRONTEND_ADMIN_MEMBER_SAVED'));
			}
			else
			{
				$app->enqueueMessage($resp->error, 'error');
			}

			$this->editmember();
		}
	}

	/**
	 * Print person suggestions as json
	 *
	 * @return void
	 */
	public function personsuggestions()
	{
		require_once JPATH_SITE . '/components/com_redmember/lib/redmemberlib.php';

		$return = array();

		$search       = $this->input->get('q', '', 'string');
		$organization = $this->input->getint('org', 0);

		if ($search && $organization)
		{
			$res = RedmemberLib::searchMember($search, $organization);

			// Check the data.
			if (empty($res))
			{
				$return = array();
			}
			else
			{
				foreach ($res as $member)
				{
					$return[] = $member->name;
				}
			}
		}

		// Use the correct json mime-type
		header('Content-Type: application/json');

		// Send the response.
		echo json_encode($return);
		JFactory::getApplication()->close();
	}
}
