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
				RToolbarBuilder::createStandardButton('attendees.emailall', 'COM_REDEVENT_ATTENDEES_TOOLBAR_EMAIL_ALL', 'send', 'icon-email')
			);
			$firstGroup->addButton(
				RToolbarBuilder::createStandardButton('attendees.email', 'COM_REDEVENT_ATTENDEES_TOOLBAR_EMAIL_SELECTED', 'send', 'icon-email')
			);

			$secondGroup->addButton(
				RToolbarBuilder::createNewButton('attendees.new')
			);
			$secondGroup->addButton(
				RToolbarBuilder::createEditButton('attendees.edit')
			);
			$secondGroup->addButton(
				RToolbarBuilder::createStandardButton('attendees.move', 'COM_REDEVENT_ATTENDEES_TOOLBAR_MOVE', '', 'icon-move')
			);

			if ($this->state->get('filter.cancelled') == 1)
			{
				$restore = RToolbarBuilder::createStandardButton('attendees.uncancelreg', 'COM_REDEVENT_ATTENDEES_TOOLBAR_RESTORE', '', ' icon-circle-arrow-left');
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

	function _display($tpl = null)
	{
		$mainframe = &JFactory::getApplication();
		$option = JRequest::getCmd('option');
		$params = &JComponentHelper::getParams('com_redevent');

		if($this->getLayout() == 'print') {
			$this->_displayprint($tpl);
			return;
		}
		if($this->getLayout() == 'move') {
			$this->_displaymove($tpl);
			return;
		}

		//initialise variables
		$db = JFactory::getDBO();
		$elsettings = JComponentHelper::getParams('com_redevent');
		$document	= JFactory::getDocument();
		$user = JFactory::getUser();
		$state = &$this->get('State');

		//get vars
		$filter_order		= $mainframe->getUserStateFromRequest( $option.'.attendees.filter_order', 'filter_order', 'u.username', 'cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $option.'.attendees.filter_order_Dir',	'filter_order_Dir',	'', 'word' );
		$xref = JRequest::getInt('xref');
		// $search 			= $mainframe->getUserStateFromRequest( $option.'.attendees.search', 'search', '', 'string' );
		// $search 			= $db->getEscaped( trim(JString::strtolower( $search ) ) );

		$document->setTitle(JText::_('COM_REDEVENT_PAGETITLE_ATTENDEES'));
		//add css and submenu to document
		FOFTemplateUtils::addCSS('media://com_redevent/css/backend.css');

		// add javascript
		JHTML::_('behavior.modal', 'a.answersmodal');

		//Create Submenu
    ELAdmin::setMenu();

		//add toolbar
		JToolBarHelper::title( JText::_('COM_REDEVENT_REGISTRATIONS' ), 'registrations' );
//		JToolBarHelper::custom('submitters', 'redevent_submitters', 'redevent_submitters', JText::_('COM_REDEVENT_Attendees'), false);
		JToolBarHelper::custom('emailall', 'send.png', 'send.png', 'COM_REDEVENT_ATTENDEES_TOOLBAR_EMAIL_ALL', false, true);
		JToolBarHelper::custom('email', 'send.png', 'send.png', 'COM_REDEVENT_ATTENDEES_TOOLBAR_EMAIL_SELECTED', true, true);
		JToolBarHelper::spacer();
		JToolBarHelper::addNew();
		JToolBarHelper::editList();
		JToolBarHelper::custom('move', 'move', 'move', 'COM_REDEVENT_ATTENDEES_TOOLBAR_MOVE', true, true);
		if ($state->get('filter_cancelled', 0) == 0) {
			JToolBarHelper::custom('cancelreg', 'cancel', 'cancel', 'COM_REDEVENT_ATTENDEES_TOOLBAR_CANCEL', true, true);
		}
		if ($state->get('filter_cancelled', 0) == 1) {
			JToolBarHelper::custom('uncancelreg', 'redrestore', 'redrestore', 'COM_REDEVENT_ATTENDEES_TOOLBAR_RESTORE', true, true);
			JToolBarHelper::deleteList(JText::_('COM_REDEVENT_ATTENDEES_DELETE_WARNING'));
		}
		JToolBarHelper::spacer();
		JToolBarHelper::back();
		JToolBarHelper::spacer();
		if ($user->authorise('core.admin', 'com_redevent')) {
			JToolBarHelper::preferences('com_redevent', '600', '800');
		}

		// Get data from the model
		$rows      = $this->get( 'Data');
		$pageNav   = $this->get( 'Pagination' );
		$event     = $this->get( 'Event' );
		$form      = $this->get( 'Form' );
		$rf_fields = $this->get( 'RedFormFrontFields' );

		$event->dates = RedeventHelper::isValidDate($event->dates) ? strftime($elsettings->get('backend_formatdate', '%d.%m.%Y'), strtotime( $event->dates )) : JText::_('COM_REDEVENT_OPEN_DATE');

		//build filter selectlist
		$datetimelocation = $this->get('DateTimeLocation');
		$filters = array();
		foreach ($datetimelocation as $key => $value)
		{
			/* Get the date */
			if (RedeventHelper::isValidDate($value->dates))
			{
				$date = strftime( $elsettings->get('backend_formatdate', '%d.%m.%Y'), strtotime( $value->dates ));
				$enddate 	= strftime( $elsettings->get('backend_formatdate', '%d.%m.%Y'), strtotime( $value->enddates ));
				$displaydate = $date.' - '.$enddate;
			}
			else {
				$displaydate = JText::_('COM_REDEVENT_OPEN_DATE');
			}

			/* Get the time */
			if ($value->times)
			{
				$time = strftime( $elsettings->get('formattime', '%H:%M'), strtotime( $value->times ));
				$displaydate .= ' '. $time;
				if ($value->endtimes) {
					$endtimes = strftime( $elsettings->get('formattime', '%H:%M'), strtotime( $value->endtimes ));
					$displaydate .= ' - '.$endtimes;
				}
			}
			$filters[] = JHTML::_('select.option', $value->id, $value->venue.' '.$displaydate );
		}
		$lists['filter'] = JHTML::_('select.genericlist', $filters, 'xref', 'class="inputbox"', 'value', 'text', $event->xref );

		// search filter
		// $lists['search'] = $search;

		// confirmed filter
		$options = array(JHTML::_('select.option', 0, JText::_('COM_REDEVENT_ATTENDEES_FILTER_CONFIRMED_ALL')),
		                 JHTML::_('select.option', 1, JText::_('COM_REDEVENT_ATTENDEES_FILTER_CONFIRMED_CONFIRMED')),
		                 JHTML::_('select.option', 2, JText::_('COM_REDEVENT_ATTENDEES_FILTER_CONFIRMED_UNCONFIRMED')),
		                 );
		$lists['filter_confirmed'] =  JHTML::_('select.genericlist', $options, 'filter_confirmed', 'class="inputbox" onchange="this.form.submit();"', 'value', 'text', $state->get('filter_confirmed') );

		// waiting list filter
		$options = array(JHTML::_('select.option', 0, JText::_('COM_REDEVENT_ATTENDEES_FILTER_WAITING_ALL')),
		                 JHTML::_('select.option', 1, JText::_('COM_REDEVENT_ATTENDEES_FILTER_WAITING_ATTENDING')),
		                 JHTML::_('select.option', 2, JText::_('COM_REDEVENT_ATTENDEES_FILTER_WAITING_WAITING')),
		                 );
		$lists['filter_waiting'] =  JHTML::_('select.genericlist', $options, 'filter_waiting', 'class="inputbox" onchange="this.form.submit();"', 'value', 'text', $state->get('filter_waiting') );

		// cancelled filter
		$options = array(JHTML::_('select.option', 0, JText::_('COM_REDEVENT_ATTENDEES_FILTER_CANCELLED_NOT_CANCELLED')),
		                 JHTML::_('select.option', 1, JText::_('COM_REDEVENT_ATTENDEES_FILTER_CANCELLED_CANCELLED')),
		                 JHTML::_('select.option', 2, JText::_('COM_REDEVENT_ATTENDEES_FILTER_CANCELLED_ALL')),
		                 );
		$lists['filter_cancelled'] =  JHTML::_('select.genericlist', $options, 'filter_cancelled', 'class="inputbox" onchange="this.form.submit();"', 'value', 'text', $state->get('filter_cancelled') );

		// table ordering
		$lists['order_Dir'] = $filter_order_Dir;
		$lists['order']		= $filter_order;

		//assign to template
		$this->assignRef('lists',     $lists);
		$this->assignRef('rows',      $rows);
		$this->assignRef('pageNav',   $pageNav);
		$this->assignRef('event',     $event);
		$this->assignRef('rf_fields', $rf_fields);
		$this->assignRef('form',      $form);
		$this->assignRef('user',      $user);
		$this->assignRef('params',    $params);
		$this->assignRef('cancelled', $state->get('filter_cancelled'));

		parent::display($tpl);
	}

	/**
	 * Prepares the print screen
	 *
	 * @param $tpl
	 *
	 * @since 0.9
	 */
	function _displayprint($tpl = null)
	{
		$elsettings = JComponentHelper::getParams('com_redevent');
		$document	= & JFactory::getDocument();
		FOFTemplateUtils::addCSS('media://com_redevent/css/backend.css');

		$rows      	= & $this->get( 'Data');
		$event 		= & $this->get( 'Event' );
		$rf_fields = $this->get( 'RedFormFrontFields' );
		$form      = $this->get( 'Form' );

		$event->dates = RedeventHelper::isValidDate($event->dates) ? strftime($elsettings->get('backend_formatdate', '%d.%m.%Y'), strtotime( $event->dates )) : JText::_('COM_REDEVENT_OPEN_DATE');

		//assign data to template
		$this->assignRef('rows'      	, $rows);
		$this->assignRef('event'		, $event);
		$this->assignRef('rf_fields', $rf_fields);
		$this->assignRef('form',      $form);

		parent::display($tpl);
	}

	/**
	 * Prepares the print screen
	 *
	 * @param $tpl
	 *
	 * @since 0.9
	 */
	function _displaymove($tpl = null)
	{
		FOFTemplateUtils::addCSS('media://com_redevent/css/backend.css');

		$cid = JRequest::getVar('cid', array(), 'post', 'array');

		$event = $this->get('Event');

		//add toolbar
		JToolBarHelper::title(JText::_('COM_REDEVENT_REGISTRATIONS' ), 'users');
		JToolBarHelper::apply('applymove');
		JToolBarHelper::cancel('cancelmove');

		//assign data to template
		$this->assignRef('form_id',  JRequest::getInt('form_id'));
		$this->assignRef('cid',      $cid);
		$this->assignRef('session',  $event);

		parent::display($tpl);
	}
}
