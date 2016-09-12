<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevent Component bundle Controller
 *
 * @package  Redevent.admin
 * @since    3.2.0
 */
class RedeventControllerBundle extends RControllerForm
{
	/**
	 * Function that allows child controller access to model data
	 * after the data has been saved.
	 *
	 * @param   JModelLegacy  $model      The data model object.
	 * @param   array         $validData  The validated data.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	protected function postSaveHook(JModelLegacy $model, $validData = array())
	{
		$recordId = $model->getState($this->context . '.id');

		$event_ids = $this->input->get('event_id', array(), 'array');
		JArrayHelper::toInteger($event_ids);

		$session_ids = $this->input->get('session_id', array(), 'array');
		JArrayHelper::toInteger($session_ids);

		if (!$event_ids)
		{
			return;
		}

		$events = array();

		foreach ($event_ids as $k => $event_id)
		{
			if (!$event_id)
			{
				continue;
			}

			if (!isset($events[$event_id]))
			{
				$obj = new stdclass;
				$obj->id = $event_id;
				$obj->sessions = array();
				$events[$event_id] = $obj;
			}

			if ($session_ids[$k])
			{
				$events[$event_id]->sessions[] = $session_ids[$k];
			}
		}

		try
		{
			$model->saveEvents($recordId, $events);
		}
		catch (Exception $e)
		{
			$this->setMessage(JText::_('COM_REDEVENT_BUNDLE_ERROR_SAVING_EVENTS'), 'notice');
		}

		exit('done');
	}
}
