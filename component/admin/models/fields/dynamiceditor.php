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
class FOFFormFieldDynamiceditor extends FOFFormFieldEditor
{

	protected $static;
	protected $repeatable;

	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string  $name  The property name for which to the the value.
	 *
	 * @return  mixed  The property value or null.
	 *
	 * @since   2.0
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'static':
				if (empty($this->static))
				{
					$this->static = $this->getStatic();
				}

				return $this->static;
				break;

			case 'repeatable':
				if (empty($this->repeatable))
				{
					$this->repeatable = $this->getRepeatable();
				}

				return $this->static;
				break;

			default:
				return parent::__get($name);
		}
	}

	/**
	 * Get the rendering of this field type for static display, e.g. in a single
	 * item view (typically a "read" task).
	 *
	 * @since 2.0
	 */
	public function getStatic()
	{
		$class = $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';

		return '<div id="' . $this->id . '" ' . $class . '>' . $this->value . '</div>';
	}

	/**
	 * Get the rendering of this field type for a repeatable (grid) display,
	 * e.g. in a view listing many item (typically a "browse" task)
	 *
	 * @since 2.0
	 */
	public function getRepeatable()
	{
		return $this->getStatic();
	}
	
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
