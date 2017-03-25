<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * View class for Events screen
 *
 * @package  Redevent.admin
 * @since    0.9
 */
class RedeventViewEvents extends RedeventViewAdmin
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
		$this->eventvenues = $this->get('eventvenues');
		$this->state = $this->get('State');
		$this->pagination = $this->get('Pagination');
		$this->filterForm = $this->get('Form');
		$this->activeFilters = $this->get('ActiveFilters');
		$this->user = JFactory::getUser();

		// Fields ordering
		$this->ordering = array();

		if ($this->items)
		{
			foreach ($this->items as &$item)
			{
				$this->ordering[0][] = $item->id;
			}
		}

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
	 * Get the page title
	 *
	 * @return  string  The title to display
	 *
	 * @since   0.9.1
	 */
	public function getTitle()
	{
		return JText::_('COM_REDEVENT_PAGETITLE_EVENTS');
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
		$thirdGroup = new RToolbarButtonGroup;
		$fourthGroup = new RToolbarButtonGroup;

		if ($user->authorise('core.create', 'com_redevent'))
		{
			$new = RToolbarBuilder::createNewButton('event.add');
			$firstGroup->addButton($new);
		}

		if ($user->authorise('core.edit', 'com_redevent'))
		{
			$edit = RToolbarBuilder::createEditButton('event.edit');
			$firstGroup->addButton($edit);

			$importExport = RToolbarBuilder::createStandardButton(
				'eventscsv.edit', JText::_('COM_REDEVENT_BUTTON_IMPORTEXPORT'), '', 'icon-table', false
			);
			$fourthGroup->addButton($importExport);
		}

		if ($user->authorise('core.create', 'com_redevent'))
		{
			$copy = RToolbarBuilder::createCopyButton('events.copy');
			$fourthGroup->addButton($copy);
		}

		if ($user->authorise('core.edit.state', 'com_redevent'))
		{
			$publish = RToolbarBuilder::createPublishButton('events.publish');
			$secondGroup->addButton($publish);

			$unPublish = RToolbarBuilder::createUnpublishButton('events.unpublish');
			$secondGroup->addButton($unPublish);

			$button = RToolbarBuilder::createStandardButton('events.archive', JText::_('COM_REDEVENT_ARCHIVE'), '', 'icon-archive', true);
			$thirdGroup->addButton($button);

			$button = RToolbarBuilder::createStandardButton(
				'events.archivepast', JText::_('COM_REDEVENT_ARCHIVE_OLD_EVENTS'), '', 'icon-archive', true
			);
			$thirdGroup->addButton($button);
		}

		if ($user->authorise('core.delete', 'com_redevent'))
		{
			$delete = RToolbarBuilder::createDeleteButton('events.delete');
			$firstGroup->addButton($delete);
		}

		$toolbar = new RToolbar;
		$toolbar->addGroup($firstGroup)->addGroup($secondGroup)->addGroup($thirdGroup)->addGroup($fourthGroup);

		$this->toolbar = $toolbar;

		return parent::getToolbar();
	}

	/**
	 * returns toggle image link for event publish state
	 *
	 * @param   object  $row  item data
	 * @param   int     $i    row number
	 *
	 * @return string html
	 */
	public function published($row, $i)
	{
		$states = array(
			1 => array('unpublish', 'JPUBLISHED', 'JLIB_HTML_UNPUBLISH_ITEM', 'JPUBLISHED', false, 'ok-sign icon-green', 'ok-sign icon-green'),
			0 => array('publish', 'JUNPUBLISHED', 'JLIB_HTML_PUBLISH_ITEM', 'JUNPUBLISHED', false, 'remove icon-red', 'remove icon-red'),
			-1 => array('unpublish', 'JARCHIVED', 'JLIB_HTML_UNPUBLISH_ITEM', 'JARCHIVED', false, 'hdd', 'hdd'),
		);

		return JHtml::_('rgrid.state', $states, $row->published, $i, 'events.', $this->canEditState, true);
	}
}
