<?php
/**
 * @package     Redcomponent.redeventsync
 * @subpackage  com_redeventsync
 * @copyright   Copyright (C) 2013 redCOMPONENT.com
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die();

require_once JPATH_ADMINISTRATOR . '/components/com_redeventsync/defines.php';

/**
 * RedEVENT sync helper for message queuing
 *
 * @package  RED.redeventsync
 * @since    2.5
 */
class ResyncHelperQueue
{
	/**
	 * Add message to queue message
	 *
	 * @param   string  $message  message to enqueue
	 * @param   string  $plugin   plugin that should send the message
	 *
	 * @return boolean true on success
	 *
	 * @throws Exception
	 */
	public static function add($message, $plugin)
	{
		$log = FOFTable::getAnInstance('Queuedmessages', 'RedeventsyncTable');
		$log->queued = JFactory::getDate()->toSql(true);
		$log->plugin = $plugin;
		$log->message = $message;

		if (!$log->store())
		{
			throw new Exception($log->getError());
		}

		return true;
	}
}
