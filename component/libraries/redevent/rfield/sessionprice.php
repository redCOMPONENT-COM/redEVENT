<?php
/**
 * @package     Redevent.Libraries
 * @subpackage  Rfield
 *
 * @copyright   Copyright (C) 2012 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * redEVENT session price field
 *
 * @package     Redform.Libraries
 * @subpackage  Rfield
 * @since       2.5
 */
class RedeventRfieldSessionprice extends RdfRfieldRadio
{
	protected $type = 'sessionprice';

	protected $hasOptions = true;

	/**
	 * Set options from sessions price groups
	 *
	 * @param   array  $sessionpriceGroups  session price groups
	 *
	 * @return void
	 */
	public function setOptions($sessionpriceGroups)
	{
		$this->options = array();

		if (is_array($sessionpriceGroups) && count($sessionpriceGroups))
		{
			foreach ($sessionpriceGroups as $sessionPricegroup)
			{
				$option = new stdclass;
				$option->value = $sessionPricegroup->id;
				$option->label = $sessionPricegroup->name;
				$option->sku = $sessionPricegroup->sku;
				$option->price = $sessionPricegroup->price;
				$option->vat = $sessionPricegroup->vatrate;
				$option->currency = $sessionPricegroup->currency;

				$this->options[] = $option;
			}
		}
	}

	/**
	 * Set field value, try to look up if null
	 *
	 * @param   string  $value   value
	 * @param   bool    $lookup  set true to lookup for a default value if value is null
	 *
	 * @return string new value
	 */
	public function setValue($value, $lookup = false)
	{
		$this->load();

		if ($value)
		{
			$this->data->readonly = true;
		}

		return parent::setValue($value, false);
	}

	/**
	 * Get selected option
	 *
	 * @return mixed
	 */
	public function getSelectedOption()
	{
		if (!$this->value)
		{
			return false;
		}

		foreach ($this->options as $option)
		{
			if ($option->value == $this->value)
			{
				return $option;
			}
		}
	}

	/**
	 * Get options
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return $this->options;
	}

	/**
	 * Load field data from database
	 *
	 * @return mixed|null
	 *
	 * @throws Exception
	 */
	protected function load()
	{
		if (!$this->data)
		{
			$data = new stdClass;
			$data->field = JText::_('COM_REDEVENT_RFIELD_SESSIONPRICE_LABEL');
			$data->tooltip = JText::_('COM_REDEVENT_RFIELD_SESSIONPRICE_TOOLTIP');
			$data->redmember_field = null;
			$data->fieldtype = $this->type;
			$data->params = '';
			$data->default = 0;
			$data->id = null;
			$data->field_id = null;
			$data->validate = true;
			$data->readonly = false;
			$data->form_id = 0;
			$data->published = true;
			$data->field_header = JText::_('COM_REDEVENT_RFIELD_SESSIONPRICE_LABEL');

			$this->data = $data;
		}

		return $this->data;
	}

	/**
	 * Return price, possibly depending on current field value
	 *
	 * @return float
	 */
	public function getPrice()
	{
		$options = $this->getOptions();

		if (count($options))
		{
			$option = reset($options);
			$price = $option->price;
		}
		else
		{
			$price = $this->getValue();
		}

		return $price;
	}

	/**
	 * Return input properties array
	 *
	 * @return array
	 */
	public function getInputProperties()
	{
		$app = JFactory::getApplication();

		$properties = array();
		$properties['type'] = 'text';
		$properties['name'] = $this->getFormElementName();
		$properties['id'] = $this->getFormElementId();

		$properties['class'] = 'eventprice';

		if (trim($this->getParam('class')))
		{
			$properties['class'] .= ' ' . trim($this->getParam('class'));
		}

		$properties['value'] = $this->getValue();

		$properties['size'] = $this->getParam('size', 25);
		$properties['maxlength'] = $this->getParam('maxlength', 250);

		if ($this->load()->readonly && !$app->isAdmin())
		{
			$properties['readonly'] = 'readonly';
		}

		if (is_numeric($this->getParam('vat')))
		{
			$properties['vat'] = $this->getParam('vat');
		}

		if ($this->load()->validate && !$this->load()->readonly)
		{
			if ($properties['class'])
			{
				$properties['class'] .= ' required';
			}
			else
			{
				$properties['class'] = ' required';
			}
		}

		if ($placeholder = $this->getParam('placeholder'))
		{
			$properties['placeholder'] = addslashes($placeholder);
		}

		return $properties;
	}

	/**
	 * Returns field Input
	 *
	 * @return string
	 */
	public function getInput()
	{
		$data = $this->load();

		$element = RdfHelperLayout::render(
			'rform.rfield.' . $this->type,
			$this,
			'',
			array('client' => 0, 'component' => 'com_redevent')
		);

		return $element;
	}

	/**
	 * Get postfixed field name for form
	 *
	 * @return string
	 */
	public function getFormElementName()
	{
		$name = 'sessionprice';

		if ($this->formIndex)
		{
			$name .= '_' . $this->formIndex;
		}

		return $name;
	}

	/**
	 * Try to get a default value from integrations
	 *
	 * @return void
	 */
	public function lookupDefaultValue()
	{
		return $this->value;
	}
}
