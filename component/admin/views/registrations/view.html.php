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
class RedEventViewRegistrations extends RedeventViewAdmin
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
		$secondGroup	= new RToolbarButtonGroup;

		if ($user->authorise('core.edit', 'com_redevent'))
		{
			$cancel = RToolbarBuilder::createCancelButton('registration.cancel', 'COM_REDEVENT_ATTENDEES_TOOLBAR_CANCEL');
			$firstGroup->addButton($cancel);

			$restore = RToolbarBuilder::createStandardButton('registration.uncancelreg', 'COM_REDEVENT_ATTENDEES_TOOLBAR_RESTORE', '', ' icon-circle-arrow-left');
			$firstGroup->addButton($restore);

			$delete = RToolbarBuilder::createDeleteButton('registration.delete');
			$firstGroup->addButton($delete);
		}

		$back = RToolbarBuilder::createStandardButton('registration.back', 'BACK', '', 'icon-back');
		$secondGroup->addButton($back);

		$toolbar = new RToolbar;
		$toolbar->addGroup($firstGroup)->addGroup($secondGroup);

		return $toolbar;
	}
	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 */
	public function _display($tpl = null)
	{
		$app = JFactory::getApplication();

		//initialise variables
		$settings = JComponentHelper::getParams('com_redevent');
		$document = JFactory::getDocument();
		$user = JFactory::getUser();
		$state = $this->get('State');

		// Get vars
		$filter_order = $state->get('filter_order');
		$filter_order_Dir = $state->get('filter_order_Dir');

		$document->setTitle(JText::_('COM_REDEVENT_PAGETITLE_REGISTRATIONS'));

		// Add css and submenu to document
		FOFTemplateUtils::addCSS('media://com_redevent/css/backend.css');

		// Add javascript
		JHTML::_('behavior.modal', 'a.answersmodal');

		// Create Submenu
		ELAdmin::setMenu();

		// Add toolbar
		JToolBarHelper::title( JText::_( 'COM_REDEVENT_PAGETITLE_REGISTRATIONS' ), 'registrations' );

		if ($state->get('filter_cancelled', 0) == 0)
		{
			JToolBarHelper::custom('cancelreg', 'cancel', 'cancel', 'COM_REDEVENT_ATTENDEES_TOOLBAR_CANCEL', true, true);
		}

		if ($state->get('filter_cancelled', 0) == 1)
		{
			JToolBarHelper::custom('uncancelreg', 'redrestore', 'redrestore', 'COM_REDEVENT_ATTENDEES_TOOLBAR_RESTORE', true, true);
			JToolBarHelper::deleteList(JText::_('COM_REDEVENT_ATTENDEES_DELETE_WARNING'));
		}

		JToolBarHelper::spacer();
		JToolBarHelper::back();

		if ($user->authorise('core.admin', 'com_redevent'))
		{
			JToolBarHelper::preferences('com_redevent', '600', '800');
		}

		// Get data from the model
		$rows      = $this->get( 'Data');
		$pageNav   = $this->get( 'Pagination' );

		// Build filter selectlist
		$filters = array();

		// Confirmed filter
		$options = array(JHTML::_('select.option', 0, JText::_('COM_REDEVENT_ATTENDEES_FILTER_CONFIRMED_ALL')),
		                 JHTML::_('select.option', 1, JText::_('COM_REDEVENT_ATTENDEES_FILTER_CONFIRMED_CONFIRMED')),
		                 JHTML::_('select.option', 2, JText::_('COM_REDEVENT_ATTENDEES_FILTER_CONFIRMED_UNCONFIRMED')),
		                 );
		$lists['filter_confirmed'] =  JHTML::_('select.genericlist', $options, 'filter_confirmed', 'class="inputbox" onchange="this.form.submit();"', 'value', 'text', $state->get('filter_confirmed') );

		// Waiting list filter
		$options = array(JHTML::_('select.option', 0, JText::_('COM_REDEVENT_ATTENDEES_FILTER_WAITING_ALL')),
		                 JHTML::_('select.option', 1, JText::_('COM_REDEVENT_ATTENDEES_FILTER_WAITING_ATTENDING')),
		                 JHTML::_('select.option', 2, JText::_('COM_REDEVENT_ATTENDEES_FILTER_WAITING_WAITING')),
		                 );
		$lists['filter_waiting'] =  JHTML::_('select.genericlist', $options, 'filter_waiting', 'class="inputbox" onchange="this.form.submit();"', 'value', 'text', $state->get('filter_waiting') );

		// Cancelled filter
		$options = array(JHTML::_('select.option', 0, JText::_('COM_REDEVENT_ATTENDEES_FILTER_CANCELLED_NOT_CANCELLED')),
		                 JHTML::_('select.option', 1, JText::_('COM_REDEVENT_ATTENDEES_FILTER_CANCELLED_CANCELLED')),
		                 JHTML::_('select.option', 2, JText::_('COM_REDEVENT_ATTENDEES_FILTER_CANCELLED_ALL')),
		                 );
		$lists['filter_cancelled'] =  JHTML::_('select.genericlist', $options, 'filter_cancelled', 'class="inputbox" onchange="this.form.submit();"', 'value', 'text', $state->get('filter_cancelled') );

		// Table ordering
		$lists['order_Dir'] = $filter_order_Dir;
		$lists['order']		= $filter_order;

		// Assign to template
		$this->assignRef('lists',     $lists);
		$this->assignRef('rows',      $rows);
		$this->assignRef('pageNav',   $pageNav);
		$this->assignRef('user',      $user);
		$this->assignRef('settings',  $settings);
		$this->assignRef('cancelled', $state->get('filter_cancelled'));

		$this->returnUrl = base64_encode('index.php?option=com_redevent&view=registrations');

		parent::display($tpl);
	}
}
