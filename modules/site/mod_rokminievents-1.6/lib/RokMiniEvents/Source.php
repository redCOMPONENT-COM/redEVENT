<?php
/**
 * @version   1.6 October 6, 2011
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

defined('ROKMINIEVENTS') or die('Restricted access');

interface RokMiniEvents_Source {
    function getEvents(&$params);

    /**
     * Checks to see if the source is available to be used
     * @abstract
     * @return bool
     */
    function available();
}
