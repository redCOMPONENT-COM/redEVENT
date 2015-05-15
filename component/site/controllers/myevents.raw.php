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
		$this->input->set('layout', 'default');
		$this->display();
	}

	/**
	 * return sessions html table
	 *
	 * @return void
	 */
	public function managedevents()
	{
		$this->input->set('layout', 'managedevents');
		$this->display();
	}

	/**
	 * return venues html table
	 *
	 * @return void
	 */
	public function managedvenues()
	{
		$this->input->set('layout', 'managedvenues');
		$this->display();
	}

	/**
	 * return attending html table
	 *
	 * @return void
	 */
	public function attending()
	{
		$this->input->set('layout', 'attending');
		$this->display();
	}

	/**
	 * return attended html table
	 *
	 * @return void
	 */
	public function attended()
	{
		$this->input->set('layout', 'attended');
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

		$resp = new stdclass;

		if (!$useracl->canPublishXref($xref))
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
}
