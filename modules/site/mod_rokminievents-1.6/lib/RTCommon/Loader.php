<?php
/**
 * @version   1.6 October 6, 2011
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

defined('RTCOMMON') or die('Restricted access');

interface RTCommon_Loader {
    const FILE_EXTENSION = '.php';

    /**
     * @abstract
     * @param  string $className the class name to look for and load
     * @return bool True if the class was found and loaded.
     */
    function loadClass($className);
}
