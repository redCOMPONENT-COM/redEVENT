<?php
/**
 * @package     Redeventsync
 * @subpackage  Admin
 * @copyright   Copyright (C) 2013 - 2015 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die();

/**
 * controller for log
 *
 * @package     Redeventsync
 * @subpackage  Admin
 * @since       2.5
 */
class RedeventsyncControllerLog extends RControllerForm
{
	/**
	 * Clear logs
	 *
	 * @return void
	 */
	public function clear()
	{
		$model = $this->getModel('Logs');

		try
		{
			$model->clear();
			$message = JText::_('COM_REDEVENTSYNC_TRANSACTIONS_LOG_CLEARED');
			$messageType = 'message';
		}
		catch (Exception $e)
		{
			$message = $e->getMessage();
			$messageType = 'error';
		}

		$this->setRedirect('index.php?option=com_redeventsync&view=logs', $message, $messageType);
	}
}
