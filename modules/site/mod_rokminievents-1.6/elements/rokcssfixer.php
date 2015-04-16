<?php
/**
 * @version		1.6 October 6, 2011
 * @author		RocketTheme http://www.rockettheme.com
 * @copyright 	Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 */

// no direct access
defined('_JEXEC') or die();

class JElementRokCSSFixer extends JElement {
	
	function fetchElement($name, $value, &$node, $control_name){
		
		if (defined('ROKCSSFIXER')) return;
		
		$document 	=& JFactory::getDocument();
		
		$document->addStyleSheet(JURI::Root(true)."/modules/mod_rokminievents/admin/rokminievents-admin.css");
		
		define('ROKCSSFIXER', 1);
	}
}

?>