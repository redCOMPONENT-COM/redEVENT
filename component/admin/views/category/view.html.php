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
 * @subpackage EventList
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

		//get vars
		$cid 		= JRequest::getVar( 'cid' );
    $url    = $mainframe->isAdmin() ? $mainframe->getSiteURL() : JURI::base();

		//add css to document
		$document->addStyleSheet($url.'/administrator/components/com_redevent/assets/css/redeventbackend.css');
    // js color picker
    $document->addStyleSheet($url.'/administrator/components/com_redevent/assets/css/colorpicker.css');
    $document->addScript($url.'/administrator/components/com_redevent/assets/js/colorpicker.js');

		//create the toolbar
		if ( $cid ) {
			JToolBarHelper::title( JText::_( 'EDIT CATEGORY' ), 'categoriesedit' );

		} else {
			JToolBarHelper::title( JText::_( 'ADD CATEGORY' ), 'categoriesedit' );

			//set the submenu
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
		}
		JToolBarHelper::apply();
		JToolBarHelper::spacer();
		JToolBarHelper::save();
		JToolBarHelper::spacer();
		JToolBarHelper::media_manager();
		JToolBarHelper::spacer();
		JToolBarHelper::cancel();
		JToolBarHelper::spacer();
		JToolBarHelper::help( 'el.editcategories', true );

		//Get data from the model
		$model		= & $this->getModel();
		$row     	= & $this->get( 'Data' );
		$groups 	= & $this->get( 'Groups' );

		// fail if checked out not by 'me'
		if ($row->id) {
			if ($model->isCheckedOut( $user->get('id') )) {
				JError::raiseWarning( 'SOME_ERROR_CODE', $row->catname.' '.JText::_( 'EDITED BY ANOTHER ADMIN' ));
				$mainframe->redirect( 'index.php?option=com_redevent&view=categories' );
			}
		}

		//clean data
		JFilterOutput::objectHTMLSafe( $row, ENT_QUOTES, 'catdescription' );
		
		/* Initiate the Lists array */
		$Lists = array();
		
		/* Build a select list for categories */
		$Lists['categories'] = $this->get('Categories');
		
		//build selectlists
		$javascript = "onchange=\"javascript:if (document.forms[0].image.options[selectedIndex].value!='') {document.imagelib.src='../images/stories/' + document.forms[0].image.options[selectedIndex].value} else {document.imagelib.src='../images/blank.png'}\"";
		$Lists['imagelist'] 		= JHTML::_('list.images', 'image', $row->image, $javascript, '/images/stories/' );
		$Lists['access'] 			= JHTML::_('list.accesslevel', $row );


		//build grouplist
		$grouplist		= array();
		$grouplist[] 	= JHTML::_('select.option', '0', JText::_( 'NO GROUP' ) );
		$grouplist 		= array_merge( $grouplist, $groups );

		$Lists['groups']	= JHTML::_('select.genericlist', $grouplist, 'groupid', 'size="1" class="inputbox"', 'value', 'text', $row->groupid );
		
		// event 
		JHTML::_('behavior.modal', 'a.modal');
		$js = "
		function elSelectEvent(id, title, field) {
			document.getElementById(field).value = id;
			document.getElementById(field+'_name').value = title;
			document.getElementById('sbox-window').close();
		}";
		$document->addScriptDeclaration($js);

		//assign data to template
		$this->assignRef('Lists'      	, $Lists);
		$this->assignRef('row'      	, $row);
		$this->assignRef('editor'		, $editor);
		$this->assignRef('pane'			, $pane);

		parent::display($tpl);
	}
}
?>