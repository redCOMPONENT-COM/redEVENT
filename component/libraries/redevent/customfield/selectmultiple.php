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

class RedeventCustomfieldSelectmultiple extends RedeventCustomfieldSelect
{

	/**
	 * Element name
	 *
	 * @access protected
	 * @var    string
	 */
	var $_name = 'selectmultiple';

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

		$options = $this->getOptions();

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

		return JHTML::_('select.genericlist', $options, $this->fieldname . '[]', 'multiple="multiple" size="' . min(10, count($options)) . '" ' . $this->attributesToString($attributes), 'value', 'text', $selected, $this->fieldid);
	}

	public function renderFilter($attributes = array(), $selected = null)
	{
		if ($selected)
		{
			$value = $selected;
		}
		else
		{
			$value = '';
		}

		$options = $this->getOptions();

		return JHTML::_('select.genericlist', $options, 'filtercustom[' . $this->id . ']', $this->attributesToString($attributes), 'value', 'text', $value);
	}
}
