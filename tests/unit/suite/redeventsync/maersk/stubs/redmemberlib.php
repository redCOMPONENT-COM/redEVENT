<?php
/**
 * maersk sync plugin test
 *
 * @package    Redeventsync.UnitTest
 * @copyright  Copyright (C) 2013 redCOMPONENT.com
 * @license    GNU General Public License version 2 or later
 */

class RedmemberLib
{
	/**
	 * Store user data
	 *
	 * @param   boolean  $use_request      get data from request
	 * @param   array    $data             data to use if not getting from request, or to override request field(s). Must be indexed by field
	 * @param   boolean  $send_activation  send activation email
	 * @param   array    $options          options
	 *
	 * @throws Exception
	 * @return object created user
	 */
	public static function saveUser($use_request = true, $data = null, $send_activation = false, $options = null)
	{
		print_r($data);
		return true;
	}
}
