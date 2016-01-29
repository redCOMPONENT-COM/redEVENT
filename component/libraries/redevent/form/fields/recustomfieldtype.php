<?php
/**
 * @package    Redevent.library
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

JFormHelper::loadFieldClass('list');

/**
 * custom types field
 *
 * @package  Redevent.library
 * @since    2.5
 */
class JFormFieldRECustomFieldType extends JFormFieldList
{
	/**
	 * field type
	 * @var string
	 */
	protected $type = 'recustomfieldtype';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		$types = RedeventFactoryCustomfield::getTypes();
		sort($types);
		$res = array();

		foreach ($types as $type)
		{
			$res[] = JHtml::_('select.option', $type, JText::_('COM_REDEVENT_CUSTOM_FIELD_TYPE_OPTION_' . $type));
		}

		$options = array_merge(parent::getOptions(), $res);

		return $options;
	}
}
