<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * View class for Custom fields list
 *
/**
 * @package  Redevent.admin
 * @since    2.5
 */
class RedeventViewCustomfields extends RedeventViewAdmin
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
		if ($this->getLayout() == 'import')
		{
			return $this->_displayImport($tpl);
		}

		$user = JFactory::getUser();

		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->filterForm = $this->get('Form');
		$this->activeFilters = $this->get('ActiveFilters');
		$this->state = $this->get('State');

		// Ordering
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
		return JText::_('COM_REDEVENT_PAGETITLE_CUSTOMFIELDS');
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
			$new = RToolbarBuilder::createNewButton('customfield.add');
			$firstGroup->addButton($new);
		}

		if ($user->authorise('core.edit', 'com_redevent'))
		{
			$edit = RToolbarBuilder::createEditButton('customfield.edit');
			$secondGroup->addButton($edit);

			$export = RToolbarBuilder::createStandardButton('customfield.export', 'csvexport', 'csvexport', JText::_('COM_REDEVENT_BUTTON_EXPORT'), false);
			$secondGroup->addButton($export);

			$import = RToolbarBuilder::createStandardButton('customfield.import', 'csvimport', 'csvimport', JText::_('COM_REDEVENT_BUTTON_IMPORT'), false);
			$secondGroup->addButton($import);
		}

		if ($user->authorise('core.delete', 'com_redevent'))
		{
			$delete = RToolbarBuilder::createDeleteButton('customfields.delete');
			$fourthGroup->addButton($delete);
		}

		$toolbar = new RToolbar;
		$toolbar->addGroup($firstGroup)->addGroup($secondGroup)->addGroup($thirdGroup)->addGroup($fourthGroup);

		return $toolbar;
	}

	private function _displayImport($tpl = null)
	{
		$document	= & JFactory::getDocument();
		$document->setTitle(JText::_('COM_REDEVENT_PAGETITLE_CUSTOMFIELDS_IMPORT'));
		//add css to document
		FOFTemplateUtils::addJS("media://com_redevent/css/backend.less||media://com_redevent/css/backend.css");

		//Create Submenu
		ELAdmin::setMenu();

		JHTML::_('behavior.tooltip');

		//create the toolbar
		JToolBarHelper::title( JText::_( 'COM_REDEVENT_PAGETITLE_CUSTOMFIELDS_IMPORT' ), 'events' );

		JToolBarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_redevent&view=customfields');

		$lists = array();

		//assign data to template
		$this->assignRef('lists'      	, $lists);

		parent::display($tpl);
	}
}
