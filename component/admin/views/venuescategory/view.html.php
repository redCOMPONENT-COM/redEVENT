<?php
/**
 * @version 1.0 $Id: view.html.php 30 2009-05-08 10:22:21Z roland $
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
 * View class for the EventList category screen
 *
 * @package Joomla
 * @subpackage redEVENT
 * @since 0.9
 */
class RedEventViewVenuesCategory extends JView {

	function display($tpl = null)
	{
		$mainframe = &JFactory::getApplication();

		//initialise variables
		$editor 	= JFactory::getEditor();
		$document	= JFactory::getDocument();
		$user 		= JFactory::getUser();

		//get vars
		$cid 		= JRequest::getVar( 'cid' );

		$document->setTitle(JText::_('COM_REDEVENT_PAGETITLE_EDITVENUECATEGORY'));
		//add css to document
		FOFTemplateUtils::addCSS('media://com_redevent/css/backend.css');

		//create the toolbar
		if ( $cid ) {
			JToolBarHelper::title( JText::_('COM_REDEVENT_EDIT_VENUES_CATEGORY' ), 'venuescategories' );

		} else {
			JToolBarHelper::title( JText::_('COM_REDEVENT_ADD_VENUES_CATEGORY' ), 'venuescategories' );

			//set the submenu
      		ELAdmin::setMenu();
		}
		JToolBarHelper::apply();
		JToolBarHelper::save();
		JToolBarHelper::cancel();
		//JToolBarHelper::help( 'el.editcategories', true );

		//Get data from the model
		$model   = $this->getModel();
		$row     = $this->get( 'Data' );
		$form    = $this->get( 'Form' );

		// fail if checked out not by 'me'
		if ($row->id) {
			if ($model->isCheckedOut( $user->get('id') )) {
				JError::raiseWarning( 'REDEVENT_GENERIC_ERROR', $row->catname.' '.JText::_('COM_REDEVENT_EDITED_BY_ANOTHER_ADMIN' ));
				$mainframe->redirect( 'index.php?option=com_redevent&view=venuescategories' );
			}
		}

		//clean data
		JFilterOutput::objectHTMLSafe( $row, ENT_QUOTES, 'description' );

		/* Initiate the Lists array */
		$lists = array();

		//build selectlists		//build selectlists
		$lists['access'] 			= JHTML::_('list.accesslevel', $row );

		//assign data to template
		$this->assignRef('lists'      	, $lists);
		$this->assignRef('row'      	, $row);
		$this->assignRef('form'      	, $form);
		$this->assignRef('editor'		, $editor);
		$this->assignRef('pane'			, $pane);

		parent::display($tpl);
	}
}
