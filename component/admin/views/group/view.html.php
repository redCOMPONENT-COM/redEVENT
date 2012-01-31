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
 * View class for the EventList editgroup screen
 *
 * @package Joomla
 * @subpackage EventList
 * @since 0.9
 */
class RedEventViewGroup extends JView {

	function display($tpl = null)
	{
		$mainframe = &JFactory::getApplication();

		//Load pane behavior
		jimport('joomla.html.pane');

		//initialise variables
		$document	= & JFactory::getDocument();
		$pane 		= & JPane::getInstance('sliders');
		$user 		= & JFactory::getUser();

		//get vars
		$template		= $mainframe->getTemplate();
		$cid 			= JRequest::getInt( 'cid' );

		$document->setTitle(JText::_('COM_REDEVENT_PAGETITLE_EDITGROUP'));
		//add css
		$document->addStyleSheet('components/com_redevent/assets/css/redeventbackend.css');

		//Get data from the model
		$model				= & $this->getModel();
		$row      			= & $this->get( 'Data');

		// fail if checked out not by 'me'
		if ($row->id) 
		{
			if ($model->isCheckedOut( $user->get('id') )) 
			{
				JError::raiseWarning( 'REDEVENT_GENERIC_ERROR', $row->name.' '.JText::_('COM_REDEVENT_EDITED_BY_ANOTHER_ADMIN' ));
				$mainframe->redirect( 'index.php?option=com_redevent&view=groups' );
			}
		}

		//make data safe
		JFilterOutput::objectHTMLSafe( $row );

		/*
		 * extended data
		 */
		$paramsdata = $row->parameters;
		$paramsdefs = JPATH_COMPONENT . DS . 'models' . DS . 'group.xml';
		$parameters = new JParameter( $paramsdata, $paramsdefs );
		
		//build toolbar
		if ( $cid ) {
			JToolBarHelper::title( JText::_('COM_REDEVENT_EDIT_GROUP' ), 'groupedit' );
			JToolBarHelper::spacer();
		} else {
			JToolBarHelper::title( JText::_('COM_REDEVENT_ADD_GROUP' ), 'groupedit' );
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
		
		//edit event
		$options = array(
		                 JHTML::_('select.option', 0, JText::_('COM_REDEVENT_No')),
		                 JHTML::_('select.option', 1, JText::_('COM_REDEVENT_GROUP_OWN_EVENTS')),
		                 JHTML::_('select.option', 2, JText::_('COM_REDEVENT_GROUP_GROUP_EVENTS')),
		                );
		$lists['edit_events'] = JHTML::_('select.genericlist', $options, 'edit_events', '', 'value', 'text', $row->edit_events);
		
		//edit venue
		$options = array(
		                 JHTML::_('select.option', 0, JText::_('COM_REDEVENT_No')),
		                 JHTML::_('select.option', 1, JText::_('COM_REDEVENT_GROUP_OWN_VENUES')),
		                 JHTML::_('select.option', 2, JText::_('COM_REDEVENT_GROUP_GROUP_VENUES')),
		                );
		$lists['edit_venues'] = JHTML::_('select.genericlist', $options, 'edit_venues', '', 'value', 'text', $row->edit_venues);		
		
		$options = array(
		                  JHTML::_('select.option', 0, JText::_('COM_REDEVENT_No')),
		                  JHTML::_('select.option', 1, JText::_('COM_REDEVENT_Own')),
		                  JHTML::_('select.option', 2, JText::_('COM_REDEVENT_Group')),
		                );
		$lists['publish_events'] = JHTML::_('select.genericlist', $options, 'publish_events', '', 'value', 'text', $row->publish_events);
		$lists['publish_venues'] = JHTML::_('select.genericlist', $options, 'publish_venues', '', 'value', 'text', $row->publish_venues);
		
		//assign data to template
		$this->assignRef('row'      	, $row);
		$this->assignRef('parameters'	, $parameters);
		$this->assignRef('pane'      	, $pane);
		$this->assignRef('template'		, $template);
		$this->assignRef('lists'      	, $lists);

		parent::display($tpl);
	}
}
