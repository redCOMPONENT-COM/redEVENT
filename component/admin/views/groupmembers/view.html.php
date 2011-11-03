<?php
/**
 * @version 1.0 $Id: view.html.php 165 2009-06-01 16:37:38Z julien $
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

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

/**
 * View class for the EventList groups screen
 *
 * @package Joomla
 * @subpackage EventList
 * @since 0.9
 */
class RedEventViewGroupmembers extends JView {

	function display($tpl = null)
	{
		$mainframe = &JFactory::getApplication();
		$option = JRequest::getCmd('option');

		//initialise variables
		$document	= & JFactory::getDocument();
		$db			= & JFactory::getDBO();
		$user 		= & JFactory::getUser();
		$group_id = JRequest::getVar('group_id',  0, '', 'int');

		//get vars
		$filter_order		= $mainframe->getUserStateFromRequest( $option.'.groupmembers.filter_order', 'filter_order', 	'name', 'cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $option.'.groupmembers.filter_order_Dir', 'filter_order_Dir', '', 'word' );
		$search 			= $mainframe->getUserStateFromRequest( $option.'.groupmembers.search', 'search', '', 'string' );
		$search 			= $db->getEscaped( trim(JString::strtolower( $search ) ) );
		$template			= $mainframe->getTemplate();

		$document->setTitle(JText::_('COM_REDEVENT_PAGETITLE_GROUPMEMBERS'));
		//add css and submenu to document
		$document->addStyleSheet('components/com_redevent/assets/css/redeventbackend.css');

		//Create Submenu
    ELAdmin::setMenu();

		JHTML::_('behavior.tooltip');

		// Get data from the model
		$rows      	= & $this->get( 'Data');
		$group     	= & $this->get( 'Group');
		$pageNav 	= & $this->get( 'Pagination' );

		//create the toolbar
		JToolBarHelper::title( JText::_('COM_REDEVENT_GROUP_MEMBERS' ). ' - ' . $group->name, 'groups' );
		JToolBarHelper::custom('back', 'back', 'back', 'back', false);
		JToolBarHelper::addNew();
		JToolBarHelper::spacer();
		JToolBarHelper::editList();
		JToolBarHelper::spacer();
		JToolBarHelper::deleteList();
		JToolBarHelper::spacer();
		JToolBarHelper::help( 'el.listgroups', true );

		// table ordering
		$lists['order_Dir'] = $filter_order_Dir;
		$lists['order'] = $filter_order;

		// search filter
		$lists['search']= $search;

		//assign data to template
		$this->assignRef('lists'      	, $lists);
		$this->assignRef('rows'      	, $rows);
		$this->assignRef('pageNav' 		, $pageNav);
		$this->assignRef('user'			, $user);
		$this->assignRef('group_id'			, $group_id);

		parent::display($tpl);
	}
}
?>