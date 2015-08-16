<?php
/**
 * @version   1.6 October 6, 2011
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

defined('ROKMINIEVENTS') or die('Restricted access');

abstract class RokMiniEvents_SourceBase implements RokMiniEvents_Source {
	protected static function getTime($params, $date){
		$display = $params->get('timedisplay', 24);

		if ($display == '24') return date('H:i', $date);
		else return date('h:iA', $date);
	}
}
