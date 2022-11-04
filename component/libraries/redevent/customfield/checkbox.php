<?php
/**
 * @package    Redevent.Library
 * @copyright  Copyright (C) 2008 - 2020 redCOMPONENT.com. All rights reserved.
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
		$options = $this->getOptions();
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

		$layoutData = array(
			'id'           => $this->id,
			'selected'     => $selected,
			'attributes'   => $this->attributesToString($attributes),
			'options'      => $options,
			'field'        => $this,
		);

		return RedeventLayoutHelper::render('redevent.customfields.checkbox', $layoutData, null, array('component' => 'com_redevent'));
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
		if ($selected)
		{
			$value = $selected;
		}
		else
		{
			$value = array();
		}

		$html = '';
		$options = $this->getOptions();

		if ($options)
		{
			foreach ($options as $option)
			{
				$html .= '<input type="checkbox" name="filtercustom[' . $this->id . '][]" value="' . $option->value . '"'
					. (in_array($option->value, $value) ? ' checked="checked"' : '')
					. ' ' . $this->attributesToString($attributes) . '/>' . $option->text;
			}
		}

		return $html;
	}

	/**
	 * return options
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		$option_list = array();
		$options = explode("\n", $this->options);

		if ($options)
		{
			foreach ($options as $opt)
			{
				$option = $this->getOptionValueText($opt);
				$option_list[] = JHTML::_('select.option', $option->value, $option->text);
			}
		}

		return $option_list;
	}
}
