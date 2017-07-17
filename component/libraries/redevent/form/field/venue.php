<?php
/**
 * @package    Redevent.library
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

JFormHelper::loadFieldClass('list');

RLoader::registerPrefix('Redevent', JPATH_LIBRARIES . '/redevent');

/**
 * redEVENT venue form field
 *
 * @package  Redevent.admin
 * @since    3.2.4
 */
class RedeventFormFieldVenue extends JFormFieldList
{
	/**
	 * field type
	 * @var string
	 */
	protected $type = 'venue';

	protected $filter_published = '';

	protected $createModal = false;

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		$options = array();

		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('obj.id, obj.venue, obj.language')
			->from('#__redevent_venues AS obj')
			->order('obj.venue ASC');

		// Join over the asset groups.
		$query->select('ag.title AS access_level');
		$query->join('LEFT', '#__viewlevels AS ag ON ag.id = obj.access');

		// Join over the language
		$query->select('lg.title AS language_title');
		$query->join('LEFT', $db->quoteName('#__languages') . ' AS lg ON lg.lang_code = obj.language');

		if (is_numeric($this->filter_published))
		{
			$query->where('obj.published = ' . $this->filter_published);
		}

		if (isset($this->element['acl_check']) && filter_var((string) $this->element['acl_check'], FILTER_VALIDATE_BOOLEAN))
		{
			$acl = RedeventUserAcl::getInstance();
			$ids = $acl->getAllowedForEventsVenues();

			if (!$ids)
			{
				$query->where('0');
			}
			else
			{
				$query->where('obj.id IN (' . implode(',', $ids) . ')');
			}
		}

		$db->setQuery($query);

		$showLang = isset($this->element['show_lang']) && filter_var($this->element['show_lang'], FILTER_VALIDATE_BOOLEAN);

		if ($rows = $db->loadObjectList())
		{
			foreach ($rows as $row)
			{
				$language = $row->language && $row->language != '*' ? $row->language : '';
				$options[] = JHtml::_('select.option', $row->id, $showLang && $language ? $row->venue . ' (' . $language . ')' : $row->venue);
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

		if (!empty($element['create_modal']))
		{
			$val = (string) $element['create_modal'];

			if (strtolower($val == "true") || $val == "1")
			{
				$this->createModal = true;
			}
		}

		return parent::setup($element, $value, $group);
	}

	/**
	 * Method to get the field input markup for a generic list.
	 * Use the multiple attribute to enable multiselect.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getInput()
	{
		$html = parent::getInput();

		$acl = RedeventUserAcl::getInstance();

		if ($this->createModal && $acl->canAddVenue())
		{
			$html .= RedeventLayoutHelper::render('form.fields.revenuelist.buttonadd', array('fieldId' => $this->id));
		}

		return $html;
	}
}
