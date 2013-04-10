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

require_once 'baseeventslist.php';

/**
 * Redevents Component events list Model
 *
 * @package  Redevent
 * @since    2.5
 */
class RedeventModelFrontadmin extends RedeventModelBaseEventList
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

	/**
	 * cache for quickbook
	 * @var object
	 */
	protected $form;

	protected $useracl = null;

	public function __construct($config = array())
	{
		parent::__construct($config);

		$app = JFactory::getApplication();

		$this->useracl = UserAcl::getInstance();

		// Bookings filter
		$this->setState('filter_organization',    $app->getUserStateFromRequest('com_redevent.' . $this->getName() . '.filter_organization',    'filter_organization',    0, 'int'));
		$this->setState('filter_person',    $app->getUserStateFromRequest('com_redevent.' . $this->getName() . '.filter_person',    'filter_person',    '', 'string'));
		$this->setState('filter_person_active',    $app->input->get('filter_person_active',    0, 'int'));
		$this->setState('filter_person_archive',   $app->input->get('filter_person_archive',    0, 'int'));

		// Manage sessions filters
		$this->setState('filter_session',    $app->getUserStateFromRequest('com_redevent.' . $this->getName() . '.filter_session',    'filter_session',    0, 'int'));
		$this->setState('filter_from',    $app->getUserStateFromRequest('com_redevent.' . $this->getName() . '.filter_from',    'filter_from',    '', 'string'));
		$this->setState('filter_to',    $app->getUserStateFromRequest('com_redevent.' . $this->getName() . '.filter_to',    'filter_to',    '', 'string'));
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
			$this->pagination_sessions = new JPagination($this->getTotalSessions(), $this->getState('limitstart_sessions'), $this->getState('limit'));
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
	public function getBookedPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->pagination_booked))
		{
			jimport('joomla.html.pagination');
			$this->pagination_booked = new JPagination($this->getTotalBooked(), $this->getState('limitstart_sessions'), $this->getState('limit'));
		}

		return $this->pagination_booked;
	}

	/**
	 * Total nr of events
	 *
	 * @return integer
	 */
	public function getTotalBooked()
	{
		// Lets load the total nr if it doesn't already exist
		if (empty($this->total_booked))
		{
			$query = $this->_buildQueryBooked();
			$this->total_booked = $this->_getListCount($query);
		}

		return $this->total_booked;
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

		$ids = $this->useracl->getCanEditEvents();

		if (!$ids)
		{
			return array();
		}

		$query->select('a.id AS value, a.title as text');
		$query->from('#__redevent_events AS a');

		$query->where('a.id IN(' . implode(',', $ids) . ')');
		$query->order('a.title');

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

		$ids = $this->useracl->getCanEditXrefs();

		if (!$ids)
		{
			return array();
		}

		$query->select('x.id AS value');
		$query->select('CASE WHEN CHAR_LENGTH(x.title) THEN CONCAT_WS(\' - \', a.title, x.title) ELSE x.dates END as text');
		$query->from('#__redevent_event_venue_xref AS x');
		$query->join('INNER', '#__redevent_events AS a ON x.eventid = a.id');

		$query->where('x.id IN(' . implode(',', $ids) . ')');
		$query->where('x.eventid = ' . $this->getState('filter_event'));

		if ($this->getState('filter_venue'))
		{
			$query->where('x.venueid = ' . $this->getState('filter_venue'));
		}

		$query->order('x.dates');

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

		$query->select('id as value, venue as text');
		$query->from('#__redevent_venues');
		$query->where('id IN (' . implode(',', $allowed) . ')');
		$query->order('venue');

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

		$query->select('id as value, catname as text');
		$query->from('#__redevent_categories');
		$query->where('id IN (' . implode(',', $allowed) . ')');
		$query->order('catname');

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
		}

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
			$query = $this->_buildQueryBooked();
			$pagination = $this->getBookedPagination();
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
	protected function _buildQueryBooked()
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

		// Join over
		$query->join('INNER', '#__redevent_register AS r ON r.xref = x.id');
		$query->join('INNER', '#__redmember_users AS rmu ON rmu.user_id = r.uid');
		$query->join('INNER', '#__redmember_user_organization_xref AS rmuo ON rmuo.user_id = rmu.user_id');
		$query->join('INNER', '#__users AS u ON u.id = rmu.user_id');
		$query->where('rmuo.organization_id = ' . $this->getState('filter_organization'));

		$session_state = array();

		if ($this->getState('filter_person_archive'))
		{
			$session_state[] = 'x.published = -1';
		}

		if (!count($session_state) || $this->getState('filter_person_active'))
		{
			$session_state[] = 'x.published = 1';
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

		$filter_order = $this->getState('filter_order');
		$filter_order_dir = $this->getState('filter_order_dir');

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
		$acl = UserAcl::getInstance();

		$query->where('x.published > -1');

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

		if ($from = $this->getState('filter_from') && redEVENTHelper::isValidDate($this->getState('filter_from')))
		{
			$query->where('DATE(x.dates) >= ' . $db->quote($this->getState('filter_from')));
		}

		if ($to = $this->getState('filter_to') && redEVENTHelper::isValidDate($this->getState('filter_to')))
		{
			$query->where('x.dates > 0 AND DATE(x.dates) <= ' . $db->quote($this->getState('filter_to')));
		}

		return $query;
	}

	/**
	 * return organization members and their booking status for the session
	 *
	 * @param   int  $xref          session id
	 * @param   int  $organization  organization id
	 *
	 * @return array
	 */
	public function getAttendees($xref, $organization)
	{
		// Get organization members
		$db      = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('u.*');
		$query->from('#__redmember_user_organization_xref AS rmuo');
		$query->join('INNER', '#__redmember_users AS rmu ON rmuo.user_id = rmu.user_id');
		$query->join('INNER', '#__users AS u ON u.id = rmu.user_id');
		$query->where('rmuo.organization_id = ' . (int) $organization);
		$query->order('u.name');

		$db->setQuery($query);
		$users = $db->loadObjectList();

		if (!$users)
		{
			return array();
		}

		// Now get the one registered for the session
		// Get the ids first
		$ids = array();
		foreach ($users as $u)
		{
			$ids[] = $u->id;
		}

		$query = $db->getQuery(true);

		$query->select('r.*');
		$query->from('#__redevent_register AS r');
		$query->where('r.xref = ' . $xref);
		$query->where('r.uid IN (' . implode(',', $ids) . ')');

		$db->setQuery($query);
		$regs = $db->loadObjectList('uid');

		foreach ($users as &$u)
		{
			if (isset($regs[$u->id]))
			{
				$u->registered = $regs[$u->id];
			}
			else
			{
				$u->registered =null;
			}
		}

		return $users;
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
	 * @return boolean true on success
	 */
	public function quickbook($user_id, $xref)
	{
		require_once 'registration.php';

		$registrationmodel = JModel::getInstance('Registration', 'RedeventModel');
		$registrationmodel->setXref($xref);

		$details = $registrationmodel->getSessionDetails();

		$pricegroup = $this->getPricegroup($xref);

		$options = array('baseprice' => $pricegroup->price);

		$redform = RedFormCore::getInstance($details->redform_id);
		$result = $redform->quickSubmit($user_id, 'redevent', $options);

		if (!$result)
		{
			$this->setError(JText::_('COM_REDEVENT_REGISTRATION_REDFORM_SAVE_FAILED').' - '.$rfcore->getError());

			return false;
		}

		$submit_key = $result->submit_key;
		$user = JFactory::getUser($user_id);
		$rfpost = $result->posts[0];

		if (!$res = $registrationmodel->register($user, $rfpost['sid'], $result->submit_key, $pricegroup->id))
		{
			$this->setError(JText::_('COM_REDEVENT_REGISTRATION_REGISTRATION_FAILED'));
			return false;
		}

		if ($details->notify)
		{
			$mail = $registrationmodel->sendNotificationEmail($submit_key);
		}
		$mail = $registrationmodel->notifyManagers($submit_key);

		return true;
	}

	protected function getPricegroup($xref)
	{
		$db      = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('pricegroup_id AS id, price');
		$query->from('#__redevent_sessions_pricegroups');
		$query->where('xref = ' . $xref);

		$db->setQuery($query, 0, 1);
		$res = $db->loadObject();

		return $res;
	}
}
