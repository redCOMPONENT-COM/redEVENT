<?php
/**
 * @package    Redevent.Library
 * @copyright  Copyright (C) 2008 - 2014 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * Renders a Textbox Custom field
 *
 * @package        redEVENT
 * @since          2.0
 */

class RedeventCustomfieldSelectmultiple extends RedeventAbstractCustomfield
{

	/**
	 * Element name
	 *
	 * @access protected
	 * @var    string
	 */
	var $_name = 'select';

	/**
	 * returns the html code for the form element
	 *
	 * @param array $attributes
	 *
	 * @return string
	 */
	public function render($attributes = array())
	{
		if ($this->required)
		{
			if (isset($attributes['class']))
			{
				$attributes['class'] .= ' required';
			}
			else
			{
				$attributes['class'] = 'required';
			}
		}

		$option_list = array();
		$options = explode("\n", $this->options);
		if ($options)
		{
			foreach ($options as $opt)
			{
				$option = $this->getOptionLabelValue($opt);
				$option_list[] = JHTML::_('select.option', $option->value, $option->label);
			}
		}

		// selected options
		$values = explode("\n", $this->value);
		$default_values = explode("\n", $this->default_value);

		$default = array();
		foreach ($default_values as $d)
		{
			$d = trim($d);
			if (!empty($d))
			{
				$default[] = $d;
			}
		}
		if (!is_null($this->value))
		{
			$selected = $values;
		}
		else
		{
			$selected = $default;
		}

		return JHTML::_('select.genericlist', $option_list, $this->fieldname . '[]', 'multiple="multiple" size="' . min(10, count($options)) . '" ' . $this->attributesToString($attributes), 'value', 'text', $selected, $this->fieldid);
	}

	public function renderFilter($attributes = array(), $selected = null)
	{
		$app = & JFactory::getApplication();

		if ($selected)
		{
			$value = $selected;
		}
		else
		{
			$value = '';
		}

		$option_list = array();
		$option_list[] = JHTML::_('select.option', '', JText::_('COM_REDEVENT_Select'));
		$options = explode("\n", $this->options);
		if ($options)
		{
			foreach ($options as $opt)
			{
				$option = $this->getOptionLabelValue($opt);
				$option_list[] = JHTML::_('select.option', $option->value, $option->label);
			}
		}
		return JHTML::_('select.genericlist', $option_list, 'filtercustom[' . $this->id . ']', $this->attributesToString($attributes), 'value', 'text', $value);
	}
}
