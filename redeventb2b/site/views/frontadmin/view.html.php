<?php
/**
 * @package    Redeventb2b.site
 * @copyright  Copyright (C) 2008 - 2016 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * HTML View class for the frontend admin View
 *
 * @since  2.0
 */
class Redeventb2bViewFrontadmin extends RViewAdmin
{
	/**
	 * Creates the View
	 *
	 * @param   string  $tpl  template to display
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 *
	 * @since 2.5
	 */
	public function display($tpl = null)
	{
		if ($this->getLayout() == 'searchsessions')
		{
			return $this->displaySearchSessions($tpl);
		}

		if ($this->getLayout() == 'searchbookings')
		{
			return $this->displaySearchBookings($tpl);
		}

		if ($this->getLayout() == 'attendees')
		{
			return $this->displayAttendees($tpl);
		}

		if ($this->getLayout() == 'members')
		{
			return $this->displayMembers($tpl);
		}

		if ($this->getLayout() == 'editmember')
		{
			return $this->displayEditmember($tpl);
		}

		if ($this->getLayout() == 'memberbooked')
		{
			return $this->displayMemberBooked($tpl);
		}

		if ($this->getLayout() == 'memberprevious')
		{
			return $this->displayMemberPrevious($tpl);
		}

		if ($this->getLayout() == 'infoform')
		{
			return $this->displayInfoform($tpl);
		}

		if ($this->getLayout() == 'closemodalmember')
		{
			return $this->displayCloseModalMember($tpl);
		}

		JHtml::_('behavior.framework');
		JHtml::_('behavior.tooltip');
		JHtml::_('behavior.modal');

		$mainframe = JFactory::getApplication();

		$user = JFactory::getUser();

		if (!$user->get('id'))
		{
			$mainframe->redirect('index.php', JText::_('COM_REDEVENT_Only_logged_users_can_access_this_page'), 'error');
		}

		// Initialize variables
		$document   = JFactory::getDocument();
		$elsettings = RedeventHelper::config();
		$pathway    = $mainframe->getPathWay();
		$params     = $mainframe->getParams();
		$uri        = JFactory::getURI();
		$modelAttendees = RModel::getFrontInstance('FrontadminMembers');

		$menu = JFactory::getApplication()->getMenu();
		$item = $menu->getActive();

		// Add css file
		if (!$params->get('custom_css'))
		{
			RHelperAsset::load('redevent-b2b.css');
		}
		else
		{
			$document->addStyleSheet($params->get('custom_css'));
		}

		$document->addScript('media/jui/js/jquery.autocomplete.min.js');
		Redeventb2bHelperScript::load('b2b.js');

		// For redmember
		$document->addScript('components/com_redmember/assets/js/threeselectdate.js');

		$document->addCustomTag('<!--[if IE]><style type="text/css">.floattext{zoom:1;}, * html #eventlist dd { height: 1%; }</style><![endif]-->');

		$useracl = RedeventUserAcl::getInstance();

		$state = $this->get('state');

		// Events filter
		$options = array(JHtml::_('select.option', '', JText::_('COM_REDEVENT_FILTER_SELECT_EVENT')));
		$this->events_options     = array_merge($options, $this->get('EventsOptions'));

		// Sessions filter
		JText::script("COM_REDEVENT_SESSION");
		$options = array(JHtml::_('select.option', '', JText::_('COM_REDEVENT_FILTER_SELECT_SESSION')));
		$this->sessions_options   = array_merge($options, $this->get('SessionsOptions'));

		// Venues filter
		$options = array(JHtml::_('select.option', '', JText::_('COM_REDEVENT_FILTER_SELECT_VENUE')));
		$this->venues_options     = array_merge($options, $this->get('VenuesOptions'));

		// Categories filter
		$options = array(JHtml::_('select.option', '', JText::_('COM_REDEVENT_FILTER_SELECT_CATEGORY')));
		$this->categories_options = array_merge($options, $this->get('CategoriesOptions'));

		// Organizations filter
		$options = array(JHtml::_('select.option', '', JText::_('COM_REDEVENT_FRONTEND_ADMIN_ORGANIZATION')));
		$this->organizations_options = array_merge($options, $this->get('OrganizationsOptions'));

		// Users filter
		JText::script("COM_REDEVENT_FRONTEND_ADMIN_SELECT_USER");
		$options = array(JHtml::_('select.option', '', JText::_('COM_REDEVENT_FRONTEND_ADMIN_SELECT_USER')));
		$this->users_options = array_merge($options, $this->get('UsersOptions'));

		$this->filter_from        = $state->get('filter_from');
		$this->filter_to          = $state->get('filter_to');

		$this->order     = $state->get('filter_order');
		$this->order_Dir = $state->get('filter_order_Dir');

		$this->useracl = $useracl;
		$this->params  = $params;
		$this->state   = $state;

		$this->organization = $this->get('Organization');
		$this->limitstart = $state->get('limitstart');

		// JS language strings
		JText::script("COM_REDEVENT_FRONTEND_ADMIN_COURSE_SEARCH_TITLE");
		JText::script("COM_REDEVENT_BOOK_SESSION");
		JText::script("COM_REDEVENT_FRONTEND_ADMIN_CONFIRM_CLOSE");
		JText::script("COM_REDEVENT_FRONTEND_ADMIN_CONFIRM");
		JText::script("COM_REDEVENT_FRONTEND_ADMIN_CONFIRM_PUBLISH");
		JText::script("COM_REDEVENT_FRONTEND_ADMIN_CONFIRM_UNPUBLISH");
		JText::script("COM_REDEVENT_FRONTEND_ADMIN_SELECT_SESSION_FIRST");
		JText::script("COM_REDEVENT_FRONTEND_ADMIN_SELECT_MEMBER_FIRST");
		JText::script("COM_REDEVENTB2B_EDIT_MEMBER_JS_VALIDATION_ERROR");
		JText::script("COM_REDEVENT_FILTER_SELECT_EVENT");
		JText::script("COM_REDEVENT_FILTER_SELECT_SESSION");
		JText::script("COM_REDEVENT_FILTER_SELECT_VENUE");
		JText::script("COM_REDEVENT_FILTER_SELECT_CATEGORY");

		JText::script("COM_REDEVENT_FRONTEND_ADMIN_BREADCRUMB_YOU_ARE_HERE");
		JText::script("COM_REDEVENT_FRONTEND_ADMIN_BREADCRUMB_OVERVIEW");
		JText::script("COM_REDEVENT_FRONTEND_ADMIN_BREADCRUMB_BOOK");
		JText::script("COM_REDEVENT_FRONTEND_ADMIN_BREADCRUMB_SEARCH_RESULTS");
		JText::script("COM_REDEVENT_FRONTEND_ADMIN_NOT_ENOUGH_PLACES_LEFT");
		JText::script("COM_REDEVENT_FRONTEND_ADMIN_PLEASE_SELECT_SESSION_FIRST");
		JText::script("COM_REDEVENT_FRONTEND_ADMIN_COMMENT_EMAIL_SENT");
		JText::script("COM_REDEVENT_FRONTEND_ADMIN_MEMBER_SAVED");

		return parent::display($tpl);
	}

