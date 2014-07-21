<?php
/**
 * @package    RedEVENT
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
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
defined('_JEXEC') or die('Restricted access');

require_once JPATH_SITE . '/components/com_redmember/lib/redmemberlib.php';

/**
 * Redevents Component events list Model
 *
 * @package  Redevent
 * @since    2.5
 */
class RedeventModelFrontadmin extends RedeventModelBaseeventlist
{
	/**
	 * caching for sessions
	 *
	 * @var array
	 */
	protected $sessions = null;
	protected $pagination_sessions = null;
	protected $total_sessions = null;

	protected $booked = null;
	protected $pagination_booked = null;
	protected $total_booked = null;

	protected $previous = null;
	protected $pagination_previous = null;
	protected $total_previous = null;

	protected $uid = null;

	/**
	 * cache for quickbook
	 * @var object
	 */
	protected $form;

	/**
	 * user acl object
	 * @var RedeventUserAcl
	 */
	protected $useracl = null;

	public function __construct($config = array())
	{
		parent::__construct($config);

		$app = JFactory::getApplication();
		$params = RedeventHelper::config();

		$this->uid = $app->input->get('uid', 0, 'int');
		$this->setState('uid', $this->uid);

		$this->useracl = RedeventUserAcl::getInstance();

		// Get the number of events from database
		$limit       	= $app->getUserStateFromRequest('com_redevent.limit', 'limit', $params->def('b2b_sessions_display_num', 20), 'int');
		$limitstart		= JRequest::getVar('limitstart', 0, '', 'int');

		// In case limit has been changed, adjust it
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

		$this->setState('limit', $limit);
		$this->setState('limitstart_sessions', $limitstart);

		// Bookings filter
		$this->setState(
			'filter_organization',
			$app->getUserStateFromRequest('com_redevent.' . $this->getName() . '.filter_organization',    'filter_organization',    $this->getUserDefaultOrganization(), 'int')
		);
		$this->setState('filter_person', $app->getUserStateFromRequest('com_redevent.' . $this->getName() . '.filter_person',    'filter_person',    '', 'string'));
		$this->setState('filter_person_active',    $app->input->get('filter_person_active',    0, 'int'));
		$this->setState('filter_person_archive',    $app->input->get('filter_person_archive',    0, 'int'));

		// Manage sessions filters
		$this->setState('filter_session',    $app->getUserStateFromRequest('com_redevent.' . $this->getName() . '.filter_session',    'filter_session',    0, 'int'));
		$this->setState('filter_from',    $app->getUserStateFromRequest('com_redevent.' . $this->getName() . '.filter_from',    'filter_from',    '', 'string'));
		$this->setState('filter_to',    $app->getUserStateFromRequest('com_redevent.' . $this->getName() . '.filter_to',    'filter_to',    '', 'string'));

		// Sessions
		$this->setState('filter_order',     JRequest::getCmd('filter_order', 'x.dates'));
		$this->setState('filter_order_dir', strtoupper(JRequest::getCmd('filter_order_Dir', 'DESC')) == 'DESC' ? 'DESC' : 'ASC');

		// Organization bookings
		$this->setState('bookings_order',     JRequest::getCmd('bookings_order', 'x.dates'));
		$this->setState('bookings_order_dir', strtoupper(JRequest::getCmd('bookings_order_dir', 'DESC')) == 'DESC' ? 'DESC' : 'ASC');

		$bookings_limitstart		= JRequest::getVar('bookings_limitstart', 0, '', 'int');
		// In case limit has been changed, adjust it
		$bookings_limitstart = ($limit != 0 ? (floor($bookings_limitstart / $limit) * $limit) : 0);
		$this->setState('bookings_limitstart', $bookings_limitstart);

		// Editmember
		$this->setState('booked_order',     JRequest::getCmd('booked_order', 'x.dates'));
		$this->setState('booked_order_dir', strtoupper(JRequest::getCmd('booked_order_dir', 'DESC')) == 'DESC' ? 'DESC' : 'ASC');

		$booked_limitstart		= JRequest::getVar('booked_limitstart', 0, '', 'int');
		// In case limit has been changed, adjust it
		$booked_limitstart = ($limit != 0 ? (floor($booked_limitstart / $limit) * $limit) : 0);
		$this->setState('booked_limitstart', $booked_limitstart);

		$this->setState('previous_order',     JRequest::getCmd('previous_order', 'x.dates'));
		$this->setState('previous_order_dir', strtoupper(JRequest::getCmd('previous_order_dir', 'DESC')) == 'DESC' ? 'DESC' : 'ASC');

		$previous_limitstart		= JRequest::getVar('previous_limitstart', 0, '', 'int');
		// In case limit has been changed, adjust it
		$previous_limitstart = ($limit != 0 ? (floor($previous_limitstart / $limit) * $limit) : 0);
		$this->setState('previous_limitstart', $previous_limitstart);
	}

