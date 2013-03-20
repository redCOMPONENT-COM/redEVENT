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

defined( '_JEXEC' ) or die( '' );

// include base class of josettadefinition.
require_once JPATH_ADMINISTRATOR . '/components/com_josetta/classes/extensionplugin.php';

/**
 * Josetta! translation Plugin
 *
 * @package		Josetta
 * @subpackage	josetta.redeventcustomfield
 */
class plgJosetta_extRedeventcustomfield extends JosettaClassesExtensionplugin
{
	protected $_context = 'com_redevent_customfield';
	protected $_defaultTable = 'redevent_customfields';

	public function __construct(&$subject, $config = array())
	{
		include_once JPATH_LIBRARIES . '/fof/include.php';
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
	 *
	 */
	public function onJosettaGetTypes()
	{
		$item = array( self::$this->_context => 'redEVENT - ' . JText::_('COM_REDEVENT_title_customfields'));
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
		JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_redevent/tables');
		$table = FOFTable::getAnInstance('Customfield', 'RedeventTable');

		return $table;
	}

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
				$q = 'ALTER IGNORE TABLE ' . $table . ' ADD COLUMN custom' . $row->id . ' ' . $columntype;
				$db->setQuery($q);
				if (!$db->query()) {
					JError::raiseWarning(0, 'failed adding custom field to table');
				}
			}
			return true;
		}
	}
}
