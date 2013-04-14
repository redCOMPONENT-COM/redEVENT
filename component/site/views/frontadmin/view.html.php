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
		$elsettings = redEVENTHelper::config();
		$pathway    = $mainframe->getPathWay();
		$params     = $mainframe->getParams();
		$uri        = JFactory::getURI();

		$menu = JSite::getMenu();
		$item = $menu->getActive();

		// Add css file
		if (!$params->get('custom_css'))
		{
			$document->addStyleSheet($this->baseurl . '/components/com_redevent/assets/css/redevent.css');
			$document->addStyleSheet($this->baseurl . '/media/com_redevent/css/redevent-b2b.css');
		}
		else
		{
			$document->addStyleSheet($params->get('custom_css'));
		}

		FOFTemplateUtils::addJS('media://com_redevent/js/b2b.js');

		$document->addCustomTag('<!--[if IE]><style type="text/css">.floattext{zoom:1;}, * html #eventlist dd { height: 1%; }</style><![endif]-->');

		$useracl = UserAcl::getInstance();

		$state = $this->get('state');

		// Events filter
		$options = array(JHtml::_('select.option', '', JText::_('COM_REDEVENT_EVENT')));
		$this->events_options     = array_merge($options, $this->get('EventsOptions'));

		// Sessions filter
		JText::script("COM_REDEVENT_SESSION");
		$options = array(JHtml::_('select.option', '', JText::_('COM_REDEVENT_SESSION')));
		$this->sessions_options   = array_merge($options, $this->get('SessionsOptions'));

		// Venues filter
		$options = array(JHtml::_('select.option', '', JText::_('COM_REDEVENT_VENUE')));
		$this->venues_options     = array_merge($options, $this->get('VenuesOptions'));

		// Categories filter
		$options = array(JHtml::_('select.option', '', JText::_('COM_REDEVENT_CATEGORY')));
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

		$this->order_Dir = $state->get('filter_order');
		$this->order     = $state->get('filter_order_Dir');

		$this->useracl = $useracl;
		$this->params  = $params;
		$this->state   = $state;

		$this->sessions = $this->get('Sessions');

		$this->organization = $this->get('Organization');
		$this->bookings   = $this->get('OrganizationBookings');

		// JS language strings
		JText::script("COM_REDEVENT_FRONTEND_ADMIN_COURSE_SEARCH_TITLE");
		JText::script("COM_REDEVENT_BOOK_SESSION");
		JText::script("COM_REDEVENT_FRONTEND_ADMIN_CONFIRM_CLOSE");
		JText::script("COM_REDEVENT_FRONTEND_ADMIN_CONFIRM");

		parent::display($tpl);
	}

	protected function displaySearchSessions($tpl = null)
	{
		$useracl = UserAcl::getInstance();
		$params = JFactory::getApplication()->getParams('com_redevent');
		$state = $this->get('state');

		$this->order_Dir = $state->get('filter_order_dir');
		$this->order     = $state->get('filter_order');

		$this->params  = $params;
		$this->state   = $state;

		$this->useracl = $useracl;
		$this->sessions = $this->get('Sessions');
		$this->params  = $params;

		parent::display($tpl);
	}

	protected function displayBookings($tpl = null)
	{
		$useracl = UserAcl::getInstance();
		$params = JFactory::getApplication()->getParams('com_redevent');
		$state = $this->get('state');

		$this->order_Dir = $state->get('filter_order');
		$this->order     = $state->get('filter_order_Dir');

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
	 * @param   object  $row  session data
	 *
	 * @return string
	 */
	protected function printPlaces($row)
	{
		if (!$row->maxattendees)
		{
			return '-';
		}

		$left = max(array($row->maxattendees - $row->registered, 0));

		return $row->maxattendees . '/' . ($left > 6 ? '6+' : $left);
	}


	/**
	 * Creates the attendees edit button
	 *
	 * @param   int  $id  xref id
	 *
	 * @since 2.0
	 */
	public static function bookbutton($id)
	{
		JHTML::_('behavior.tooltip');

		$image = JHTML::_('image', 'components/com_redevent/assets/images/attendees.png', JText::_('COM_REDEVENT_BOOK_EVENT' ));

		$overlib = JText::_('COM_REDEVENT_BOOK_EVENT_DESC' );
		$text = JText::_('COM_REDEVENT_BOOK_EVENT' );
		$output	= '<a href="#" id="bookid' . $id . '" class="bookthis hasTip" title="'.$text.'::'.$overlib.'">'.$image.'</a>';

		return $output;
	}

	protected function displayAttendees($tpl= null)
	{
		parent::display($tpl);
	}

	protected function displayEditMember($tpl= null)
	{
		$member = $this->get('MemberInfo');
		$booked = $this->get('MemberBooked');
		$previous = $this->get('MemberPrevious');
		$state = $this->get('state');

		$this->params = JFactory::getApplication()->getParams('com_redevent');

		$this->assignRef('member',     $member);
		$this->assignRef('booked',     $booked);
		$this->assignRef('previous',   $previous);
		$this->uid       = $state->get('uid');

		$this->booked_order = $state->get('booked_order');
		$this->booked_order_dir = $state->get('booked_order_dir');

		$this->previous_order = $state->get('previous_order');
		$this->previous_order_dir = $state->get('previous_order_dir');

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

		$this->setLayout('editmember_sessions');
		parent::display($tpl);
	}
}
