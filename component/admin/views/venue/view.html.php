<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Venue edit view
 *
 * @package  Redevent.admin
 * @since    2.0
 */
class RedeventViewVenue extends RedeventViewAdmin
{
	/**
	 * @var  boolean
	 */
	protected $displaySidebar = false;

	/**
	 * Display the edit page
	 *
	 * @param   string  $tpl  The template file to use
	 *
	 * @return   string
	 */
	public function display($tpl = null)
	{
		$user = JFactory::getUser();

		$this->form = $this->get('Form');
		$this->item = $this->get('Item');

		$this->canConfig = false;

		if ($user->authorise('core.admin', 'com_redevent'))
		{
			$this->canConfig = true;
		}

		// Display the template
		parent::display($tpl);
	}

	/**
	 * Display the category edit page
	 *
	 * @param   string  $tpl  The template file to use
	 *
	 * @return   string
	 */
	public function _display($tpl = null)
	{
		$mainframe = JFactory::getApplication();
		$params    = JComponentHelper::getParams('com_redevent');

		// Load pane behavior
		JHTML::_('behavior.framework');

		// Initialise variables
		$editor 	= JFactory::getEditor();
		$document	= JFactory::getDocument();
		$user 		= JFactory::getUser();
		$settings	= JComponentHelper::getParams('com_redevent');

		// get vars
		$cid 			= JRequest::getVar( 'cid' );
    	$url    = JURI::root();

		$document->setTitle(JText::_('COM_REDEVENT_PAGETITLE_EDITVENUE'));
		// Add css and js to document
		FOFTemplateUtils::addCSS("media://com_redevent/css/backend.css");
// 		$document->addScript('../includes/js/joomla/popup.js');
// 		$document->addStyleSheet('../includes/js/joomla/popup.css');

		FOFTemplateUtils::addJS("media://com_redevent/js/attachments.js");
		$document->addScriptDeclaration('var removemsg = "'.JText::_('COM_REDEVENT_ATTACHMENT_CONFIRM_MSG').'";' );

		// Get data from the model
		$model		= & $this->getModel();
		$row      	= & $this->get( 'Data');

		// fail if checked out not by 'me'
		if ($row->id) {
			if ($model->isCheckedOut( $user->get('id') )) {
				JError::raiseWarning( 'REDEVENT_GENERIC_ERROR', $row->venue.' '.JText::_('COM_REDEVENT_EDITED_BY_ANOTHER_ADMIN' ));
				$mainframe->redirect( 'index.php?option=com_redevent&view=venues' );
			}
		}

		$task = JRequest::getVar('task');

		//create the toolbar
		if ($task == 'copy') {
		  	JToolBarHelper::title( JText::_('COM_REDEVENT_COPY_VENUE'), 'venuesedit');
		} elseif ( $cid ) {
			JToolBarHelper::title( JText::_('COM_REDEVENT_EDIT_VENUE' ), 'venuesedit' );

			//makes data safe
			JFilterOutput::objectHTMLSafe( $row, ENT_QUOTES, 'locdescription' );

		} else {
			JToolBarHelper::title( JText::_('COM_REDEVENT_ADD_VENUE' ), 'venuesedit' );

			//set the submenu
			JSubMenuHelper::addEntry( JText::_('COM_REDEVENT' ), 'index.php?option=com_redevent');
			JSubMenuHelper::addEntry( JText::_('COM_REDEVENT_EVENTS' ), 'index.php?option=com_redevent&view=events');
			JSubMenuHelper::addEntry( JText::_('COM_REDEVENT_VENUES' ), 'index.php?option=com_redevent&view=venues');
			JSubMenuHelper::addEntry( JText::_('COM_REDEVENT_CATEGORIES' ), 'index.php?option=com_redevent&view=categories');
			JSubMenuHelper::addEntry( JText::_('COM_REDEVENT_ARCHIVESCREEN' ), 'index.php?option=com_redevent&view=archive');
			JSubMenuHelper::addEntry( JText::_('COM_REDEVENT_HELP' ), 'index.php?option=com_redevent&view=help');
		}
		JToolBarHelper::apply();
		JToolBarHelper::spacer();
		JToolBarHelper::save();
		JToolBarHelper::spacer();
		JToolBarHelper::cancel();
		JToolBarHelper::spacer();
		//JToolBarHelper::help( 'el.editvenues', true );

		$lists = array();
	    // categories selector
	    $selected = array();
	    foreach ((array) $row->categories as $cat) {
	      $selected[] = $cat;
	    }
	    $lists['categories'] = JHTML::_('select.genericlist', (array) $this->get('Categories'), 'categories[]', 'class="inputbox" multiple="multiple" size="10"', 'value', 'text', $selected);

	    $countries = array();
	    $countries[] = JHTML::_('select.option', '', JText::_('COM_REDEVENT_Select_country'));
	    $countries = array_merge($countries, RedeventHelperCountries::getCountryOptions());
	    $lists['countries'] = JHTML::_('select.genericlist', $countries, 'country', 'class="inputbox"', 'value', 'text', $row->country );
	    unset($countries);

	    $pinpointicon = RedeventHelperOutput::pinpointicon( $row );

		if ($task == 'copy')
		{
			$row->id = null;
			$row->venue .= ' '.JText::_('COM_REDEVENT_copy');
			$row->alias = '';
		}

		//assign data to template
		$this->assignRef('row'      	, $row);
		$this->assignRef('editor'      	, $editor);
		$this->assignRef('settings'     , $settings);
		$this->assignRef('params'     , $params);
		$this->assignRef('lists'      , $lists);
		$this->assignRef('imageselect' 	, $imageselect);
		$this->assignRef('pinpointicon', $pinpointicon);
		$this->assignRef('access'	, RedeventHelper::getAccesslevelOptions());
		$this->assignRef('form'      	, $this->get('form'));

		parent::display($tpl);
	}

	/**
	 * Get the view title.
	 *
	 * @return  string  The view title.
	 */
	public function getTitle()
	{
		$subTitle = ' <small>' . JText::_('COM_REDEVENT_NEW') . '</small>';

		if ($this->item->id)
		{
			$subTitle = ' <small>' . JText::_('COM_REDEVENT_EDIT') . '</small>';
		}

		return JText::_('COM_REDEVENT_PAGETITLE_EDITVENUE') . $subTitle;
	}

	/**
	 * Get the toolbar to render.
	 *
	 * @return  RToolbar
	 */
	public function getToolbar()
	{
		$group = new RToolbarButtonGroup;

		$save = RToolbarBuilder::createSaveButton('venue.apply');
		$saveAndClose = RToolbarBuilder::createSaveAndCloseButton('venue.save');
		$saveAndNew = RToolbarBuilder::createSaveAndNewButton('venue.save2new');
		$save2Copy = RToolbarBuilder::createSaveAsCopyButton('venue.save2copy');

		$group->addButton($save)
			->addButton($saveAndClose)
			->addButton($saveAndNew)
			->addButton($save2Copy);

		if (empty($this->item->id))
		{
			$cancel = RToolbarBuilder::createCancelButton('venue.cancel');
		}
		else
		{
			$cancel = RToolbarBuilder::createCloseButton('venue.cancel');
		}

		$group->addButton($cancel);

		$toolbar = new RToolbar;
		$toolbar->addGroup($group);

		return $toolbar;
	}
}
