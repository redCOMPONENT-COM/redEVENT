<?php
/**
 * @version		1.6 October 6, 2011
 * @author		RocketTheme http://www.rockettheme.com
 * @copyright 	Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 */

defined('_JEXEC') or die();

/**
 * @package     gantry
 * @subpackage  admin.elements
 */
class JElementDateFormats extends JElement
{
	var	$_name = 'DateFormats';

	function fetchElement($name, $value, &$node, $control_name)
	{
		$class = ( $node->attributes('class') ? 'class="'.$node->attributes('class').'"' : 'class="inputbox"' );

		$options = array();
		$dates = $node->children();

	    $now = JFactory::getDate();

        $user = JFactory::getUser();
        $now->setOffset($user->getParam('timezone',0));

		foreach ($dates as $option)
		{
			$val = $option->attributes('value');
			$option->_data = $now->toFormat($val);
			$options[] = JHTML::_('select.option', $val, $option->data());
		}
        return JHTML::_('select.genericlist',  $options, ''.$control_name.'['.$name.']', 'class="inputbox"', 'value', 'text', $value, $control_name.$name );
	}
}
