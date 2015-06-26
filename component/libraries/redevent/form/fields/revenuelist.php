<?php
/**
 * @package    Redevent.library
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

JFormHelper::loadFieldClass('list');

/**
 * redEVENT venue form field
 *
 * @package  Redevent.admin
 * @since    0.9
 */
class JFormFieldRevenuelist extends JFormFieldList
{
	/**
	 * field type
	 * @var string
	 */
	protected $type = 'revenuelist';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		$options = array();
		$model = RModel::getAdminInstance('Venues', array('ignore_request' => true), 'com_redevent');
		$model->setState('list.ordering', 'obj.venue');
		$model->setState('list.direction', 'asc');
		$model->setState('list.limit', 0);

		if (isset($this->element['acl_check']))
		{
			$val = (string) $this->element['acl_check'];
			$model->setState('filter.acl', $val == 'true' || $val == '1');
		}

		$rows = $model->getItems();

		if ($rows)
		{
			foreach ($rows as $row)
			{
				$options[] = JHtml::_('select.option', $row->id, $row->venue);
			}
		}

		return array_merge(parent::getOptions(), $options);
	}
}
