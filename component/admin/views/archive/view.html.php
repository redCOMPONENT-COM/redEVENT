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
 * View class for the EventList archive screen
 *
 * @package Joomla
 * @subpackage redEVENT
 * @since 0.9
 */
class RedEventViewArchive extends JView {

	function display($tpl = null)
	{
		$mainframe = &JFactory::getApplication();
		$option = JRequest::getCmd('option');

		//initialise variables
		$document	= & JFactory::getDocument();
		$db			= & JFactory::getDBO();
		$user		= & JFactory::getUser();
		$elsettings = ELAdmin::config();

		//get vars
		$filter_order		= $mainframe->getUserStateFromRequest( $option.'.archive.filter_order', 'filter_order', 'x.dates', 'cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $option.'.archive.filter_order_Dir',	'filter_order_Dir',	'', 'word' );
		$filter 			= $mainframe->getUserStateFromRequest( $option.'.archive.filter', 'filter', '', 'int' );
		$filter 			= intval( $filter );
		$search 			= $mainframe->getUserStateFromRequest( $option.'.archive.search', 'search', '', 'string' );
		$search 			= $db->getEscaped( trim(JString::strtolower( $search ) ) );
		$template			= $mainframe->getTemplate();

		$document->setTitle(JText::_('COM_REDEVENT_PAGETITLE_ARCHIVE'));
		//add css and submenu to document
		$document->addStyleSheet('components/com_redevent/assets/css/redeventbackend.css');
		
    //Create Submenu
    ELAdmin::setMenu();

		JHTML::_('behavior.tooltip');

		//create the toolbar
		JToolBarHelper::title( JText::_('COM_REDEVENT_ARCHIVESCREEN' ), 'archive' );
		JToolBarHelper::customX('unarchive', 'redevent_unarchive', 'redevent_unarchive', JText::_('COM_REDEVENT_Unarchive'), true);
		JToolBarHelper::spacer();
		JToolBarHelper::deleteList();
		JToolBarHelper::spacer();
		JToolBarHelper::help( 'el.archive', true );

		// Get data from the model
		$rows      	= & $this->get( 'Data');
		
		//$total      = & $this->get( 'Total');
		$pageNav 	= & $this->get( 'Pagination' );

		//search filter
		$filters = array();
		$filters[] = JHTML::_('select.option', '1', JText::_('COM_REDEVENT_EVENT_TITLE' ) );
		$filters[] = JHTML::_('select.option', '2', JText::_('COM_REDEVENT_VENUE' ) );
		$filters[] = JHTML::_('select.option', '3', JText::_('COM_REDEVENT_CITY' ) );
		$filters[] = JHTML::_('select.option', '4', JText::_('COM_REDEVENT_CATEGORY' ) );
		$lists['filter'] = JHTML::_('select.genericlist', $filters, 'filter', 'size="1" class="inputbox"', 'value', 'text', $filter );

		// table ordering
		$lists['order_Dir'] = $filter_order_Dir;
		$lists['order'] = $filter_order;
		
		/* Venue and time details */
		$eventvenues = $this->get('ArchiveEventVenues');
		
		// search filter
		$lists['search']= $search;

		//assign data to template
		$this->assignRef('lists'      	, $lists);
    $this->assignRef('user'        , $user);
		$this->assignRef('rows'      	, $rows);
		$this->assignRef('pageNav' 		, $pageNav);
		$this->assignRef('elsettings'	, $elsettings);
		$this->assignRef('template'		, $template);
		$this->assignRef('eventvenues'	, $eventvenues);

		parent::display($tpl);
	}
}
