<?php
/**
 * @package    Redevent.library
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

JFormHelper::loadFieldClass('list');

/**
 * RedEvent Category form field
 *
 * @package  Redevent.admin
 * @since    0.9
 */
class RedeventFormFieldCategory extends JFormFieldList
{
	/**
	 * field type
	 * @var string
	 */
	protected $type = 'category';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		$options = array();
		$model = RModel::getAdminInstance('Categories', array('ignore_request' => true), 'com_redevent');
		$model->setState('list.ordering', 'name');
		$model->setState('list.direction', 'asc');
		$model->setState('list.limit', 0);

		if (isset($this->element['acl_check']))
		{
			$val = (string) $this->element['acl_check'];
			$model->setState('filter.acl', $val == 'true' || $val == '1');
		}

		if (isset($this->element['published']))
		{
			$val = (string) $this->element['published'];
			$model->setState('filter.published', $val == 'true' || $val == '1');
		}

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
