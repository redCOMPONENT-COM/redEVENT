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
 * @subpackage	josetta.redeventrole
 */
class plgJosetta_extRedeventrole extends JosettaClassesExtensionplugin
{
	protected $_context = 'com_redevent_role';
	protected $_defaultTable = 'redevent_roles';

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
	 *
	 */
	public function onJosettaGetTypes()
	{
		$item = array( self::$this->_context => 'redEVENT - ' . JText::_('COM_REDEVENT_ROLES'));
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
		$table = RTable::getAdminInstance('Role');

		return $table;
	}
}
