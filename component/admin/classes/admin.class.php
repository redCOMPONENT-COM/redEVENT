<?php
/**
 * @version 1.0 $Id$
 * @package Joomla
 * @subpackage redEVENT
 * @copyright redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license GNU/GPL, see LICENSE.php
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

defined('_JEXEC') or die('Restricted access');

/**
 * Holds helpfull administration related stuff
 *
 * @package Joomla
 * @subpackage EventList
 * @since 0.9
 */
class ELAdmin {

	/**
	* Writes footer. Do not remove!
	*
	* @since 0.9
	*/
	function footer( )
	{

		// echo 'EventList by <a href="http://www.schlu.net" target="_blank">schlu.net</a>';

	}

	function config()
	{
		$db =& JFactory::getDBO();

		$sql = 'SELECT * FROM #__redevent_settings WHERE id = 1';
		$db->setQuery($sql);
		$config = $db->loadObject();

		return $config;
	}
	
	function setMenu()
	{
		$user = & JFactory::getUser();
		$view = JRequest::getVar('view', '');
		$controller = JRequest::getVar('controller', '');
	  //Create Submenu
    JSubMenuHelper::addEntry( JText::_( 'REDEVENT' ), 'index.php?option=com_redevent', $view == '');
    JSubMenuHelper::addEntry( JText::_( 'EVENTS' ), 'index.php?option=com_redevent&view=events', $view == 'events');
    JSubMenuHelper::addEntry( JText::_( 'VENUES' ), 'index.php?option=com_redevent&view=venues', $view == 'venues');
    JSubMenuHelper::addEntry( JText::_( 'CATEGORIES' ), 'index.php?option=com_redevent&view=categories', $view == 'categories');
    JSubMenuHelper::addEntry( JText::_( 'VENUES CATEGORIES' ), 'index.php?option=com_redevent&view=venuescategories', $view == 'venuescategories');
    JSubMenuHelper::addEntry( JText::_( 'ARCHIVESCREEN' ), 'index.php?option=com_redevent&view=archive', $view == 'archive');
    JSubMenuHelper::addEntry( JText::_( 'GROUPS' ), 'index.php?option=com_redevent&view=groups', $view == 'groups');
    JSubMenuHelper::addEntry( JText::_( 'TEXT_LIBRARY' ), 'index.php?option=com_redevent&view=textlibrary', $view == 'textlibrary');
    JSubMenuHelper::addEntry( JText::_( 'HELP' ), 'index.php?option=com_redevent&view=help', $view == 'help');
    if ($user->get('gid') > 24) {
      JSubMenuHelper::addEntry( JText::_( 'SETTINGS' ), 'index.php?option=com_redevent&controller=settings&task=edit', $controller == 'settings');
    }
	}
}

?>