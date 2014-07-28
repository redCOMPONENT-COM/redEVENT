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

class RedeventsyncToolbar extends FOFToolbar
{
	/**
	 * Renders the toolbar for the component's sync page
	 *
	 * @return void
	 */
	public function onSyncsBrowse()
	{
		// On frontend, buttons must be added specifically
		list($isCli, $isAdmin) = FOFDispatcher::isCliAdmin();

		if ($isAdmin || $this->renderFrontendSubmenu)
		{
			$this->renderSubmenu();
		}

		if (!$isAdmin && !$this->renderFrontendButtons)
		{
			return;
		}

		JToolBarHelper::title(JText::_('COM_REDEVENTSYNC_MENU_SYNC'), 'redeventsync');
		JToolBarHelper::back();
	}

	/**
	 * Renders the toolbar for the component's sync page
	 *
	 * @return void
	 */
	public function onLogsBrowse()
	{
		parent::onBrowse();
		JToolBarHelper::custom('clear', 'trash', 'trash', Jtext::_('COM_REDEVENTSYNC_BUTTON_LOGS_CLEAR'), false);
	}

	public function onQueuedMessagesBrowse()
	{
		$this->renderSubmenu();

		// Set toolbar title
		$option = $this->input->getCmd('option', 'com_foobar');
		$subtitle_key = strtoupper($option . '_TITLE_' . $this->input->getCmd('view', 'cpanel'));
		JToolBarHelper::title(JText::_(strtoupper($option)) . ' &ndash; <small>' . JText::_($subtitle_key) . '</small>', str_replace('com_', '', $option));

		JToolbarHelper::custom('process', 'sendqm', 'sendqm', JText::_('COM_REDEVENTSYNC_BUTTON_QUEUED_MESSAGES_SEND'), true);
		JToolbarHelper::deleteList();
	}
}
