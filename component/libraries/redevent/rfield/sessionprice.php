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
	 * @param   RedeventEntitySessionpricegroups[]  $sessionpriceGroups  session price groups
	 *
	 * @return void
	 */
	public function setOptions($sessionpriceGroups)
	{
		$application = JFactory::getApplication();
		$this->options = array();

		if (is_array($sessionpriceGroups) && count($sessionpriceGroups))
		{
			foreach ($sessionpriceGroups as $sessionPricegroup)
			{
				if (!$sessionPricegroup->active && !$application->isAdmin())
				{
					continue;
				}

				$option = new stdclass;
				$option->value = $sessionPricegroup->id;
				$option->label = $sessionPricegroup->getPricegroup()->name
					. ($sessionPricegroup->active ? '' : JText::_('LIB_REDEVENT_PRICEGROUP_LABEL_INACTIVE'));
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

		return parent::setValue($value, false);
	}

	/**
	 * Get selected option
	 *
	 * @return mixed
	 */
	public function getSelectedOption()
	{
		if (empty($this->options))
		{
			return false;
		}

		if (!$this->value)
		{
			return count($this->options) == 1 ? reset($this->options) : false;
		}

		foreach ($this->options as $option)
		{
			if ($option->value == $this->value)
			{
				return $option;
			}
		}

		return false;
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
			$data->field = JText::_('LIB_REDEVENT_RFIELD_SESSIONPRICE_LABEL');
			$data->tooltip = JText::_('LIB_REDEVENT_RFIELD_SESSIONPRICE_TOOLTIP');
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
			$data->field_header = JText::_('LIB_REDEVENT_RFIELD_SESSIONPRICE_HEADER');

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
		if (!$selected = $this->getSelectedOption())
		{
			return false;
		}

		return $selected->price ? : 0;
	}

	/**
	 * Return vat, possibly depending on current field value
	 *
	 * @return float
	 */
	public function getVat()
	{
		if (!$selected = $this->getSelectedOption())
		{
			return false;
		}

		return $selected->vat ? $selected->vat * $selected->price / 100 : 0;
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

		$properties['size'] = 1;
		$properties['maxlength'] = $this->getParam('maxlength', 250);

		if ($this->isReadonly())
		{
			$properties['readonly'] = 'readonly';
		}

		if (is_numeric($this->getParam('vat')))
		{
			$properties['vat'] = $this->getParam('vat');
		}

		if ($this->isRequired())
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

		$element = RdfLayoutHelper::render(
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
	 * @return mixed
	 */
	public function lookupDefaultValue()
	{
		return $this->value;
	}

	/**
	 * Get customized label for price item
	 *
	 * @return string
	 */
	public function getPriceItemLabel()
	{
		return $this->load()->field;
	}

	/**
	 * SKU associated to price
	 *
	 * @return string
	 */
	public function getSku()
	{
		$sku = array();

		if (!$this->value)
		{
			return '';
		}

		foreach ($this->getOptions() as $option)
		{
			if ($option->value == $this->getValue())
			{
				$sku[] = $option->sku ?: 'REGISTRATION_' . $option->value;
			}
		}

		if (empty($sku))
		{
			return 'REGISTRATION';
		}

		return implode('-', $sku);
	}

	/**
	 * Is required ?
	 *
	 * @return boolean
	 */
	public function isReadonly()
	{
		$app = JFactory::getApplication();

		return ($this->load()->readonly && !$app->isAdmin()) || (count($this->getOptions()) < 2);
	}

	/**
	 * Is required ?
	 *
	 * @return boolean
	 */
	public function isRequired()
	{
		return $this->load()->validate && !$this->isReadonly();
	}

	/**
	 * Set custom label
	 *
	 * @param   string  $label  label to set
	 *
	 * @return $this
	 */
	public function setLabel($label)
	{
		$this->load();
		$this->data->label = $label;

		return $this;
	}

	/**
	 * Returns field value ready to be printed.
	 * Array values will be separated with separator (default '~~~')
	 *
	 * @param   string  $separator  separator
	 *
	 * @return string
	 */
	public function getValueAsString($separator = '~~~')
	{
		// We just want to return the pricegroup name in that case
		if (!$spg = $this->getValue())
		{
			return false;
		}

		$sessionPriceGroup = RedeventEntitySessionpricegroup::load($spg);

		return $sessionPriceGroup->getPricegroup()->name;
	}

	/**
	 * Get default currency
	 *
	 * @return mixed
	 *
	 * @since 3.2.9
	 *
	 * @throws RuntimeException
	 */
	public function getDefaultCurrency()
	{
		if (!$this->form || !$this->form->isValid())
		{
			throw new RuntimeException('Form not defined');
		}

		return $this->form->currency;
	}
}
