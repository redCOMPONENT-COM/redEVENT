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
 * View class for the EventList attendees screen
 *
 * @package Joomla
 * @subpackage EventList
 * @since 0.9
 */
class RedEventViewAttendees extends JView {

	function display($tpl = null)
	{
		global $mainframe, $option;

		if($this->getLayout() == 'print') {
			$this->_displayprint($tpl);
			return;
		}
		
		/* See if we need to add a new attendee */
		if (JRequest::getVar('action') == 'addattendee') {
			/* Add the attendee */
			$this->get('AddAttendee');
			/* Run the waitinglist */
			$model_wait = $this->getModel('Waitinglist', 'RedEventModel');
			$model_wait->setXrefId(JRequest::getInt('xref'));
			$model_wait->UpdateWaitingList();
		}
		
		//initialise variables
		$db = JFactory::getDBO();
		$elsettings = ELAdmin::config();
		$document	= JFactory::getDocument();
		$user = JFactory::getUser();

		//get vars
		$filter_order		= $mainframe->getUserStateFromRequest( $option.'.attendees.filter_order', 'filter_order', 'u.username', 'cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $option.'.attendees.filter_order_Dir',	'filter_order_Dir',	'', 'word' );
		$filter 			= $mainframe->getUserStateFromRequest( $option.'.attendees.filter', 'filter', '1', 'int' );
		// $search 			= $mainframe->getUserStateFromRequest( $option.'.attendees.search', 'search', '', 'string' );
		// $search 			= $db->getEscaped( trim(JString::strtolower( $search ) ) );

		//add css and submenu to document
		$document->addStyleSheet('components/com_redevent/assets/css/redeventbackend.css');

		//Create Submenu
		JSubMenuHelper::addEntry( JText::_( 'REDEVENT' ), 'index.php?option=com_redevent');
		JSubMenuHelper::addEntry( JText::_( 'EVENTS' ), 'index.php?option=com_redevent&view=events');
		JSubMenuHelper::addEntry( JText::_( 'VENUES' ), 'index.php?option=com_redevent&view=venues');
		JSubMenuHelper::addEntry( JText::_( 'CATEGORIES' ), 'index.php?option=com_redevent&view=categories');
		JSubMenuHelper::addEntry( JText::_( 'ARCHIVESCREEN' ), 'index.php?option=com_redevent&view=archive');
		JSubMenuHelper::addEntry( JText::_( 'GROUPS' ), 'index.php?option=com_redevent&view=groups');
		JSubMenuHelper::addEntry( JText::_( 'HELP' ), 'index.php?option=com_redevent&view=help');
		if ($user->get('gid') > 24) {
			JSubMenuHelper::addEntry( JText::_( 'SETTINGS' ), 'index.php?option=com_redevent&controller=settings&task=edit');
		}

		//add toolbar
		JToolBarHelper::title( JText::_( 'REGISTERED USERS' ), 'users' );
		JToolBarHelper::custom('submitters', 'redevent_submitters', 'redevent_submitters', JText::_('Attendees'), false);
		JToolBarHelper::deleteList();
		JToolBarHelper::spacer();
		JToolBarHelper::back();
		JToolBarHelper::spacer();
		JToolBarHelper::help( 'el.registereduser', true );

		// Get data from the model
		$rows =  $this->get( 'Data');
		$pageNav = $this->get( 'Pagination' );
		$event = $this->get( 'Event' );
		
		$event->dates = strftime($elsettings->formatdate, strtotime( $event->dates ));
		
		//build filter selectlist
		$filters = array();
		$filters[] = JHTML::_('select.option', '1', JText::_( 'NAME' ) );
		$filters[] = JHTML::_('select.option', '2', JText::_( 'USERNAME' ) );
		//$lists['filter'] = JHTML::_('select.genericlist', $filters, 'filter', 'size="1" class="inputbox"', 'value', 'text', $filter );
		$datetimelocation = $this->get('DateTimeLocation');
		$filters = array();
		$filters[] = JHTML::_('select.option', 0, JText::_('ALL') );
		foreach ($datetimelocation as $key => $value) {
			/* Get the date */
			$date = strftime( $elsettings->formatdate, strtotime( $value->dates )); 
			$enddate 	= strftime( $elsettings->formatdate, strtotime( $value->enddates ));
			$displaydate = $date.' - '.$enddate;
			
			/* Get the time */
			$time = strftime( $elsettings->formattime, strtotime( $value->times ));
			$endtimes = strftime( $elsettings->formattime, strtotime( $value->endtimes ));
			$displaytime = $time.' '.$elsettings->timename.' - '.$endtimes. ' '.$elsettings->timename;
			$filters[] = JHTML::_('select.option', $value->id, $value->venue.' '.$displaydate.' '.$displaytime );
		}
		$lists['filter'] = JHTML::_('select.genericlist', $filters, 'filter', 'size="1" class="inputbox"', 'value', 'text', $filter );
		
		// search filter
		// $lists['search'] = $search;

		// table ordering
		$lists['order_Dir'] = $filter_order_Dir;
		$lists['order']		= $filter_order;

		//assign to template
		$this->assignRef('lists'      	, $lists);
		$this->assignRef('rows'      	, $rows);
		$this->assignRef('pageNav' 		, $pageNav);
		$this->assignRef('event'		, $event);

		parent::display($tpl);
	}

	/**
	 * Prepares the print screen
	 *
	 * @param $tpl
	 *
	 * @since 0.9
	 */
	function _displayprint($tpl = null)
	{
		$elsettings = ELAdmin::config();
		$document	= & JFactory::getDocument();
		$document->addStyleSheet('components/com_redevent/assets/css/redeventbackend.css');

		$rows      	= & $this->get( 'Data');
		$event 		= & $this->get( 'Event' );

		$event->dates = strftime($elsettings->formatdate, strtotime( $event->dates ));

		//assign data to template
		$this->assignRef('rows'      	, $rows);
		$this->assignRef('event'		, $event);

		parent::display($tpl);
	}
}
?>