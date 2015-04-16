<?php
/**
 * @package    Redevent.Library
 *
 * @copyright  Copyright (C) 2009 - 2014 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

class RedeventError extends JError
{
	public static function raiseError($code, $msg, $info = null)
	{
		RedeventHelperLog::simplelog("Error $code: $msg");

		return parent::raiseError($code, $msg, $info = null);
	}

	public static function raiseNotice($code, $msg, $info = null)
	{
		RedeventHelperLog::simplelog("Notice $code: $msg");

		return parent::raiseNotice($code, $msg, $info = null);
	}

	public static function raiseWarning($code, $msg, $info = null)
	{
		RedeventHelperLog::simplelog("Notice $code: $msg");

		return parent::raiseWarning($code, $msg, $info = null);
	}
}
