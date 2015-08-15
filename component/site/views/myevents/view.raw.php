<?php
/**
 * @package    Redevent.Site
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Raw View class for the my events View
 *
 * @package  Redevent.Site
 * @since    2.0
 */
class RedeventViewMyevents extends RViewSite
{
	/**
	 * Creates the MyItems View
	 *
	 * @param   string  $tpl  template file to load
	 *
	 * @return void
	 */
	public function display($tpl = null)
	{
		switch ($this->getLayout())
		{
			case 'managedevents':
				return $this->displayEvents($tpl);

			case 'managedsessions':
				return $this->displaySessions($tpl);

			case 'managedvenues':
				return $this->displayVenues($tpl);

			case 'attending':
				return $this->displayAttending($tpl);

			case 'attended':
				return $this->displayAttended($tpl);

			default:
				echo 'Error: unkown layout ' . $this->getLayout();
		}
	}

	/**
	 * Specialize MyItems layout
	 *
	 * @param   string  $tpl  template file to load
	 *
	 * @return void
	 */
	protected function displaySessions($tpl)
	{
		$user      = JFactory::getUser();
		$mainframe = JFactory::getApplication();
		$params    = $mainframe->getParams();

		if (!$user->get('id'))
		{
			return false;
		}

		$model = RModel::getFrontInstance('Mysessions');

		$acl = RedeventUserAcl::getInstance();

		$state = $model->getState();
		$filter_event = $state->get('filter_event');

		// Get data from model
		$sessions = $model->getItems();
		$sessions_pageNav = $model->getPagination();

		// Sorting and filtering
		$lists = $this->buildSortLists();
		$lists['limitstart'] = $state->get('list.start');

		$options = array(JHTML::_('select.option', 0, JText::_('COM_REDEVENT_select_event')));

		if ($ev = $this->get('EventsOptions'))
		{
			$options = array_merge($options, $ev);
		}

		$lists['filter_event'] = JHTML::_('select.genericlist', $options, 'filter_event', '', 'value', 'text', $filter_event);

		$this->assign('action', JRoute::_(RedeventHelperRoute::getMyeventsRoute()));

		$this->assignRef('sessions', $sessions);
		$this->assignRef('params', $params);
		$this->assignRef('sessions_pageNav', $sessions_pageNav);
		$this->assignRef('acl',         $acl);
		$this->assignRef('lists',      $lists);

		$cols = explode(',', $params->get('lists_columns', 'date, title, venue, city, category'));
		$cols = RedeventHelper::validateColumns($cols);
		$this->assign('columns',        $cols);

		$this->setLayout('default');
		echo $this->loadTemplate('sessions');

		return true;
	}

	/**
	 * Specialize MyItems layout
	 *
	 * @param   string  $tpl  template file to load
	 *
	 * @return void
	 */
	protected function displayVenues($tpl)
	{
		$user      = JFactory::getUser();
		$mainframe = JFactory::getApplication();
		$params    = $mainframe->getParams();

		if (!$user->get('id'))
		{
			return false;
		}

		$acl = RedeventUserAcl::getInstance();

		$model = RModel::getFrontInstance('Myvenues');
		$state = $model->getState();

		// Get data from model
		$items = $model->getItems();
		$pageNav = $model->getPagination();

		// Sorting and filtering
		$lists = $this->buildSortLists();

		$lists['limitstart'] = $state->get('list.start');

		$this->assign('action', JRoute::_(RedeventHelperRoute::getMyeventsRoute()));

		$this->assignRef('venues', $items);
		$this->assignRef('params', $params);
		$this->assignRef('venues_pageNav', $pageNav);
		$this->assignRef('acl',         $acl);
		$this->assignRef('lists',      $lists);

		$this->setLayout('default');
		echo $this->loadTemplate('venues');

		return true;
	}

