<?php
/**
 * @package    Redevent.Library
 * @copyright  Copyright (C) 2008 - 2020 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * Represents a Custom field, generic class
 *
 * @package  Redevent.Library
 * @since    2.5
 */
abstract class RedeventAbstractCustomfield extends JObject
{
	/**
	 * custom field id
	 *
	 * @var int
	 */
	public $id;

	/**
	 * name to display as label
	 *
	 * @var string
	 */
	public $name;

	public $fieldid;

	/**
	 * name to display as field name
	 *
	 * @var string
	 */
	public $fieldname;

	/**
	 * name to display as tag
	 *
	 * @var string
	 */
	public $tag;

	public $type;

	/**
	 * tooltip
	 *
	 * @var string
	 */
	public $tips;

	/**
	 * show in lists
	 *
	 * @var int
	 */
	public $in_lists;

	public $required;

	/**
	 * options
	 *
	 * @var string
	 */
	public $options;

	/**
	 * min lenght
	 *
	 * @var int
	 */
	public $min;

	/**
	 * max length
	 *
	 * @var int
	 */
	public $max;

	public $value = null;

	public $default_value;

	/**
	 * @var int
	 */
	public $ordering;

	/**
	 * Returns element form html code
	 *
	 * @param   array  $attributes  attributes
	 *
	 * @return string html
	 */
	abstract public function render($attributes = array());

	/**
	 * bind properties to an object or array
	 *
	 * @param   mixed  $source  object or array source
	 *
	 * @return void
	 */
	public function bind($source)
	{
		if (is_object($source))
		{
			$source = get_object_vars($source);
		}

		if (is_array($source))
		{
			$obj_keys = array_keys(get_object_vars($this));

			foreach ($source AS $key => $value)
			{
				if (in_array($key, $obj_keys))
				{
					$this->$key = $value;
				}
			}
		}

		$this->fieldname = 'custom' . $this->id;
		$this->fieldid   = 'custom' . $this->id;
	}

	/**
	 * Return field html label
	 *
	 * to be adapted with layout ?
	 *
	 * @return string html
	 */
	public function getLabel()
	{
		$label = RedeventLayoutHelper::render('customfields.label', $this, null, array('component' => 'com_redevent'));

		return $label;
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
		return 'no filter';
	}

	/**
	 * returns the value
	 *
	 * @return mixed
	 */
	public function renderValue()
	{
		return $this->value;
	}

	/**
	 * return the attributes array as a html tag property string
	 *
	 * @param   array  $attributes  attributes
	 *
	 * @return  string
	 */
	protected function attributesToString($attributes)
	{
		$res = array();

		foreach ((array) $attributes as $k => $v)
		{
			$res[] = $k . '="' . $v . '"';
		}

		return implode(' ', $res);
	}

	/**
	 * return an object with value and text properties for an option (value;text)
	 *
	 * @param   string  $option  option
	 *
	 * @return object
	 */
	protected function getOptionValueText($option)
	{
		$res = new stdClass;
		$opt = trim($option);
		$parts = explode(";", $opt);

		if (count($parts) == 2)
		{
			$res->value = trim($parts[0]);
			$res->text = trim($parts[1]);
		}
		else
		{
			$res->value = trim($parts[0]);
			$res->text = trim($parts[0]);
		}

		return $res;
	}
}
