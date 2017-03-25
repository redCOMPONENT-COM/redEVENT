<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * View class for Venues screen
 *
 * @package  Redevent.admin
 * @since    2.0
 */
class RedeventViewSessions extends RedeventViewAdmin
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 */
	public function display($tpl = null)
	{
		$user = JFactory::getUser();

		$this->items = $this->get('Items');
		$this->state = $this->get('State');
		$this->pagination = $this->get('Pagination');
		$this->filterForm = $this->get('Form');
		$this->activeFilters = $this->get('ActiveFilters');
		$this->event = $this->get('Event');
		$this->user = $user;
		$this->params = RedeventHelper::config();

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

		return parent::display($tpl);
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
		$secondGroup	= new RToolbarButtonGroup;
		$thirdGroup		= new RToolbarButtonGroup;
		$fourthGroup		= new RToolbarButtonGroup;

		if ($user->authorise('core.create', 'com_redevent'))
		{
			$new = RToolbarBuilder::createNewButton('session.add');
			$firstGroup->addButton($new);
		}

		if ($user->authorise('core.edit', 'com_redevent'))
		{
			$edit = RToolbarBuilder::createEditButton('session.edit');

			$secondGroup->addButton($edit);
		}

		if ($user->authorise('core.create', 'com_redevent'))
		{
			$button = RToolbarBuilder::createCopyButton('sessions.copy');
			$secondGroup->addButton($button);
		}

		if ($user->authorise('core.edit.state', 'com_redevent'))
		{
			$publish = RToolbarBuilder::createPublishButton('sessions.publish');
			$thirdGroup->addButton($publish);

			$unPublish = RToolbarBuilder::createUnpublishButton('sessions.unpublish');
			$thirdGroup->addButton($unPublish);

			$archive = RToolbarBuilder::createStandardButton('sessions.archive', JText::_('COM_REDEVENT_ARCHIVE'), '', 'icon-archive', true);
			$thirdGroup->addButton($archive);

			$button = RToolbarBuilder::createStandardButton('sessions.feature', JText::_('COM_REDEVENT_FEATURE'), '', 'icon-star', true);
			$thirdGroup->addButton($button);

			$button = RToolbarBuilder::createStandardButton('sessions.unfeature', JText::_('COM_REDEVENT_UNFEATURE'), '', 'icon-star-empty', true);
			$thirdGroup->addButton($button);
		}

		if ($user->authorise('core.delete', 'com_redevent'))
		{
			$delete = RToolbarBuilder::createDeleteButton('sessions.delete');

			$fourthGroup->addButton($delete);
		}

		$toolbar = new RToolbar;
		$toolbar->addGroup($firstGroup)->addGroup($secondGroup)->addGroup($thirdGroup)->addGroup($fourthGroup);

		$this->toolbar = $toolbar;

		return parent::getToolbar();
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
		if ($this->getLayout() == 'element')
		{
			return JText::_('COM_REDEVENT_SELECT_SESSION');
		}

		// Set toolbar items for the page
		if ($this->event)
		{
			return JText::sprintf('COM_REDEVENT_PAGETITLE_SESSIONS_EVENT', $this->event->title);
		}
		else
		{
			return JText::_('COM_REDEVENT_PAGETITLE_SESSIONS');
		}
	}

	/**
	 * returns toggle image link for session feature
	 * Kept here for b/c
	 *
	 * @param   object  $row  item data
	 * @param   int     $i    row number
	 *
	 * @return string html
	 */
	public function featured($row, $i)
	{
		return RedeventHtmlSessions::featured($row, $i, $this->canEditState);
	}

	/**
	 * returns toggle image link for session publish state
	 * Kept here for b/c
	 *
	 * @param   object  $row  item data
	 * @param   int     $i    row number
	 *
	 * @return string html
	 */
	public function published($row, $i)
	{
		return RedeventHtmlSessions::published($row, $i, $this->canEditState);
	}
}
