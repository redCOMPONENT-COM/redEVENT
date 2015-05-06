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
		$this->params = JComponentHelper::getParams('com_redform');

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
			$copy = RToolbarBuilder::createCopyButton('session.copy');

			$firstGroup->addButton($new);
			$firstGroup->addButton($copy);
		}

		if ($user->authorise('core.edit', 'com_redevent'))
		{
			$edit = RToolbarBuilder::createEditButton('session.edit');

			$secondGroup->addButton($edit);
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

		return $toolbar;
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
	 *
	 * @param   object  $row  item data
	 * @param   int     $i    row number
	 *
	 * @return string html
	 */
	public function featured($row, $i)
	{
		$states = array(
			1 => array('unfeature', 'COM_REDEVENT_FEATURED', 'COM_REDEVENT_SESSION_UNFEATURE_SESSION', '', false, 'star', 'star'),
			0 => array('feature', '', 'COM_REDEVENT_SESSION_FEATURE_SESSION', '', false, 'star-empty', 'star-empty'),
		);

		return JHtml::_('rgrid.state', $states, $row->featured, $i, 'sessions.', $this->canEditState, true);
	}

	/**
	 * returns toggle image link for session publish state
	 *
	 * @param   object  $row  item data
	 * @param   int     $i    row number
	 *
	 * @return string html
	 */
	public function published($row, $i)
	{
		$states = array(1 => array('unpublish', 'JPUBLISHED', 'JLIB_HTML_UNPUBLISH_ITEM', 'JPUBLISHED', false, 'ok-sign icon-green', 'ok-sign icon-green'),
			0 => array('publish', 'JUNPUBLISHED', 'JLIB_HTML_PUBLISH_ITEM', 'JUNPUBLISHED', false, 'remove icon-red', 'remove icon-red'),
			-1 => array('unpublish', 'JARCHIVED', 'JLIB_HTML_UNPUBLISH_ITEM', 'JARCHIVED', false, 'hdd', 'hdd'),
		);

		return JHtml::_('rgrid.state', $states, $row->published, $i, 'sessions.', $this->canEditState, true);
	}
}
