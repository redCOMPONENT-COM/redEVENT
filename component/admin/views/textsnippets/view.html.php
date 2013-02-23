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

/**
 * View class for the EventList category screen
 *
 * @package Joomla
 * @subpackage redEVENT
 * @since 0.9
 */
class RedEventViewTextsnippets extends FOFView {

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
