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
 * redEVENT event session price field
 *
 * @package     Redform.Libraries
 * @subpackage  Rfield
 * @since       2.5
 */
class RedeventRfieldEventsessionprice extends RdfRfieldSelect
{
	protected $type = 'eventsessionprice';

	protected $hasOptions = true;

	/**
	 * @var RedeventEntityEvent
	 */
	private $event;

	/**
	 * Set event id
	 *
	 * @param   int  $eventId  event id
	 *
	 * @return void
	 */
	public function setEvent($eventId)
	{
		$this->event = RedeventEntityEvent::load($eventId);
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
		$application = JFactory::getApplication();

		if (!$this->options)
		{
			if (!$this->event || !$this->event->isValid())
			{
				return false;
			}

			if (!$sessions = $this->event->getSessions('dates', 'asc', array('published' => 1)))
			{
				return false;
			}

			$selectOption = new stdClass;
			$selectOption->value = '';
			$selectOption->label = JText::_('LIB_REDEVENT_RFIELD_EVENTSESSIONPRICE_OPTION_SELECT_SESSION');

			$options = array(
				$selectOption
			);

			foreach ($sessions as $session)
			{
				if (!$session->canRegister())
				{
					continue;
				}

				if (!$prices = $session->getPricegroups(true))
				{
					$option = new stdClass;
					$option->value = $session->id;
					$option->label = JText::sprintf(
						'LIB_REDEVENT_RFIELD_EVENTSESSIONPRICE_OPTION_SESSION',
						$session->getVenue()->venue,
						$session->getFormattedStartDate()
					);
					$option->price = 0;
					$option->currency = '';
					$option->vat = 0;

					$options[] = $option;

					continue;
				}

				foreach ($prices as $price)
				{
					if (!$price->active && !$application->isAdmin())
					{
						continue;
					}

					$option = new stdClass;
					$option->value = $session->id . '_' . $price->id;
					$option->label = JText::sprintf(
						'LIB_REDEVENT_RFIELD_EVENTSESSIONPRICE_OPTION_SESSION_PRICE',
						$session->getVenue()->venue,
						$session->getFormattedStartDate(),
						$price->getPricegroup()->name
					);
					$option->price = $price->price;
					$option->currency = $price->currency;
					$option->vat = $price->vat;

					$options[] = $option;
				}
			}

			$this->options = $options;
		}

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
			$data->field = JText::_('LIB_REDEVENT_RFIELD_EVENTSESSIONPRICE_LABEL');
			$data->tooltip = JText::_('LIB_REDEVENT_RFIELD_EVENTSESSIONPRICE_TOOLTIP');
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
			$data->field_header = JText::_('LIB_REDEVENT_RFIELD_EVENTSESSIONPRICE_LABEL');

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
			if ($value = $this->getValue())
			{
				foreach ($this->options as $option)
				{
					if ($option->value == $value)
					{
						return $option->price;
					}
				}

				throw new RuntimeException('undefined sessionprice value');
			}
		}

		return $this->getValue();
	}

	/**
	 * Return input properties array
	 *
	 * @return array
	 */
	public function getSelectProperties()
	{
		$app = JFactory::getApplication();

		$properties = array();
		$properties['type'] = 'text';
		$properties['name'] = $this->getFormElementName();
		$properties['id'] = $this->getFormElementId();

		$properties['class'] = 'eventsessionprice';

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
		$name = 'eventsessionprice';

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
		return parent::getSku() ?: 'REGISTRATION';
	}

	/**
	 * Is read only ?
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
}