	/**
	 * Specialize MyItems layout
	 *
	 * @param   string  $tpl  template file to load
	 *
	 * @return void
	 */
	protected function displayAttending($tpl)
	{
		$user      = JFactory::getUser();
		$mainframe = JFactory::getApplication();
		$params    = $mainframe->getParams();

		if (!$user->get('id'))
		{
			return false;
		}

		$acl = RedeventUserAcl::getInstance();

		$model = RModel::getFrontInstance('Myattending');
		$state = $model->getState();

		// Get data from model
		$items = $model->getItems();
		$pageNav = $model->getPagination();

		// Sorting and filtering
		$lists = $this->buildSortLists();

		$lists['limitstart'] = $state->get('list.start');

		$this->assign('action', JRoute::_(RedeventHelperRoute::getMyeventsRoute()));

		$this->assignRef('attending', $items);
		$this->assignRef('params', $params);
		$this->assignRef('attending_pageNav', $pageNav);
		$this->assignRef('acl',         $acl);
		$this->assignRef('lists',      $lists);

		$this->setLayout('default');
		echo $this->loadTemplate('attending');

		return true;
	}

	/**
	 * Specialize MyItems layout
	 *
	 * @param   string  $tpl  template file to load
	 *
	 * @return void
	 */
	protected function displayAttended($tpl)
	{
		$user      = JFactory::getUser();
		$mainframe = JFactory::getApplication();
		$params    = $mainframe->getParams();

		if (!$user->get('id'))
		{
			return false;
		}

		$acl = RedeventUserAcl::getInstance();

		$model = RModel::getFrontInstance('Myattended');
		$state = $model->getState();

		// Get data from model
		$items = $model->getItems();
		$pageNav = $model->getPagination();

		// Sorting and filtering
		$lists = $this->buildSortLists();

		$lists['limitstart'] = $state->get('list.start');

		$this->assign('action', JRoute::_(RedeventHelperRoute::getMyeventsRoute()));

		$this->assignRef('attended', $items);
		$this->assignRef('params', $params);
		$this->assignRef('attended_pageNav', $pageNav);
		$this->assignRef('acl',         $acl);
		$this->assignRef('lists',      $lists);

		$this->setLayout('default');
		echo $this->loadTemplate('attended');

		return true;
	}

	/**
	 * Method to build the sortlists
	 *
	 * @return array
	 */
	protected function buildSortLists()
	{
		$filter_order = JFactory::getApplication()->input->getCmd('filter_order', 'x.dates');
		$filter_order_Dir = JFactory::getApplication()->input->getWord('filter_order_Dir', 'ASC');

		$filter = $this->escape(JFactory::getApplication()->input->getString('filter'));
		$filter_type = JFactory::getApplication()->input->getString('filter_type');

		$sortselects = array ();
		$sortselects[]	= JHTML::_('select.option', 'title', JText::_('COM_REDEVENT_FILTER_SELECT_EVENT'));
		$sortselects[] 	= JHTML::_('select.option', 'venue', JText::_('COM_REDEVENT_FILTER_SELECT_VENUE'));
		$sortselects[] 	= JHTML::_('select.option', 'city', JText::_('COM_REDEVENT_FILTER_SELECT_CITY'));
		$sortselects[] 	= JHTML::_('select.option', 'type', JText::_('COM_REDEVENT_FILTER_SELECT_CATEGORY'));

		$sortselect = JHTML::_('select.genericlist', $sortselects, 'filter_type', 'size="1" class="inputbox"', 'value', 'text', $filter_type);

		$lists['order_Dir'] = $filter_order_Dir;
		$lists['order'] = $filter_order;
		$lists['filter'] = $filter;
		$lists['filter_types'] = $sortselect;

		return $lists;
	}

	/**
	 * Creates the xref edit button
	 *
	 * @param   int  $id    event id
	 * @param   int  $xref  session id
	 *
	 * @return string html
	 */
	public static function xrefeditbutton($id, $xref)
	{
		$acl = &RedeventUserAcl::getInstance();

		if (!$acl->canEditXref($xref))
		{
			return '';
		}

		JHTML::_('behavior.tooltip');

		$image = JHTML::_('image', 'media/com_redevent/images/calendar_edit.png', JText::_('COM_REDEVENT_EDIT_XREF'));

		$overlib = JText::_('COM_REDEVENT_EDIT_XREF_TIP');
		$text = JText::_('COM_REDEVENT_EDIT_XREF');

		$link 	= 'index.php?option=com_redevent&view=editevent&layout=eventdate&id=' . $id . '&xref=' . $xref;
		$output	= '<a href="' . JRoute::_($link) . '" class="editlinktip hasTooltip" title="' . $text . '<br/>' . $overlib . '">' . $image . '</a>';

		return $output;
	}

