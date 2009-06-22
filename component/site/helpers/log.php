<?php
/**
 * @version 1.0 $Id: helper.php 270 2009-06-17 12:37:35Z julien $
 * @package Joomla
 * @subpackage redEVENT
 * @copyright redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license GNU/GPL, see LICENSE.php
 * redEVENT is based on EventList made by Christoph Lukes from schlu.net
 * redEVENT can be downloaded from www.redcomponent.com
 * redEVENT is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * redEVENT is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with redEVENT; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Helper class for logging
 * @package    Notes
 * @subpackage com_notes
 */
class RedeventHelperLog
{
    /**
     * Simple log
     * @param string $comment  The comment to log
     * @param int $userId      An optional user ID
     */
    function simpleLog($comment, $userId = 0)
    {
        // Include the library dependancies
        jimport('joomla.error.log');
        $options = array(
            'format' => "{DATE}\t{TIME}\t{USER_ID}\t{COMMENT}"
        );
        // Create the instance of the log file in case we use it later
        $log = &JLog::getInstance('com_redevent.log', $options);
        $log->addEntry(array('comment' => $comment, 'user_id' => $userId));
    }
}
?>