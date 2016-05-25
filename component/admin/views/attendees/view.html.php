<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * View class for Attendees screen
 *
 * @package  Redevent.admin
 * @since    0.9
 */
class RedeventViewAttendees extends RedeventViewAdmin
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
		$this->session = $this->get('session');
		$this->redformFields = $this->get('RedformFields');
		$this->selectedRedformFields = $this->get('SelectedFrontRedformFields');
		$this->pagination = $this->get('Pagination');
		$this->filterForm = $this->get('Form');
		$this->activeFilters = $this->get('ActiveFilters');
		$this->state = $this->get('State');
		$this->params = JComponentHelper::getParams('com_redevent');

		// Edit permission
		$this->canEdit = false;

		if ($user->authorise('core.edit', 'com_redevent'))
		{
			$this->canEdit = true;
		}

		// We need to do this when ony the get parameter &session is set.
		if ($this->session)
		{
			$this->filterForm->setValue('session', 'filter', $this->session->xref);
			$this->filterForm->setFieldAttribute('session', 'event', $this->session->eventid, 'filter');
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
		return JText::sprintf('COM_REDEVENT_PAGETITLE_ATTENDEES', $this->session->title);
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
		$secondGroup		= new RToolbarButtonGroup;

		if ($user->authorise('core.edit', 'com_redevent'))
		{
			$firstGroup->addButton(
				RToolbarBuilder::createStandardButton(
					'emailattendees.emailall', 'COM_REDEVENT_ATTENDEES_TOOLBAR_EMAIL_ALL', 'send', 'icon-email', false
				)
			);
			$firstGroup->addButton(
				RToolbarBuilder::createStandardButton(
					'emailattendees.email', 'COM_REDEVENT_ATTENDEES_TOOLBAR_EMAIL_SELECTED', 'send', 'icon-email'
				)
			);
			$firstGroup->addButton(
				RToolbarBuilder::createCsvButton()
			);

			$secondGroup->addButton(
				RToolbarBuilder::createNewButton('attendee.add')
			);
			$secondGroup->addButton(
				RToolbarBuilder::createEditButton('attendee.edit')
			);
			$secondGroup->addButton(
				RToolbarBuilder::createStandardButton('attendees.move', 'COM_REDEVENT_ATTENDEES_TOOLBAR_MOVE', '', 'icon-move')
			);

			if ($this->state->get('filter.cancelled') == 1)
			{
				$restore = RToolbarBuilder::createStandardButton(
					'attendees.uncancelreg', 'COM_REDEVENT_ATTENDEES_TOOLBAR_RESTORE', '', ' icon-circle-arrow-left'
				);
				$secondGroup->addButton($restore);

				$delete = RToolbarBuilder::createDeleteButton('attendees.delete');
				$secondGroup->addButton($delete);
			}

			if ($this->state->get('filter.cancelled') == 0)
			{
				$cancel = RToolbarBuilder::createCancelButton('attendees.cancelreg', 'COM_REDEVENT_ATTENDEES_TOOLBAR_CANCEL');
				$secondGroup->addButton($cancel);
			}
		}

		$toolbar = new RToolbar;
		$toolbar->addGroup($firstGroup)->addGroup($secondGroup);

		return $toolbar;
	}

	/**
	 * returns toggle image link for session feature
	 *
	 * @param   object  $row  item data
	 * @param   int     $i    row number
	 *
	 * @return string html
	 */
	public function confirmed($row, $i)
	{
		$states = array(
			1 => array('unconfirm', 'COM_REDEVENT_REGISTRATION_ACTIVATED',
				Jtext::sprintf('COM_REDEVENT_REGISTRATION_ACTIVATED_ON_S',
					JHTML::Date($row->confirmdate, JText::_('COM_REDEVENT_JDATE_FORMAT_DATETIME'))
				)
			, '', false, 'ok', 'ok'),
			0 => array('confirm', '', 'COM_REDEVENT_REGISTRATION_NOT_ACTIVATED', 'COM_REDEVENT_CLICK_TO_ACTIVATE', false, 'remove', 'remove'),
		);

		return JHtml::_('rgrid.state', $states, $row->confirmed, $i, 'attendees.', $this->canEdit, true);
	}

	/**
	 * returns toggle image link for session feature
	 *
	 * @param   object  $row  item data
	 * @param   int     $i    row number
	 *
	 * @return string html
	 */
	public function waitingStatus($row, $i)
	{
		$states = array(
			1 => array('offwaiting', 'COM_REDEVENT_REGISTRATION_CURRENTLY_ON_WAITING_LIST',
				Jtext::sprintf('COM_REDEVENT_REGISTRATION_CLICK_TO_TAKE_OFF_WAITING_LIST',
					JHTML::Date($row->confirmdate, JText::_('COM_REDEVENT_JDATE_FORMAT_DATETIME'))
				)
			, '', false, 'time', 'time'),
			0 => array(
				'onwaiting', '', 'COM_REDEVENT_REGISTRATION_CURRENTLY_ATTENDING',
				'COM_REDEVENT_REGISTRATION_CLICK_TO_PUT_ON_WAITING_LIST', false, 'user', 'user'
			),
		);

		return JHtml::_('rgrid.state', $states, $row->waitinglist, $i, 'attendees.', $this->canEdit, true);
	}

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 */
	public function _displayprint($tpl = null)
	{
		$elsettings = JComponentHelper::getParams('com_redevent');
		RHelperAsset::load('redevent-backend.css');

		$rows = $this->get('Data');
		$event = $this->get('Event');
		$rf_fields = $this->get('RedFormFrontFields');
		$form = $this->get('Form');

		$event->dates = RedeventHelperDate::isValidDate($event->dates)
			? strftime($elsettings->get('backend_formatdate', '%d.%m.%Y'), strtotime($event->dates))
			: JText::_('COM_REDEVENT_OPEN_DATE');

		// Assign data to template
		$this->assignRef('rows', $rows);
		$this->assignRef('event', $event);
		$this->assignRef('rf_fields', $rf_fields);
		$this->assignRef('form',      $form);

		parent::display($tpl);
	}
}
