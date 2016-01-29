<?php
/**
 * @package     Redcomponent.redeventsync
 * @subpackage  com_redeventsync
 * @copyright   Copyright (C) 2013 redCOMPONENT.com
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die();

/**
 * RedEVENT sync base exception
 *
 * @package  RED.redeventsync
 * @since    2.5
 */
class ResyncException extends Exception
{
	public $status;

	public $debug;

	/**
	 * Constructor
	 *
	 * @param   int     $message  message
	 * @param   string  $status   sync status
	 * @param   string  $debug    error info
	 */
	public function __construct($message, $status = 'error', $debug = null)
	{
		$this->status = $status;
		$this->debug = $debug;

		// Make sure everything is assigned properly
		parent::__construct($message);
	}
}
