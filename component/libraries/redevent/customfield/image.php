<?php
/**
 * @package    Redevent.Library
 * @copyright  Copyright (C) 2008 - 2014 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * Renders a image Custom field
 *
 * @package  Redevent.Library
 * @since    2.0
 */
class RedeventCustomfieldImage extends RedeventAbstractCustomfield
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
		$config = $config = JComponentHelper::getParams('com_redevent');
		$imagePreview = '';

		if (!empty($value))
		{
			$imagePreview = JUri::root() . '/media/com_redevent/images/customfields/temporary/' . $this->value;
		}

		$layoutData = array(
			'id'           => $this->id,
			'value'        => $this->value,
			'attributes'   => $attributes,
			'config'       => $config,
			'imagePreview' => $imagePreview,
		);

		return RLayoutHelper::render('customfields.image', $layoutData, null, array('component' => 'com_redevent'));
	}
}
