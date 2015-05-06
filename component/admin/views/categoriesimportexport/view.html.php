<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * View class for categories screen
 *
 * @package  Redevent.admin
 * @since    0.9
 */
class RedeventViewCategories extends RedeventViewAdmin
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
		if ($this->getLayout() == 'importexport')
		{
			return $this->displayExport($tpl);
		}

		$user = JFactory::getUser();

		$this->items = $this->get('Items');
		$this->state = $this->get('State');
		$this->pagination = $this->get('Pagination');
		$this->filterForm = $this->get('Form');
		$this->activeFilters = $this->get('ActiveFilters');

		// Fields ordering
		$this->ordering = array();

		if ($this->items)
		{
			foreach ($this->items as &$item)
			{
				$this->ordering[0][] = $item->id;
			}
		}

		$this->canEdit = false;

		if ($user->authorise('core.edit', 'com_redevent'))
		{
			$this->canEdit = true;
		}

		parent::display($tpl);
	}

	function _display($tpl = null)
	{
		$mainframe = JFactory::getApplication();
		$option = JRequest::getCmd('option');

		if ($this->getLayout() == 'importexport') {
			return $this->_displayExport($tpl);
		}

		//initialise variables
		$user 		= JFactory::getUser();
		$db  		= JFactory::getDBO();
		$document	= JFactory::getDocument();

		JHTML::_('behavior.tooltip');

		$this->state = $this->get('state');

		//get vars
		$filter_order		= $this->state->get('filter_order');
		$filter_order_Dir	= $this->state->get('filter_order_Dir');
		$filter_state 		= $this->state->get('filter_state');
		$search 			= $this->state->get('search');

		$document->setTitle(JText::_('COM_REDEVENT_PAGETITLE_CATEGORIES'));
		//add css and submenu to document
		RHelperAsset::load('backend.css');

		//Create Submenu
		ELAdmin::setMenu();

		//create the toolbar
		JToolBarHelper::title( JText::_('COM_REDEVENT_CATEGORIES' ), 'elcategories' );
		JToolBarHelper::publishList();
		JToolBarHelper::unpublishList();
		JToolBarHelper::spacer();
		JToolBarHelper::addNew();
		JToolBarHelper::editList();
		JToolBarHelper::spacer();
		JToolBarHelper::deleteList();
		JToolBarHelper::spacer();
		JToolBarHelper::custom('importexport', 'csvexport', 'csvexport', JText::_('COM_REDEVENT_BUTTON_IMPORTEXPORT'), false);
		JToolBarHelper::spacer();
		if ($user->authorise('core.admin', 'com_redevent')) {
			JToolBarHelper::preferences('com_redevent', '600', '800');
		}

		//Get data from the model
		$rows      	= & $this->get( 'ItemList');
		//$total      = & $this->get( 'Total');
		$pageNav 	= & $this->get( 'Pagination' );

		//publish unpublished filter
		$lists['state']	= JHTML::_('grid.state', $filter_state );
		// search filter
		$lists['search']= $search;

		// table ordering
		$lists['order_Dir'] = $filter_order_Dir;
		$lists['order'] = $filter_order;

		$ordering = ($lists['order'] == 'c.ordering');

		//assign data to template
		$this->assignRef('lists'      	, $lists);
		$this->assignRef('rows'      	, $rows);
		$this->assignRef('pageNav' 		, $pageNav);
		$this->assignRef('ordering'		, $ordering);
		$this->assignRef('user'			, $user);
	    $this->assignRef('filter_order'     , $filter_order);

		parent::display($tpl);
	}

	public function displayExport($tpl = null)
	{
		$document	= JFactory::getDocument();
		$document->setTitle(JText::_('COM_REDEVENT_PAGETITLE_CATEGORIES_EXPORT'));
		//add css and submenu to document
		RHelperAsset::load('backend.css');

		//Create Submenu
		ELAdmin::setMenu();

		JHTML::_('behavior.tooltip');

		//create the toolbar
		JToolBarHelper::title( JText::_( 'COM_REDEVENT_PAGETITLE_CATEGORIES_EXPORT' ), 'events' );

		JToolBarHelper::back();
		JToolBarHelper::custom('doexport', 'exportevents', 'exportevents', JText::_('COM_REDEVENT_BUTTON_EXPORT'), false);

		$lists = array();

		//assign data to template
		$this->assignRef('lists'      	, $lists);

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
		return JText::_('COM_REDMEMBER_RMADMINTABS_TITLE');
	}

	/**
	 * Get the tool-bar to render.
	 *
	 * @todo	The commented lines are going to be implemented once we have setup ACL requirements for redMEMBER
	 * @return  RToolbar
	 */
	public function getToolbar()
	{
		$user = JFactory::getUser();

		$firstGroup		= new RToolbarButtonGroup;
		$secondGroup	= new RToolbarButtonGroup;
		$thirdGroup		= new RToolbarButtonGroup;

		if ($user->authorise('core.create', 'com_redmember'))
		{
			$new = RToolbarBuilder::createNewButton('rmadmintab.add');
			$firstGroup->addButton($new);
		}

		if ($user->authorise('core.edit', 'com_redmember'))
		{
			$edit = RToolbarBuilder::createEditButton('rmadmintab.edit');
			$secondGroup->addButton($edit);
		}

		if ($user->authorise('core.delete', 'com_redmember'))
		{
			$delete = RToolbarBuilder::createDeleteButton('rmadmintabs.delete');
			$thirdGroup->addButton($delete);
		}

		$toolbar = new RToolbar;
		$toolbar->addGroup($firstGroup)->addGroup($secondGroup)->addGroup($thirdGroup);

		return $toolbar;
	}
}
