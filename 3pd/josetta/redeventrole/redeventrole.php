<?php
/**
 * @version     $Id: menuitem.php 423 2012-03-14 16:03:24Z yannick $
 * @package     Josetta
 * @copyright   Diffubox (c) 2012
 * @copyright   weeblr, llc (c) 2012
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined( '_JEXEC' ) or die( '' );

// include base class of josettadefinition.
require_once JPATH_ADMINISTRATOR . '/components/com_josetta/classes/extensionplugin.php';

/**
 * Josetta! Sample translation Plugin
 *
 * @package		Josetta
 * @subpackage	josetta.sampleitem
 */

class plgJosetta_extRedeventrole extends JosettaClassesExtensionplugin
{
	protected $_context = 'com_redevent_role';
	protected $_defaultTable = 'redevent_roles';

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
		$table = FOFTable::getAnInstance('Role', 'RedeventTable');

		return $table;
	}
}
