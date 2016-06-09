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
		$class = $this->element['class'] ? $this->element['class'] : '';

		return RedeventLayoutHelper::render('form.fields.datetimepicker',
			array(
				'field'    => $this,
				'class'    => $class,
				'id'       => $this->id,
				'required' => $this->required,
				'name'     => $this->name,
				'value'    => $this->value
			)
		);
	}
}
