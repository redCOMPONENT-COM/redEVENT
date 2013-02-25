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
		
		JToolBarHelper::title(JText::_('COM_REDEVENT_MENU_ROLES'), 'roles');
		JToolBarHelper::addNewX();
		JToolBarHelper::editListX();
		JToolBarHelper::deleteList();
		if (JFactory::getUser()->authorise('core.admin', 'com_redevent')) {
			JToolBarHelper::preferences('com_redevent', '600', '600');
		}
	}
	
	/**
	 * Renders the toolbar for the component's Textsnippets page
	 */
	public function onTextsnippetsBrowse()
	{
		//on frontend, buttons must be added specifically
		list($isCli, $isAdmin) = FOFDispatcher::isCliAdmin();
	
		if($isAdmin || $this->renderFrontendSubmenu) {
			$this->renderSubmenu();
		}
	
		if(!$isAdmin && !$this->renderFrontendButtons) return;
		
		JToolBarHelper::title(JText::_('COM_REDEVENT_MENU_ROLES'), 'roles');
		JToolBarHelper::addNewX();
		JToolBarHelper::editListX();
		JToolBarHelper::custom('export', 'csvexport', 'csvexport', JText::_('COM_REDEVENT_BUTTON_EXPORT'), false);
		JToolBarHelper::custom('import', 'csvimport', 'csvimport', JText::_('COM_REDEVENT_BUTTON_IMPORT'), false);		
		JToolBarHelper::deleteList();
		if (JFactory::getUser()->authorise('core.admin', 'com_redevent')) {
			JToolBarHelper::preferences('com_redevent', '600', '600');
		}
	}
	
	public function onBrowse()
	{
		parent::onBrowse();

		// Set toolbar title
		$option = 'com_redevent';
		$subtitle_key = strtoupper($option . '_TITLE_' . $this->input->getCmd('view', 'cpanel'));
		JToolBarHelper::title(JText::_(strtoupper($option)) . ' &ndash; <small>' . JText::_($subtitle_key) . '</small>', $this->input->getCmd('view', 'cpanel'));		
	}

	public function onRead()
	{
		parent::onRead();
		
		// Set toolbar title
		$option = 'com_redevent';
		$subtitle_key = strtoupper($option . '_TITLE_' . $this->input->getCmd('view', 'cpanel') . '_READ');
		JToolBarHelper::title(JText::_(strtoupper($option)) . ' &ndash; <small>' . JText::_($subtitle_key) . '</small>', FOFInflector::pluralize($this->input->getCmd('view', 'cpanel')));
	}

	public function onAdd()
	{
		parent::onAdd();
		
		// Set toolbar title
		$option = 'com_redevent';
		// Set toolbar title
		$subtitle_key = strtoupper($option . '_TITLE_' . FOFInflector::pluralize($this->input->getCmd('view', 'cpanel'))) . '_EDIT';
		JToolBarHelper::title(JText::_(strtoupper($option)) . ' &ndash; <small>' . JText::_($subtitle_key) . '</small>', FOFInflector::pluralize($this->input->getCmd('view', 'cpanel')));
	}
}