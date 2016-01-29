<?php
/**
 * @package     Redeventsync
 * @subpackage  Admin
 * @copyright   Copyright (C) 2013 - 2015 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die();

/**
 * controller for Queued message
 *
 * @package     Redeventsync
 * @subpackage  Admin
 * @since       3.0
 */
class RedeventsyncControllerQueuedmessages extends RControllerAdmin
{
	/**
	 * Send selected item(s)
	 *
	 * @return  void
	 */
	public function process()
	{
		// Check for request forgeries
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

		// Get items to publish from the request.
		$cid = JFactory::getApplication()->input->get('cid', array(), 'array');

		if (empty($cid))
		{
			JLog::add(JText::_($this->text_prefix . '_NO_ITEM_SELECTED'), JLog::WARNING, 'jerror');
		}
		else
		{
			// Make sure the item ids are integers
			JArrayHelper::toInteger($cid);
			$model = $this->getModel();

			if (!$model->process($cid))
			{
				$this->setMessage($model->getError(), 'error');
			}
			else
			{
				$this->setMessage(JText::_('COM_REDEVENTSYNC_MESSAGES_DEQUEUED'));
			}
		}

		$this->setRedirect($this->getRedirectTolistRoute());
	}
}
