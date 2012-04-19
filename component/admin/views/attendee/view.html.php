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
 * View class for the edit attendee screen
 *
 * @package Joomla
 * @subpackage redEvent
 * @since 2.0
 */
class RedEventViewAttendee extends JView {

	function display($tpl = null)
	{
		$mainframe = &JFactory::getApplication();

		//Load pane behavior
		jimport('joomla.html.pane');
		//initialise variables
		$editor 	= & JFactory::getEditor();
		$document	= & JFactory::getDocument();
		$user 		= & JFactory::getUser();
		$cid 			= JRequest::getVar( 'cid' );
		
		$document->setTitle(JText::_('COM_REDEVENT_PAGETITLE_EDITATTENDEE'));
    //add css to document
    $document->addStyleSheet('components/com_redevent/assets/css/redeventbackend.css');
		
    $row = & $this->get('data');
		//make data safe
		JFilterOutput::objectHTMLSafe( $row );
    
    //create selectlists
		$lists = array();
		
		//user list
		$lists['user'] = JHTML::_('list.users', 'uid', $row->uid, 1, NULL, 'name', 0);
		
		$sessionpricegroups = $this->get('Pricegroups');
		$lists['pricegroup_id'] = redEVENTHelper::getRfPricesSelect($sessionpricegroups, $row->pricegroup_id);
		
		//build toolbar
		if ( !empty($cid) ) {
			JToolBarHelper::title( JText::_('COM_REDEVENT_EDIT_REGISTRATION' ), 'registrations' );
			JToolBarHelper::spacer();
		} else {
			JToolBarHelper::title( JText::_('COM_REDEVENT_ADD_REGISTRATION' ), 'registrations' );
			JToolBarHelper::spacer();
		}
		JToolBarHelper::apply();
		JToolBarHelper::save();
		JToolBarHelper::spacer();
		JToolBarHelper::cancel();
		JToolBarHelper::spacer();
		//JToolBarHelper::help( 'el.registrationedit', true );
		
//		echo '<pre>';print_r($row); echo '</pre>';exit;
    $this->assignRef('row',    $row);
    $this->assignRef('lists',  $lists);

		parent::display($tpl);
	}
}