	/**
	 * Creates the search View
	 *
	 * @param   string  $tpl  template to display
	 *
	 * @return void
	 */
	protected function displaySearchSessions($tpl = null)
	{
		$useracl = RedeventUserAcl::getInstance();
		$params = JFactory::getApplication()->getParams('com_redevent');
		$state = $this->get('state');

		$this->order_Dir = $state->get('filter_order_dir');
		$this->order     = $state->get('filter_order');

		$this->params  = $params;
		$this->state   = $state;

		$this->useracl = $useracl;
		$this->sessions = $this->get('Sessions');
		$this->params  = $params;

		$this->pagination = $this->get('SessionsPagination');
		$this->limitstart = $state->get('limitstart');

		parent::display($tpl);
	}

	/**
	 * Creates the search View
	 *
	 * @param   string  $tpl  template to display
	 *
	 * @return void
	 */
	protected function displaySearchBookingss($tpl = null)
	{
		$useracl = RedeventUserAcl::getInstance();
		$params = JFactory::getApplication()->getParams('com_redevent');
		$state = $this->get('state');

		$this->order_Dir = $state->get('filter_order_dir');
		$this->order     = $state->get('filter_order');

		$this->params  = $params;
		$this->state   = $state;

		$this->useracl = $useracl;
		$this->sessions = $this->get('Bookings');
		$this->params  = $params;

		$this->pagination = $this->get('SessionsPagination');
		$this->limitstart = $state->get('limitstart');

		$this->bookings_pagination = $this->get('BookingsPagination');
		$this->bookings_limitstart = $state->get('bookings_limitstart');

		parent::display($tpl);
	}

