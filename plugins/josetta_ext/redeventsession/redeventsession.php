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
 * @subpackage  josetta.redeventsession
 * @since       2.5
 */
class plgJosetta_extRedeventsession extends JosettaClassesExtensionplugin
{
	protected $_context = 'com_redevent_session';

	protected $_defaultTable = 'redevent_sessions';

	protected $customfields = null;

	protected $origineventid = 0;

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
		$this->loadLanguage();
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
		$item = array($this->_context => 'redEVENT - ' . JText::_('COM_REDEVENT_sessions'));
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
		$table = RTable::getAdminInstance('Session', array(), 'com_redevent');

		return $table;
	}

	/**
	 * Method to build a list filter for the main translator view
	 * Used when such filter is not one of the Josetta built in filters type
	 *
	 * @return array
	 *
	 */
	public function onJosettaGet3rdPartyFilter( $context, $filterType, $filterName, $current)
	{
		if (!empty( $context) && ( $context != $this->_context))
		{
			return;
		}

		$filterHtml = '';

		switch ($filterType)
		{
			case 'reevent':
				// this is a category, so use Joomla html helper to build the drop down
				$filterHtml = '';
				$filterHtml .= JText::_('COM_REDEVENT_EVENT');
				$filterHtml .= '<select name="' . $filterName . '" id="' . $filterName . '" class="inputbox" onchange="this.form.submit()">' . "\n";
				$filterHtml .= '<option value="0">' . JText::_('COM_REDEVENT_SELECT_EVENT') . '</option>' . "\n";

				$db      = JFactory::getDbo();
				$query = $db->getQuery(true);

				$query->select('id AS value, title AS text');
				$query->from('#__redevent_events');
				$query->where('published > -1');
				$query->order('title');

				$db->setQuery($query);
				$options = $db->loadObjectList();

				$filterHtml .= JHtml::_('select.options', $options, 'value', 'text', (int)($current) ) . "\n";
				$filterHtml .= "</select>\n";
				break;

			default:
				break;
		}

		return empty($filterHtml) ? null : $filterHtml;
	}

	/**
	 * Get html for buttons to be offered to users, to carry over
	 * translation: normally a copy button, and optionnally a suggest
	 * translation button
	 * Basic types fields display is builtin, but plugins can/will add
	 * their own fields, for which they should provide buttons
	 *
	 * Note: plugin can make use of JosettaHelper::getCopyButton()
	 * and JosesttaHelper::getSuggestButton() methods to add
	 * the common default buttons
	 * In the latter case, use also the @link JosettaHelper::canSuggestTranslation()
	 * method before adding the suggest button code, to
	 *
	 * @param object $form the Joomla! form object to which the field belongs
	 * @param object $field the Joomla! field object
	 * @param string $language target translation language
	 * @return string the formatted, ready to display, string
	 */
	public function onJosettaGet3rdPartycreateButton($form, $field, $language)
	{
		$buttonsHtml = null;

		switch ($field->type)
		{
			case 'reevent':
			case 'revenue':
				$buttonsHtml = '&nbsp;';
				break;
		}

		return $buttonsHtml;
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

		switch ($type->type)
		{
			case'reevent':
			case'revenue':
				$displayText = $type->input;
				break;
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

		if ($field->type == 'reevent')
		{
			$val = $originalItem->eventid;
			$this->origineventid = $val;

			$db      = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('title');
			$query->from('#__redevent_events');
			$query->where('id = ' . $val);

			$db->setQuery($query);
			$res = $db->loadResult();

			$displayText = $res;
		}

		if ($field->type == 'revenue')
		{
			$val = $originalItem->venueid;
			$this->originvenueid = $val;

			$db      = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('venue');
			$query->from('#__redevent_venues');
			$query->where('id = ' . $val);

			$db->setQuery($query);
			$res = $db->loadResult();

			$displayText = $res;
		}

		if ($field->type == 'recustom')
		{
			// Find if there is an associated custom field in original language
			$customid = substr($field->fieldname, 6);
			$db      = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('ja.id');
			$query->from('#__josetta_associations AS ja');
			$query->join('INNER', '#__josetta_associations AS jorg ON jorg.key = ja.key');
			$query->where('jorg.id = ' . $customid);
			$query->where('jorg.context = ' . $db->Quote('com_redevent_customfield'));
			$query->where('ja.language = ' . $db->Quote($originalItem->language));

			$db->setQuery($query);
			$resu = $db->loadResult();

			$displayText = $originalItem->{'custom' . $resu} ? $originalItem->{'custom' . $resu} : '';
		}

		return $displayText;
	}


	/**
	 * Hook for module to add raw fields definitions
	 * to the form xml
	 *
	 * @return string
	 */
	protected function _createFormAddCustomFields($targetLanguage)
	{
		$res = $this->getcustomfields();

		if (!$res)
		{
			return '';
		}

		$xml = '';

		foreach ($res as $field)
		{
			if ($field->language == $targetLanguage)
			{
				$xml .= $this->getRedeventCustomFieldXml($field);
			}
		}

		return $xml;
	}

	/**
	 * returns cutom fields objects
	 *
	 * @return array
	 */
	protected function getcustomfields()
	{
		if (!$this->customfields)
		{
			$db      = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('f.*');
			$query->from('#__redevent_fields AS f');
			$query->where('f.object_key = "redevent.xref"');
			$query->order('f.ordering');

			$db->setQuery($query);
			$this->customfields = $db->loadObjectList('id');
		}

		return $this->customfields;
	}

	/**
	 * get xml for redevent custom fields
	 *
	 * @param   object  $field  field data
	 *
	 * @return string xml
	 */
	protected function getRedeventCustomFieldXml($field)
	{
		$xmlData = new stdclass;

		// get description, if any
		$xmlData->description = empty($field->tips) ? '' : (string) $field->tips;
		$xmlData->description = empty($xmlData->description) ? '' : 'description="' . $xmlData->description . '"';

		// default value
		$xmlData->default = is_null($field->default_value) ? '' : (string) $field->default_value;
		$xmlData->default = empty($xmlData->default) ? '' : 'default="' . (string) $field->default_value . '"';

		//get the value of  <length> tag
		$xmlData->maxLength = (string) $field->max == '0' ? '' : 'maxlength="' . (string) $field->max . '"';

		$xmlData->subfield = '';
		$xmlData->other = '';
		$xmlData->class = 'class="josetta"';

		//if <require> tag is present in <field> in context.xml
		$xmlData->isRequired = $field->required ? 'required="true"' : '';

		// compute change detection event
		$onChange = ' onchange="Josetta.itemChanged(this);"';

		$type = 'recustom';
		$xmlData->addFieldPath = 'addfieldpath="/plugins/josetta_ext/redeventsession/fields"';

		//Build xml node for jforms
		$xml = ' <field  name="custom' . (string) $field->id . '" ' . $xmlData->class . $onChange . ' type="' . $type . '" label="'
		. (string) $field->name . '"   ' . $xmlData->isRequired . ' '
		. $xmlData->maxLength . ' ' . $xmlData->other . ' ' . $xmlData->description
		. ' ' . $xmlData->addFieldPath
		. ' ' . $xmlData->default . ' ></field>' . "\n";

		return $xml;
	}
}
