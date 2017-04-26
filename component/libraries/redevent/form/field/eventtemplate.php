<?php
/**
 * @package    Redevent.library
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

JFormHelper::loadFieldClass('list');

/**
 * RedEvent event template form field
 *
 * @package  Redevent.admin
 * @since    3.1
 */
class RedeventFormFieldEventtemplate extends JFormFieldList
{
	/**
	 * field type
	 * @var string
	 */
	protected $type = 'eventtemplate';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getOptions()
	{
		$model = RModel::getAdminInstance('eventtemplates', array('ignore_request' => true), 'com_redevent');
		$rows = $model->getItems() ?: array();

		$options = array_map(
			function ($row)
			{
				return array('value' => $row->id, 'text' => $row->name);
			},
			$rows
		);

		return array_merge(parent::getOptions(), $options);
	}

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   SimpleXMLElement $element   The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed            $value     The form field value to validate.
	 * @param   string           $group     The field name group control value. This acts as as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   11.1
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$value = $value ?: RedeventHelper::config()->get('default_template');

		return parent::setup($element, $value, $group);
	}
}
