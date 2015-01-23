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
 * redEVENT Component Myevents Controller
 *
 * @package     Joomla
 * @subpackage  redEVENT
 * @since       2.5
 */
class RedeventControllerMyevents extends JControllerLegacy
{
	/**
	 * Class constructor, overridden in descendant classes.
	 *
	 * @param   mixed  $properties  Either and associative array or another
	 *                              object to set the initial properties of the object.
	 *
	 * @since   11.1
	 */
	public function __construct($properties = array())
	{
		parent::__construct($properties);
		$this->registerDefaultTask('myevents');
	}
	/**
	 * return sessions html table
	 *
	 * @return void
	 */
	public function myevents()
	{
		$this->viewName  = 'myevents';
		$this->modelName = 'myevents';
		$this->layout    = 'default';

		$this->display();
	}

	/**
	 * return sessions html table
	 *
	 * @return void
	 */
	public function managedevents()
	{
		$this->viewName  = 'myevents';
		$this->modelName = 'myevents';
		$this->layout    = 'managedevents';

		$this->display();
	}

	/**
	 * return venues html table
	 *
	 * @return void
	 */
	public function managedvenues()
	{
		$this->viewName  = 'myevents';
		$this->modelName = 'myevents';
		$this->layout    = 'managedvenues';

		$this->display();
	}

	/**
	 * return attending html table
	 *
	 * @return void
	 */
	public function attending()
	{
		$this->viewName  = 'myevents';
		$this->modelName = 'myevents';
		$this->layout    = 'attending';

		$this->display();
	}

	/**
	 * return attended html table
	 *
	 * @return void
	 */
	public function attended()
	{
		$this->viewName  = 'myevents';
		$this->modelName = 'myevents';
		$this->layout    = 'attended';

		$this->display();
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

		$useracl = RedeventUserAcl::getInstance();

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
}
