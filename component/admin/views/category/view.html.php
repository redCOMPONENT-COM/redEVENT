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
 * View class for the EventList category screen
 *
 * @package Joomla
 * @subpackage redEVENT
 * @since 0.9
 */
class RedEventViewCategory extends JView {

	function display($tpl = null)
	{
		$mainframe = & JFactory::getApplication();

		//Load pane behavior
		jimport('joomla.html.pane');

		//initialise variables
		$editor 	= & JFactory::getEditor();
		$document	= & JFactory::getDocument();
		$user 		= & JFactory::getUser();
		$pane 		= & JPane::getInstance('sliders');
		$tabs 		= & JPane::getInstance('tabs');

		//get vars
		$cid 		= JRequest::getVar( 'cid' );
    $url    = JURI::root();

		$document->setTitle(JText::_('COM_REDEVENT_PAGETITLE_EDITCATEGORY'));
		//add css to document
		$document->addStyleSheet($url.'/administrator/components/com_redevent/assets/css/redeventbackend.css');
		
    $document->addScript($url.'/components/com_redevent/assets/js/attachments.js');
		$document->addScriptDeclaration('var removemsg = "'.JText::_('COM_REDEVENT_ATTACHMENT_CONFIRM_MSG').'";' );
		
    // js color picker
    $document->addStyleSheet($url.'/administrator/components/com_redevent/assets/css/colorpicker.css');
    $document->addScript($url.'/administrator/components/com_redevent/assets/js/colorpicker.js');

		//create the toolbar
		if ( $cid ) {
			JToolBarHelper::title( JText::_('COM_REDEVENT_EDIT_CATEGORY' ), 'categoriesedit' );

		} else {
			JToolBarHelper::title( JText::_('COM_REDEVENT_ADD_CATEGORY' ), 'categoriesedit' );

			//set the submenu
			JSubMenuHelper::addEntry( JText::_('COM_REDEVENT' ), 'index.php?option=com_redevent');
			JSubMenuHelper::addEntry( JText::_('COM_REDEVENT_EVENTS' ), 'index.php?option=com_redevent&view=events');
			JSubMenuHelper::addEntry( JText::_('COM_REDEVENT_VENUES' ), 'index.php?option=com_redevent&view=venues');
			JSubMenuHelper::addEntry( JText::_('COM_REDEVENT_CATEGORIES' ), 'index.php?option=com_redevent&view=categories');
			JSubMenuHelper::addEntry( JText::_('COM_REDEVENT_ARCHIVESCREEN' ), 'index.php?option=com_redevent&view=archive');
			JSubMenuHelper::addEntry( JText::_('COM_REDEVENT_GROUPS' ), 'index.php?option=com_redevent&view=groups');
			JSubMenuHelper::addEntry( JText::_('COM_REDEVENT_HELP' ), 'index.php?option=com_redevent&view=help');
		}
		JToolBarHelper::apply();
		JToolBarHelper::spacer();
		JToolBarHelper::save();
		JToolBarHelper::spacer();
		JToolBarHelper::media_manager();
		JToolBarHelper::spacer();
		JToolBarHelper::cancel();
		JToolBarHelper::spacer();
		//JToolBarHelper::help( 'el.editcategories', true );

		//Get data from the model
		$model		= & $this->getModel();
		$row     	= & $this->get( 'Data' );
		$form     = & $this->get( 'Form' );
		$groups 	= & $this->get( 'Groups' );

		// fail if checked out not by 'me'
		if ($row->id) {
			if ($model->isCheckedOut( $user->get('id') )) {
				JError::raiseWarning( 'REDEVENT_GENERIC_ERROR', $row->catname.' '.JText::_('COM_REDEVENT_EDITED_BY_ANOTHER_ADMIN' ));
				$mainframe->redirect( 'index.php?option=com_redevent&view=categories' );
			}
		}

		//clean data
		JFilterOutput::objectHTMLSafe( $row, ENT_QUOTES, 'catdescription' );
		
		/* Initiate the Lists array */
		$lists = array();
		
		/* Build a select list for categories */
		$lists['categories'] = $this->get('Categories');
		
		//build selectlists
		$lists['access'] 			= JHTML::_('list.accesslevel', $row );

		//build grouplist
		$grouplist		= array();
		$grouplist[] 	= JHTML::_('select.option', '0', JText::_('COM_REDEVENT_NO_GROUP' ) );
		$grouplist 		= array_merge( $grouplist, $groups );

		$lists['groups']	= JHTML::_('select.genericlist', $grouplist, 'groupid', 'size="1" class="inputbox"', 'value', 'text', $row->groupid );
		
		$lists['access'] 			= JHTML::_('list.accesslevel', $row );
		
		//assign data to template
		$this->assignRef('lists'      	, $lists);
		$this->assignRef('row'      	, $row);
		$this->assignRef('form'      	, $form);
		$this->assignRef('editor'		, $editor);
		$this->assignRef('pane'			, $pane);
		$this->assignRef('tabs'			, $tabs);
		$this->assignRef('access'	, redEVENTHelper::getAccesslevelOptions());
		
		JHTML::_('behavior.tooltip');

		parent::display($tpl);
	}
}
