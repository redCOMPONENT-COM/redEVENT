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
}
