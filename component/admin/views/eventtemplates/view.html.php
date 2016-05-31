<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * View class for Event templates screen
 *
 * @package  Redevent.admin
 * @since    3.1
 */
class RedeventViewEventtemplates extends RedeventViewAdmin
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 *
	 * @see     fetch()
	 * @since   11.1
	 */
	public function display($tpl = null)
	{
		$user = JFactory::getUser();

		$this->items = $this->get('Items');
		$this->state = $this->get('State');
		$this->pagination = $this->get('Pagination');
		$this->filterForm = $this->get('Form');
		$this->activeFilters = $this->get('ActiveFilters');
		$this->user = JFactory::getUser();

		// Edit permission
		$this->canEdit = false;

		if ($user->authorise('core.edit', 'com_redevent'))
		{
			$this->canEdit = true;
		}

		// Edit state permission
		$this->canEditState = false;

		if ($user->authorise('core.edit.state', 'com_redevent'))
		{
			$this->canEditState = true;
		}

		parent::display($tpl);
	}

	/**
	 * Get the page title
	 *
	 * @return  string  The title to display
	 *
	 * @since   0.9.1
	 */
	public function getTitle()
	{
		return JText::_('COM_REDEVENT_PAGETITLE_EVENTTEMPLATES');
	}

	/**
	 * Get the tool-bar to render.
	 *
	 * @return  RToolbar
	 */
	public function getToolbar()
	{
		$user = JFactory::getUser();

		$firstGroup = new RToolbarButtonGroup;
		$secondGroup = new RToolbarButtonGroup;

		if ($user->authorise('core.create', 'com_redevent'))
		{
			$new = RToolbarBuilder::createNewButton('eventtemplate.add');
			$firstGroup->addButton($new);
		}

		if ($user->authorise('core.edit', 'com_redevent'))
		{
			$edit = RToolbarBuilder::createEditButton('eventtemplate.edit');
			$firstGroup->addButton($edit);

			$importExport = RToolbarBuilder::createStandardButton('eventtemplatescsv.edit', JText::_('COM_REDEVENT_BUTTON_IMPORTEXPORT'), '', 'icon-table', false);
			$secondGroup->addButton($importExport);
		}

		if ($user->authorise('core.delete', 'com_redevent'))
		{
			$delete = RToolbarBuilder::createDeleteButton('eventtemplates.delete');
			$firstGroup->addButton($delete);
		}

		$toolbar = new RToolbar;
		$toolbar->addGroup($firstGroup)->addGroup($secondGroup);

		return $toolbar;
	}
}
