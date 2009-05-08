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
		global $mainframe;

		//Load pane behavior
		jimport('joomla.html.pane');

		//initialise variables
		$document	= JFactory::getDocument();
		$user = JFactory::getUser();
		$task = JRequest::getVar('task');
		                  
		switch ($task) {
			case 'edit':
			case 'add':
				/* Set up toolbar */
				if ($task == 'edit') JToolBarHelper::title( JText::_( 'EDIT_TEXT_LIBRARY' ), 'libraryedit' );
				else JToolBarHelper::title( JText::_( 'ADD_TEXT_LIBRARY' ), 'libraryedit' );
				JToolBarHelper::save();
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
				break;
			default:
				if ($task == 'save') {
					$this->get('Save');
				}
				JToolBarHelper::title( JText::_( 'TEXT_LIBRARY' ), 'library' );
				JToolBarHelper::addNew();
				JToolBarHelper::spacer();
				JToolBarHelper::editListX();
				JToolBarHelper::spacer();
				JToolBarHelper::deleteList();
				//Get data from the model
				$rows = $this->get('Data');
				$this->assignRef('rows', $rows);
				
				break;
		}
		
		//get vars
		$cid = JRequest::getVar( 'cid' );

		//add css to document
		$document->addStyleSheet('components/com_redevent/assets/css/redeventbackend.css');

		
		//set the submenu
			JSubMenuHelper::addEntry( JText::_( 'REDEVENT' ), 'index.php?option=com_redevent');
			JSubMenuHelper::addEntry( JText::_( 'EVENTS' ), 'index.php?option=com_redevent&view=events');
			JSubMenuHelper::addEntry( JText::_( 'VENUES' ), 'index.php?option=com_redevent&view=venues');
			JSubMenuHelper::addEntry( JText::_( 'CATEGORIES' ), 'index.php?option=com_redevent&view=categories');
			JSubMenuHelper::addEntry( JText::_( 'ARCHIVESCREEN' ), 'index.php?option=com_redevent&view=archive');
			JSubMenuHelper::addEntry( JText::_( 'GROUPS' ), 'index.php?option=com_redevent&view=groups');
			JSubMenuHelper::addEntry( JText::_( 'TEXT_LIBRARY' ), 'index.php?option=com_redevent&view=textlibrary');
			JSubMenuHelper::addEntry( JText::_( 'HELP' ), 'index.php?option=com_redevent&view=help');
			if ($user->get('gid') > 24) {
				JSubMenuHelper::addEntry( JText::_( 'SETTINGS' ), 'index.php?option=com_redevent&controller=settings&task=edit');
			}
			
		parent::display($tpl);
	}
}
?>