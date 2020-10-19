<?php
/**
 * @package    Redevent.Library
 * @copyright  Copyright (C) 2008 - 2020 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Helper class for logging
 *
 * @package  Redevent.Library
 * @since    2.5
 */
class RedeventHelperLog
{
	/**
	 * Simple log
	 *
	 * @param   string  $comment  The comment to log
	 * @param   int     $userId   An optional user ID
	 *
	 * @return void
	 */
	public static function simpleLog($comment, $userId = 0)
	{
		JLog::addLogger(
			array('text_file' => 'com_redevent.log'),
			JLog::DEBUG,
			'com_redevent'
		);
		JLog::add($comment, JLog::DEBUG, 'com_redevent');
	}

	/**
	 * Clear logs
	 *
	 * @return boolean
	 */
	public static function clear()
	{
		$app = JFactory::getApplication();

		$file = $app->getCfg('log_path') . '/com_redevent.log';

		if (file_exists($file))
		{
			unlink($file);
		}

		return true;
	}
}