	public function getUseracl()
	{
		return $this->useracl;
	}

	/**
	 * Method to get the Sessions the user can manage
	 *
	 * @return array
	 */
	public function getSessions()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->sessions))
		{
			$query = $this->_buildQuerySessions();
			$pagination = $this->getSessionsPagination();
			$this->sessions = $this->_getList($query, $pagination->limitstart, $pagination->limit);
			$this->sessions = $this->_categories($this->sessions);
			$this->sessions = $this->_getPlacesLeft($this->sessions);
		}

		return $this->sessions;
	}

	/**
	 * Method to get a pagination object for the events
	 *
	 * @access public
	 * @return integer
	 */
	public function getSessionsPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->pagination_sessions))
		{
			jimport('joomla.html.pagination');
			$this->pagination_sessions = new REAjaxPagination($this->getTotalSessions(), $this->getState('limitstart_sessions'), $this->getState('limit'));
		}

		return $this->pagination_sessions;
	}

	/**
	 * Total nr of events
	 *
	 * @return integer
	 */
	public function getTotalSessions()
	{
		// Lets load the total nr if it doesn't already exist
		if (empty($this->total_sessions))
		{
			$query = $this->_buildQuerySessions();
			$this->total_sessions = $this->_getListCount($query);
		}

		return $this->total_sessions;
	}

	/**
	 * Method to get a pagination object for the events
	 *
	 * @access public
	 * @return integer
	 */
	public function getBookingsPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->pagination_booked))
		{
			jimport('joomla.html.pagination');
			$this->pagination_booked = new REAjaxPagination($this->getTotalBookings(), $this->getState('bookings_limitstart'), $this->getState('limit'));
		}

		return $this->pagination_booked;
	}

	/**
	 * Total nr of events
	 *
	 * @return integer
	 */
	public function getTotalBookings()
	{
		// Lets load the total nr if it doesn't already exist
		if (empty($this->total_booked))
		{
			$query = $this->_buildQueryBookings();
			$this->total_booked = $this->_getListCount($query);
		}

		return $this->total_booked;
	}

	/**
	 * Method to get a pagination object for the events
	 *
	 * @access public
	 * @return integer
	 */
	public function getMemberBookedPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->pagination_booked))
		{
			jimport('joomla.html.pagination');
			$this->pagination_booked = new REAjaxPagination($this->getTotalMemberBooked(), $this->getState('booked_limitstart'), $this->getState('limit'));
		}

		return $this->pagination_booked;
	}

	/**
	 * Method to get a pagination object for the events
	 *
	 * @access public
	 * @return integer
	 */
	public function getMemberPreviousPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->pagination_previous))
		{
			jimport('joomla.html.pagination');
			$this->pagination_previous = new REAjaxPagination($this->getTotalMemberPrevious(), $this->getState('previous_limitstart'), $this->getState('limit'));
		}

		return $this->pagination_previous;
	}

	/**
	 * Total nr of events
	 *
	 * @return integer
	 */
	public function getTotalMemberBooked()
	{
		// Lets load the total nr if it doesn't already exist
		if (empty($this->total_booked))
		{
			$query = $this->_buildQueryMemberBooked();
			$this->total_booked = $this->_getListCount($query);
		}

		return $this->total_booked;
	}

	/**
	 * Total nr of events
	 *
	 * @return integer
	 */
	public function getTotalMemberPrevious()
	{
		// Lets load the total nr if it doesn't already exist
		if (empty($this->total_previous))
		{
			$query = $this->_buildQueryMemberPrevious();
			$this->total_previous = $this->_getListCount($query);
		}

		return $this->total_previous;
	}

	/**
	 * returns events as options for filter
	 *
	 * @return array
	 */
	public function getEventsOptions()
	{
		$db      = JFactory::getDbo();
		$query = $db->getQuery(true);

		$ids = array_merge($this->useracl->getCanEditEvents(),
			$this->useracl->getEventsCanViewAttendees());
		$ids = array_unique($ids);

		if (!$ids)
		{
			return array();
		}

		$query->select('a.id AS value, a.title as text');
		$query->from('#__redevent_events AS a');
		$query->join('INNER', '#__redevent_event_venue_xref AS x ON x.eventid = a.id');
		$query->join('INNER', '#__redevent_event_category_xref AS xcat ON xcat.event_id = a.id');

		$query->where('a.id IN(' . implode(',', $ids) . ')');
		$query->where('a.published = 1');
		$query->order('a.title');

		if ($this->getState('filter_category'))
		{
			$query->where('xcat.category_id = ' . $this->getState('filter_category'));
		}

		if ($this->getState('filter_venue'))
		{
			$query->where('x.venueid = ' . $this->getState('filter_venue'));
		}

		if ($this->getState('filter.language'))
		{
			$query->where('(a.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ') OR a.language IS NULL)');
		}

		$query->group('a.id');

		$db->setQuery($query);
		$res = $db->loadObjectList();

		return $res;
	}

	/**
	 * returns sessions as options for filter
	 *
	 * @return array
	 */
	public function getSessionsOptions()
	{
		$db      = JFactory::getDbo();
		$query = $db->getQuery(true);
		$config = RedeventHelper::config();

		$ids = $this->useracl->getXrefsCanViewAttendees();

		if (!$ids)
		{
			return array();
		}

		$query->select('x.id AS value');
		$query->select('x.dates as text');
		$query->from('#__redevent_event_venue_xref AS x');
		$query->join('INNER', '#__redevent_events AS a ON x.eventid = a.id');

		$query->where('x.id IN(' . implode(',', $ids) . ')');
		$query->where('x.eventid = ' . $this->getState('filter_event'));
		$query->where('x.published = 1');

		if ($this->getState('filter_venue'))
		{
			$query->where('x.venueid = ' . $this->getState('filter_venue'));
		}

		$query->order('x.dates');

		if ($config->get('b2b_show_open', 1) == 0)
		{
			$query->where('x.dates > 0');
		}

		$db->setQuery($query);
		$res = $db->loadObjectList();

		return $res;

	}

	/**
	 * returns sessions as options for filter
	 *
	 * @return array
	 */
	public function getVenuesOptions()
	{
		$allowed = $this->useracl->getAllowedForEventsVenues();

		if (!$allowed)
		{
			return array();
		}

		$db      = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('v.id as value, v.venue as text');
		$query->from('#__redevent_venues AS v');
		$query->join('INNER', '#__redevent_event_venue_xref AS x ON x.venueid = v.id');
		$query->where('v.id IN (' . implode(',', $allowed) . ')');
		$query->order('v.venue');

		if ($this->getState('filter_event'))
		{
			$query->where('x.eventid = ' . $this->getState('filter_event'));
		}

		if ($this->getState('filter.language'))
		{
			$query->where('(v.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ') OR v.language IS NULL)');
		}

		$query->group('v.id');

		$db->setQuery($query);
		$res = $db->loadObjectList();

		return $res;
	}

	/**
	 * returns sessions as options for filter
	 *
	 * @return array
	 */
	public function getCategoriesOptions()
	{
		$allowed = $this->useracl->getAuthorisedCategories('re.manageevents');

		if (!$allowed)
		{
			return array();
		}

		$db      = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('c.id as value, c.catname as text');
		$query->from('#__redevent_categories AS c');
		$query->join('INNER', '#__redevent_event_category_xref AS xcat ON xcat.category_id = c.id');
		$query->where('c.id IN (' . implode(',', $allowed) . ')');

		if ($this->getState('filter_event'))
		{
			$query->where('xcat.event_id = ' . $this->getState('filter_event'));
		}

		if ($this->getState('filter.language'))
		{
			$query->where('(c.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ') OR c.language IS NULL)');
		}

		$query->order('c.ordering ASC');

		$query->group('c.id');

		$db->setQuery($query);
		$res = $db->loadObjectList();

		return $res;
	}

	/**
	 * returns Organizations as options for filter
	 *
	 * @return array
	 */
	public function getOrganizationsOptions()
	{
		$user = JFactory::getUser();

		$db      = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('o.organization_id as value, o.organization_name as text');
		$query->from('#__redmember_organization AS o');
		$query->order('o.organization_name');

		if (!$this->useracl->superuser())
		{
			$query->join('INNER', '#__redmember_user_organization_xref AS x ON x.organization_id = o.organization_id');
			$query->where('x.user_id = ' . $user->get('id'));
			$query->where('x.level > 1');
		}

		$query->group('o.organization_id');

		$db->setQuery($query);
		$res = $db->loadObjectList();

		return $res;
	}

	/**
	 * check if user is course admin
	 *
	 * @return boolean
	 */
	public function isCourseAdmin()
	{
		$user = JFactory::getUser();

		$res = $user->authorise('re.editevent', 'com_redevent') || $user->authorise('re.addevent', 'com_redevent')
			|| $user->authorise('re.editsession', 'com_redevent') || $user->authorise('re.editsession', 'com_redevent');

		return $res;
	}

	/**
	 * get all events booked by people from organization
	 *
	 * @return boolean
	 */
	public function getBookings()
	{
		if (!$this->getState('filter_organization'))
		{
			return false;
		}

		// Lets load the content if it doesn't already exist
		if (empty($this->booked))
		{
			$query = $this->_buildQueryBookings();
			$pagination = $this->getBookingsPagination();
			$this->booked = $this->_getList($query, $pagination->limitstart, $pagination->limit);
			$this->booked = $this->_categories($this->booked);
			$this->booked = $this->_getPlacesLeft($this->booked);
		}

		return $this->booked;
	}

	/**
	 * return organization name
	 *
	 * @return boolean
	 */
	public function getOrganization()
	{
		if (!$this->getState('filter_organization'))
		{
			return false;
		}

		$id = $this->getState('filter_organization');
		$opt = $this->getOrganizationsOptions();

		foreach ($opt as $org)
		{
			if ($org->value == $id)
			{
				return $org->text;
			}
		}

		return false;
	}

	/**
	 * returns users from organization as options
	 *
	 * @return boolean
	 */
	public function getUsersOptions()
	{
		if (!$this->getState('filter_organization'))
		{
			return array();
		}

		$db      = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('u.id AS value, u.name AS text');
		$query->from('#__redmember_users AS rmu');
		$query->join('INNER', '#__users AS u ON u.id = rmu.user_id');
		$query->join('INNER', '#__redmember_user_organization_xref AS rmuo ON rmuo.user_id = rmu.user_id');
		$query->where('rmuo.organization_id = ' . (int) $this->getState('filter_organization'));
		$query->order('u.name');

		$db->setQuery($query);
		$res = $db->loadObjectList();

		return $res;
	}

	/**
	 * build the organization booked events query
	 *
	 * @return void
	 */
	protected function _buildQueryBookings()
	{
		$db      = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('x.dates, x.enddates, x.times, x.endtimes, x.registrationend, x.id AS xref, x.maxattendees, x.maxwaitinglist, x.published');
		$query->select('a.id, a.title, a.created, a.datdescription, a.registra, a.course_code');
		$query->select('l.venue, l.city, l.state, l.url, l.id as locid');
		$query->select('r.id AS rid, r.status');
		$query->select('CASE WHEN CHAR_LENGTH(x.title) THEN CONCAT_WS(\' - \', a.title, x.title) ELSE a.title END as full_title');
		$query->select('CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug');
		$query->select('CASE WHEN CHAR_LENGTH(l.alias) THEN CONCAT_WS(\':\', l.id, l.alias) ELSE l.id END as venueslug');
		$query->from('#__redevent_event_venue_xref AS x');
		$query->join('LEFT', '#__redevent_events AS a ON a.id = x.eventid');
		$query->join('LEFT', '#__redevent_venues AS l ON l.id = x.venueid');
		$query->join('LEFT', '#__redevent_event_category_xref AS xcat ON xcat.event_id = a.id');
		$query->join('LEFT', '#__redevent_categories AS c ON c.id = xcat.category_id');
		$query->group('x.id');

		// Join over the language
		$query->select('lg.title AS language_title, lg.sef AS language_sef');
		$query->join('LEFT', $db->quoteName('#__languages').' AS lg ON lg.lang_code = a.language');

		// Join over
		$query->join('INNER', '#__redevent_register AS r ON r.xref = x.id');
		$query->join('INNER', '#__redmember_users AS rmu ON rmu.user_id = r.uid');
		$query->join('INNER', '#__redmember_user_organization_xref AS rmuo ON rmuo.user_id = rmu.user_id');
		$query->join('INNER', '#__users AS u ON u.id = rmu.user_id');
		$query->where('rmuo.organization_id = ' . $this->getState('filter_organization'));
		$query->where('r.cancelled = 0');

		$session_state = array();

		if ($this->getState('filter_person_active') == 1)
		{
			$session_state[] = 'x.published = 1';
		}
		elseif ($this->getState('filter_person_active') == -1)
		{
			$session_state[] = 'x.published = -1';
		}

		if ($this->getState('filter_person_archive') == 1)
		{
			$session_state[] = 'x.published = -1';
		}

		if (!count($session_state))
		{
			$session_state[] = 'x.published <> 0';
		}

		$query->where('(' . implode(' OR ', $session_state) . ')');

		if ($this->getState('filter_person'))
		{
			$matching = array();
			$matching[] = 'u.name LIKE (' . $db->Quote('%' . $this->getState('filter_person') . '%') . ')';
			$matching[] = 'u.username LIKE (' . $db->Quote('%' . $this->getState('filter_person') . '%') . ')';
			$matching[] = 'u.email LIKE (' . $db->Quote('%' . $this->getState('filter_person') . '%') . ')';
			$query->where('(' . implode(' OR ', $matching) . ')');
		}

		// Language filter
		if ($this->getState('filter.language'))
		{
			$query->where('(c.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ') OR c.language IS NULL)');
		}

		$filter_order = $this->getState('bookings_order');
		$filter_order_dir = $this->getState('bookings_order_dir');

		$query->order($filter_order . ' ' . $filter_order_dir . ', x.dates, x.times');

		return $query;
	}

	public function getMemberBooked()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->booked))
		{
			$query = $this->_buildQueryMemberBooked();
			$pagination = $this->getMemberBookedPagination();
			$this->booked = $this->_getList($query, $pagination->limitstart, $pagination->limit);
			$this->booked = $this->_categories($this->booked);
		}

		return $this->booked;
	}

	protected function _buildQueryMemberBooked()
	{
		$query = $this->_buildQueryBookings();
		$query->clear('where');
		$query->where('r.uid = ' . $this->uid);
		$query->where('r.cancelled = 0');

		$now = strftime('%Y-%m-%d %H:%M');
 		$query->where('(x.dates = 0 OR (CASE WHEN x.times THEN CONCAT(x.dates," ",x.times) ELSE x.dates END) > ' . $this->_db->Quote($now) . ')');

 		$filter_order = $this->getState('booked_order');
 		$filter_order_dir = $this->getState('booked_order_dir');
 		$query->clear('order');
 		$query->order($filter_order . ' ' . $filter_order_dir . ', x.dates, x.times');

		return $query;
	}

	public function getMemberPrevious()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->previous))
		{
			$query = $this->_buildQueryMemberPrevious();
			$pagination = $this->getMemberPreviousPagination();
			$this->previous = $this->_getList($query, $pagination->limitstart, $pagination->limit);
			$this->previous = $this->_categories($this->previous);
		}

		return $this->previous;
	}

	protected function _buildQueryMemberPrevious()
	{
		$query = $this->_buildQueryBookings();
		$query->clear('where');
		$query->where('r.uid = ' . $this->uid);

		$now = strftime('%Y-%m-%d %H:%M');
		$query->where('x.dates > 0');
		$query->where('(CASE WHEN x.times THEN CONCAT(x.dates," ",x.times) ELSE x.dates END) < ' . $this->_db->Quote($now));

		$filter_order = $this->getState('previous_order');
		$filter_order_dir = $this->getState('previous_order_dir');
		$query->clear('order');
		$query->order($filter_order . ' ' . $filter_order_dir . ', x.dates, x.times');

		return $query;
	}

	/**
	 * Build the events query
	 *
	 * @access private
	 * @return string
	 */
	protected function _buildQuerySessions()
	{
		$db      = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('x.dates, x.enddates, x.times, x.endtimes, x.registrationend, x.id AS xref, x.maxattendees, x.maxwaitinglist, x.published');
		$query->select('a.id, a.title, a.created, a.datdescription, a.registra, a.course_code');
		$query->select('l.venue, l.city, l.state, l.url, l.id as locid');
		$query->select('CASE WHEN CHAR_LENGTH(x.title) THEN CONCAT_WS(\' - \', a.title, x.title) ELSE a.title END as full_title');
		$query->select('CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug');
		$query->select('CASE WHEN CHAR_LENGTH(l.alias) THEN CONCAT_WS(\':\', l.id, l.alias) ELSE l.id END as venueslug');
		$query->from('#__redevent_event_venue_xref AS x');
		$query->join('LEFT', '#__redevent_events AS a ON a.id = x.eventid');
		$query->join('LEFT', '#__redevent_venues AS l ON l.id = x.venueid');
		$query->join('LEFT', '#__redevent_event_category_xref AS xcat ON xcat.event_id = a.id');
		$query->join('LEFT', '#__redevent_categories AS c ON c.id = xcat.category_id');
		$query->group('x.id');

		// Join over the language
		$query->select('lg.title AS language_title, lg.sef AS language_sef');
		$query->join('LEFT', $db->quoteName('#__languages').' AS lg ON lg.lang_code = a.language');

		// Get the WHERE and ORDER BY clauses for the query
		$query = $this->_buildSessionsListWhere($query);

		$filter_order = $this->getState('filter_order');
		$filter_order_dir = $this->getState('filter_order_dir');
		$query->order($filter_order . ' ' . $filter_order_dir . ', x.dates, x.times');

		return $query;
	}

	/**
	 * Build the where clause
	 *
	 * @param   JDatabaseQuery  $query  query object
	 *
	 * @return JDatabaseQuery
	 */
	protected function _buildSessionsListWhere(JDatabaseQuery $query)
	{
		$db = JFactory::getDbo();
		$acl = RedeventUserAcl::getInstance();
		$config = RedeventHelper::config();

		$query->where('x.published = 1');

		if (!$acl->superuser())
		{
			$xrefs = $acl->getCanEditXrefs();
			$xrefs = array_merge($acl->getXrefsCanViewAttendees(), $xrefs);
			$xrefs = array_unique($xrefs);

			if ($xrefs && count($xrefs))
			{
				$query->where(' x.id IN (' . implode(",", $xrefs) . ')');
			}
			else
			{
				$query->where('0');
			}
		}

		if (JRequest::getInt('filter_event'))
		{
			$query->where('a.id = ' . JRequest::getInt('filter_event'));
		}

		if (JRequest::getInt('filter_session'))
		{
			$query->where('x.id = ' . JRequest::getInt('filter_session'));
		}

		if (JRequest::getInt('filter_venue'))
		{
			$query->where('l.id = ' . JRequest::getInt('filter_venue'));
		}

		if (JRequest::getInt('filter_category'))
		{
			$query->where('c.id = ' . JRequest::getInt('filter_category'));
		}

		if ($from = $this->getState('filter_from') && RedeventHelper::isValidDate($this->getState('filter_from')))
		{
			$query->where('DATE(x.dates) >= ' . $db->quote($this->getState('filter_from')));
		}

		if ($to = $this->getState('filter_to') && RedeventHelper::isValidDate($this->getState('filter_to')))
		{
			$query->where('x.dates > 0 AND DATE(x.dates) <= ' . $db->quote($this->getState('filter_to')));
		}

		if ($config->get('b2b_show_open', 1) == 0)
		{
			$query->where('x.dates > 0');
		}

		return $query;
	}

	/**
	 * publish xref
	 *
	 * @param   int  $xref   session id
	 * @param   int  $state  publish state
	 *
	 * @return boolean true on success
	 */
	public function publishXref($xref, $state)
	{
		$db      = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->update('#__redevent_event_venue_xref AS x');
		$query->set('x.published = ' . $db->Quote($state));
		$query->where('x.id = ' . $db->Quote($xref));

		$db->setQuery($query);
		$res = $db->query();

		return $res;
	}

	/**
	 * quickbook an user
	 *
	 * uses redmember data to try to fill up form fields
	 *
	 * @param   int  $user_id  user id
	 * @param   int  $xref     session id
	 *
	 * @return  object  attendee
	 */
	public function quickbook($user_id, $xref)
	{
		require_once 'registration.php';

		$registrationmodel = JModel::getInstance('Registration', 'RedeventModel');
		$registrationmodel->setXref($xref);

		// First check that not already regiterered
		$db      = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('id')
			->from('#__redevent_register')
			->where('uid = ' . $user_id)
			->where('cancelled = 0')
			->where('xref = ' . $xref);

		$db->setQuery($query);

		if ($db->loadResult())
		{
			$this->setError(JText::_('COM_REDEVENT_ALREADY_REGISTERED'));

			return false;
		}

		$details = $registrationmodel->getSessionDetails();
		$pricegroup = $this->getPricegroup($xref);

		if (!$pricegroup)
		{
			$pricegroup->price = 0;
			$currency = '';
		}
		else
		{
			$currency = $pricegroup->currency ? $pricegroup->currency : $pricegroup->form_currency;
		}

		$options = array('baseprice' => $pricegroup->price, 'currency' => $currency);

		$redform = RedformCore::getInstance($details->redform_id);
		$result = $redform->quickSubmit($user_id, 'redevent', $options);

		if (!$result)
		{
			$this->setError(JText::_('COM_REDEVENT_REGISTRATION_REDFORM_SAVE_FAILED') . ' - ' . $redform->getError());

			return false;
		}

		$submit_key = $result->submit_key;
		$user = JFactory::getUser($user_id);
		$rfpost = $result->posts[0];

		if (!$reg = $registrationmodel->register($user, $rfpost['sid'], $result->submit_key, $pricegroup ? $pricegroup->id : 0))
		{
			$this->setError(JText::_('COM_REDEVENT_REGISTRATION_REGISTRATION_FAILED'));
			return false;
		}

		// Force confirm
		$registrationmodel->confirm($reg->id);

		if ($details->notify && !$this->organizationDenyNotifyAttendee($user))
		{
			$mail = $registrationmodel->sendNotificationEmail($submit_key);
		}

		$mail = $registrationmodel->notifyManagers($submit_key);

		// For tracking
		$reg->event_name   = $details->event_name;
		$reg->session_name = $details->session_name;
		$reg->venue        = $details->venue;
		$reg->categories   = $details->categories;
		$reg->price = $pricegroup->price;
		$reg->currency = $currency;

		return $reg;
	}

	/**
	 * Check if user organizations deny to notify attendee
	 *
	 * @param   JUser  $user  juser
	 *
	 * @return bool
	 */
	private function organizationDenyNotifyAttendee($user)
	{
		$orgs = RedeventHelperOrganization::getUserOrganizations($user->id);

		foreach ($orgs as $orgId => $level)
		{
			$settings = RedeventHelperOrganization::getSettings($orgId);

			if ($settings->b2b_disable_all_attendee_notifications)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * get any pricegroup id and price associated to session
	 *
	 * @param   int  $xref  session id
	 *
	 * @return object
	 */
	protected function getPricegroup($xref)
	{
		$db      = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('spg.id, spg.price, spg.currency');
		$query->select('f.currency AS form_currency');
		$query->from('#__redevent_sessions_pricegroups AS spg');
		$query->join('INNER', '#__redevent_event_venue_xref AS x ON x.id = spg.xref');
		$query->join('INNER', '#__redevent_events AS e ON e.id = x.eventid');
		$query->join('INNER', '#__rwf_forms AS f ON f.id = e.redform_id');
		$query->where('spg.xref = ' . $xref);

		$db->setQuery($query, 0, 1);
		$res = $db->loadObject();

		return $res;
	}

	/**
	 * cancel a registration
	 *
	 * @param   int  $register_id  register id
	 *
	 * @return boolean true on success
	 */
	public function cancelreg($register_id)
	{
		if (!$register_id)
		{
			$this->setError('register id is required');

			return false;
		}

		// Get attendee details
		$db      = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('*');
		$query->from('#__redevent_register');
		$query->where('id = ' . $register_id);

		$db->setQuery($query, 0 ,1);
		$res = $db->loadObject();

		if (!$res)
		{
			$this->setError('Attendee not found');

			return false;
		}

		$useracl = RedeventUserAcl::getInstance();

		if (!$useracl->canManageAttendees($res->xref) or 1)
		{
			$this->setError(JText::_('COM_REDEVENT_USER_ACTION_NOT_ALLOWED'));

			return false;
		}
	}

	/**
	 * update po number
	 *
	 * @param   int     $rid    register id
	 * @param   string  $value  value
	 *
	 * @return boolean true on success
	 */
	public function updateponumber($rid, $value)
	{
		$query = ' UPDATE #__redevent_register SET ponumber = ' . $this->_db->Quote($value)
			. ' WHERE id = ' . (int) $rid;
		$this->_db->setQuery($query);

		if (!$res = $this->_db->query())
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		return true;
	}

	/**
	 * update comments
	 *
	 * @param   int     $rid    register id
	 * @param   string  $value  value
	 *
	 * @return boolean true on success
	 */
	public function updatecomments($rid, $value)
	{
		$query = ' UPDATE #__redevent_register SET comments = ' . $this->_db->Quote($value)
			. ' WHERE id = ' . (int) $rid;
		$this->_db->setQuery($query);

		if (!$res = $this->_db->query())
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		return true;
	}

	/**
	 * update updatestatus
	 *
	 * @param   int     $rid    register id
	 * @param   string  $value  value
	 *
	 * @return boolean true on success
	 */
	public function updatestatus($rid, $value)
	{
		$nextvalue = ($value + 1) % 4;
		$query = ' UPDATE #__redevent_register SET status = ' . $this->_db->Quote($nextvalue)
			. ' WHERE id = ' . (int) $rid;
		$this->_db->setQuery($query);

		if (!$res = $this->_db->query())
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		return $nextvalue;
	}

	/**
	 * returns user info
	 *
	 * @todo: get info from redmember !!
	 *
	 * @return object
	 */
	public function getMemberInfo($uid = null)
	{
		if (!$uid)
		{
			$uid = $this->uid;
		}

		$user = JFactory::getUser($uid);

		// Company
		$db      = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('organization_id');
		$query->from('#__redmember_user_organization_xref');
		$query->where('user_id = ' . $uid);

		$db->setQuery($query);
		$res = $db->loadColumn();

		$user->organizations = $res;

		return $user;
	}

	/**
	 * Get default organization for user (first one...)
	 *
	 * @param   int  $uid  uid, null for current user
	 *
	 * @return int
	 */
	public function getUserDefaultOrganization($uid = null)
	{
		if (!$uid)
		{
			$uid = JFactory::getUser()->get('id');
		}

		$data = RedmemberLib::getUserData($uid);

		if (count($data->organizations))
		{
			$orgId = reset(array_keys($data->organizations));

			return $orgId;
		}

		return false;
	}

	/**
	 * Search for person within current organization
	 *
	 * @param   string  $input  the string to search for
	 *
	 * @return array
	 */
	public function searchPerson($input)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('');
		$query->from('#__ AS ');
		$query->join('INNER', '#__');
		$query->join('LEFT', '#__');
		$query->where('');
		$query->group('');
		$query->order('');

		$db->setQuery($query);
		$res = $db->loadObjectList();
	}
}
