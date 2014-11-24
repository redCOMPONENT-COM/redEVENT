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

	function old_display($tpl = null)
	{
		$mainframe = &JFactory::getApplication();
		$option = JRequest::getCmd('option');
		$user 		= & JFactory::getUser();

		$document = &JFactory::getDocument();


		ELAdmin::setMenu();

		$db		 = &JFactory::getDBO();
		$uri	 = &JFactory::getURI();
		$state = &$this->get('state');
		$params = JComponentHelper::getParams('com_redevent');

		$filter_order		= $state->get('filter_order');
		$filter_order_Dir	= $state->get('filter_order_Dir');
		$search          = $state->get('search');
		$filter_state    = $state->get('filter_state');
		$filter_featured = $state->get('filter_featured');
		$eventid         = $state->get('eventid');
		$venueid         = $state->get('venueid');

		// Get data from the model
		$items		= & $this->get( 'Data' );
		$event		= & $this->get( 'Event' );
		$venue		= & $this->get( 'Venue' );
		$total		= & $this->get( 'Total' );
		$pagination = & $this->get( 'Pagination' );

		// table ordering
		$lists['order_Dir'] = $filter_order_Dir;
		$lists['order']     = $filter_order;

		// search filter
		$lists['search']= $search;

		//publish unpublished filter
		$options = array( JHTML::_('select.option', '', ' - '.JText::_('COM_REDEVENT_Select_state').' - '),
		                  JHTML::_('select.option', 'published', JText::_('COM_REDEVENT_Published')),
		                  JHTML::_('select.option', 'unpublished', JText::_('COM_REDEVENT_Unpublished')),
		                  JHTML::_('select.option', 'archived', JText::_('COM_REDEVENT_Archived')),
		                  JHTML::_('select.option', 'notarchived', JText::_('COM_REDEVENT_Not_archived')),
		                  );
		$lists['state']	= JHTML::_('select.genericlist', $options, 'filter_state', 'class="inputbox" onchange="submitform();" size="1"', 'value', 'text', $filter_state );

		//featured filter
		$options = array( JHTML::_('select.option', '', ' - '.JText::_('COM_REDEVENT_Select_featured').' - '),
		                  JHTML::_('select.option', 'featured', JText::_('Com_redevent_Featured')),
		                  JHTML::_('select.option', 'unfeatured', JText::_('Com_redevent_not_Featured')),
		                  );
		$lists['featured']	= JHTML::_('select.genericlist', $options, 'filter_featured', 'class="inputbox" onchange="submitform();" size="1"', 'value', 'text', $filter_featured );

		$options = $this->get('groupsoptions');
		$options = array_merge(array(JHTML::_('select.option', '', ' - '.JText::_('COM_REDEVENT_SESSIONS_filter_group_select').' - ')), $options);
		$lists['filter_group']	= JHTML::_('select.genericlist', $options, 'filter_group', 'class="inputbox" onchange="submitform();" size="1"', 'value', 'text', $state->get('filter_group'));

		$options = array(JHTML::_('select.option', 0, JText::_('COM_REDEVENT_SESSIONS_filter_group_select_view')),
		                 JHTML::_('select.option', 1, JText::_('COM_REDEVENT_SESSIONS_filter_group_select_manage')), );
		$lists['filter_group_manage']	= JHTML::_('select.genericlist', $options, 'filter_group_manage', 'class="inputbox" onchange="submitform();" size="1"', 'value', 'text', $state->get('filter_group_manage'));

		FOFTemplateUtils::addCSS('media://com_redevent/css/backend.css');

		// Set toolbar items for the page
		if ($eventid) {
			$document->setTitle(JText::sprintf('COM_REDEVENT_PAGETITLE_SESSIONS_EVENT', $event->title));
			JToolBarHelper::title(   JText::sprintf( 'COM_REDEVENT_TITLE_SESSIONS_EVENT', $event->title ), 're-sessions' );
		}
		else {
			$document->setTitle(JText::sprintf('COM_REDEVENT_PAGETITLE_SESSIONS'));
			JToolBarHelper::title(   JText::sprintf( 'COM_REDEVENT_TITLE_SESSIONS'), 're-sessions' );
		}
		if ($event && $event->id) {
			JToolBarHelper::addNewX();
		}
		JToolBarHelper::custom('copy', 'copy', 'copy', 'copy', true);
		JToolBarHelper::editListX();
		JToolBarHelper::deleteList(JText::_('COM_REDEVENT_SESSIONS_REMOVE_CONFIRM_MESSAGE'));
		JToolBarHelper::spacer();
		JToolBarHelper::publish();
		JToolBarHelper::unpublish();
		JToolBarHelper::archiveList();
		JToolBarHelper::spacer();
		JToolBarHelper::custom('featured', 'featured', 'featured', 'COM_REDEVENT_FEATURE', true);
		JToolBarHelper::custom('unfeatured', 'unfeatured', 'unfeatured', 'COM_REDEVENT_UNFEATURE', true);
		JToolBarHelper::spacer();
		JToolBarHelper::custom('back', 'back', 'back', 'COM_REDEVENT_BACK', false);
		if ($user->authorise('core.admin', 'com_redevent')) {
			JToolBarHelper::spacer();
			JToolBarHelper::preferences('com_redevent', '600', '800');
		}

		// event
		JHTML::_('behavior.modal', 'a.modal');
		$js = "
		window.addEvent('domready', function(){

			document.id('ev-reset-button').addEvent('click', function(){
				document.id('eventid').value = 0;
				document.id('eventid_name').value = '".JText::_('COM_REDEVENT_SESSIONS_EVENT_FILTER_ALL')."';
				document.id('adminForm').submit();
			});

			document.id('venue-reset-button').addEvent('click', function(){
				document.id('venueid').value = 0;
				document.id('venueid_name').value = '".JText::_('COM_REDEVENT_SESSIONS_VENUE_FILTER_ALL')."';
				document.id('adminForm').submit();
			});

		});

		function elSelectEvent(id, title, field) {
			document.id('eventid').value = id;
			document.id('eventid_name').value = title;
			SqueezeBox.close();
			document.id('adminForm').submit();
		}

		function elSelectVenue(id, title, field) {
			document.id('venueid').value = id;
			document.id('venueid_name').value = title;
			SqueezeBox.close();
			document.id('adminForm').submit();
		}";
		$document->addScriptDeclaration($js);

		$uri->delVar('eventid');
		$uri->delVar('venueid');
		$this->assignRef('user',		JFactory::getUser());
		$this->assignRef('lists',		$lists);
		$this->assignRef('items',		$items);
		$this->assignRef('event',		$event);
		$this->assignRef('venue',		$venue);
		$this->assignRef('eventid',		$eventid);
		$this->assignRef('venueid',		$venueid);
		$this->assignRef('params',    $params);
		$this->assignRef('pagination',	$pagination);
		$this->assignRef('request_url',	$uri->toString());

		parent::display($tpl);
	}

	/**
	 * returns toggle image link for session feature
	 *
	 * @param object $row
	 * @param int $i
	 * @return string html
	 */
	function featured( &$row, $i )
	{
		$params = array('border' => 0);
		$img 	= $row->featured ? JHTML::image('administrator/components/com_redevent/assets/images/icon-16-featured.png', JText::_('COM_REDEVENT_SESSION_FEATURED'), array('border' => 0))
		                       : JHTML::image('administrator/components/com_redevent/assets/images/icon-16-unfeatured.png', JText::_('COM_REDEVENT_SESSION_NOT_FEATURED'), array('border' => 0));
		$task 	= $row->featured ? 'unfeatured' : 'featured';
		$action = $row->featured ? JText::_( 'COM_REDEVENT_FEATURE' ) : JText::_( 'COM_REDEVENT_UNFEATURE' );

		$href = '
		<a href="javascript:void(0);" onclick="return listItemTask(\'cb'. $i .'\',\''. $task .'\')" title="'. $action .'">
		'. $img .'</a>'
		;

		return $href;
	}
}
