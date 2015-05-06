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
class RedEventViewSelectUser extends JView {

	function display($tpl = null)
	{
		$app = JFactory::getApplication();

		//initialise variables
		$document	= JFactory::getDocument();
		$fieldname = JRequest::getVar('field');

		JHTML::_('behavior.tooltip');
		JHTML::_('behavior.modal');

		$state = $this->get('state');

		//get var
		$filter_order     = $state->get('filter_order');
		$filter_order_Dir = $state->get('filter_order_Dir');
		$search           = $state->get('search');

		//prepare the document
		$document->setTitle(JText::_( 'COM_REDEVENT_SELECT_USER' ));
		RHelperAsset::load('backend.css');

		//Get data from the model
		$rows      	= & $this->get( 'Data');
		$pageNav 	= & $this->get( 'Pagination' );

		$lists = array();

		// table ordering
		$lists['order_Dir'] = $filter_order_Dir;
		$lists['order']     = $filter_order;

		// search filter
		$lists['search']= $search;

		//assign data to template
		$this->assignRef('lists',   $lists);
		$this->assignRef('rows',    $rows);
		$this->assignRef('pageNav', $pageNav);
		$this->assign('field',      $fieldname);

		parent::display($tpl);
	}

}//end class
