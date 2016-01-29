tmpl<?php
/**
 * @package     Redeventsync
 * @subpackage  Admin
 * @copyright   Redeventsync (C) 2008-2015 Julien Vonthron. All rights reserved.
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die();

/**
 * HTML View class for Redeventsync queuedmessage edit
 *
 * @package     Redeventsync
 * @subpackage  Admin
 * @since       3.0
 */
class RedeventsyncViewQueuedmessage extends ResyncViewAdmin
{
	/**
	 * @var  boolean
	 */
	protected $displaySidebar = false;

	/**
	 * Display the edit page
	 *
	 * @param   string  $tpl  The template file to use
	 *
	 * @return   string
	 */
	public function display($tpl = null)
	{
		$user = JFactory::getUser();

		$this->form = $this->get('Form');
		$this->item = $this->get('Item');

		$this->canConfig = false;

		if ($user->authorise('core.admin', 'com_redeventsync'))
		{
			$this->canConfig = true;
		}

		// Display the template
		parent::display($tpl);
	}

	/**
	 * Get the view title.
	 *
	 * @return  string  The view title.
	 */
	public function getTitle()
	{
		$subTitle = ' <small>' . JText::_('COM_REDEVENTSYNC_NEW') . '</small>';

		if ($this->item->id)
		{
			$subTitle = ' <small>' . JText::_('COM_REDEVENTSYNC_EDIT') . '</small>';
		}

		return JText::_('COM_REDEVENTSYNC_PAGETITLE_EDIT_QUEUEDMESSAGE') . $subTitle;
	}

	/**
	 * Get the toolbar to render.
	 *
	 * @return  RToolbar
	 */
	public function getToolbar()
	{
		$group = new RToolbarButtonGroup;

		$save = RToolbarBuilder::createSaveButton('queuedmessage.apply');
		$saveAndClose = RToolbarBuilder::createSaveAndCloseButton('queuedmessage.save');
		$saveAndNew = RToolbarBuilder::createSaveAndNewButton('queuedmessage.save2new');
		$save2Copy = RToolbarBuilder::createSaveAsCopyButton('queuedmessage.save2copy');

		$group->addButton($save)
			->addButton($saveAndClose)
			->addButton($saveAndNew)
			->addButton($save2Copy);

		if (empty($this->item->id))
		{
			$cancel = RToolbarBuilder::createCancelButton('queuedmessage.cancel');
		}
		else
		{
			$cancel = RToolbarBuilder::createCloseButton('queuedmessage.cancel');
		}

		$group->addButton($cancel);

		$toolbar = new RToolbar;
		$toolbar->addGroup($group);

		return $toolbar;
	}
}
