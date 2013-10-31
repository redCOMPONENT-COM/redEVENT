<?php
/**
 * @package		redcomponent.redeventsync
 * @subpackage	com_redeventsync
 * @copyright	Copyright (C) 2013 redCOMPONENT.com
 * @license		GNU General Public License version 2 or later
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

if (!defined('REDEVENTSYNC_LOG_DIRECTION_INCOMING'))
{
	define('REDEVENTSYNC_LOG_DIRECTION_INCOMING', 0);
}
if (!defined('REDEVENTSYNC_LOG_DIRECTION_OUTGOING'))
{
	define('REDEVENTSYNC_LOG_DIRECTION_OUTGOING', 1);
}
