<?php
/**
 * @package    Redeventsync.admin
 *
 * @copyright  Copyright (C) 2013 - 2016 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die();

/**
 * HTML View class for Redeventsync queuedmessage edit
 *
 * @package     Redeventsync
 * @subpackage  Admin
 * @since       3.0
 */
class RedeventsyncViewQueuedmessages extends ResyncView
{
	/**
	 * Display the page
	 *
	 * @param   string  $tpl  The template file to use
	 *
	 * @return   string
	 */
	public function display($tpl = null)
	{
		$user = JFactory::getUser();

		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->filterForm = $this->get('Form');
		$this->activeFilters = $this->get('ActiveFilters');
		$this->state = $this->get('State');

		// Edit permission
		$this->canEdit = false;

		if ($user->authorise('core.edit', 'com_redeventsync'))
		{
			$this->canEdit = true;
		}

		return parent::display($tpl);
	}

	/**
	 * Get the view title.
	 *
	 * @return  string  The view title.
	 */
	public function getTitle()
	{
		return JText::_('COM_REDEVENTSYNC_PAGETITLE_VIEW_QUEUEDMESSAGES');
	}

	/**
	 * Get the toolbar to render.
	 *
	 * @return  RToolbar
	 */
	public function getToolbar()
	{
		$user = JFactory::getUser();

		$firstGroup = new RToolbarButtonGroup;
		$secondGroup = new RToolbarButtonGroup;

		if ($user->authorise('core.manage', 'com_redeventsync'))
		{
			$firstGroup->addButton(
				RToolbarBuilder::createStandardButton(
					'queuedmessages.process', JText::_('COM_REDEVENTSYNC_BUTTON_QUEUED_MESSAGES_SEND'), 'btn', '', true
				)
			);
		}

		if ($user->authorise('core.create', 'com_redeventsync'))
		{
			$new = RToolbarBuilder::createNewButton('queuedmessage.add');
			$secondGroup->addButton($new);
		}

		if ($user->authorise('core.edit', 'com_redeventsync'))
		{
			$edit = RToolbarBuilder::createEditButton('queuedmessage.edit');
			$secondGroup->addButton($edit);
		}

		if ($user->authorise('core.delete', 'com_redeventsync'))
		{
			$delete = RToolbarBuilder::createDeleteButton('queuedmessages.delete');
			$secondGroup->addButton($delete);
		}

		$toolbar = new RToolbar;
		$toolbar->addGroup($firstGroup)->addGroup($secondGroup);

		return $toolbar;
	}
}
