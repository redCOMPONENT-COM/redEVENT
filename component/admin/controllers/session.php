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
	 * Function that allows child controller access to model data
	 * after the data has been saved.
	 *
	 * @param   JModelLegacy  &$model     The data model object.
	 * @param   array         $validData  The validated data.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	protected function postSaveHook(JModelLegacy &$model, $validData = array())
	{
		parent::postSaveHook($model, $validData);

		$input = JFactory::getApplication()->input;
		$sessionId = $model->getState($this->context . '.id');

		if (!$sessionId)
		{
			return;
		}

		/* Check if people need to be moved on or off the waitinglist */
		$model_wait = $this->getModel('waitinglist');
		$model_wait->setXrefId($sessionId);
		$model_wait->UpdateWaitingList();

		if ($input->get('task') == 'saveAndTwit')
		{
			JPluginHelper::importPlugin('system', 'autotweetredevent');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('onAfterRedeventSessionSave', array($sessionId));
		}

		$isNew = isset($validData['id']) && $validData['id'] ? false : true;

		// Trigger event
		JPluginHelper::importPlugin('redevent');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onAfterSessionSave', array($sessionId, $isNew));
	}
}
