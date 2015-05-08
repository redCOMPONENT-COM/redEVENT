<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevent Component sessions Controller
 *
 * @package  Redevent.admin
 * @since    0.9
 */
class RedeventControllerSessions extends RControllerAdmin
{
	/**
	 * The method => state map.
	 *
	 * @var  array
	 */
	protected $states = array(
		'publish' => 1,
		'unpublish' => 0,
		'archive' => -1
	);

	/**
	 * Method to publish a list of items
	 *
	 * @return  void
	 */
	public function publish()
	{
		parent::publish();

		$cid = JFactory::getApplication()->input->get('cid', array(), 'array');

		if (empty($cid))
		{
			return;
		}

		// Trigger plugins
		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('redevent');

		foreach ($cid as $id)
		{
			$dispatcher->trigger('onAfterSessionSaved', array($id));
		}
	}

	/**
	 * Method to feature a list of items
	 *
	 * @return  void
	 */
	public function feature()
	{
		$cid = JFactory::getApplication()->input->get('cid', array(), 'array');

		if (empty($cid))
		{
			return;
		}

		$model = $this->getModel('sessions');

		if (!$model->featured($cid, 1))
		{
			$this->setMessage($model->getError(), 'error');
		}
		else
		{
			$total = count($cid);
			$msg = JText::sprintf('COM_REDEVENT_SESSIONS_SET_AS_FEATURED', $total);

			$this->setMessage($msg);

			// Trigger plugins
			JPluginHelper::importPlugin('redevent');
			$dispatcher = JDispatcher::getInstance();

			foreach ($cid as $id)
			{
				$dispatcher->trigger('onAfterSessionSaved', array($id));
			}
		}

		// Set redirect
		$this->setRedirect($this->getRedirectToListRoute());
	}

	/**
	 * Method to un-feature a list of items
	 *
	 * @return  void
	 */
	public function unfeature()
	{
		$cid = JFactory::getApplication()->input->get('cid', array(), 'array');

		if (empty($cid))
		{
			return;
		}

		$model = $this->getModel('sessions');

		if (!$model->featured($cid, 0))
		{
			$this->setMessage($model->getError(), 'error');
		}
		else
		{
			$total = count($cid);
			$msg = JText::sprintf('COM_REDEVENT_SESSIONS_SET_AS_NOT_FEATURED', $total);

			$this->setMessage($msg);

			// Trigger plugins
			JPluginHelper::importPlugin('redevent');
			$dispatcher = JDispatcher::getInstance();

			foreach ($cid as $id)
			{
				$dispatcher->trigger('onAfterSessionSaved', array($id));
			}
		}

		// Set redirect
		$this->setRedirect($this->getRedirectToListRoute());
	}

	/**
	 * trigger save event for plugins
	 *
	 * @return void
	 */
	public function triggersave()
	{
		// Get items to publish from the request.
		$cid = JFactory::getApplication()->input->get('cid', array(), 'array');

		JPluginHelper::importPlugin('content');
		$dispatcher = JEventDispatcher::getInstance();

		foreach ($cid as $id)
		{
			$table = RTable::getAdminInstance('Session');
			$table->load($id);
			$dispatcher->trigger('onContentAfterSave', array('com_redevent.session', $table, false));
		}

		// Set redirect
		$this->setRedirect($this->getRedirectToListRoute());
	}
}
