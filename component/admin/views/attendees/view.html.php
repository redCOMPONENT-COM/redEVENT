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
		
		//initialise variables
		$db = JFactory::getDBO();
		$elsettings = ELAdmin::config();
		$document	= JFactory::getDocument();
		$user = JFactory::getUser();

		//get vars
		$filter_order		= $mainframe->getUserStateFromRequest( $option.'.attendees.filter_order', 'filter_order', 'u.username', 'cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $option.'.attendees.filter_order_Dir',	'filter_order_Dir',	'', 'word' );
		$xref = JRequest::getInt('xref');
		// $search 			= $mainframe->getUserStateFromRequest( $option.'.attendees.search', 'search', '', 'string' );
		// $search 			= $db->getEscaped( trim(JString::strtolower( $search ) ) );

		//add css and submenu to document
		$document->addStyleSheet('components/com_redevent/assets/css/redeventbackend.css');
		
		// add javascript
		JHTML::_('behavior.modal', 'a.answersmodal');

		//Create Submenu
    ELAdmin::setMenu();

		//add toolbar
		JToolBarHelper::title( JText::_( 'REGISTRATIONS' ), 'users' );
//		JToolBarHelper::custom('submitters', 'redevent_submitters', 'redevent_submitters', JText::_('Attendees'), false);
		JToolBarHelper::custom('emailall', 'send.png', 'send.png', 'COM_REDEVENT_ATTENDEES_TOOLBAR_EMAIL_ALL', false, true);
		JToolBarHelper::custom('email', 'send.png', 'send.png', 'COM_REDEVENT_ATTENDEES_TOOLBAR_EMAIL_SELECTED', true, true);
		JToolBarHelper::spacer();
		JToolBarHelper::addNew();
		JToolBarHelper::editList();
		JToolBarHelper::deleteList();
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
		
		$event->dates = redEVENTHelper::isValidDate($event->dates) ? strftime($elsettings->formatdate, strtotime( $event->dates )) : JText::_('OPEN DATE');
		
		//build filter selectlist
		$datetimelocation = $this->get('DateTimeLocation');
		$filters = array();
		foreach ($datetimelocation as $key => $value) 
		{
			/* Get the date */
			if (redEVENTHelper::isValidDate($value->dates))
			{
				$date = strftime( $elsettings->formatdate, strtotime( $value->dates ));
				$enddate 	= strftime( $elsettings->formatdate, strtotime( $value->enddates ));
				$displaydate = $date.' - '.$enddate;
			}
			else {
				$displaydate = JText::_('OPEN DATE');
			}
			
			/* Get the time */
			if ($value->times) 
			{
				$time = strftime( $elsettings->formattime, strtotime( $value->times ));	
				$displaydate .= ' '. $time.$elsettings->timename;
				if ($value->endtimes) {
					$endtimes = strftime( $elsettings->formattime, strtotime( $value->endtimes ));
					$displaydate .= ' - '.$endtimes.$elsettings->timename;
				}
			}
			$filters[] = JHTML::_('select.option', $value->id, $value->venue.' '.$displaydate );
		}
		$lists['filter'] = JHTML::_('select.genericlist', $filters, 'xref', 'class="inputbox"', 'value', 'text', $event->xref );
		
		// search filter
		// $lists['search'] = $search;

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

		$event->dates = redEVENTHelper::isValidDate($event->dates) ? strftime($elsettings->formatdate, strtotime( $event->dates )) : JText::_('OPEN DATE');

		//assign data to template
		$this->assignRef('rows'      	, $rows);
		$this->assignRef('event'		, $event);

		parent::display($tpl);
	}
}
?>