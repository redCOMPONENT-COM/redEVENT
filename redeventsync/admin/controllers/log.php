<?php
/**
 * @package     Redcomponent.redeventsync
 * @subpackage  com_redeventsync
 * @copyright   Copyright (C) 2013 redCOMPONENT.com
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die();

/**
 * controller for log
 *
 * @package  RED.redeventsync
 * @since    2.5
 */
class RedeventsyncControllerLog extends FOFController
{
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
