<?php
/**
 * @version 1.0 $Id: archive.php 30 2009-05-08 10:22:21Z roland $
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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view');

/**
 * HTML View class for redevent component
 *
 * @static
 * @package		redevent
 * @since 2.0
 */
class RedeventViewCustomfields extends JView
{
	function display($tpl = null)
	{
		$mainframe = &JFactory::getApplication();
		$option = JRequest::getCmd('option');
	
		if ($this->getLayout() == 'import') {
			return $this->_displayImport($tpl);
		}
		
    $document = & JFactory::getDocument();
    
		//add css to document
		$document->addStyleSheet('components/com_redevent/assets/css/redeventbackend.css');
		
		$document->setTitle(JText::_('COM_REDEVENT_PAGETITLE_CUSTOMFIELDS'));
		
		// Set toolbar items for the page
		JToolBarHelper::title(   JText::_('COM_REDEVENT_Custom_fields' ), 'generic.png' );
		JToolBarHelper::deleteList();
		JToolBarHelper::editListX();
		JToolBarHelper::addNewX();
		JToolBarHelper::custom('export', 'csvexport', 'csvexport', JText::_('COM_REDEVENT_BUTTON_EXPORT'), false);
		JToolBarHelper::custom('import', 'csvimport', 'csvimport', JText::_('COM_REDEVENT_BUTTON_IMPORT'), false);
    JToolBarHelper::help( 'screen.redevent', true );
        
		$db		=& JFactory::getDBO();
		$uri	=& JFactory::getURI();

		$filter_order		= $mainframe->getUserStateFromRequest( $option.'.customfields.filter_order',		'filter_order',		'obj.name',	'cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $option.'.customfields.filter_order_Dir',	'filter_order_Dir',	'ASC',				'word' );
		$search				= $mainframe->getUserStateFromRequest( $option.'.customfields.search', 'search',			'',				'string' );
		$search				= JString::strtolower( $search );

		// Get data from the model
		//$model	=& $this->getModel( );
		//print_r($model);
		$items		= & $this->get( 'Data' );
		$total		= & $this->get( 'Total' );
		$pagination = & $this->get( 'Pagination' );
		
		ELAdmin::setMenu();
		
		$lists = array();
		
		// search filter
		$lists['search']= $search;

		// table ordering
		$lists['order_Dir'] = $filter_order_Dir;
		$lists['order'] = $filter_order;
		
		$this->assignRef('user',		JFactory::getUser());
		$this->assignRef('lists',		$lists);
		$this->assignRef('items',		$items);
		$this->assignRef('pagination',	$pagination);
		$this->assignRef('request_url',	$uri->toString());

		parent::display($tpl);
	}

	function _displayImport($tpl = null)
	{
		$document	= & JFactory::getDocument();
		$document->setTitle(JText::_('COM_REDEVENT_PAGETITLE_CUSTOMFIELDS_IMPORT'));
		//add css and submenu to document
		$document->addStyleSheet('components/com_redevent/assets/css/redeventbackend.css');

		//Create Submenu
    ELAdmin::setMenu();

		JHTML::_('behavior.tooltip');

		//create the toolbar
		JToolBarHelper::title( JText::_( 'COM_REDEVENT_PAGETITLE_CUSTOMFIELDS_IMPORT' ), 'events' );
		
		JToolBarHelper::back();
		
		$lists = array();
				
		//assign data to template
		$this->assignRef('lists'      	, $lists);
		
		parent::display($tpl);
	}
}
?>
