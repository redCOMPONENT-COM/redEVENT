<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * View class for registrations list
 *
 * @package  Redevent.admin
 * @since    2.5
 */
class RedeventViewRegistrations extends RedeventViewAdmin
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
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

		if ($user->authorise('core.edit', 'com_redevent'))
		{
			$this->canEdit = true;
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
		return JText::_('COM_REDEVENT_PAGETITLE_REGISTRATIONS');
	}

	/**
	 * Get the tool-bar to render.
	 *
	 * @return  RToolbar
	 */
	public function getToolbar()
	{
		$user = JFactory::getUser();

		$firstGroup		= new RToolbarButtonGroup;

		if ($user->authorise('core.edit', 'com_redevent'))
		{
			if ($this->state->get('filter.cancelled') == 1)
			{
				$restore = RToolbarBuilder::createStandardButton('registrations.uncancelreg', 'COM_REDEVENT_ATTENDEES_TOOLBAR_RESTORE', '', ' icon-circle-arrow-left');
				$firstGroup->addButton($restore);

				$delete = RToolbarBuilder::createDeleteButton('registrations.delete');
				$firstGroup->addButton($delete);
			}

			if ($this->state->get('filter.cancelled') == 0)
			{
				$cancel = RToolbarBuilder::createCancelButton('registrations.cancelreg', 'COM_REDEVENT_ATTENDEES_TOOLBAR_CANCEL');
				$firstGroup->addButton($cancel);
			}
		}

		$toolbar = new RToolbar;
		$toolbar->addGroup($firstGroup);

		return $toolbar;
	}
}
