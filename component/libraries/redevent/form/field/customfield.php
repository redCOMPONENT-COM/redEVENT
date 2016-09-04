<?php
/**
 * @package    Redevent.library
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

JFormHelper::loadFieldClass('list');

/**
 * RedEvent custom field form field
 *
 * @package  Redevent.admin
 * @since    3.0
 */
class RedeventFormFieldCustomfield extends JFormFieldList
{
	/**
	 * field type
	 * @var string
	 */
	protected $type = 'customfield';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getOptions()
	{
		$model = RModel::getAdminInstance('Customfields', array('ignore_request' => true), 'com_redevent');
		$rows = $model->getItems() ?: array();

		$options = array_map(
				function($row) {
					return array('value' => $row->id, 'text' => $row->name . ' [' . $row->tag . ']');
				},
				$rows
		);

		return array_merge(parent::getOptions(), $options);
	}
}
