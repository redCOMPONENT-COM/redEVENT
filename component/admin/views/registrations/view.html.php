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
		$this->params = RedeventHelper::config();
		$this->return = base64_encode('index.php?option=com_redevent&view=registrations');

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
			$cancel = RToolbarBuilder::createCancelButton('registrations.cancelreg', '', 'btn-warning');
			$firstGroup->addButton($cancel);

			$restore = RToolbarBuilder::createStandardButton(
				'registrations.uncancelreg', 'COM_REDEVENT_ATTENDEES_TOOLBAR_RESTORE', 'btn-success', ' icon-circle-arrow-left'
			);
			$firstGroup->addButton($restore);

			$delete = RToolbarBuilder::createDeleteButton('registrations.delete');
			$firstGroup->addButton($delete);
		}

		$toolbar = new RToolbar;
		$toolbar->addGroup($firstGroup);

		$this->toolbar = $toolbar;

		return parent::getToolbar();
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

		return JHtml::_('rgrid.state', $states, $row->confirmed, $i, 'registrations.', $this->canEdit, true);
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
			0 => array('onwaiting', '', 'COM_REDEVENT_REGISTRATION_CURRENTLY_ATTENDING',
				'COM_REDEVENT_REGISTRATION_CLICK_TO_PUT_ON_WAITING_LIST', false, 'user', 'user'
			),
		);

		return JHtml::_('rgrid.state', $states, $row->waitinglist, $i, 'registrations.', $this->canEdit, true);
	}
}