	/**
	 * returns string for available places display
	 *
	 * @param   object  $row         session data
	 * @param   bool    $showBooked  show number of booked places
	 *
	 * @return string
	 */
	protected function printPlaces($row, $showBooked = true)
	{
		if ($this->isFull($row))
		{
			return '';
		}

		$maxLeftDisplay = 2000;

		if (!$row->maxattendees)
		{
			if ($showBooked)
			{
				$tip = JText::sprintf('COM_REDEVENT_FRONTEND_ADMIN_PLACES_BOOKED_D', $row->registered);

				return '<span class="hasTip" title="' . $tip . '">' . $row->registered . '</span>';
			}
			else
			{
				return '';
			}
		}
		else
		{
			// Only display up to $maxLeftDisplay left places
			$left = max(array($row->maxattendees - $row->registered, 0));
			$left = $left > $maxLeftDisplay ? $maxLeftDisplay . '+' : $left;

			$tip = JText::sprintf('COM_REDEVENT_FRONTEND_ADMIN_PLACES_BOOKED_D_LEFT_S', $row->registered, $left);

			if ($showBooked)
			{
				return '<span class="hasTip" title="' . $tip . '">' . $row->registered . '/' . $left . '</span>';
			}
			else
			{
				return '<span class="hasTip" title="' . $tip . '">' . $left . '</span>';
			}
		}
	}

	/**
	 * returns string for info icon when session is full
	 *
	 * @param   object  $row  session data
	 *
	 * @return string
	 */
	protected function printInfoIcon($row)
	{
		if (!$this->isFull($row))
		{
			return '';
		}

		$image = JHTML::image('media/com_redevent/images/b2b-getinfo.gif', JText::_('COM_REDEVENT_FRONTEND_ADMIN_QUERY_INFO_SESSION_FULL'));

		$tip  = JText::_('COM_REDEVENT_FRONTEND_ADMIN_QUERY_INFO_SESSION_FULL_DESC');
		$text = JText::_('COM_REDEVENT_FRONTEND_ADMIN_QUERY_INFO_SESSION_FULL');

		$attribs = array(
			'xref' => $row->xref,
			'class' => 'getinfo hasTip',
			'title' => $text,
			'tip' => $tip
		);

		$output = JHtml::link(
			'index.php?option=com_redeventb2b&task=frontadmin.getinfoform&tmpl=component&modal=1&xref=' . $row->xref, $image, $attribs
		);

		return $output;
	}

	/**
	 * Check if event is full
	 *
	 * @param   object  $row  row
	 *
	 * @return boolean
	 */
	protected function isFull($row)
	{
		// No limit
		if (!$row->maxattendees)
		{
			return false;
		}

		// Not full
		if ($row->registered >= $row->maxattendees)
		{
			return true;
		}

		return false;
	}

	/**
	 * Creates the attendees edit button
	 *
	 * @param   int  $id  xref id
	 *
	 * @return string html
	 */
	public static function bookbutton($id)
	{
		JHTML::_('behavior.tooltip');

		$image = JHTML::image('media/com_redevent/images/b2b-bookuser.png', JText::_('COM_REDEVENT_BOOK_EVENT'));

		$tip  = JText::_('COM_REDEVENT_BOOK_EVENT_DESC');
		$text = JText::_('COM_REDEVENT_BOOK_EVENT');

		$attribs = array(
			'xref' => $id,
			'class' => 'bookthis hasTip',
			'title' => $text,
			'tip' => $tip,
		);

		$output = JHtml::link('#', $image, $attribs);

		return $output;
	}

	/**
	 * Creates the attendees View
	 *
	 * @param   string  $tpl  template to display
	 *
	 * @return void
	 */
	protected function displayAttendees($tpl= null)
	{
		$model = $this->getModel('FrontadminMembers');
		$state = $model->getState();

		$this->attendees_order = $state->get('members_order');
		$this->attendees_order_dir = $state->get('members_order_dir');
		$this->state = $state;

		parent::display($tpl);
	}

	/**
	 * Creates the members View
	 *
	 * @param   string  $tpl  template to display
	 *
	 * @return void
	 */
	protected function displayMembers($tpl= null)
	{
		$model = $this->getModel('FrontadminMembers');
		$state = $model->getState();

		$this->members_order = $state->get('members_order');
		$this->members_order_dir = $state->get('members_order_dir');
		$this->members_pagination = $model->getPagination();
		$this->members_limitstart = $state->get('members_limitstart');
		$this->state = $state;

		parent::display($tpl);
	}

