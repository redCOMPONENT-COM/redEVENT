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
		if ($this->getLayout() == 'export')
		{
			return $this->_displayExport($tpl);
		}

		$user = JFactory::getUser();

		$this->items = $this->get('Items');
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

		$firstGroup		= new RToolbarButtonGroup;
		$secondGroup	= new RToolbarButtonGroup;
		$thirdGroup		= new RToolbarButtonGroup;
		$fourthGroup		= new RToolbarButtonGroup;

		if ($user->authorise('core.create', 'com_redevent'))
		{
			$new = RToolbarBuilder::createNewButton('event.add');
			$firstGroup->addButton($new);
		}

		if ($user->authorise('core.edit', 'com_redevent'))
		{
			$edit = RToolbarBuilder::createEditButton('event.edit');
			$secondGroup->addButton($edit);

			$importExport = RToolbarBuilder::createStandardButton('events.csvexport', 'csvexport', 'csvexport', JText::_('COM_REDEVENT_BUTTON_IMPORTEXPORT'), false);
			$secondGroup->addButton($importExport);
		}

		if ($user->authorise('core.edit.state', 'com_redevent'))
		{
			$publish = RToolbarBuilder::createPublishButton('events.publish');
			$thirdGroup->addButton($publish);

			$unPublish = RToolbarBuilder::createUnpublishButton('events.unpublish');
			$thirdGroup->addButton($unPublish);

			$button = RToolbarBuilder::createStandardButton('events.archive', JText::_('COM_REDEVENT_ARCHIVE'),'', 'icon-archive', true);
			$thirdGroup->addButton($button);

			$button = RToolbarBuilder::createStandardButton('events.archivepast', JText::_('COM_REDEVENT_ARCHIVE_OLD_EVENTS'), '', 'icon-archive', true);
			$thirdGroup->addButton($button);
		}

		if ($user->authorise('core.delete', 'com_redevent'))
		{
			$delete = RToolbarBuilder::createDeleteButton('events.delete');
			$fourthGroup->addButton($delete);
		}

		$toolbar = new RToolbar;
		$toolbar->addGroup($firstGroup)->addGroup($secondGroup)->addGroup($thirdGroup)->addGroup($fourthGroup);

		return $toolbar;
	}

	function _display($tpl = null)
	{
		$mainframe = JFactory::getApplication();
		$option = JRequest::getCmd('option');

		if ($this->getLayout() == 'export') {
			return $this->_displayExport($tpl);
		}

		//initialise variables
		$user 		= JFactory::getUser();
		$document	= JFactory::getDocument();
		$db  		= JFactory::getDBO();
		$elsettings = JComponentHelper::getParams('com_redevent');

		//get vars
		$filter_order		= $mainframe->getUserStateFromRequest( $option.'.events.filter_order', 'filter_order', 	'a.title', 'cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $option.'.events.filter_order_Dir', 'filter_order_Dir',	'', 'word' );
		$filter_state 		= $mainframe->getUserStateFromRequest( $option.'.events.filter_state', 'filter_state', 	'*', 'word' );
		$filter 			= $mainframe->getUserStateFromRequest( $option.'.events.filter', 'filter', '', 'int' );
		$search 			= $mainframe->getUserStateFromRequest( $option.'.events.search', 'search', '', 'string' );
		$search 			= $db->getEscaped( trim(JString::strtolower( $search ) ) );
		$template			= $mainframe->getTemplate();

		$document->setTitle(JText::_('COM_REDEVENT_PAGETITLE_EVENTS'));
		//add css and submenu to document
		FOFTemplateUtils::addCSS('media://com_redevent/css/backend.css');

		//Create Submenu
		ELAdmin::setMenu();

		JHTML::_('behavior.tooltip');

		//create the toolbar
		JToolBarHelper::title( JText::_('COM_REDEVENT_EVENTS' ), 'events' );
		JToolBarHelper::customX('archive', 'redevent_archive', 'redevent_archive', JText::_('COM_REDEVENT_ARCHIVE'), true);
		JToolBarHelper::customX('archivepast', 'redevent_archive', 'redevent_archive', JText::_('COM_REDEVENT_ARCHIVE_OLD_EVENTS'), true);
		JToolBarHelper::spacer();
		JToolBarHelper::publishList();
		JToolBarHelper::unpublishList();
		JToolBarHelper::spacer();
		JToolBarHelper::addNew();
		JToolBarHelper::editList();
		JToolBarHelper::deleteList(JText::_( 'COM_REDEVENT_EVENTS_REMOVE_CONFIRM_MESSAGE'));
		JToolBarHelper::custom( 'copy', 'copy.png', 'copy_f2.png', 'Copy' );
		JToolBarHelper::custom('export', 'exportevents', 'exportevents', JText::_('COM_REDEVENT_BUTTON_IMPORTEXPORT'), false);
		JToolBarHelper::spacer();
		if ($user->authorise('core.admin', 'com_redevent')) {
			JToolBarHelper::preferences('com_redevent', '600', '800');
		}

		// Get data from the model
		$rows      	= $this->get( 'Data');
		//$total      = & $this->get( 'Total');
		$pageNav 	= $this->get( 'Pagination' );

		//publish unpublished filter
		$lists['state']	= JHTML::_('grid.state', $filter_state );

		// table ordering
		$lists['order_Dir'] = $filter_order_Dir;
		$lists['order'] = $filter_order;

		/* Venue and time details */
		$eventvenues = $this->get('EventVenues');

		//search filter
		$filters = array();
		$filters[] = JHTML::_('select.option', '1', JText::_('COM_REDEVENT_EVENT_TITLE' ) );
		$filters[] = JHTML::_('select.option', '2', JText::_('COM_REDEVENT_VENUE' ) );
		$filters[] = JHTML::_('select.option', '3', JText::_('COM_REDEVENT_CITY' ) );
		$filters[] = JHTML::_('select.option', '4', JText::_('COM_REDEVENT_CATEGORY' ) );
		$lists['filter'] = JHTML::_('select.genericlist', $filters, 'filter', 'size="1" class="inputbox"', 'value', 'text', $filter );

		// search filter
		$lists['search']= $search;

		//assign data to template
		$this->assignRef('lists'      	, $lists);
		$this->assignRef('rows'      	, $rows);
		$this->assignRef('pageNav' 		, $pageNav);
		$this->assignRef('user'			, $user);
		$this->assignRef('template'		, $template);
		$this->assignRef('elsettings'	, $elsettings);
		$this->assignRef('eventvenues'	, $eventvenues);
		$this->assign('state'        , $this->get('State'));

// 		echo '<pre>';print_r($this->state); echo '</pre>';exit;

		parent::display($tpl);
	}

	function _displayExport($tpl = null)
	{
		$document	= JFactory::getDocument();
		$document->setTitle(JText::_('COM_REDEVENT_PAGETITLE_EVENTS_EXPORT'));
		//add css and submenu to document
		FOFTemplateUtils::addCSS('media://com_redevent/css/backend.css');

		//Create Submenu
    ELAdmin::setMenu();

		JHTML::_('behavior.tooltip');

		//create the toolbar
		JToolBarHelper::title( JText::_( 'COM_REDEVENT_PAGETITLE_EVENTS_EXPORT' ), 'events' );

		JToolBarHelper::back();
		JToolBarHelper::custom('doexport', 'exportevents', 'exportevents', JText::_('COM_REDEVENT_BUTTON_EXPORT'), false);

		$lists = array();

		$lists['categories'] = JHTML::_('select.genericlist', $this->get('CategoriesOptions'), 'categories[]'
		                                        , 'size="15" multiple="multiple"', 'value', 'text');
		$lists['venues'] = JHTML::_('select.genericlist', $this->get('VenuesOptions'), 'venues[]'
		                                        , 'size="15" multiple="multiple"', 'value', 'text');

		//assign data to template
		$this->assignRef('lists'      	, $lists);

		parent::display($tpl);
	}
}
