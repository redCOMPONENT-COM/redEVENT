<?php
/**
 * Redeventsync test message log stub
 *
 * @package    Redevent.UnitTest
 * @copyright  Copyright (C) 2013 redCOMPONENT.com
 * @license    GNU General Public License version 2 or later
 */

/**
 * Class ResyncHelperMessagelogStub
 *
 * @package  Redevent.UnitTest
 * @since    2.5
 */
class ResyncHelperMessagelogStub
{
	/**
	 * log stub
	 *
	 * @param   string  $direction      direction
	 * @param   string  $type           type
	 * @param   string  $transactionid  tid
	 * @param   string  $message        msg
	 * @param   string  $status         status
	 * @param   bool    $debug          dbg
	 *
	 * @return bool
	 */
	public function log($direction, $type, $transactionid, $message, $status, $debug = null)
	{
		return true;
	}
}