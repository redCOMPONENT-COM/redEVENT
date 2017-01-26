<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevent Component Event templates Controller
 *
 * @package  Redevent.admin
 * @since    3.1
 */
class RedeventControllerEventtemplates extends RControllerAdmin
{
	/**
	 * Batch process event templates
	 *
	 * @return void
	 */
	public function batch()
	{
		// Check for request forgeries
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

		// Get items to publish from the request.
		$cid = $this->input->get('cid', array(), 'array');
		$target = $this->input->getInt('merge_target');

		if (empty($cid))
		{
			$this->setMessage(JText::_($this->text_prefix . '_NO_ITEM_SELECTED'), 'error');
		}
		elseif (!$target)
		{
			$this->setMessage(JText::_('COM_REDEVENT_MERGE_TEMPLATES_ERROR_MISSNG_TARGET'), 'error');
		}
		else
		{
			// Get the model.
			$model = $this->getModel('eventtemplates');

			// Make sure the item ids are integers
			JArrayHelper::toInteger($cid);

			try
			{
				$model->mergeTemplates($cid, $target);
				$this->setMessage(JText::_('COM_REDEVENT_MERGE_TEMPLATES_SUCCESS'));
			}
			catch (Exception $e)
			{
				$this->setMessage($e->getMessage(), 'error');
			}
		}

		// Set redirect
		$this->setRedirect($this->getRedirectToListRoute());
	}

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
}
