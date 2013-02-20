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

class RedeventToolbar extends FOFToolbar
{	
	protected function renderSubmenu()
	{
		//Create Submenu
		ELAdmin::setMenu();
	} 
	
	/**
	 * Renders the toolbar for the component's Roles page
	 */
	public function onRolesBrowse()
	{
		//on frontend, buttons must be added specifically
		list($isCli, $isAdmin) = FOFDispatcher::isCliAdmin();
	
		if($isAdmin || $this->renderFrontendSubmenu) {
			$this->renderSubmenu();
		}
	
		if(!$isAdmin && !$this->renderFrontendButtons) return;
	
		$option = $this->input->getCmd('option','com_redevent');
	
		JToolBarHelper::title(JText::_('COM_REDEVENT_MENU_ROLES'), 'roles');
		JToolBarHelper::addNewX();
		JToolBarHelper::editListX();
		JToolBarHelper::deleteList();
		if (JFactory::getUser()->authorise('core.admin', 'com_redevent')) {
			JToolBarHelper::preferences('com_redevent', '600', '600');
		}
	}
}