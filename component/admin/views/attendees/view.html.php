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
class RedEventViewAttendees extends JView {

	function display($tpl = null)
	{
		$mainframe = &JFactory::getApplication();
		$option = JRequest::getCmd('option');
		$params = &JComponentHelper::getParams('com_redevent');

		if($this->getLayout() == 'print') {
			$this->_displayprint($tpl);
			return;
		}
		if($this->getLayout() == 'move') {
			$this->_displaymove($tpl);
			return;
		}
		
		//initialise variables
		$db = JFactory::getDBO();
		$elsettings = ELAdmin::config();
		$document	= JFactory::getDocument();
		$user = JFactory::getUser();
		$state = &$this->get('State');

		//get vars
		$filter_order		= $mainframe->getUserStateFromRequest( $option.'.attendees.filter_order', 'filter_order', 'u.username', 'cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $option.'.attendees.filter_order_Dir',	'filter_order_Dir',	'', 'word' );
		$xref = JRequest::getInt('xref');
		// $search 			= $mainframe->getUserStateFromRequest( $option.'.attendees.search', 'search', '', 'string' );
		// $search 			= $db->getEscaped( trim(JString::strtolower( $search ) ) );

		$document->setTitle(JText::_('COM_REDEVENT_PAGETITLE_ATTENDEES'));
		//add css and submenu to document
		$document->addStyleSheet('components/com_redevent/assets/css/redeventbackend.css');
		
		// add javascript
		JHTML::_('behavior.modal', 'a.answersmodal');

		//Create Submenu
    ELAdmin::setMenu();

		//add toolbar
		JToolBarHelper::title( JText::_('COM_REDEVENT_REGISTRATIONS' ), 'registrations' );
//		JToolBarHelper::custom('submitters', 'redevent_submitters', 'redevent_submitters', JText::_('COM_REDEVENT_Attendees'), false);
		JToolBarHelper::custom('emailall', 'send.png', 'send.png', 'COM_REDEVENT_ATTENDEES_TOOLBAR_EMAIL_ALL', false, true);
		JToolBarHelper::custom('email', 'send.png', 'send.png', 'COM_REDEVENT_ATTENDEES_TOOLBAR_EMAIL_SELECTED', true, true);
		JToolBarHelper::spacer();
		JToolBarHelper::addNew();
		JToolBarHelper::editList();
		JToolBarHelper::custom('move', 'move', 'move', 'COM_REDEVENT_ATTENDEES_TOOLBAR_MOVE', true, true);
		if ($state->get('filter_cancelled', 0) == 0) {
			JToolBarHelper::custom('cancelreg', 'cancel', 'cancel', 'COM_REDEVENT_ATTENDEES_TOOLBAR_CANCEL', true, true);
		}
		if ($state->get('filter_cancelled', 0) == 1) {
			JToolBarHelper::custom('uncancelreg', 'redrestore', 'redrestore', 'COM_REDEVENT_ATTENDEES_TOOLBAR_RESTORE', true, true);
			JToolBarHelper::deleteList(JText::_('COM_REDEVENT_ATTENDEES_DELETE_WARNING'));
		}
		JToolBarHelper::spacer();
		JToolBarHelper::back();
		JToolBarHelper::spacer();
		JToolBarHelper::help( 'el.registereduser', true );

		// Get data from the model
		$rows      = $this->get( 'Data');
		$pageNav   = $this->get( 'Pagination' );
		$event     = $this->get( 'Event' );
		$form      = $this->get( 'Form' );
		$rf_fields = $this->get( 'RedFormFrontFields' );
		
		$event->dates = redEVENTHelper::isValidDate($event->dates) ? strftime($elsettings->get('formatdate', '%d.%m.%Y'), strtotime( $event->dates )) : JText::_('COM_REDEVENT_OPEN_DATE');
		
		//build filter selectlist
		$datetimelocation = $this->get('DateTimeLocation');
		$filters = array();
		foreach ($datetimelocation as $key => $value) 
		{
			/* Get the date */
			if (redEVENTHelper::isValidDate($value->dates))
			{
				$date = strftime( $elsettings->get('formatdate', '%d.%m.%Y'), strtotime( $value->dates ));
				$enddate 	= strftime( $elsettings->get('formatdate', '%d.%m.%Y'), strtotime( $value->enddates ));
				$displaydate = $date.' - '.$enddate;
			}
			else {
				$displaydate = JText::_('COM_REDEVENT_OPEN_DATE');
			}
			
			/* Get the time */
			if ($value->times) 
			{
				$time = strftime( $elsettings->get('formattime', '%H:%M'), strtotime( $value->times ));	
				$displaydate .= ' '. $time;
				if ($value->endtimes) {
					$endtimes = strftime( $elsettings->get('formattime', '%H:%M'), strtotime( $value->endtimes ));
					$displaydate .= ' - '.$endtimes;
				}
			}
			$filters[] = JHTML::_('select.option', $value->id, $value->venue.' '.$displaydate );
		}
		$lists['filter'] = JHTML::_('select.genericlist', $filters, 'xref', 'class="inputbox"', 'value', 'text', $event->xref );
		
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
		$this->assignRef('event',     $event);
		$this->assignRef('rf_fields', $rf_fields);
		$this->assignRef('form',      $form);
		$this->assignRef('user',      $user);
		$this->assignRef('params',    $params);
		$this->assignRef('cancelled', $state->get('filter_cancelled'));
		
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
		$rf_fields = $this->get( 'RedFormFrontFields' );
		$form      = $this->get( 'Form' );

		$event->dates = redEVENTHelper::isValidDate($event->dates) ? strftime($elsettings->get('formatdate', '%d.%m.%Y'), strtotime( $event->dates )) : JText::_('COM_REDEVENT_OPEN_DATE');

		//assign data to template
		$this->assignRef('rows'      	, $rows);
		$this->assignRef('event'		, $event);
		$this->assignRef('rf_fields', $rf_fields);
		$this->assignRef('form',      $form);

		parent::display($tpl);
	}

	/**
	 * Prepares the print screen
	 *
	 * @param $tpl
	 *
	 * @since 0.9
	 */
	function _displaymove($tpl = null)
	{
		$elsettings = ELAdmin::config();
		$document	= & JFactory::getDocument();
		$document->addStyleSheet('components/com_redevent/assets/css/redeventbackend.css');
		
		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );

		$event 		= & $this->get( 'Event' );		
		
		//add toolbar
		JToolBarHelper::title( JText::_('COM_REDEVENT_REGISTRATIONS' ), 'users' );
		JToolBarHelper::apply('applymove');
		JToolBarHelper::cancel('cancelmove');
		
		//assign data to template
		$this->assignRef('form_id',  JRequest::getInt('form_id'));
		$this->assignRef('cid',      $cid);
		$this->assignRef('session',  $event);
		
		parent::display($tpl);
	}
}
