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
class RedeventViewCustomfields extends FOFView
{

	function display($tpl = null)
	{	
		if ($this->getLayout() == 'import') {
			return $this->_displayImport($tpl);
		}		
				
		parent::display($tpl);
	}

	function _displayImport($tpl = null)
	{
		$document	= & JFactory::getDocument();
		$document->setTitle(JText::_('COM_REDEVENT_PAGETITLE_CUSTOMFIELDS_IMPORT'));
		//add css to document
		FOFTemplateUtils::addJS("media://com_redevent/css/backend.less||media://com_redevent/css/backend.css");

		//Create Submenu
		ELAdmin::setMenu();

		JHTML::_('behavior.tooltip');

		//create the toolbar
		JToolBarHelper::title( JText::_( 'COM_REDEVENT_PAGETITLE_CUSTOMFIELDS_IMPORT' ), 'events' );

		JToolBarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_redevent&view=customfields');
		
		$lists = array();
				
		//assign data to template
		$this->assignRef('lists'      	, $lists);
		
		parent::display($tpl);
	}
}
