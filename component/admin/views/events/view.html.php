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

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

/**
 * View class for the EventList events screen
 *
 * @package Joomla
 * @subpackage redEVENT
 * @since 0.9
 */
class RedEventViewEvents extends JView {

	function display($tpl = null)
	{
		$mainframe = JFactory::getApplication();
		$option = JRequest::getCmd('option');

		if ($this->getLayout() == 'export') {
			return $this->_displayExport($tpl);
		}

		//initialise variables
		$user 		= JFactory::getUser();
		$document	= JFactory::getDocument();
		$db  		= JFactory::getDBO();
		$elsettings = JComponentHelper::getParams('com_redevent');

		//get vars
		$filter_order		= $mainframe->getUserStateFromRequest( $option.'.events.filter_order', 'filter_order', 	'a.title', 'cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $option.'.events.filter_order_Dir', 'filter_order_Dir',	'', 'word' );
		$filter_state 		= $mainframe->getUserStateFromRequest( $option.'.events.filter_state', 'filter_state', 	'*', 'word' );
		$filter 			= $mainframe->getUserStateFromRequest( $option.'.events.filter', 'filter', '', 'int' );
		$search 			= $mainframe->getUserStateFromRequest( $option.'.events.search', 'search', '', 'string' );
		$search 			= $db->getEscaped( trim(JString::strtolower( $search ) ) );
		$template			= $mainframe->getTemplate();

		$document->setTitle(JText::_('COM_REDEVENT_PAGETITLE_EVENTS'));
		//add css and submenu to document
		FOFTemplateUtils::addCSS('media://com_redevent/css/backend.css');

		//Create Submenu
		ELAdmin::setMenu();

		JHTML::_('behavior.tooltip');

		//create the toolbar
		JToolBarHelper::title( JText::_('COM_REDEVENT_EVENTS' ), 'events' );
		JToolBarHelper::customX('archive', 'redevent_archive', 'redevent_archive', JText::_('COM_REDEVENT_ARCHIVE'), true);
		JToolBarHelper::customX('archivepast', 'redevent_archive', 'redevent_archive', JText::_('COM_REDEVENT_ARCHIVE_OLD_EVENTS'), true);
		JToolBarHelper::spacer();
		JToolBarHelper::publishList();
		JToolBarHelper::unpublishList();
		JToolBarHelper::spacer();
		JToolBarHelper::addNew();
		JToolBarHelper::editList();
		JToolBarHelper::deleteList(JText::_( 'COM_REDEVENT_EVENTS_REMOVE_CONFIRM_MESSAGE'));
		JToolBarHelper::custom( 'copy', 'copy.png', 'copy_f2.png', 'Copy' );
		JToolBarHelper::custom('export', 'exportevents', 'exportevents', JText::_('COM_REDEVENT_BUTTON_IMPORTEXPORT'), false);
		JToolBarHelper::spacer();
		if ($user->authorise('core.admin', 'com_redevent')) {
			JToolBarHelper::preferences('com_redevent', '600', '800');
		}

		// Get data from the model
		$rows      	= $this->get( 'Data');
		//$total      = & $this->get( 'Total');
		$pageNav 	= $this->get( 'Pagination' );

		//publish unpublished filter
		$lists['state']	= JHTML::_('grid.state', $filter_state );

		// table ordering
		$lists['order_Dir'] = $filter_order_Dir;
		$lists['order'] = $filter_order;

		/* Venue and time details */
		$eventvenues = $this->get('EventVenues');

		//search filter
		$filters = array();
		$filters[] = JHTML::_('select.option', '1', JText::_('COM_REDEVENT_EVENT_TITLE' ) );
		$filters[] = JHTML::_('select.option', '2', JText::_('COM_REDEVENT_VENUE' ) );
		$filters[] = JHTML::_('select.option', '3', JText::_('COM_REDEVENT_CITY' ) );
		$filters[] = JHTML::_('select.option', '4', JText::_('COM_REDEVENT_CATEGORY' ) );
		$lists['filter'] = JHTML::_('select.genericlist', $filters, 'filter', 'size="1" class="inputbox"', 'value', 'text', $filter );

		// search filter
		$lists['search']= $search;

		//assign data to template
		$this->assignRef('lists'      	, $lists);
		$this->assignRef('rows'      	, $rows);
		$this->assignRef('pageNav' 		, $pageNav);
		$this->assignRef('user'			, $user);
		$this->assignRef('template'		, $template);
		$this->assignRef('elsettings'	, $elsettings);
		$this->assignRef('eventvenues'	, $eventvenues);
		$this->assign('state'        , $this->get('State'));

// 		echo '<pre>';print_r($this->state); echo '</pre>';exit;

		parent::display($tpl);
	}

	function _displayExport($tpl = null)
	{
		$document	= JFactory::getDocument();
		$document->setTitle(JText::_('COM_REDEVENT_PAGETITLE_EVENTS_EXPORT'));
		//add css and submenu to document
		FOFTemplateUtils::addCSS('media://com_redevent/css/backend.css');

		//Create Submenu
    ELAdmin::setMenu();

		JHTML::_('behavior.tooltip');

		//create the toolbar
		JToolBarHelper::title( JText::_( 'COM_REDEVENT_PAGETITLE_EVENTS_EXPORT' ), 'events' );

		JToolBarHelper::back();
		JToolBarHelper::custom('doexport', 'exportevents', 'exportevents', JText::_('COM_REDEVENT_BUTTON_EXPORT'), false);

		$lists = array();

		$lists['categories'] = JHTML::_('select.genericlist', $this->get('CategoriesOptions'), 'categories[]'
		                                        , 'size="15" multiple="multiple"', 'value', 'text');
		$lists['venues'] = JHTML::_('select.genericlist', $this->get('VenuesOptions'), 'venues[]'
		                                        , 'size="15" multiple="multiple"', 'value', 'text');

		//assign data to template
		$this->assignRef('lists'      	, $lists);

		parent::display($tpl);
	}
}
