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
class JFormFieldRemanagedcategorylist extends JFormFieldList
{
	/**
	 * field type
	 * @var string
	 */
	protected $type = 'remanagedcategorylist';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		$acl = RedeventUserAcl::getInstance();
		$allowed = $acl->getManagedCategories();

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

		$categories = $model->getItems();

		if ($categories)
		{
			foreach ($categories as $category)
			{
				if (in_array($category->id, $allowed))
				{
					$options[] = JHtml::_('select.option', $category->id, $category->name);
				}
			}
		}

		return array_merge(parent::getOptions(), $options);
	}
}
