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
}
