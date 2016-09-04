<?php
/**
 * @package    Redevent.library
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * RedEvent datetimepicker field form field
 *
 * Based on https://github.com/trentrichardson/jQuery-Timepicker-Addon
 *
 * @package  Redevent.admin
 * @since    3.1
 */
class RedeventFormFieldDatetimepicker extends JFormField
{
	/**
	 * field type
	 * @var string
	 */
	protected $type = 'datetimepicker';

	/**
	 * The filter.
	 *
	 * @var    integer
	 * @since  3.2
	 */
	protected $filter;

	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string  $name  The property name for which to the the value.
	 *
	 * @return  mixed  The property value or null.
	 *
	 * @since   3.2
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'filter':
				return $this->$name;
		}

		return parent::__get($name);
	}

	/**
	 * Method to set certain otherwise inaccessible properties of the form field object.
	 *
	 * @param   string  $name   The property name for which to the the value.
	 * @param   mixed   $value  The value of the property.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'filter':
				$this->$name = (string) $value;
				break;

			default:
				parent::__set($name, $value);
		}
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 */
	protected function getInput()
	{
		$class = $this->element['class'] ?: '';
		$picker = $this->element['picker'] ?: '';
		$dateformat = (string) $this->element['dateformat'] ?: 'yy-mm-dd';
		$timeformat = (string) $this->element['timeformat'] ?: 'HH:mm:ss';
		$altDateformat = (string) $this->element['altDateformat'] ?: $dateformat;
		$altTimeformat = (string) $this->element['altTimeformat'] ?: $timeformat;
		$showSecond = (string) $this->element['showSecond'] ?: false;

		switch ($picker)
		{
			case 'date':
				$layout = 'form.fields.datetimepicker.datepicker';
				break;

			case 'time':
				$layout = 'form.fields.datetimepicker.timepicker';
				break;

			case 'datetime':
			default:
				$this->filterDatetimeValue();
				$layout = 'form.fields.datetimepicker.datetimepicker';
				break;
		}

		return RedeventLayoutHelper::render(
			$layout,
			array(
				'field'         => $this,
				'class'         => $class,
				'id'            => $this->id,
				'required'      => $this->required,
				'name'          => $this->name,
				'dateformat'    => $dateformat,
				'timeformat'    => $timeformat,
				'altDateformat' => $altDateformat,
				'altTimeformat' => $altTimeformat,
				'showSecond'    => $showSecond,
				'value'         => $this->value
			)
		);
	}

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value. This acts as as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @see     JFormField::setup()
	 * @since   3.2
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return)
		{
			$this->filter    = (string) $this->element['filter'] ? (string) $this->element['filter'] : 'USER_UTC';
		}

		return $return;
	}

	/**
	 * Apply filter to value for datetime picker
	 *
	 * @return void
	 */
	private function filterDatetimeValue()
	{
		// Get some system objects.
		$config = JFactory::getConfig();
		$user = JFactory::getUser();

		// If a known filter is given use it.
		switch (strtoupper($this->filter))
		{
			case 'SERVER_UTC':
				// Convert a date to UTC based on the server timezone.
				if ($this->value && $this->value != JFactory::getDbo()->getNullDate())
				{
					// Get a date object based on the correct timezone.
					$date = JFactory::getDate($this->value, 'UTC');
					$date->setTimezone(new DateTimeZone($config->get('offset')));

					// Transform the date string.
					$this->value = $date->format('Y-m-d H:i:s', true, false);
				}

				break;

			case 'USER_UTC':
				// Convert a date to UTC based on the user timezone.
				if ($this->value && $this->value != JFactory::getDbo()->getNullDate())
				{
					// Get a date object based on the correct timezone.
					$date = JFactory::getDate($this->value, 'UTC');

					$date->setTimezone(new DateTimeZone($user->getParam('timezone', $config->get('offset'))));

					// Transform the date string.
					$this->value = $date->format('Y-m-d H:i:s', true, false);
				}

				break;
		}
	}
}
