<?php
/**
 * @package    Redevent.Library
 * @copyright  Copyright (C) 2008 - 2014 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * Renders a select Custom field
 *
 * @package  Redevent.Library
 * @since    2.0
 */
class RedeventCustomfieldSelect extends RedeventAbstractCustomfield
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

		$option_list = $this->getOptions();

		// Selected option
		if (!is_null($this->value))
		{
			$selected = $this->value;
		}
		else
		{
			$selected = $this->default_value;
		}

		return JHTML::_('select.genericlist', $option_list, 'jform[' . $this->fieldname . ']', $this->attributesToString($attributes), 'value', 'text', $selected, $this->fieldid);
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

		$option_list = $this->getOptions();

		return JHTML::_('select.genericlist', $option_list, 'filtercustom[' . $this->id . ']', $this->attributesToString($attributes), 'value', 'text', $value);
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
			$selectAll = JHTML::_('select.option', '', JText::_('JALL'));
			$option_list[] = $selectAll;

			foreach ($options as $opt)
			{
				$option = $this->getOptionLabelValue($opt);
				$option_list[] = JHTML::_('select.option', $option->value, $option->label);
			}
		}

		return $option_list;
	}
}