	/**
	 * Creates the edit member View
	 *
	 * @param   string  $tpl  template to display
	 *
	 * @return void
	 */
	protected function displayEditMember($tpl= null)
	{
		$document = JFactory::getDocument();

		$member = $this->get('MemberInfo');
		$booked = $this->get('MemberBooked');
		$previous = $this->get('MemberPrevious');
		$state = $this->get('state');

		$this->params = JFactory::getApplication()->getParams('com_redevent');

		$modal = JFactory::getApplication()->input->get('modal');

		if (!$orgId = JFactory::getApplication()->input->get('orgId'))
		{
			RedeventHelperLog::simpleLog('edit member view missing orgid');
			echo 'edit member view missing orgid';

			return;
		}

		$rmUser = RedmemberApi::getUser(JFactory::getApplication()->input->get('uid'));

		$this->form = $rmUser->getBaseForm();
		$this->tabs = $rmUser->getTabs();

		if ($modal)
		{
			// Add css file
			if (!$this->params->get('custom_css'))
			{
				RHelperAsset::load('redevent-b2b.css');
			}
			else
			{
				$document->addStyleSheet($this->params->get('custom_css'));
			}
		}

		$this->assignRef('member',     $member);
		$this->assignRef('booked',     $booked);
		$this->assignRef('previous',   $previous);
		$this->assignRef('modal',      $modal);
		$this->uid       = $state->get('uid');
		$this->orgId = $orgId;

		$this->booked_order = $state->get('booked_order');
		$this->booked_order_dir = $state->get('booked_order_dir');
		$this->booked_pagination = $this->get('MemberBookedPagination');
		$this->booked_limitstart = $state->get('booked_limitstart');

		$this->previous_order = $state->get('previous_order');
		$this->previous_order_dir = $state->get('previous_order_dir');
		$this->previous_pagination = $this->get('MemberPreviousPagination');
		$this->previous_limitstart = $state->get('previous_limitstart');

		$this->organizations_options = $this->get('OrganizationsOptions');

		parent::display($tpl);
	}

	/**
	 * Creates the member booked View
	 *
	 * @param   string  $tpl  template to display
	 *
	 * @return void
	 */
	protected function displayMemberBooked($tpl= null)
	{
		$booked = $this->get('MemberBooked');
		$state = $this->get('state');

		$this->params = JFactory::getApplication()->getParams('com_redevent');

		$this->sessions  = $booked;
		$this->order_input = "booked_order";
		$this->order_dir_input = "booked_order_dir";
		$this->order     = $state->get('booked_order');
		$this->order_dir = $state->get('booked_order_dir');
		$this->task      = 'getmemberbooked';
		$this->uid       = $state->get('uid');
		$this->pagination = $this->get('MemberBookedPagination');
		$this->limitstart_name = "booked_limitstart";
		$this->limitstart = $state->get('booked_limitstart');

		$this->setLayout('editmember_sessions');
		parent::display($tpl);
	}

	/**
	 * Creates the member prevous bookings View
	 *
	 * @param   string  $tpl  template to display
	 *
	 * @return void
	 */
	protected function displayMemberPrevious($tpl= null)
	{
		$booked = $this->get('MemberPrevious');
		$state = $this->get('state');

		$this->params = JFactory::getApplication()->getParams('com_redevent');

		$this->sessions  = $booked;
		$this->order_input = "previous_order";
		$this->order_dir_input = "previous_order_dir";
		$this->order     = $state->get('previous_order');
		$this->order_dir = $state->get('previous_order_dir');
		$this->task      = 'getmemberprevious';
		$this->uid       = $state->get('uid');
		$this->pagination = $this->get('MemberPreviousPagination');
		$this->limitstart_name = "previous_limitstart";
		$this->limitstart = $state->get('previous_limitstart');

		$this->setLayout('editmember_sessions');
		parent::display($tpl);
	}

	/**
	 * Display info form
	 *
	 * @param   string  $tpl  template to display
	 *
	 * @return void
	 */
	protected function displayInfoForm($tpl= null)
	{
		// Load Akeeba Strapper
		include_once JPATH_ROOT . '/media/akeeba_strapper/strapper.php';
		AkeebaStrapper::bootstrap();

		$this->action = 'index.php?option=com_redeventb2b&view=frontadmin&layout=infoform';
		$this->xref = JFactory::getApplication()->input->getInt('xref', 0);

		parent::display($tpl);
	}

	/**
	 * Display close modal edit member window
	 *
	 * @param   string  $tpl  template to display
	 *
	 * @return void
	 */
	protected function displayCloseModalMember($tpl= null)
	{
		$app = JFactory::getApplication();
		$this->uid = $app->input->get('uid');
		$this->name = $app->input->get('uname');

		parent::display($tpl);
	}

	/**
	 * return html for limit box
	 *
	 * @return string html
	 */
	protected function getLimitBox()
	{
		$state = $this->get('state');

		$options = array(
			JHtml::_('select.option', 15, 15),
			JHtml::_('select.option', 25, 25),
			JHtml::_('select.option', 50, 50)
		);
		$html = JHtml::_('select.genericlist', $options, 'limit',
			array('class' => 'inputbox ajaxlimit'), 'value', 'text', $state->get('limit')
		);

		return $html;
	}
}
