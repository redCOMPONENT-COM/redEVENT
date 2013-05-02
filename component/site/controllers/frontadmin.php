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

		$model = $this->getModel('frontadmin');

		$att = $model->getAttendees($app->input->get('xref', 0, 'int'), $app->input->get('org', 0, 'int'), $app->input->get('filter_person', '', 'string'));

		$view = $this->getThisView();
		$view->assignRef('attendees', $att);
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

		JFactory::getApplication()->close();
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

	public function quickbook()
	{
		$app = JFactory::getApplication();

		$xref = $app->input->get('xref', 0, 'int');
		$regs = $app->input->get('reg', array(), 'array');
		JArrayHelper::toInteger($regs);

		$resp = new stdclass;
		$resp->status = 1;
		$resp->regs = array();

		$acl = UserAcl::getInstance();

		if (!$acl->canManageAttendees($xref))
		{
			$resp->status = 0;
			$resp->error = JText::_('COM_REDEVENT_USER_ACTION_NOT_ALLOWED');
		}
		else
		{
			$model = $this->getModel('frontadmin');

			foreach ($regs as $user_id)
			{
				$res = $model->quickbook($user_id, $xref);
				$regresp = new stdclass;

				if ($res)
				{
					$regresp->status = 1;
				}
				else
				{
					$resp->status = 0;
					$regresp->status = 0;
					$regresp->error = $model->getError();
				}

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

		$useracl = UserAcl::getInstance();

		$resp = new stdclass();

		if (!$useracl->canPublishXref($xref)) {
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

		$resp = new stdClass();

		if ($res = $model->updateponumber($rid, $value))
		{
			$resp->status = 1;
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

		$resp = new stdClass();

		if ($res = $model->updatecomments($rid, $value))
		{
			$resp->status = 1;
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

		$resp = new stdClass();
		$res = $model->updatestatus($rid, $value);

		if ($res !== false)
		{
			$resp->status = 1;
			$resp->html = redEVENTHelper::getStatusIcon($res);
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
		$app      = JFactory::getApplication();
		$id       = $app->input->get('id', 0, 'int');
		$username = $app->input->get('username', '', 'string');
		$name     = $app->input->get('name', '', 'string');
		$email    = $app->input->get('email', '', 'string');

		$user = JFactory::getUser($id);

		$user->username = $username;
		$user->name  = $name;
		$user->email = $email;

		$resp = new stdclass;

		if($user->save())
		{
			$resp->status = 1; //echo JText::_('COM_USERS_USER_SAVE_SUCCESS');
		}
		else
		{
			$resp->status = 0;
			$resp->error  = JText::_('COM_USERS_USER_SAVE_FAILED');
		}

		echo json_encode($resp);
		JFactory::getApplication()->close();
	}
}
