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
class plgJosetta_extRedeventvenue extends JosettaClassesExtensionplugin
{
	protected $_context = 'com_redevent_venue';

	protected $_defaultTable = 'redevent_venues';

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
		$item = array( self::$this->_context => 'redEVENT - ' . JText::_('COM_REDEVENT_venues'));
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
		$table = RTable::getAdminInstance('Venue', array(), 'com_redevent');

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
			case 'relanguagevenuecategory':
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
	 * Method to load an item from the database
	 *
	 * @param string $context a context string, to either process or ignore an event
	 * @param int $id id of the element
	 *
	 * @return  object  an object representing the item, with no particular type
	 *
	 */
	public function onJosettaLoadItem($context, $id = '')
	{
		$item = parent::onJosettaLoadItem($context, $id);

		if (!$item)
		{
			return $item;
		}

		// Get Categories
		$db      = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('vc.id AS value, vc.name AS text');
		$query->from('#__redevent_venues_categories AS vc');
		$query->join('INNER', '#__redevent_venue_category_xref AS x ON x.category_id = vc.id');
		$query->where('venue_id = ' . $item->id);

		$db->setQuery($query);
		$res = $db->loadObjectList();

		$item->categories = array();
		$item->categories_names = array();

		foreach ($res as $r)
		{
			$item->categories[] = $r->value;
			$item->categories_names[] = $r->text;
		}


		return $item;
	}

	/**
	 * Save an item after it has been translated
	 * This will be called by Josetta when a user clicks
	 * the Save button. The context is passed so
	 * that each plugin knows if it must process the data or not
	 *
	 * if $item->(id) is empty, this is
	 * a new item, otherwise we are updating the item
	 * The name of the (id) field may vary, but should already be set
	 * to the proper value before the data is sent to the plugin
	 * (by the translate model)
	 *
	 * $item->data contains the fields entered by the user
	 * that needs to be saved
	 *
	 *@param context type
	 *@param data in form of array
	 *
	 *return table id if data is inserted
	 *
	 *return false if error occurs
	 *
	 */
	public function onJosettaSaveItem($context, $item, &$errors)
	{
		$id = parent::onJosettaSaveItem($context, $item, $errors);

		if (!$id)
		{
			return $id;
		}
		$db      = JFactory::getDbo();

		// Save categories
		// first, delete current rows for this event
		$query = ' DELETE FROM #__redevent_venue_category_xref WHERE venue_id = ' . $db->Quote($id);
		$db->setQuery($query);
		if (!$db->query())
		{
			$this->setError($db->getErrorMsg());
			return false;
		}

		// insert new ref
		if (isset($item['categories']))
		{
			foreach ((array) $item['categories'] as $cat_id)
			{
				$query = ' INSERT INTO #__redevent_venue_category_xref (venue_id, category_id) VALUES (' . $db->Quote($id) . ', '. $db->Quote($cat_id) . ')';
				$db->setQuery($query);
				if (!$db->query())
				{
					$this->setError($db->getErrorMsg());
					return false;
				}
			}
		}

		return $id;
	}

	/**
	 * Format a translation field for display on the translate view
	 *
	 * @param string $data the raw field data
	 * @param string $type the field type
	 * @return string the formatted string
	 */
	public function onJosettaGet3rdPartyFormatTranslationField($data, $type)
	{
		$displayText = null;

		if ($type->type == 'RELanguageVenueCategory')
		{
// 			echo '<pre>type';print_r($type); echo '</pre>';
			$displayText = $type->input;
		}


		return $displayText;
	}

	/**
	 * Format a the original field value for display on the translate view
	 *
	 * @param object $originalItem the actual data of the original item
	 * @param string $originalFieldTitle the field title
	 * @param object $field the Joomla! field object
	 * @param string the formatted, ready to display, string
	 */
	public function onJosettaGet3rdPartyFormatOriginalField($originalItem, $originalFieldTitle, $field)
	{
		$displayText = null;

		if ($field->type == 'RELanguageVenueCategory')
		{
			$displayText = implode("\n", $originalItem->categories_names);
		}

		return $displayText;
	}
}
