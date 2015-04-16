<?php
/**
 * @package    Redevent.library
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

JFormHelper::loadFieldClass('list');

/**
 * RedEvent Venue Category form field
 *
 * @package  Redevent.admin
 * @since    0.9
 */
class JFormFieldRevenuecategorylist extends JFormFieldList
{
	/**
	 * field type
	 * @var string
	 */
	protected $type = 'revenuecategorylist';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		$options = array();
		$model = RModel::getAdminInstance('Venuescategories', array('ignore_request' => true), 'com_redevent');
		$model->setState('list.ordering', 'name');
		$model->setState('list.direction', 'asc');
		$model->setState('list.limit', 0);
		$categories = $model->getItems();

		if ($categories)
		{
			foreach ($categories as $category)
			{
				$options[] = JHtml::_('select.option', $category->id, $category->name);
			}
		}

		return array_merge(parent::getOptions(), $options);
	}
}
