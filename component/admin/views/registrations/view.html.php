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
 * @subpackage redEVENT
 * @since 0.9
 */
class RedEventViewRegistrations extends JView {

	function display($tpl = null)
	{
		$mainframe = &JFactory::getApplication();
		$option = JRequest::getCmd('option');
		
		//initialise variables
		$db = JFactory::getDBO();
		$settings = ELAdmin::config();
		$document	= JFactory::getDocument();
		$user = JFactory::getUser();
		$state = &$this->get('State');

		//get vars
		$filter_order		= $state->get('filter_order');
		$filter_order_Dir	= $state->get('filter_order_Dir');
		
		$xref = JRequest::getInt('xref');
		// $search 			= $mainframe->getUserStateFromRequest( $option.'.attendees.search', 'search', '', 'string' );
		// $search 			= $db->getEscaped( trim(JString::strtolower( $search ) ) );

		$document->setTitle(JText::_('COM_REDEVENT_PAGETITLE_REGISTRATIONS'));
		//add css and submenu to document
		$document->addStyleSheet('components/com_redevent/assets/css/redeventbackend.css');
		
		// add javascript
		JHTML::_('behavior.modal', 'a.answersmodal');

		//Create Submenu
    ELAdmin::setMenu();

		//add toolbar
		JToolBarHelper::title( JText::_( 'COM_REDEVENT_PAGETITLE_REGISTRATIONS' ), 'registrations' );
		JToolBarHelper::back();
		JToolBarHelper::spacer();
		JToolBarHelper::help( 'redevent.registrations', true );

		// Get data from the model
		$rows      = $this->get( 'Data');
		$pageNav   = $this->get( 'Pagination' );
				
		//build filter selectlist
		$filters = array();
		
		// search filter
		// $lists['search'] = $search;
		
		// confirmed filter
		$options = array(JHTML::_('select.option', 0, JText::_('COM_REDEVENT_ATTENDEES_FILTER_CONFIRMED_ALL')),
		                 JHTML::_('select.option', 1, JText::_('COM_REDEVENT_ATTENDEES_FILTER_CONFIRMED_CONFIRMED')), 
		                 JHTML::_('select.option', 2, JText::_('COM_REDEVENT_ATTENDEES_FILTER_CONFIRMED_UNCONFIRMED')), 
		                 );
		$lists['filter_confirmed'] =  JHTML::_('select.genericlist', $options, 'filter_confirmed', 'class="inputbox" onchange="this.form.submit();"', 'value', 'text', $state->get('filter_confirmed') );
		
		// waiting list filter
		$options = array(JHTML::_('select.option', 0, JText::_('COM_REDEVENT_ATTENDEES_FILTER_WAITING_ALL')),
		                 JHTML::_('select.option', 1, JText::_('COM_REDEVENT_ATTENDEES_FILTER_WAITING_ATTENDING')), 
		                 JHTML::_('select.option', 2, JText::_('COM_REDEVENT_ATTENDEES_FILTER_WAITING_WAITING')), 
		                 );
		$lists['filter_waiting'] =  JHTML::_('select.genericlist', $options, 'filter_waiting', 'class="inputbox" onchange="this.form.submit();"', 'value', 'text', $state->get('filter_waiting') );
		
		// cancelled filter
		$options = array(JHTML::_('select.option', 0, JText::_('COM_REDEVENT_ATTENDEES_FILTER_CANCELLED_NOT_CANCELLED')),
		                 JHTML::_('select.option', 1, JText::_('COM_REDEVENT_ATTENDEES_FILTER_CANCELLED_CANCELLED')), 
		                 JHTML::_('select.option', 2, JText::_('COM_REDEVENT_ATTENDEES_FILTER_CANCELLED_ALL')), 
		                 );
		$lists['filter_cancelled'] =  JHTML::_('select.genericlist', $options, 'filter_cancelled', 'class="inputbox" onchange="this.form.submit();"', 'value', 'text', $state->get('filter_cancelled') );

		// table ordering
		$lists['order_Dir'] = $filter_order_Dir;
		$lists['order']		= $filter_order;

		//assign to template
		$this->assignRef('lists',     $lists);
		$this->assignRef('rows',      $rows);
		$this->assignRef('pageNav',   $pageNav);
		$this->assignRef('user',      $user);
		$this->assignRef('settings',  $settings);
		$this->assignRef('cancelled', $state->get('filter_cancelled'));
		
		parent::display($tpl);
	}
}
