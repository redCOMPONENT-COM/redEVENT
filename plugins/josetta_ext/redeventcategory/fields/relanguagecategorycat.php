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

JFormHelper::loadFieldClass('list');
JFormHelper::loadFieldClass('category');

/**
 * Form Field class for the Joomla Platform.
 * Supports an HTML select list of categories
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */
class JFormFieldRELanguageCategorycat extends JFormFieldCategory
{
	public $type = 'RELanguageCategorycat';

	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string  $name  The property name for which to the the value.
	 *
	 * @return  mixed  The property value or null.
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'element':
				return $this->$name;
				break;
		}

		$value = parent::__get($name);

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

		// Insert custom options passed in xml file
		$options = array();

		if (!is_null($this->element->option))
		{
			foreach ($this->element->option as $option)
			{
				$options[] = JHtml::_('select.option', $option->getAttribute('value'), JText::_($option->data()));
			}
		}

		// Filter over published state or not depending upon if it is present.
		// Include k2item helper, which has the method we want
		require_once dirname(__DIR__) . '/helpers/helper.php';

		if ($published)
		{
			$categoriesoptions = JosettaReCategoryHelper::getCategoryOptionsPerLanguage(array('filter.published' => explode(',', $published), 'filter.languages' => explode(',', $languages)));
		}
		else
		{
			$categoriesoptions = JosettaReCategoryHelper::getCategoryOptionsPerLanguage(array('filter.languages' => explode(',', $languages)));
		}

		$options = array_merge($options, $categoriesoptions);

		if (!empty($this->element['show_root']) && strtolower($this->element['show_root']) == 'yes')
		{
			array_unshift($options, JHtml::_('select.option', '0', JText::_('JGLOBAL_ROOT')));
		}

		return $options;
	}
}
