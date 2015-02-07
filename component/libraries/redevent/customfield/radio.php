<?php
/**
 * @package    Redevent.Library
 * @copyright  Copyright (C) 2008 - 2014 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * Renders a radio Custom field
 *
 * @package  Redevent.Library
 * @since    2.0
 */
class RedeventCustomfieldRadio extends RedeventAbstractCustomfield
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

		// selected option
		if (!empty($this->value))
		{
			$selected = trim($this->value);
		}
		else
		{
			$selected = trim($this->default_value);
		}
		return JHTML::_('select.radiolist', $option_list, 'jform[' . $this->fieldname . ']', $this->attributesToString($attributes), 'value', 'text', $selected);
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
