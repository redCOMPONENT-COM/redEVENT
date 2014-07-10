<?php
/**
 * @version     2.5
 * @package     Joomla
 * @subpackage  redEVENT
 * @copyright   redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license     GNU/GPL, see LICENSE.php
 * redEVENT is based on EventList made by Christoph Lukes from schlu.net
 * redEVENT can be downloaded from www.redcomponent.com
 * redEVENT is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * redEVENT is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with redEVENT; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

// No direct access
defined('_JEXEC') or die ('Restricted access');

jimport('joomla.application.component.view');

require_once JPATH_SITE . '/components/com_redevent/views/myevents/view.html.php';
require_once JPATH_SITE . '/components/com_redmember/lib/redmemberlib.php';

/**
 * HTML View class for the frontend admin View
 *
 * @package     Joomla
 * @subpackage  redevent
 * @since       2.0
*/
class RedeventViewFrontadmin extends JView
{
	/**
	 * Creates the View
	 *
	 * @param   string  $tpl  template to display
	 *
	 * @return void
	 *
	 * @since 2.5
	 */
	public function display($tpl = null)
	{
		if ($this->getLayout() == 'searchsessions')
		{
			return $this->displaySearchSessions($tpl);
		}

		if ($this->getLayout() == 'bookings')
		{
			return $this->displayBookings($tpl);
		}

		if ($this->getLayout() == 'attendees')
		{
			return $this->displayAttendees($tpl);
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

		JHTML::_('behavior.framework');
		JHtml::_('behavior.tooltip');
		JHtml::_('behavior.modal');

		// Load Akeeba Strapper
		include_once JPATH_ROOT.'/media/akeeba_strapper/strapper.php';
		AkeebaStrapper::bootstrap();

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
		$modelAttendees = FOFModel::getAnInstance('FrontadminMembers', 'RedeventModel');

		$menu = JSite::getMenu();
		$item = $menu->getActive();

		// Add css file
		if (!$params->get('custom_css'))
		{
			$document->addStyleSheet('media/com_redevent/css/redevent.css');
			$document->addStyleSheet($this->baseurl . '/media/com_redevent/css/redevent-b2b.css');
		}
		else
		{
			$document->addStyleSheet($params->get('custom_css'));
		}

		FOFTemplateUtils::addJS('media://com_redevent/js/b2b.js');

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

		$this->members_limitstart = $modelAttendees->getState('members_limitstart');
		$this->members_order = $modelAttendees->getState('members_order');
		$this->members_order_dir = $modelAttendees->getState('members_order_dir');

		$this->bookings_limitstart = $state->get('bookings_limitstart');
		$this->bookings_order = $state->get('bookings_order');
		$this->bookings_order_dir = $state->get('bookings_order_dir');

		$this->useracl = $useracl;
		$this->params  = $params;
		$this->state   = $state;

		$this->sessions = $this->get('Sessions');

		$this->organization = $this->get('Organization');
		$this->bookings   = $this->get('OrganizationBookings');

		$this->pagination = $this->get('SessionsPagination');
		$this->limitstart = $state->get('limitstart');

		// JS language strings
		JText::script("COM_REDEVENT_FRONTEND_ADMIN_COURSE_SEARCH_TITLE");
		JText::script("COM_REDEVENT_BOOK_SESSION");
		JText::script("COM_REDEVENT_FRONTEND_ADMIN_CONFIRM_CLOSE");
		JText::script("COM_REDEVENT_FRONTEND_ADMIN_CONFIRM");
		JText::script("COM_REDEVENT_FRONTEND_ADMIN_CONFIRM_PUBLISH");
		JText::script("COM_REDEVENT_FRONTEND_ADMIN_CONFIRM_UNPUBLISH");
		JText::script("COM_REDEVENT_FRONTEND_ADMIN_SELECT_SESSION_FIRST");
		JText::script("COM_REDEVENT_FILTER_SELECT_EVENT");
		JText::script("COM_REDEVENT_FILTER_SELECT_SESSION");
		JText::script("COM_REDEVENT_FILTER_SELECT_VENUE");
		JText::script("COM_REDEVENT_FILTER_SELECT_CATEGORY");

		JText::script("COM_REDEVENT_FRONTEND_ADMIN_BREADCRUMB_YOU_ARE_HERE");
		JText::script("COM_REDEVENT_FRONTEND_ADMIN_BREADCRUMB_OVERVIEW");
		JText::script("COM_REDEVENT_FRONTEND_ADMIN_BREADCRUMB_BOOK");
		JText::script("COM_REDEVENT_FRONTEND_ADMIN_BREADCRUMB_SEARCH_RESULTS");

		parent::display($tpl);
	}

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

		$this->bookings_pagination = $this->get('BookingsPagination');
		$this->bookings_limitstart = $state->get('bookings_limitstart');

		parent::display($tpl);
	}

	protected function displayBookings($tpl = null)
	{
		$useracl = RedeventUserAcl::getInstance();
		$params = JFactory::getApplication()->getParams('com_redevent');
		$state = $this->get('state');

		$this->bookings_order_dir = $state->get('bookings_order_dir');
		$this->bookings_order     = $state->get('bookings_order');

		$this->bookings_pagination = $this->get('BookingsPagination');
		$this->bookings_limitstart = $state->get('bookings_limitstart');

		$this->params  = $params;
		$this->state   = $state;

		$this->useracl = $useracl;
		$this->bookings = $this->get('Bookings');
		$this->params  = $params;

		$this->organization = $this->get('Organization');

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
		$maxLeftDisplay = 6;

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

	protected function displayAttendees($tpl= null)
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

	protected function displayEditMember($tpl= null)
	{
		$document = JFactory::getDocument();

		$member = $this->get('MemberInfo');
		$booked = $this->get('MemberBooked');
		$previous = $this->get('MemberPrevious');
		$state = $this->get('state');

		$this->params = JFactory::getApplication()->getParams('com_redevent');

		$modal = JFactory::getApplication()->input->get('modal');




		$rmu_fields = RedmemberLib::getUserFields(JFactory::getApplication()->input->get('uid'),
			array('assign_organization' => JFactory::getApplication()->input->get('orgId')));

		$this->assignRef('tabs', $rmu_fields);

		if ($modal)
		{
			// Load Akeeba Strapper
			include_once JPATH_ROOT . '/media/akeeba_strapper/strapper.php';
			AkeebaStrapper::bootstrap();

			// Add css file
			if (!$this->params->get('custom_css'))
			{
				$document->addStyleSheet('media/com_redevent/css/redevent.css');
				$document->addStyleSheet($this->baseurl . '/media/com_redevent/css/redevent-b2b.css');
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
}
