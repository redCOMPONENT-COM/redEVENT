<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Event edit view
 *
 * @package  Redevent.admin
 * @since    0.9
 */
class RedeventViewEvent extends RedeventViewAdmin
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
		$this->customfields = $this->get('Customfields');

		$this->canConfig = false;

		if ($user->authorise('core.admin', 'com_redevent'))
		{
			$this->canConfig = true;
		}

		// Display the template
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

		return JText::_('COM_REDEVENT_PAGETITLE_EDITEVENT') . $subTitle;
	}

	/**
	 * Get the toolbar to render.
	 *
	 * @return  RToolbar
	 */
	public function getToolbar()
	{
		$group = new RToolbarButtonGroup;

		$save = RToolbarBuilder::createSaveButton('event.apply');
		$saveAndClose = RToolbarBuilder::createSaveAndCloseButton('event.save');
		$saveAndNew = RToolbarBuilder::createSaveAndNewButton('event.save2new');
		$save2Copy = RToolbarBuilder::createSaveAsCopyButton('event.save2copy');

		$group->addButton($save)
			->addButton($saveAndClose)
			->addButton($saveAndNew)
			->addButton($save2Copy);

		if (empty($this->item->id))
		{
			$cancel = RToolbarBuilder::createCancelButton('event.cancel');
		}
		else
		{
			$cancel = RToolbarBuilder::createCloseButton('event.cancel');
		}

		$group->addButton($cancel);

		$toolbar = new RToolbar;
		$toolbar->addGroup($group);

		return $toolbar;
	}

	public function _display($tpl = null)
	{
		$mainframe = JFactory::getApplication();

		if($this->getLayout() == 'editxref')
		{
			$this->_displayeditxref($tpl);

			return;
		}

		else if($this->getLayout() == 'closexref')
		{
			$this->_displayclosexref($tpl);

			return;
		}

		//Load behavior
		jimport('joomla.html.pane');
		JHTML::_('behavior.tooltip');
		JHTML::_('behavior.formvalidation');
		JHTML::_('behavior.framework');

		//initialise variables
		$editor 	= JFactory::getEditor();
		$document	= JFactory::getDocument();
		$pane 		= JPane::getInstance('tabs');
		$user 		= JFactory::getUser();
		$params   = JComponentHelper::getParams('com_redevent');

		//get vars
		$cid		= JRequest::getVar( 'cid' );
		$task		= JRequest::getVar('task');
		$url 		= JURI::root();

		$document->setTitle(JText::_('COM_REDEVENT_PAGETITLE_EDITEVENT'));
		//add the custom stylesheet and the seo javascript
		$document->addStyleSheet($url.'administrator/components/com_redevent/assets/css/redeventbackend.css');
		$document->addScript($url.'administrator/components/com_redevent/assets/js/seo.js');

		$document->addScript($url.'administrator/components/com_redevent/assets/js/xrefedit.js');
		$document->addScript($url.'administrator/components/com_redevent/assets/js/editevent.js');
		$document->addScript($url.'components/com_redevent/assets/js/attachments.js');
		$document->addScriptDeclaration('var removemsg = "'.JText::_('COM_REDEVENT_ATTACHMENT_CONFIRM_MSG').'";' );

		//get data from model
		$form  = $this->get('form');
		$model = $this->getModel();

		$row = $this->get('Data');

		if ($task == 'copy')
		{
			$row->id = null;
			$row->title .= ' '.JText::_('COM_REDEVENT_copy');
			$row->alias = '';
		}

		if ($task == 'add')
		{
			$row->id = null;
			$row->title = '';
			$row->alias = '';
		}

		$customfields = $this->get('Customfields');

		/* Check if we have a redFORM id */
		if (empty($row->redform_id))
		{
			$row->redform_id = $params->get('defaultredformid', 1);
		}

		// fail if checked out not by 'me'
		if ($row->id)
		{
			if ($model->isCheckedOut($user->get('id')))
			{
				JError::raiseWarning( 'REDEVENT_GENERIC_ERROR', $row->titel.' '.JText::_('COM_REDEVENT_EDITED_BY_ANOTHER_ADMIN' ));
				$mainframe->redirect( 'index.php?option=com_redevent&view=events' );
			}
		}

		//make data safe
		JFilterOutput::objectHTMLSafe( $row, ENT_QUOTES, 'datdescription' );

		//Create category list
		$lists = array();
		$lists['category'] = $model->getCategories();

		/* Create venue selection tab */
		$venueslist = $this->get('Venues');
		$xrefs = $this->get('xrefs');

		// categories selector
		$selected = array();

		foreach ((array) $row->categories_ids as $cat)
		{
			$selected[] = $cat;
		}
		$lists['categories'] = JHTML::_('select.genericlist', (array) $this->get('Categories'), 'categories[]', 'class="inputbox required validate-categories" multiple="multiple" size="10"', 'value', 'text', $selected);

		// event layout
		$options = array(
				JHTML::_('select.option', 0, JText::_('COM_REDEVENT_DEFAULT')),
				JHTML::_('select.option', 1, JText::_('COM_REDEVENT_EVENT_LAYOUT_TAGS')),
				JHTML::_('select.option', 2, JText::_('COM_REDEVENT_EVENT_LAYOUT_FIXED')),
		);
		$lists['details_layout'] = JHTML::_('select.genericlist', $options, 'details_layout', '', 'value', 'text', $row->details_layout);

		// enable ical button
		$options = array(
				JHTML::_('select.option', 0, JText::_('COM_REDEVENT_DEFAULT')),
				JHTML::_('select.option', 1, JText::_('COM_REDEVENT_Yes')),
				JHTML::_('select.option', 2, JText::_('COM_REDEVENT_No')),
		);
		$lists['enable_ical'] = JHTML::_('select.genericlist', $options, 'enable_ical', '', 'value', 'text', $row->enable_ical);

		/* Create submission types */
		$submission_types = explode(',', $row->submission_types);

		/* Check if redFORM is installed */
		$redform_install = $this->get('CheckredFORM');

		$hasattendees = $model->hasAttendees();

		if ($redform_install)
		{
			/* Get a list of redFORM forms */
			$redforms = $this->get('RedForms');

			if ($redforms)
			{
				if ($hasattendees)
				{ // can't reassign the form in that case !
					foreach ($redforms as $aform)
					{
						if ($aform->id == $row->redform_id)
						{
							$lists['redforms'] = $aform->formname.'<input type="hidden" name="redform_id" value="'.$row->redform_id.'"/>';
							break;
						}
					}
				}
				else
				{
					$lists['redforms'] = JHTML::_('select.genericlist', $redforms, 'redform_id', '', 'id', 'formname', $row->redform_id );
				}
			}
			else
			{
				$lists['redforms'] = '';
			}

			/* Check if a redform ID exists, if so, get the fields */
			if (isset($row->redform_id) && $row->redform_id > 0)
			{
				$formfields = $this->get('formfields');
				if (!$formfields) $formfields = array();
			}
		}
		else
		{
			$lists['redforms'] = '';
			$formfields = '';
		}

		JHTML::script('modal.js');
		JHTML::stylesheet('modal.css');

		//build toolbar
		if ($task == 'copy')
		{
			JToolBarHelper::title( JText::_('COM_REDEVENT_COPY_EVENT'), 'eventedit');
		}
		elseif ($cid)
		{
			JToolBarHelper::title( JText::_('COM_REDEVENT_EDIT_EVENT' ).' - '.$row->title, 'eventedit' );
		}
		else
		{
			JToolBarHelper::title( JText::_('COM_REDEVENT_ADD_EVENT' ), 'eventedit' );

			//set the submenu
			JSubMenuHelper::addEntry( JText::_('COM_REDEVENT' ), 'index.php?option=com_redevent');
			JSubMenuHelper::addEntry( JText::_('COM_REDEVENT_EVENTS' ), 'index.php?option=com_redevent&view=events');
			JSubMenuHelper::addEntry( JText::_('COM_REDEVENT_VENUES' ), 'index.php?option=com_redevent&view=venues');
			JSubMenuHelper::addEntry( JText::_('COM_REDEVENT_CATEGORIES' ), 'index.php?option=com_redevent&view=categories');
			JSubMenuHelper::addEntry( JText::_('COM_REDEVENT_ARCHIVESCREEN' ), 'index.php?option=com_redevent&view=archive');
			JSubMenuHelper::addEntry( JText::_('COM_REDEVENT_HELP' ), 'index.php?option=com_redevent&view=help');
			if ($user->get('gid') > 24)
			{
				JSubMenuHelper::addEntry( JText::_('COM_REDEVENT_SETTINGS' ), 'index.php?option=com_redevent&controller=settings&task=edit');
			}
		}
		JToolBarHelper::apply();
		JToolBarHelper::spacer();
		JToolBarHelper::save();

		if (JPluginHelper::isEnabled('system', 'autotweetredevent'))
		{
			//If the AutoTweet NG Component is installed
			// Ignore warnings because component may not be installed
			$warnHandlers = JERROR::getErrorHandling( E_WARNING );
			JERROR::setErrorHandling( E_WARNING, 'ignore' );

			if (JComponentHelper::isEnabled('com_autotweet', true) && !$row->id )
			{
				JToolBarHelper::save('saveAndTwit', 'Save & twit');
			}

			// Reset the warning handler(s)
			foreach( $warnHandlers as $mode )
			{
				JERROR::setErrorHandling( E_WARNING, $mode );
			}
		}

		JToolBarHelper::spacer();
		JToolBarHelper::cancel();
		JToolBarHelper::spacer();
		//JToolBarHelper::help( 'el.editevents', true );

		//assign vars to the template
		$this->assignRef('lists'      	, $lists);
		$this->assignRef('row'      	, $row);
		$this->assignRef('formfields'  	, $formfields);
		$this->assignRef('imageselect'	, $imageselect);
		$this->assignRef('submission_types'	, $submission_types);
		$this->assignRef('editor'		, $editor);
		$this->assignRef('pane'			, $pane);
		$this->assignRef('task'			, $task);
		$this->assignRef('params'	,   $params);
		$this->assignRef('venueslist'	, $venueslist);
		$this->assignRef('redform_install'	, $redform_install);
		$this->assignRef('customfields'  , $customfields);
		$this->assignRef('access'	, RedeventHelper::getAccesslevelOptions());
		$this->assignRef('xrefs'  , $xrefs);
		$this->assignRef('form',    $form);

		if (!$row->id)
		{
			$this->_prepareSessionTab();
		}

		parent::display($tpl);
	}

	/**
	 * Creates the output for the add venue screen
	 *
	 * @since 0.9
	 *
	 */
	function _displayeditxref($tpl)
	{
		//initialise variables
		$editor 	= JFactory::getEditor();
		$document	= JFactory::getDocument();
		$uri 		= JFactory::getURI();
		$params   = JComponentHelper::getParams('com_redevent');

		//add css and js to document
		//JHTML::_('behavior.modal', 'a.modal');
		JHTML::_('behavior.tooltip');
		JHTML::_('behavior.formvalidation');

		jimport('joomla.html.pane');

		$document->addScript(JURI::root().'components/com_redevent/assets/js/xref_recurrence.js');

		$xref = $this->get('xref');
		$xref->eventid = ($xref->eventid) ? $xref->eventid : JRequest::getVar('eventid', 0, 'request', 'int');
		$customfields =& $this->get('XrefCustomfields');

		$lists = array();

		// venues selector
		$venues = array(JHTML::_('select.option', 0, JText::_('COM_REDEVENT_Select_Venue')));
		$venues = array_merge($venues, $this->get('VenuesOptions'));
		$lists['venue'] = JHTML::_('select.genericlist', $venues, 'venueid', '', 'value', 'text', $xref->venueid);

		// if this is not the first xref of the recurrence, we shouldn't modify it
		$lockedrecurrence = ($xref->count > 0);

		// Recurrence selector
		$recur_type = array( JHTML::_('select.option', 'NONE', JText::_('COM_REDEVENT_NO_REPEAT')),
				JHTML::_('select.option', 'DAILY', JText::_('COM_REDEVENT_DAILY')),
				JHTML::_('select.option', 'WEEKLY', JText::_('COM_REDEVENT_WEEKLY')),
				JHTML::_('select.option', 'MONTHLY', JText::_('COM_REDEVENT_MONTHLY')),
				JHTML::_('select.option', 'YEARLY', JText::_('COM_REDEVENT_YEARLY'))
		);
		$lists['recurrence_type'] = JHTML::_('select.radiolist', $recur_type, 'recurrence_type', '', 'value', 'text', $xref->rrules->type);

		// published state selector
		$published = array( JHTML::_('select.option', '1', JText::_('COM_REDEVENT_PUBLISHED')),
				JHTML::_('select.option', '0', JText::_('COM_REDEVENT_UNPUBLISHED')),
				JHTML::_('select.option', '-1', JText::_('COM_REDEVENT_ARCHIVED'))
		);
		$lists['published'] = JHTML::_('select.radiolist', $published, 'published', '', 'value', 'text', $xref->published);

		// featured state selector
		$options = array( JHTML::_('select.option', '0', JText::_('COM_REDEVENT_SESSION_NOT_FEATURED')),
				JHTML::_('select.option', '1', JText::_('COM_REDEVENT_SESSION_IS_FEATURED'))
		);
		$lists['featured'] = JHTML::_('select.booleanlist', 'featured', '', $xref->featured);

		$pane 		= & JPane::getInstance('tabs');

		//assign to template
		$this->assignRef('xref'         , $xref);
		$this->assignRef('editor'      	, $editor);
		$this->assignRef('lists'        , $lists);
		$this->assignRef('request_url'	, $uri->toString());
		$this->assignRef('params'	  ,     $params);
		$this->assignRef('customfields' , $customfields);
		$this->assignRef('pane'			    , $pane);

		parent::display($tpl);
	}

	protected function _displayclosexref($tpl)
	{
		$document = JFactory::getDocument();
		$params   = JComponentHelper::getParams('com_redevent');

		$xref = $this->get('xref');

		$date = (!RedeventHelper::isValidDate($xref->dates) ? JText::_('COM_REDEVENT_Open_date') : strftime( $params->get('backend_formatdate', '%d.%m.%Y'), strtotime( $xref->dates )));
		$enddate  = (!RedeventHelper::isValidDate($xref->enddates) || $xref->enddates == $xref->dates) ? '' : strftime( $params->get('backend_formatdate', '%d.%m.%Y'), strtotime( $xref->enddates ));

		$displaydate = $date . ($enddate ? ' - '.$enddate: '');

		$displaytime = '';
		/* Get the time */
		if (isset($xref->times) && $xref->times != '00:00:00')
		{
			$displaytime = strftime( $params->get('formattime', '%H:%M'), strtotime( $xref->times ));

			if (isset($xref->endtimes) && $xref->endtimes != '00:00:00')
			{
				$displaytime .= ' - '.strftime( $params->get('formattime', '%H:%M'), strtotime( $xref->endtimes ));
			}
		}

		$json_data = array( 'id'        => $xref->id,
				'venue'     => $xref->venue,
				'date'      => $displaydate,
				'time'      => $displaytime,
				'published' => $xref->published,
				'note'      => $xref->note,
				'featured'  => $xref->featured,
		);

		if (function_exists('json_encode'))
		{
			$js = 'window.parent.updatexref('.json_encode($json_data).');';
			$document->addScriptDeclaration($js);
		}
		else
		{
			echo JText::_('COM_REDEVENT_ERROR_JSON_IS_NOT_ENABLED');
		}

		return;
	}

	/**
	 * prints the code for tags display
	 *
	 * @param array tags to exclude from printing
	 */
	protected function printTags($field = '')
	{
		?>
<div class="tagsdiv">
	<?php echo JHTML::link('index.php?option=com_redevent&view=tags&tmpl=component&field='.$field, JText::_('COM_REDEVENT_TAGS'), 'class="modal"'); ?>
</div>
<?php
	}
}
