<?php
/**
 * @package    Redevent.Library
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Html helper for signup
 *
 * @package  Redevent.Library
 * @since    3.0
 */
class RedeventHtmlSessions
{
	/**
	 * returns toggle image link for session feature
	 *
	 * @param   object  $row      item data
	 * @param   int     $i        row number
	 * @param   bool    $allowed  is user allowed to perform action
	 *
	 * @return string html
	 */
	public static function featured($row, $i, $allowed)
	{
		$states = array(
			1 => array('unfeature', 'COM_REDEVENT_FEATURED', 'COM_REDEVENT_SESSION_UNFEATURE_SESSION', '', false, 'star', 'star'),
			0 => array('feature', '', 'COM_REDEVENT_SESSION_FEATURE_SESSION', '', false, 'star-empty', 'star-empty'),
		);

		return JHtml::_('rgrid.state', $states, $row->featured, $i, 'sessions.', $allowed, true);
	}

	/**
	 * returns toggle image link for session publish state
	 *
	 * @param   object  $row      item data
	 * @param   int     $i        row number
	 * @param   bool    $allowed  is user allowed to perform action
	 *
	 * @return string html
	 */
	public static function published($row, $i, $allowed)
	{
		$states = array(1 => array('unpublish', 'JPUBLISHED', 'JLIB_HTML_UNPUBLISH_ITEM', 'JPUBLISHED', false, 'ok-sign icon-green', 'ok-sign icon-green'),
			0 => array('publish', 'JUNPUBLISHED', 'JLIB_HTML_PUBLISH_ITEM', 'JUNPUBLISHED', false, 'remove icon-red', 'remove icon-red'),
			-1 => array('unpublish', 'JARCHIVED', 'JLIB_HTML_UNPUBLISH_ITEM', 'JARCHIVED', false, 'hdd', 'hdd'),
		);

		return JHtml::_('rgrid.state', $states, $row->published, $i, 'sessions.', $allowed, true);
	}
}
