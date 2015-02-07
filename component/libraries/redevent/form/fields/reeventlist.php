<?php
/**
 * @package    Redevent.library
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

JFormHelper::loadFieldClass('list');

/**
 * redEVENT event form field
 *
 * @package  Redevent.admin
 * @since    0.9
 */
class JFormFieldReeventlist extends JFormFieldList
{
	/**
	 * field type
	 * @var string
	 */
	protected $type = 'reeventlist';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		$options = array();
		$model = RModel::getAdminInstance('Events', array('ignore_request' => true));
		$model->setState('list.ordering', 'obj.title');
		$model->setState('list.direction', 'asc');
		$model->setState('list.limit', 0);
		$rows = $model->getItems();

		if ($rows)
		{
			foreach ($rows as $row)
			{
				$options[] = JHtml::_('select.option', $row->id, $row->title);
			}
		}

		return array_merge(parent::getOptions(), $options);
	}
}
