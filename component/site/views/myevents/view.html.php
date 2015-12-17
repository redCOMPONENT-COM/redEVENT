<?php
/**
 * @package    Redevent.Site
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * HTML View class for the my events View
 *
 * @package  Redevent.Site
 * @since    2.0
 */
class RedeventViewMyevents extends RedeventViewFront
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
		$mainframe = JFactory::getApplication();

		$user = JFactory::getUser();

		if (!$user->get('id'))
		{
			$mainframe->redirect('index.php', JText::_('COM_REDEVENT_Only_logged_users_can_access_this_page'), 'error');
		}

		// Initialize variables
		$config = RedeventHelper::config();
		$params     = $mainframe->getParams();
		$uri        = JFactory::getURI();
		$acl        = RedeventUserAcl::getInstance();

		$menu = $mainframe->getMenu();
		$item = $menu->getActive();

		RHelperAsset::load('myevents.js');
		RHelperAsset::load('ajaxnav.js', 'com_redevent');
		JText::script("COM_REDEVENT_CONFIRM_DELETE_DATE");
		JText::script("COM_REDEVENT_MYEVENTS_CANCEL_REGISTRATION_WARNING");

		// Get variables
		$task = JFactory::getApplication()->input->getWord('task');
		$pop = JFactory::getApplication()->input->getBool('pop');

		$modelEvents = RModel::getFrontInstance('Myevents');
		$modelSessions = RModel::getFrontInstance('Mysessions');
		$modelAttended = RModel::getFrontInstance('Myattended');
		$modelAttending = RModel::getFrontInstance('Myattending');
		$modelVenues = RModel::getFrontInstance('Myvenues');

		// Params
		$params->def('page_title', $item ? $item->title : 'COM_REDEVENT_VIEW_MYEVENTS_TITLE');

		if ($pop)
		{
			// If printpopup set true
			$params->set('popup', 1);
		}

		// Set Page title
		$pagetitle = $params->get('page_title', JText::_('COM_REDEVENT_MY_EVENTS'));
		JFactory::getDocument()->setTitle($pagetitle);

		// Create select lists
		$lists = $this->_buildSortLists();

		if ($lists['filter'])
		{
			$uri->setVar('filter', $lists['filter']);
			$uri->setVar('filter_type', JFactory::getApplication()->input->getString('filter_type'));
		}
		else
		{
			$uri->delVar('filter');
			$uri->delVar('filter_type');
		}

		// Pagination will be done by ajax, so all are set to 0 when loading the initial page
		$lists['limitstart'] = 0;

		// Events filter
		$hasManagedEvents = false;
		$options = array(JHTML::_('select.option', 0, JText::_('COM_REDEVENT_select_event')));

		if ($ev = $modelSessions->getEventsOptions())
		{
			$hasManagedEvents = count($ev);
			$options = array_merge($options, $ev);
		}

		$lists['filter_event'] = JHTML::_('select.genericlist', $options, 'filter_event', '', 'value', 'text', $modelSessions->getState()->get('filter'));

		$this->assign('action', JRoute::_(RedeventHelperRoute::getMyeventsRoute()));

		$this->events = $modelEvents->getItems();
		$this->sessions = $modelSessions->getItems();
		$this->venues = $modelVenues->getItems();
		$this->attending = $modelAttending->getItems();
		$this->attended = $modelAttended->getItems();
		$this->task = $task;
		$this->params = $params;
		$this->events_pageNav = $modelEvents->getPagination();
		$this->sessions_pageNav = $modelSessions->getPagination();
		$this->venues_pageNav = $modelVenues->getPagination();
		$this->attending_pageNav = $modelAttending->getPagination();
		$this->attended_pageNav = $modelAttended->getPagination();
		$this->config = $config;
		$this->pagetitle = $pagetitle;
		$this->lists = $lists;
		$this->acl = $acl;
		$this->hasManagedEvents = $hasManagedEvents;
		$this->canAddXref = $acl->canAddXref();
		$this->canAddEvent = $acl->canAddEvent();
		$this->canAddVenue = $acl->canAddVenue();

		$cols = explode(',', $params->get('lists_columns', 'date, title, venue, city, category'));
		$cols = RedeventHelper::validateColumns($cols);
		$this->columns = $cols;

		parent::display($tpl);
	}

	/**
	 * Method to build the sortlists
	 *
	 * @return array
	 */
	protected function _buildSortLists()
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

		RHtml::_('rbootstrap.tooltip');

		$image = RHelperAsset::load('calendar_edit.png', null, array('alt' => JText::_('COM_REDEVENT_EDIT_XREF')));

		$overlib = JText::_('COM_REDEVENT_EDIT_XREF_TIP');
		$text = JText::_('COM_REDEVENT_EDIT_XREF');

		$link 	= RedeventHelperRoute::getEditSessionTaskRoute($id, $xref);
		$output = RHtml::tooltip($overlib, $text, null, $image, $link);

		return $output;
	}

	/**
	 * Creates the event delete button
	 *
	 * @param   int  $id    event id
	 * @param   int  $xref  session id
	 *
	 * @return string html
	 */
	public static function eventeditbutton($id, $xref = null)
	{
		$acl = RedeventUserAcl::getInstance();

		if (!$acl->canEditEvent($id))
		{
			return '';
		}

		RHtml::_('rbootstrap.tooltip');

		$image = RHelperAsset::load('calendar_edit.png', null, array('alt' => JText::_('COM_REDEVENT_EDIT_EVENT')));

		$overlib = JText::_('COM_REDEVENT_EDIT_EVENT_TIP');
		$text = JText::_('COM_REDEVENT_EDIT_EVENT');

		$link 	= RedeventHelperRoute::getEditEventRoute($id, $xref);
		$output = RHtml::tooltip($overlib, $text, null, $image, $link);

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
		RHtml::_('rbootstrap.tooltip');

		$image = RHelperAsset::load('no.png', null, array('alt' => JText::_('COM_REDEVENT_DELETE_XREF')));

		$overlib = JText::_('COM_REDEVENT_DELETE_XREF_TIP');
		$text = JText::_('COM_REDEVENT_DELETE_XREF');

		$link 	= 'index.php?option=com_redevent&task=deletexref&xref=' . $id;
		$output = RHtml::tooltip($overlib, $text, null, $image, $link, '', 'hasTooltip deletelink');

		return $output;
	}

	/**
	 * Creates the event delete button
	 *
	 * @param   int  $id  event id
	 *
	 * @return string html
	 */
	public static function eventdeletebutton($id)
	{
		JHTML::_('behavior.tooltip');

		$image = RHelperAsset::load('no.png', null, array('alt' => JText::_('COM_REDEVENT_DELETE_EVENT')));

		$overlib = JText::_('COM_REDEVENT_DELETE_EVENT_TIP');
		$text = JText::_('COM_REDEVENT_DELETE_EVENT');

		$return = base64_encode(RedeventHelperRoute::getMyeventsRoute());
		$link = 'index.php?option=com_redevent&task=editevent.delete&id=' . $id . '&return=' . $return;
		$output = RHtml::tooltip($overlib, $text, null, $image, $link, '', 'hasTooltip deletelink');

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
		RHtml::_('rbootstrap.tooltip');

		$image = RHelperAsset::load('attendees.png', null, array('alt' => JText::_('COM_REDEVENT_EDIT_ATTENDEES')));

		$overlib = JText::_('COM_REDEVENT_EDIT_ATTENDEES_TIP');
		$text = JText::_('COM_REDEVENT_EDIT_ATTENDEES');
		$link 	= RedeventHelperRoute::getManageAttendees($id, 'registration.manageattendees');
		$output = RHtml::tooltip($overlib, $text, null, $image, $link);

		return $output;
	}

	/**
	 * Creates the venue edit button
	 *
	 * @param   int     $id      venue id
	 * @param   string  $return  return url
	 *
	 * @return string html
	 */
	public static function venueeditbutton($id, $return = 'auto')
	{
		RHtml::_('rbootstrap.tooltip');

		if ($return == 'auto')
		{
			$returnAppend = '&return=' . base64_encode(RedeventHelperRoute::getMyeventsRoute());
		}
		elseif ($return)
		{
			$returnAppend = '&return=' . base64_encode($return);
		}
		else
		{
			$returnAppend = '';
		}

		$image = RHelperAsset::load('edit_venue.png', null, array('alt' => JText::_('COM_REDEVENT_EDIT_VENUE')));

		$overlib = JText::_('COM_REDEVENT_EDIT_VENUE_TIP');
		$text = JText::_('COM_REDEVENT_EDIT_VENUE');

		$link = RedeventHelperRoute::getEditVenueRoute($id) . $returnAppend;
		$output = RHtml::tooltip($overlib, $text, null, $image, $link);

		return $output;
	}
}
