<?php
/**
 * @package    Redevent.Library
 *
 * @copyright  Copyright (C) 2009 - 2014 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Class RedeventError
 *
 * @package  Redevent.Library
 * @since    2.5
 */
class RedeventError
{
	/**
	 * Throw an exception after logging
	 *
	 * @param   int     $code  error code
	 * @param   string  $msg   message
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public static function raiseError($code, $msg)
	{
		RedeventHelperLog::simplelog("Error $code: $msg");

		throw new Exception($msg, $code);
	}

	/**
	 * display notice after logging
	 *
	 * @param   int     $code  error code
	 * @param   string  $msg   message
	 *
	 * @return void
	 */
	public static function raiseNotice($code, $msg)
	{
		RedeventHelperLog::simplelog("Notice $code: $msg");

		JFactory::getApplication()->enqueueMessage($msg, 'notice');
	}

	/**
	 * display warning after logging
	 *
	 * @param   int     $code  error code
	 * @param   string  $msg   message
	 *
	 * @return void
	 */
	public static function raiseWarning($code, $msg)
	{
		RedeventHelperLog::simplelog("Warning $code: $msg");

		JFactory::getApplication()->enqueueMessage($msg, 'warning');
	}
}
