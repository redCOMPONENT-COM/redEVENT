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
 * RedEVENT sync helper for message logging
 *
 * @package  RED.redeventsync
 * @since    2.5
 */
class ResyncHelperMessagelog
{
	/**
	 * log transaction
	 *
	 * @param   int     $direction      up or down
	 * @param   string  $type           message type
	 * @param   string  $transactionid  transaction id
	 * @param   string  $message        xml message
	 * @param   string  $status         status
	 * @param   string  $debug          debug info
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public static function log($direction, $type, $transactionid, $message, $status, $debug = null)
	{
		static $tz;

		if (!$tz)
		{
			$config = JFactory::getConfig();
			$tz = new DateTimeZone($config->get('offset'));
		}

		$log = FOFTable::getAnInstance('logs', 'RedeventsyncTable');
		$log->direction = $direction;
		$log->transactionid = $transactionid;
		$log->type = $type;
		$log->message = $message;
		$log->status = $status;
		$log->debug = $debug;
		$date = new JDate('now', $tz);
		$log->date = $date->toSql(true);

		if (!$log->store())
		{
			throw new Exception($log->getError());
		}
	}
}
