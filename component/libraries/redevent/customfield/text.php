<?php
/**
 * @package    Redevent.Library
 * @copyright  Copyright (C) 2008 - 2020 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * Renders a text Custom field
 *
 * @package  Redevent.Library
 * @since    2.0
 */
class RedeventCustomfieldText extends RedeventAbstractCustomfield
{
	/**
	 * Element name
	 *
	 * @access protected
	 * @var    string
	 */
	public $name = 'text';

	/**
	 * returns the html code for the form element
	 *
	 * @param   array  $attributes  attributes
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

		/*
		   * Required to avoid a cycle of encoding &
		   * html_entity_decode was used in place of htmlspecialchars_decode because
		   * htmlspecialchars_decode is not compatible with PHP 4
		   */
		if (!is_null($this->value))
		{
			$value = $this->value;
		}
		else
		{
			$value = $this->default_value;
		}

		$value = htmlspecialchars(html_entity_decode($value, ENT_QUOTES), ENT_QUOTES);

		return '<input type="text" name="jform[' . $this->fieldname . ']" id="'
			. $this->fieldid . '" value="' . $value . '" ' . $this->attributesToString($attributes) . '/>';
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
			$value = '';
		}

		$value = htmlspecialchars(html_entity_decode($value, ENT_QUOTES), ENT_QUOTES);

		return '<input type="text" name="filtercustom[' . $this->id . ']" id="filtercustom[' . $this->id . ']" value="'
			. $value . '" ' . $this->attributesToString($attributes) . '/>';
	}
}
