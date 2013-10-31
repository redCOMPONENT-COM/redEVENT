<?php
/**
 * @package    Redevent.Admin
 * @copyright  redEVENT (C) 2008-2013 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Class RedeventToolbar
 *
 * @package  Redevent.Admin
 * @since    2.5
 */
class RedeventToolbar extends FOFToolbar
{
	/**
	 * Renders the submenu (toolbar links) for all detected views of this component
	 *
	 * @return  void
	 */
	public function renderSubmenu()
	{
		// Create Submenu
		ELAdmin::setMenu();
	}

	/**
	 * Renders the toolbar for the component's Roles page
	 *
	 * @return void
	 */
	public function onRolesBrowse()
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

		JToolBarHelper::title(JText::_('COM_REDEVENT_MENU_ROLES'), 'roles');
		JToolBarHelper::addNewX();
		JToolBarHelper::editListX();
		JToolBarHelper::deleteList();

		if (JFactory::getUser()->authorise('core.admin', 'com_redevent'))
		{
			JToolBarHelper::preferences('com_redevent', '600', '600');
		}
	}

	/**
	 * Renders the toolbar for the component's Textsnippets page
	 *
	 * @return void
	 */
	public function onCustomfieldsBrowse()
	{
		$this->onBrowseImportExport();
	}

	/**
	 * Renders the toolbar for the component's Textsnippets page
	 *
	 * @return void
	 */
	public function onTextsnippetsBrowse()
	{
		$this->onBrowseImportExport();
	}

	/**
	 * adds import/export buttons to regular onBrowse toolbar
	 *
	 * @return void
	 */
	protected function onBrowseImportExport()
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

		// Set toolbar title
		$option = 'com_redevent';
		$subtitle_key = strtoupper($option . '_TITLE_' . $this->input->getCmd('view', 'cpanel'));
		JToolBarHelper::title(
			JText::_(strtoupper($option)) . ' &ndash; <small>' . JText::_($subtitle_key) . '</small>',
			$this->input->getCmd('view', 'cpanel')
		);
		JToolBarHelper::addNewX();
		JToolBarHelper::editListX();
		JToolBarHelper::custom('export', 'csvexport', 'csvexport', JText::_('COM_REDEVENT_BUTTON_EXPORT'), false);
		JToolBarHelper::custom('import', 'csvimport', 'csvimport', JText::_('COM_REDEVENT_BUTTON_IMPORT'), false);
		JToolBarHelper::deleteList();

		if (JFactory::getUser()->authorise('core.admin', 'com_redevent'))
		{
			JToolBarHelper::preferences('com_redevent', '600', '600');
		}
	}

	/**
	 * Renders the toolbar for the component's Browse pages (the plural views)
	 *
	 * @return  void
	 */
	public function onBrowse()
	{
		parent::onBrowse();

		// Set toolbar title
		$option = 'com_redevent';
		$subtitle_key = strtoupper($option . '_TITLE_' . $this->input->getCmd('view', 'cpanel'));
		JToolBarHelper::title(
			JText::_(strtoupper($option)) . ' &ndash; <small>' . JText::_($subtitle_key) . '</small>',
			$this->input->getCmd('view', 'cpanel')
		);
	}

	/**
	 * Renders the toolbar for the component's Read pages
	 *
	 * @return  void
	 */
	public function onRead()
	{
		parent::onRead();

		// Set toolbar title
		$option = 'com_redevent';
		$subtitle_key = strtoupper($option . '_TITLE_' . $this->input->getCmd('view', 'cpanel') . '_READ');
		JToolBarHelper::title(
			JText::_(strtoupper($option)) . ' &ndash; <small>' . JText::_($subtitle_key) . '</small>',
			FOFInflector::pluralize($this->input->getCmd('view', 'cpanel'))
		);
	}

	/**
	 * Renders the toolbar for the component's Add pages
	 *
	 * @return  void
	 */
	public function onAdd()
	{
		parent::onAdd();

		// Set toolbar title
		$option = 'com_redevent';

		// Set toolbar title
		$subtitle_key = strtoupper($option . '_TITLE_' . FOFInflector::pluralize($this->input->getCmd('view', 'cpanel'))) . '_EDIT';
		JToolBarHelper::title(
			JText::_(strtoupper($option)) . ' &ndash; <small>' . JText::_($subtitle_key) . '</small>',
			FOFInflector::pluralize($this->input->getCmd('view', 'cpanel'))
		);
	}
}
