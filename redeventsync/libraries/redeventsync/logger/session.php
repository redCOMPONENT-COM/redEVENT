<?php
/**
 * @package     Redcomponent.redeventsync
 * @subpackage  com_redeventsync
 * @copyright   Copyright (C) 2013 redCOMPONENT.com
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die();

/**
 * RedEVENT sync logger session
 *
 * @package  RED.redeventsync
 * @since    2.5
 */
class ResyncLoggerSession
{
	/**
	 * create a new log
	 *
	 * @return void
	 */
	public function init()
	{
		$session = JFactory::getSession();
		$session->set('redeventsynclog', array());
	}

	/**
	 * Write to the log
	 *
	 * @param   string  $data  string to append to the log
	 *
	 * @return void
	 */
	public function write($data)
	{
		$session = JFactory::getSession();
		$log = $session->get('redeventsynclog');
		$log[] = $data;
		$session->set('redeventsynclog', $log);
	}

	/**
	 * Read from log, and empty it
	 *
	 * @return array log lines
	 */
	public function read()
	{
		$session = JFactory::getSession();
		$log = $session->get('redeventsynclog');
		$session->set('redeventsynclog', array());

		return $log;
	}
}
