<?php
/**
 * @package    Redevent.Library
 * @copyright  Copyright (C) 2008 - 2014 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * Renders a date Custom field
 *
 * @package  Redevent.Library
 * @since    2.0
 */
class RedeventCustomfieldDate extends RedeventAbstractCustomfield
{

	/**
	 * Element name
	 *
	 * @access protected
	 * @var    string
	 */
	var $_name = 'date';

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

		if (!is_null($this->value))
		{
			$selected = $this->value;
		}
		else
		{
			$selected = $this->default_value;
		}
		return JHTML::calendar($selected, $this->fieldname, $this->fieldid, '%Y-%m-%d', $this->attributesToString($attributes));
	}
}
