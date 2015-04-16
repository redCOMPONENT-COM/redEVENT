<?php
/**
 * @package		redcomponent.redeventsync
 * @subpackage	com_redeventsync
 * @copyright	Copyright (C) 2013 redCOMPONENT.com
 * @license		GNU General Public License version 2 or later
 */

defined('_JEXEC') or die();

/**
 *controller for cpanel
 *
 * @package  RED.redeventsync
 * @since    2.5
 *
 */
class RedeventsyncControllerCpanel extends FOFController
{
	/**
	 * (non-PHPdoc)
	 * @see FOFController::execute()
	 */
	public function execute($task)
	{
		parent::execute('browse');
	}
}
