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

/**
 * Josetta! translation Plugin
 *
 * @since  2.5
 */
class plgJosetta_extRedeventcustomfield extends JosettaClassesExtensionplugin
{
	protected $_context = 'com_redevent_customfield';

	protected $_defaultTable = 'redevent_fields';

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
	 *
	 */
	public function onJosettaGetTypes()
	{
		$item = array($this->_context => 'redEVENT - ' . JText::_('COM_REDEVENT_title_customfields'));
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
	 * Get table
	 *
	 * @return RTable
	 */
	protected function _getTable()
	{
		// Set the table directory
		$table = RTable::getAdminInstance('Customfield', array(), 'com_redevent');

		return $table;
	}

	/**
	 * onJosettaSaveItem
	 *
	 * @param   string  $context  context
	 * @param   object  $item     item
	 * @param   array   &$errors  errors
	 *
	 * @return boolean|void
	 */
	public function onJosettaSaveItem($context, $item, &$errors)
	{
		if (($context != $this->_context))
		{
			return;
		}

		if ($id = parent::onJosettaSaveItem($context, $item, $errors))
		{
			// Need to check if we must add the field in tables
			$db      = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('f.*');
			$query->from('#__redevent_fields AS f');
			$query->where('id = ' . $id);

			$db->setQuery($query);
			$row = $db->loadObject();

			// add the field to the object table
			switch ($row->object_key)
			{
				case 'redevent.event':
					$table = '#__redevent_events';
					break;
				case 'redevent.xref':
					$table = '#__redevent_event_venue_xref';
					break;
				default:
					JError::raiseWarning(0, 'undefined custom field object_key');
					break;
			}
			$tables = $db->getTableFields(array($table), false);
			$cols = $tables[$table];

			if (!array_key_exists('custom' . $row->id, $cols))
			{
				switch ($row->type)
				{
					default: // for now, let's not restrict the type...
						$columntype = 'TEXT';
				}
				$q = 'ALTER TABLE ' . $table . ' ADD COLUMN custom' . $row->id . ' ' . $columntype;
				$db->setQuery($q);
				if (!$db->query()) {
					JError::raiseWarning(0, 'failed adding custom field to table');
				}
			}
			return $id;
		}

		return false;
	}
}
