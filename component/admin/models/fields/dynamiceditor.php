<?php
/**
 * @package    FrameworkOnFramework
 * @copyright  Copyright (C) 2010 - 2012 Akeeba Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// Protect from unauthorized access
defined('_JEXEC') or die();

/**
 * extends form Field class for the FOF framework, adding tags notice
 * An editarea field for content creation and formatted HTML display
 *
 * @since       2.0
 */
class JFormFieldDynamiceditor extends JFormFieldEditor
{
	protected function getInput()
	{
		$html = array();
		$html[] = "<div class=\"tagsdiv\">"
		        . JHTML::link('index.php?option=com_redevent&view=tags&tmpl=component', JText::_('COM_REDEVENT_TAGS'), 'class="modal" rel="{handler: \'iframe\'}"')
	            . "</div>";

		$html[] = parent::getInput();

		return implode("\n", $html);
	}
}
