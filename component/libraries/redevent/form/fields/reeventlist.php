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

	protected $filter_published = '';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		$options = array();
		$model = RModel::getAdminInstance('Events', array('ignore_request' => true), 'com_redevent');
		$model->setState('list.ordering', 'obj.title');
		$model->setState('list.direction', 'asc');
		$model->setState('list.limit', 0);
		$model->setState('filter.published', $this->filter_published);

		if (isset($this->element['acl_check']))
		{
			$val = filter_var((string) $this->element['acl_check'], FILTER_VALIDATE_BOOLEAN);
			$model->setState('filter.acl', $val);
		}

		$showLang = isset($this->element['show_lang']) && ($this->element['show_lang'] == 'true' || $this->element['show_lang'] == '1');

		$rows = $model->getItems();

		if ($rows)
		{
			foreach ($rows as $row)
			{
				$language = $row->language && $row->language != '*' ? $row->language : '';
				$options[] = JHtml::_('select.option', $row->id, $showLang && $language ? $row->title . ' (' . $language . ')' : $row->title);
			}
		}

		return array_merge(parent::getOptions(), $options);
	}

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the <field /> tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value. This acts as as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		if (isset($element['filter_published']))
		{
			$this->filter_published = (string) $element['filter_published'];
		}
		else
		{
			$this->filter_published = '';
		}

		return parent::setup($element, $value, $group);
	}
}
