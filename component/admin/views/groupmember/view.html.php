<?php
/**
 * @version 1.0 $Id: view.html.php 1586 2009-11-17 16:39:21Z julien $
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
 * View class for the EventList editgroupmember screen
 *
 * @package Joomla
 * @subpackage EventList
 * @since 0.9
 */
class RedEventViewGroupmember extends JView {

	function display($tpl = null)
	{
		global $mainframe;
		
		//Load pane behavior
		jimport('joomla.html.pane');

		//initialise variables
		$document	= & JFactory::getDocument();
		$pane 		= & JPane::getInstance('sliders');
		$user 		= & JFactory::getUser();
		$group_id = JRequest::getVar('group_id',  0, '', 'int');

		//get vars
		$template		= $mainframe->getTemplate();
		$cid 			= JRequest::getInt( 'cid' );

		//add css
		$document->addStyleSheet('components/com_redevent/assets/css/redeventbackend.css');

		//Get data from the model
		$model				= & $this->getModel();
		$row      		= & $this->get( 'Data');
		$group   			= & $this->get( 'Group');

		// fail if checked out not by 'me'
		if ($row->id) 
		{
			if ($model->isCheckedOut( $user->get('id') )) 
			{
				JError::raiseWarning( 'SOME_ERROR_CODE', $row->name.' '.JText::_( 'EDITED BY ANOTHER ADMIN' ));
				$mainframe->redirect( 'index.php?option=com_redevent&view=groups' );
			}
		}

		//make data safe
		JFilterOutput::objectHTMLSafe( $row );

		//build toolbar
		if ( $cid ) {
			JToolBarHelper::title( $group->name .' - '. JText::_( 'EDIT GROUP MEMBER' ), 'groupedit' );
			JToolBarHelper::spacer();
		} else {
			JToolBarHelper::title( $group->name .' - '. JText::_( 'ADD GROUP MEMBER' ), 'groupedit' );
			JToolBarHelper::spacer();
		}
		JToolBarHelper::apply();
		JToolBarHelper::save();
		JToolBarHelper::spacer();
		JToolBarHelper::cancel();
		JToolBarHelper::spacer();
		JToolBarHelper::help( 'el.editgroup', true );

		//create selectlists
		$lists = array();
		
		//user list
		$lists['user'] = JHTML::_('list.users', 'member', $row->member, 0, NULL, 'name', 0);
		
		$lists['is_admin'] = JHTML::_('select.booleanlist', 'is_admin', '', $row->is_admin);
		$lists['add_events'] = JHTML::_('select.booleanlist', 'add_events', '', $row->add_events);
		$lists['add_xrefs'] = JHTML::_('select.booleanlist', 'add_xrefs', '', $row->add_xrefs);
		$lists['edit_venues'] = JHTML::_('select.booleanlist', 'edit_venues', '', $row->edit_venues);
		$lists['receive_registrations'] = JHTML::_('select.booleanlist', 'receive_registrations', '', $row->receive_registrations);
		
		//assign data to template
		$this->assignRef('row'      	, $row);
		$this->assignRef('group_id'  	, $group_id);
		$this->assignRef('pane'      	, $pane);
		$this->assignRef('lists'      , $lists);

		parent::display($tpl);
	}
}
?>