	/**
	 * Creates the event edit button
	 *
	 * @param   int  $id    event id
	 * @param   int  $xref  session id
	 *
	 * @return string html
	 */
	public static function eventeditbutton($id, $xref)
	{
		$acl = &RedeventUserAcl::getInstance();

		if (!$acl->canEditEvent($id))
		{
			return '';
		}

		JHTML::_('behavior.tooltip');

		$image = JHTML::_('image', 'media/com_redevent/images/calendar_edit.png', JText::_('COM_REDEVENT_EDIT_EVENT'));

		$overlib = JText::_('COM_REDEVENT_EDIT_EVENT_TIP');
		$text = JText::_('COM_REDEVENT_EDIT_EVENT');

		$link 	= RedeventHelperRoute::getEditEventRoute($id, $xref) . '&referer=myevents';
		$output	= '<a href="' . JRoute::_($link) . '" class="editlinktip hasTooltip" title="' . $text . '<br/>' . $overlib . '">' . $image . '</a>';

		return $output;
	}

	/**
	 * Creates the xref edit button
	 *
	 * @param   int  $id  session id
	 *
	 * @return string html
	 */
	public static function xrefdeletebutton($id)
	{
		JHTML::_('behavior.tooltip');
		$document = JFactory::getDocument();

		$image = JHTML::_('image', 'media/com_redevent/images/no.png', JText::_('COM_REDEVENT_DELETE_XREF'));

		$overlib = JText::_('COM_REDEVENT_DELETE_XREF_TIP');
		$text = JText::_('COM_REDEVENT_DELETE_XREF');

		$link 	= 'index.php?option=com_redevent&task=deletexref&xref=' . $id;
		$output	= '<a href="' . JRoute::_($link) . '" class="deletelink hasTooltip" title="' . $text . '<br/>' . $overlib . '">' . $image . '</a>';

		return $output;
	}

	/**
	 * Creates the attendees edit button
	 *
	 * @param   int  $id  id
	 *
	 * @return string html
	 */
	public static function xrefattendeesbutton($id)
	{
		JHTML::_('behavior.tooltip');

		$image = JHTML::_('image', 'media/com_redevent/images/attendees.png', JText::_('COM_REDEVENT_EDIT_ATTENDEES'));

		$overlib = JText::_('COM_REDEVENT_EDIT_ATTENDEES_TIP');
		$text = JText::_('COM_REDEVENT_EDIT_ATTENDEES');
		$link 	= RedeventHelperRoute::getManageAttendees($id, 'registration.manageattendees');
		$output	= '<a href="' . JRoute::_($link) . '" class="editlinktip hasTooltip" title="' . $text . '<br/>' . $overlib . '">' . $image . '</a>';

		return $output;
	}

	/**
	 * Creates the venue edit button
	 *
	 * @param   int  $id  venue id
	 *
	 * @return string html
	 */
	public static function venueeditbutton($id)
	{
		JHTML::_('behavior.tooltip');

		$image = JHTML::_('image', 'media/com_redevent/images/calendar_edit.png', JText::_('COM_REDEVENT_EDIT_VENUE'));

		$overlib = JText::_('COM_REDEVENT_EDIT_VENUE_TIP');
		$text = JText::_('COM_REDEVENT_EDIT_VENUE');

		$link 	= 'index.php?option=com_redevent&view=editvenue&id=' . $id;
		$output	= '<a href="' . JRoute::_($link) . '" class="editlinktip hasTooltip" title="' . $text . '<br/>' . $overlib . '">' . $image . '</a>';

		return $output;
	}
}
