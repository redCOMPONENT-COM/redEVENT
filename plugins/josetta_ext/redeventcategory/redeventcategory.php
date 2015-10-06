<?php
/**
 * @version     2.5
 * @package     Redevent
 * @subpackage  Redevent.Josetta
 * @copyright   redEVENT (C) 2008 - 2015 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license     GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('');

// Include base class of josettad efinition.
require_once JPATH_ADMINISTRATOR . '/components/com_josetta/classes/extensionplugin.php';

// Load redEVENT library
$redeventLoader = JPATH_LIBRARIES . '/redevent/bootstrap.php';

if (!file_exists($redeventLoader))
{
	throw new Exception(JText::_('COM_REDEVENT_INIT_FAILED'), 404);
}

include_once $redeventLoader;

RedeventBootstrap::bootstrap();

/**
 * Josetta! translation Plugin
 *
 * @since  2.5
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
	 *
	 * @return void
	 */
	public function loadLanguages()
	{
		parent::loadLanguages();

		$language = JFactory::getLanguage();

		// Load the administrator english language of the component
		$language->load('com_redevent', JPATH_ADMINISTRATOR . '/components/com_redevent', 'en-GB', true);

		// Load the administrator default language of the component
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

	/**
	 * set path
	 *
	 * @return void
	 */
	protected function _setPath()
	{
		$this->_path = JPATH_PLUGINS . '/josetta_ext/' . $this->_name;
	}

	/**
	 * Hook for module to add their own fields processing
	 * to the form xml
	 *
	 * @param   Object  $xmlData         xml data
	 * @param   Object  $field           field
	 * @param   Object  $itemType        item type
	 * @param   Object  $item            item
	 * @param   Object  $originalItem    original item
	 * @param   String  $targetLanguage  string
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
	 * Get table
	 *
	 * @return RTable
	 */
	protected function _getTable()
	{
		// Set the table directory
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_redevent/tables');
		$table = RTable::getAdminInstance('Category', array(), 'com_redevent');

		return $table;
	}

	/**
	 * Method to get the field options.
	 *
	 * @param   object  $field  field
	 *
	 * @return  array  The field option objects.
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
				$table = RTable::getAdminInstance('category', array(), 'com_redevent');
				$table->load($originalItem->parent_id);
				$displayText = $table->name;
				break;
		}

		return $displayText;
	}
}
