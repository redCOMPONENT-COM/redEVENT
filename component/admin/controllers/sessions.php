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
	 * Copy item(s)
	 *
	 * @return  void
	 */
	public function copy()
	{
		// Check for request forgeries
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

		// Get items to remove from the request.
		$cid = JFactory::getApplication()->input->get('cid', array(), 'array');

		// Get the model.
		$model = $this->getModel();

		if (!is_array($cid) || count($cid) < 1)
		{
			JLog::add(JText::_($this->text_prefix . '_NO_ITEM_SELECTED'), JLog::WARNING, 'jerror');
		}
		else
		{
			// Make sure the item ids are integers
			jimport('joomla.utilities.arrayhelper');
			JArrayHelper::toInteger($cid);

			// Remove the items.
			if ($model->copy($cid))
			{
				$this->setMessage(JText::plural($this->text_prefix . '_N_ITEMS_COPIED', count($cid)));
			}
			else
			{
				$this->setMessage($model->getError(), 'error');
			}
		}

		// Set redirect
		$this->setRedirect($this->getRedirectToListRoute());
	}

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
}
