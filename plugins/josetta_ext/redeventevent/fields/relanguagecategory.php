<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
/**
 * @version     $Id: josetta.php 315 2012-02-21 12:31:10Z josetta2 $
 * @package     Josetta
 * @copyright   Diffubox (c) 2012
 * @copyright   weeblr, llc (c) 2012
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass( 'list');
JFormHelper::loadFieldClass( 'category');

/**
 * Form Field class for the Joomla Platform.
 * Supports an HTML select list of categories
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
*/
class JFormFieldRELanguageCategory extends JFormFieldCategory {


	public $type = 'RELanguageCategory';

	/**
	 * Cached array of the category items.
	 *
	 * @var    array
	 */
	protected $_categoriesOptionsPerLanguage = array();

	protected $_categoriesDataPerLanguage = array();

	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string  $name  The property name for which to the the value.
	 *
	 * @return  mixed  The property value or null.
	 *
	 */
	public function __get($name)
	{

		switch ($name)
		{
			case 'element':
				return $this->$name;
				break;
		}

		$value = parent::__get( $name);
		return $value;
	}

	/**
	 * Method to get the field options for category
	 * Use the extension attribute in a form to specify the.specific extension for
	 * which categories should be displayed.
	 * Use the show_root attribute to specify whether to show the global category root in the list.
	 *
	 * @return  array    The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getOptions()
	{
		// Initialise variables.
		$options = array();
		$published = (string) $this->element['published'];
		$languages = (string) $this->element['languages'];
		$name = (string) $this->element['name'];

		// insert custom options passed in xml file
		$options = array();

		if(!is_null( $this->element->option))
		{
// 			echo '<pre>';print_r($this->element); echo '</pre>';exit;
			foreach($this->element->option as $option)
			{
				$options[] = JHtml::_('select.option', $option->getAttribute( 'value'), JText::_($option->data()));
			}
		}

		// Filter over published state or not depending upon if it is present.
		if ($published) {
			$categoriesoptions = $this->getCategoryOptionsPerLanguage( array( 'filter.published' => explode(',', $published), 'filter.languages' => explode( ',', $languages)));
		} else {
			$categoriesoptions = $this->getCategoryOptionsPerLanguage( array( 'filter.languages' => explode( ',', $languages)));
		}

		$options = array_merge( $options, $categoriesoptions);

		if (!empty($this->element['show_root']) && strtolower( $this->element['show_root']) == 'yes') {
			array_unshift($options, JHtml::_('select.option', '0', JText::_('JGLOBAL_ROOT')));
		}

		return $options;
	}

	/**
	 * Returns a select list from the redevent categories
	 *
	 * @param   array  $config  An array of configuration options. By default, only
	 *                              published and unpublished categories are returned.
	 *
	 * @return  array
	 */
	public function getCategoryOptionsPerLanguage($config = array('filter.published' => array(0, 1), 'filter.languages' => array()))
	{
		$hash = md5(serialize($config));

		if (!isset($this->_categoriesOptionsPerLanguage[$hash]))
		{
			$config = (array) $config;

			// Read categories from db
			$items = $this->getCategoriesPerLanguage($config);

			// B/C compat.
			foreach ($items as &$item)
			{
				$item->title = $item->name;
			}

			// Indent cat list, for easier reading
			$items = self::indentCategories($items);

			$this->_categoriesOptionsPerLanguage[$hash] = array();

			foreach ($items as &$item)
			{
				$this->_categoriesOptionsPerLanguage[$hash][] = JHtml::_('select.option', $item->id, str_replace('<sup>|_</sup>', '', $item->treename));
			}
		}

		return $this->_categoriesOptionsPerLanguage[$hash];
	}

	/**
	 * Returns raw k2 categories
	 *
	 * @param   array  $config  An array of configuration options. By default, only
	 *                              published and unpublished categories are returned.
	 *
	 * @return  array
	 */
	public function getCategoriesPerLanguage( $config = array('filter.published' => array(0, 1), 'filter.languages' => array()), $index = null)
	{
		$hash = md5(serialize($config));

		if (!isset($this->_categoriesDataPerLanguage[$hash]))
		{
			$config = (array) $config;
			$db      = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('c.*');
			$query->from('#__redevent_categories as c');

			if (!empty($config['filter.published']))
			{
				$query->where('c.published in (' . ShlDbHelper::arrayToIntvalList($config['filter.published']) . ')');
			}

			if (!empty($config['filter.languages']))
			{
				$query->where('c.language in (' . ShlDbHelper::arrayToQuotedList($config['filter.languages']) . ')');
			}

			$db->setQuery($query);
			$items = $db->loadObjectList($index);

			foreach ($items as &$item)
			{
				$item->title = $item->name;
			}

			$this->_categoriesDataPerLanguage[$hash] = $items;
		}

		return $this->_categoriesDataPerLanguage[$hash];
	}

	public static function indentCategories(& $rows, $root = 0)
	{
		$children = array ();

		if (count($rows))
		{
			foreach ($rows as $v)
			{
				$pt = $v->parent_id;
				$list = @$children[$pt] ? $children[$pt] : array ();
				array_push($list, $v);
				$children[$pt] = $list;
			}
		}

		$categories = JHTML::_('menu.treerecurse', $root, '', array (), $children);

		return $categories;
	}
}
