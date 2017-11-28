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
class RedeventControllerSession extends RedeventControllerForm
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *                          Recognized key values include 'name', 'default_task', 'model_path', and
	 *                          'view_path' (this list is not meant to be comprehensive).
	 *
	 * @throws  Exception
	 */
	public function __construct(array $config)
	{
		parent::__construct($config);

		$this->registerTask('saveAndTwit', 'save');
	}

	/**
	 * Function that allows child controller access to model data
	 * after the data has been saved.
	 *
	 * @param   JModelLegacy  $model      The data model object.
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

		// Check if people need to be moved on or off the waitinglist

		$model_wait = $this->getModel('waitinglist');
		$model_wait->setXrefId($sessionId);
		$model_wait->updateWaitingList();

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

	/**
	 * Gets the URL arguments to append to an item redirect.
	 *
	 * @param   integer  $recordId  The primary key id for the item.
	 * @param   string   $urlVar    The name of the URL variable for the id.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 */
	protected function getRedirectToItemAppend($recordId = null, $urlVar = 'id')
	{
		$append = parent::getRedirectToItemAppend($recordId, $urlVar);

		$jform = $this->input->get('jform', '', 'array');

		if (isset($jform['eventid']))
		{
			$append .= '&jform[eventid]=' . $jform['eventid'];
		}

		return $append;
	}

	/**
	 * Gets the URL arguments to append to a list redirect.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 */
	protected function getRedirectToListAppend()
	{
		$append = parent::getRedirectToListAppend();

		$jform = $this->input->get('jform', '', 'array');

		if (isset($jform['eventid']))
		{
			$append .= '&jform[eventid]=' . $jform['eventid'];
		}

		return $append;
	}

	/**
	 * Get the JRoute object for a redirect to list.
	 *
	 * @param   string  $append  An optionnal string to append to the route
	 *
	 * @return  JRoute  The JRoute object
	 */
	protected function getRedirectToListRoute($append = null)
	{
		$returnUrl = $this->input->get('return');

		if ($returnUrl)
		{
			$returnUrl = base64_decode($returnUrl);

			return JRoute::_($returnUrl . $append, false);
		}
		else
		{
			return JRoute::_('index.php?option=' . $this->option . '&view=sessions' . $append, false);
		}
	}
}
