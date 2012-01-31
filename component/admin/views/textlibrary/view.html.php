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
class RedEventViewTextLibrary extends JView {

	function display($tpl = null)
	{
		$mainframe = &JFactory::getApplication();
	
		if ($this->getLayout() == 'import') {
			return $this->_displayImport($tpl);
		}
		
		//Load pane behavior
		jimport('joomla.html.pane');

		//initialise variables
		$document	= JFactory::getDocument();
		$user = JFactory::getUser();
		$task = JRequest::getVar('task');
		                  
		$document->setTitle(JText::_('COM_REDEVENT_PAGETITLE_LIBRARY'));
		
		switch ($task) {
			case 'edit':
			case 'add':
				return $this->_displayEdit($tpl);
		}
		
		JToolBarHelper::title( JText::_('COM_REDEVENT_TEXT_LIBRARY' ), 'library' );
		JToolBarHelper::addNew();
		JToolBarHelper::editListX();
		JToolBarHelper::spacer();
		JToolBarHelper::deleteList();
		JToolBarHelper::spacer();
		JToolBarHelper::custom('export', 'csvexport', 'csvexport', JText::_('COM_REDEVENT_BUTTON_EXPORT'), false);
		JToolBarHelper::custom('import', 'csvimport', 'csvimport', JText::_('COM_REDEVENT_BUTTON_IMPORT'), false);
		
		// Get data from the model
		$rows = $this->get('Data');
		$pagination = $this->get('Pagination');
		
		//add css to document
		$document->addStyleSheet('components/com_redevent/assets/css/redeventbackend.css');
		
		//set the submenu
    ELAdmin::setMenu();
    
    $option = 'com_redevent';
		$filter_order		= $mainframe->getUserStateFromRequest( $option.'.textlibrary.filter_order',		'filter_order',		'obj.text_name',	'cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $option.'.textlibrary.filter_order_Dir',	'filter_order_Dir',	'ASC',				'word' );
    
		// lists
		$lists = array();
		
		// table ordering
		$lists['order_Dir'] = $filter_order_Dir;
		$lists['order'] = $filter_order;
		
		$this->assignRef('user', $user);
		$this->assignRef('rows', $rows);
		$this->assignRef('pagination', $pagination);
		$this->assignRef('lists',		$lists);
		
		parent::display($tpl);
	}
	
	function _displayEdit($tpl = null)
	{
		$mainframe = &JFactory::getApplication();

		//Load pane behavior
		jimport('joomla.html.pane');

		//initialise variables
		$document	= JFactory::getDocument();
		$user = JFactory::getUser();
		
		$task = JRequest::getVar('task');
		
		/* Set up toolbar */
		if ($task == 'edit') JToolBarHelper::title( JText::_('COM_REDEVENT_EDIT_TEXT_LIBRARY' ), 'libraryedit' );
		else JToolBarHelper::title( JText::_('COM_REDEVENT_ADD_TEXT_LIBRARY' ), 'libraryedit' );
		JToolBarHelper::save();
		JToolBarHelper::apply();
		JToolBarHelper::spacer();
		JToolBarHelper::cancel();
		JToolBarHelper::spacer();

		/* Load the editor */
		$editor = JFactory::getEditor();
		$this->assignRef('editor' , $editor);

		/* Get the data */
		$row = $this->get('Text');
		$this->assignRef('row', $row);

		/* Set the template */
		$tpl = 'edit';

		//add css to document
		$document->addStyleSheet('components/com_redevent/assets/css/redeventbackend.css');
		
		//set the submenu
    ELAdmin::setMenu();
			
		$this->assignRef('user', $user);
		
		parent::display($tpl);
		
	}
	

	function _displayImport($tpl = null)
	{
		$document	= & JFactory::getDocument();
		$document->setTitle(JText::_('COM_REDEVENT_PAGETITLE_TEXTLIBRARY_IMPORT'));
		//add css and submenu to document
		$document->addStyleSheet('components/com_redevent/assets/css/redeventbackend.css');

		//Create Submenu
    ELAdmin::setMenu();

		JHTML::_('behavior.tooltip');

		//create the toolbar
		JToolBarHelper::title( JText::_( 'COM_REDEVENT_PAGETITLE_TEXTLIBRARY_IMPORT' ), 'events' );
		
		JToolBarHelper::back();
		
		$lists = array();
				
		//assign data to template
		$this->assignRef('lists'      	, $lists);
		
		parent::display($tpl);
	}
}
