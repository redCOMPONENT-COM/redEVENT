<?php
/**
 * @package    Redevent.Library
 * @copyright  Copyright (C) 2008 - 2014 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * Renders a checkbox Custom field
 *
 * @package  Redevent.Library
 * @since    2.0
 */
class RedeventCustomfieldCheckbox extends RedeventAbstractCustomfield
{
	/**
	 * Element name
	 *
	 * @access protected
	 * @var    string
	 */
	public $name = 'checkbox';

	/**
	 * returns the html code for the form element
	 *
	 * @param   array  $attributes  attributes
	 *
	 * @return string
	 */
	public function render($attributes = array())
	{
		$html = '';
		$options = explode("\n", $this->options);
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

		if ($options)
		{
			foreach ($options as $opt)
			{
				$option = $this->getOptionLabelValue($opt);
				$html .= '<input type="checkbox" name="jform[' . $this->fieldname . '][]" value="' . $option->value . '"'
					. (in_array($option->value, $selected) ? ' checked="checked"' : '') . ' ' . $this->attributesToString($attributes) . '/>'
					. $option->label;
			}
		}

		return $html;
	}

	/**
	 * returns form field for filtering
	 *
	 * @param   array  $attributes  attributes
	 * @param   mixed  $selected    selected value
	 *
	 * @return html string
	 */
	public function renderFilter($attributes = array(), $selected = null)
	{
		$app = JFactory::getApplication();

		if ($selected)
		{
			$value = $selected;
		}
		else
		{
			$value = array();
		}

		$html = '';
		$options = explode("\n", $this->options);

		if ($options)
		{
			foreach ($options as $opt)
			{
				$option = $this->getOptionLabelValue($opt);
				$html .= '<input type="checkbox" name="filtercustom[' . $this->id . '][]" value="' . $option->value . '"'
					. (in_array($option->value, $value) ? ' checked="checked"' : '')
					. ' ' . $this->attributesToString($attributes) . '/>' . $option->label;
			}
		}

		return $html;
	}
}
