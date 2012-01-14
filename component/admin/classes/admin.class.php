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
		JSubMenuHelper::addEntry( JText::_('COM_REDEVENT' ), 'index.php?option=com_redevent', $view == '');
		JSubMenuHelper::addEntry( JText::_('COM_REDEVENT_EVENTS' ), 'index.php?option=com_redevent&view=events', $view == 'events');
		JSubMenuHelper::addEntry( JText::_('COM_REDEVENT_SESSIONS' ), 'index.php?option=com_redevent&view=sessions&eventid=0&venueid=0', $view == 'sessions');
		JSubMenuHelper::addEntry( JText::_( 'COM_REDEVENT_MENU_REGISTRATIONS' ), 'index.php?option=com_redevent&view=registrations', $view == 'registrations');
		JSubMenuHelper::addEntry( JText::_('COM_REDEVENT_VENUES' ), 'index.php?option=com_redevent&view=venues', $view == 'venues');
		JSubMenuHelper::addEntry( JText::_('COM_REDEVENT_CATEGORIES' ), 'index.php?option=com_redevent&view=categories', $view == 'categories');
		JSubMenuHelper::addEntry( JText::_('COM_REDEVENT_VENUES_CATEGORIES' ), 'index.php?option=com_redevent&view=venuescategories', $view == 'venuescategories');
		JSubMenuHelper::addEntry( JText::_('COM_REDEVENT_ARCHIVESCREEN' ), 'index.php?option=com_redevent&view=archive', $view == 'archive');
		JSubMenuHelper::addEntry( JText::_('COM_REDEVENT_GROUPS' ), 'index.php?option=com_redevent&view=groups', $view == 'groups');
		JSubMenuHelper::addEntry( JText::_('COM_REDEVENT_TEXT_LIBRARY' ), 'index.php?option=com_redevent&view=textlibrary', $view == 'textlibrary');
		JSubMenuHelper::addEntry( JText::_('COM_REDEVENT_CUSTOM_FIELDS' ), 'index.php?option=com_redevent&view=customfields', $view == 'customfields');
		JSubMenuHelper::addEntry( JText::_( 'COM_REDEVENT_MENU_ROLES' ), 'index.php?option=com_redevent&view=roles', $view == 'roles');
		JSubMenuHelper::addEntry( JText::_( 'COM_REDEVENT_MENU_PRICEGROUPS' ), 'index.php?option=com_redevent&view=pricegroups', $view == 'pricegroups');
		JSubMenuHelper::addEntry( JText::_('COM_REDEVENT_HELP' ), 'index.php?option=com_redevent&view=help', $view == 'help');
		JSubMenuHelper::addEntry( JText::_('COM_REDEVENT_LOG' ), 'index.php?option=com_redevent&view=log', $view == 'log');
		JSubMenuHelper::addEntry( JText::_('COM_REDEVENT_SETTINGS' ), 'index.php?option=com_redevent&controller=settings&task=edit', $controller == 'settings');
	}

	/**
	 * Get a option list of all categories
	 */
	public function getCategoriesOptions() 
	{
		$db =& JFactory::getDBO();
		$params = JComponentHelper::getParams('com_redevent');
		$ordering = $params->get('cat_select_ordering');
		
		$query = ' SELECT c.id, c.catname, (COUNT(parent.catname) - 1) AS depth '
           . ' FROM #__redevent_categories AS c, '
           . ' #__redevent_categories AS parent '
           . ' WHERE c.lft BETWEEN parent.lft AND parent.rgt '
           . ' GROUP BY c.id '
           . ($ordering ? ' ORDER BY c.catname': ' ORDER BY c.lft')
           ;
    $db->setQuery($query);
    
    $results = $db->loadObjectList();
    
    $options = array();
    foreach((array) $results as $cat)
    {
      $options[] = JHTML::_('select.option', $cat->id, ($ordering == 0 ? str_repeat('>', $cat->depth) : '') . ' ' . $cat->catname);
    }
		return $options;
	}
	
	/**
	 * Get a option list of all categories
	 */
	public function getVenuesCategoriesOptions() 
	{
		$db =& JFactory::getDBO();
	 $query = ' SELECT c.id, c.name, (COUNT(parent.name) - 1) AS depth '
           . ' FROM #__redevent_venues_categories AS c, '
           . ' #__redevent_venues_categories AS parent '
           . ' WHERE c.lft BETWEEN parent.lft AND parent.rgt '
           . ' GROUP BY c.id '
           . ' ORDER BY c.lft;'
           ;
    $db->setQuery($query);
    
    $results = $db->loadObjectList();
    
    $options = array();
    foreach((array) $results as $cat)
    {
      $options[] = JHTML::_('select.option', $cat->id, str_repeat('>', $cat->depth) . ' ' . $cat->name);
    }
		return $options;
	}

	/**
	 * Get a option list of all categories
	 */
	public function getVenuesOptions() 
	{
		$db =& JFactory::getDBO();
	 $query = ' SELECT v.id, v.venue '
           . ' FROM #__redevent_venues AS v '
           . ' ORDER BY v.venue'
           ;
    $db->setQuery($query);
    
    $results = $db->loadObjectList();
    
    $options = array();
    foreach((array) $results as $r)
    {
      $options[] = JHTML::_('select.option', $r->id, $r->venue);
    }
		return $options;
	}
	
	/**
	 * checks wether a tag already exists
	 * 
	 * @param string $tag tag name
	 * @return mixed boolean false if doesn't exists, tag object if it does
	 */
	function checkTagExists($tag)
	{
		$db = &JFactory::getDBO();
		$model = JModel::getInstance('tags', 'redeventModel');
		$core = $model->getData();
		foreach ($core as $cat)
		{
			foreach ($cat as $t)
			{
				if (strcasecmp($t->name, $tag) == 0) 
				{
					return $t;
				}
			}
		}
		return false;
	}
}

?>