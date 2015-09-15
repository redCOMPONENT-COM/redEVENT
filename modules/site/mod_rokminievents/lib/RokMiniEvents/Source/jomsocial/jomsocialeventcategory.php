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
class JElementJomSocialEventCategory extends JElement
{
	var	$_name = 'JomSocialEventCategory';

	function fetchElement($name, $value, &$node, $control_name)
	{

        require_once( JPATH_ROOT . '/components/com_community/libraries/core.php' );
        CFactory::load( 'helpers' , 'event' );
        $model		= CFactory::getModel( 'events' );

        $categories = $model->getCategories();

		$options = array();
        $options[] = JHTML::_('select.option', 0, '--');
		foreach ($categories as $option)
		{
			$options[] = JHTML::_('select.option', $option->id, $option->name);
		}
        return JHTML::_('select.genericlist',  $options, ''.$control_name.'['.$name.']', 'class="inputbox"', 'value', 'text', $value, $control_name.$name );
	}
}
