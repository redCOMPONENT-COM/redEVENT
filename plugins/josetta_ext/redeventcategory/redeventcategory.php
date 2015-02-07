<?php
/**
 * @version     2.5
 * @package     Joomla
 * @subpackage  redEVENT
 * @copyright   redEVENT (C) 2008 - 2010 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license     GNU/GPL, see LICENSE.php
 * redEVENT is based on EventList made by Christoph Lukes from schlu.net
 * redEVENT can be downloaded from www.redcomponent.com
 * redEVENT is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * redEVENT is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with redEVENT; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

defined('_JEXEC') or die('');

// Include base class of josettadefinition.
require_once JPATH_ADMINISTRATOR . '/components/com_josetta/classes/extensionplugin.php';

/**
 * Josetta! translation Plugin
 *
 * @package     Josetta
 * @subpackage  josetta.redeventcustomfield
 * @since       2.5
 */
class plgJosetta_extRedeventcategory extends JosettaClassesExtensionplugin
{
	protected $_context = 'com_redevent_category';

	protected $_defaultTable = 'redevent_categories';

	/**
	 * constructor
	 *
	 * @param   string  &$subject  subject
	 * @param   array   $config    config
	 */
	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);
		$this->loadLanguages();
	}

	/**
	 * Load language files that may be needed by the extension being
	 * translated
	 */
	public function loadLanguages()
	{
		// load Joomla global language files
		parent::loadLanguages();

		$language = JFactory::getLanguage();
		// load the administrator english language of the component
		$language->load('com_redevent', JPATH_ADMINISTRATOR.'/components/com_redevent', 'en-GB', true);
		// load the administrator default language of the component
		$language->load('com_redevent', JPATH_ADMINISTRATOR, null, true);
	}

	/**
	 * Method to build the dropdown of josetta translator screen
	 * Returns an array made up of the unique content type id (the context)
	 * and a displayable title
	 *
	 * @return array
	 */
	public function onJosettaGetTypes()
	{
		$item = array( self::$this->_context => 'redEVENT - ' . JText::_('COM_REDEVENT_title_categories'));
		$items[] = $item;

		return $items;
	}

	protected function _setPath()
	{
		$this->_path = JPATH_PLUGINS . '/josetta_ext/' . $this->_name;
	}

	protected function _getTable()
	{
		// Set the table directory
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_redevent/tables');
		$table = RTable::getAdminInstance('Category');

		return $table;
	}

	/**
	 * Hook for module to add their own fields processing
	 * to the form xml
	 *
	 * @return string
	 */
	protected function _output3rdPartyFieldsXml($xmlData, $field, $itemType, $item, $originalItem, $targetLanguage)
	{
		switch ($xmlData->fieldType)
		{
			case 'relanguagecategory':
				$options = $this->getOptions($field);

				foreach ($options as $option)
				{
					$xmlData->subfield .= '<option value="' . (string) $option->value . '">' . (string) $option->text . '</option>';
				}

				$xmlData->other .= ' languages="' . $targetLanguage . '"';
				$multiple = !empty($field->multiple) && (string) $field->multiple == 'yes';
				$xmlData->other .= $multiple ? ' multiple="true"' : '';
				break;
		}

		return $xmlData;
	}

	/**
	 * Method to get the field options.
	 *
	 * @param   object  $field  field
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getOptions($field)
	{
		// Initialize variables.
		$options = array();

		foreach ($field->children() as $option)
		{
			// Only add <option /> elements.
			if ($option->getName() != 'option')
			{
				continue;
			}

			// Create a new option object based on the <option /> element.
			$tmp = JHtml::_(
			'select.option', (string) $option['value'],
			JText::alt(trim((string) $option), preg_replace('/[^a-zA-Z0-9_\-]/', '_', $field->fieldname)), 'value', 'text',
			((string) $option['disabled'] == 'true')
			);

			// Set some option attributes.
			$tmp->class = (string) $option['class'];

			// Set some JavaScript option attributes.
			$tmp->onclick = (string) $option['onclick'];

			// Add the option object to the result set.
			$options[] = $tmp;
		}

		reset($options);

		return $options;
	}

	/**
	 * Format a the original field value for display on the translate view
	 *
	 * @param   object  $originalItem        the actual data of the original item
	 * @param   string  $originalFieldTitle  the field title
	 * @param   object  $field               the Joomla! field object
	 *
	 * @return   string the formatted, ready to display, string
	 */
	public function onJosettaGet3rdPartyFormatOriginalField($originalItem, $originalFieldTitle, $field)
	{
		$displayText = null;

		switch ($originalFieldTitle)
		{
			case 'parent_id':
				$table = $table = RTable::getAdminInstance('category');
				$table->load($originalItem->parent_id);
				$displayText = $table->catname;
				break;
		}

		return $displayText;
	}
}
