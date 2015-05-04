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
 * View class for the EventList eventelement screen
 *
 * @package Joomla
 * @subpackage redEVENT
 * @since 0.9
 */
class RedEventViewEventelement extends JView {

	function display($tpl = null)
	{
		$app = JFactory::getApplication();
		$option = JRequest::getCmd('option');

		//initialise variables
		$db			= JFactory::getDBO();
		$elsettings = JComponentHelper::getParams('com_redevent');
		$document	= JFactory::getDocument();
		$fieldname = JRequest::getVar('field');

		JHTML::_('behavior.tooltip');
		JHTML::_('behavior.modal');

		//get var
		$filter_order		= $app->getUserStateFromRequest( $option.'.eventelement.filter_order', 'filter_order', 'x.dates', 'cmd' );
		$filter_order_Dir	= $app->getUserStateFromRequest( $option.'.eventelement.filter_order_Dir', 'filter_order_Dir', '', 'word' );
		$filter 			= $app->getUserStateFromRequest( $option.'.eventelement.filter', 'filter', '', 'int' );
		$filter_state 		= $app->getUserStateFromRequest( $option.'.eventelement.filter_state', 'filter_state', '*', 'word' );
		$search 			= $app->getUserStateFromRequest( $option.'.eventelement.search', 'search', '', 'string' );
		$search 			= $db->escape( trim(JString::strtolower( $search ) ) );
		$template 			= $app->getTemplate();

		//prepare the document
		$document->setTitle(JText::_('COM_REDEVENT_SELECTEVENT' ));
		$document->addStyleSheet('templates/'.$template.'/css/general.css');

		RHelperAsset::load('backend.css');

		//Get data from the model
		$rows      	= & $this->get( 'Data');
//		$total      = & $this->get( 'Total');
		$pageNav 	= & $this->get( 'Pagination' );

		//publish unpublished filter
		$lists['state']	= JHTML::_('grid.state', $filter_state );

		// table ordering
		$lists['order_Dir'] = $filter_order_Dir;
		$lists['order'] = $filter_order;

		//Create the filter selectlist
		$filters = array();
		$filters[] = JHTML::_('select.option', '1', JText::_('COM_REDEVENT_EVENT_TITLE' ) );
		$filters[] = JHTML::_('select.option', '2', JText::_('COM_REDEVENT_VENUE' ) );
		$filters[] = JHTML::_('select.option', '3', JText::_('COM_REDEVENT_CITY' ) );
		$filters[] = JHTML::_('select.option', '4', JText::_('COM_REDEVENT_CATEGORY' ) );
		$lists['filter'] = JHTML::_('select.genericlist', $filters, 'filter', 'size="1" class="inputbox"', 'value', 'text', $filter );

		// search filter
		$lists['search']= $search;

		//assign data to template
		$this->assignRef('lists'      	, $lists);
		$this->assignRef('rows'      	, $rows);
		$this->assignRef('pageNav' 		, $pageNav);
		$this->assignRef('elsettings'	, $elsettings);
		$this->assign('function',  $app->input->getCmd('function'));

		parent::display($tpl);
	}
}
