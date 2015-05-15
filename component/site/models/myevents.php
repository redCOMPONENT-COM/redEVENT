<?php
/**
 * @package    Redevent.Site
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevents Component my events Model
 *
 * @package  Redevent.Site
 * @since    2.0
 */
class RedeventModelMyevents extends RedeventModelBaseeventlist
{
	/**
	 * Events data array
	 *
	 * @var array
	 */
	protected $events = null;

	/**
	 * Events total
	 *
	 * @var integer
	 */
	protected $total_events = null;

	/**
	 * Pagination object
	 *
	 * @var object
	 */
	protected $pagination_events = null;

	protected $venues = null;

	protected $total_venues = null;

	/**
	 * Pagination object
	 *
	 * @var object
	 */
	protected $pagination_venues = null;

	protected $attending = null;

	protected $total_attending = null;

	protected $pagination_attending = null;

	protected $attended = null;

	protected $total_attended = null;

	protected $pagination_attended = null;

	/**
	 * Constructor
	 *
	 * @since 0.9
	 */
	public function __construct()
	{
		parent::__construct();

		$mainframe = JFactory::getApplication();

		// Get the paramaters of the active menu item
		$params = $mainframe->getParams('com_redevent');

		// Get the number of events from database
		$limit 					= $mainframe->getUserStateFromRequest('com_redevent.myevents.limit', 'limit', $params->def('display_num', 0), 'int');
		$limitstart_events 		= $mainframe->input->get('limitstart', 0, '', 'int');
		$limitstart_venues 		= $mainframe->input->get('limitstart_venues', 0, '', 'int');
		$limitstart_attending 	= $mainframe->input->get('limitstart_attending', 0, '', 'int');
		$limitstart_attended 	= $mainframe->input->get('limitstart_attended', 0, '', 'int');

		$this->setState('limit', $limit);
		$this->setState('limitstart_events', $limitstart_events);
		$this->setState('limitstart_venues', $limitstart_venues);
		$this->setState('limitstart_attending', $limitstart_attending);
		$this->setState('limitstart_attended', $limitstart_attended);

		// Get the filter request variables
		$this->setState('filter_order', $mainframe->input->getCmd('filter_order', 'x.dates'));
		$this->setState('filter_order_dir', $mainframe->input->getCmd('filter_order_Dir', 'ASC'));
	}

	/**
	 * Method to get the Events
	 *
	 * @return array
	 */
	public function getEvents()
	{
		$pop = JRequest::getBool('pop');

		// Lets load the content if it doesn't already exist
		if (empty($this->events))
		{
			$query = $this->_buildQueryEvents();
			$pagination = $this->getEventsPagination();

			if ($pop)
			{
				$this->events = $this->_getList($query);
			}
			else
			{
				$this->events = $this->_getList($query, $pagination->limitstart, $pagination->limit);
			}

			$this->events = $this->_categories($this->events);
			$this->events = $this->_getPlacesLeft($this->events);
		}

		return $this->events;
	}

	/**
	 * Method to get the Events user is attending
	 *
	 * @return array
	 */
	public function getAttending()
	{
		$pop = JRequest::getBool('pop');

		// Lets load the content if it doesn't already exist
		if (empty($this->attending))
		{
			$query = $this->_buildQueryAttending();
			$pagination = $this->getAttendingPagination();

			if ($pop)
			{
				$this->attending = $this->_getList($query);
			}
			else
			{
				$this->attending = $this->_getList($query, $pagination->limitstart, $pagination->limit);
			}
		}

		$this->attending = $this->_categories($this->attending);
		$this->attending = $this->_getPlacesLeft($this->attending);
		$this->attending = $this->_getPrices($this->attending);

		return $this->attending;
	}

	/**
	 * Method to get the Events user attended
	 *
	 * @return array
	 */
	public function getAttended()
	{
		$pop = JRequest::getBool('pop');

		// Lets load the content if it doesn't already exist
		if (empty($this->attended))
		{
			$query = $this->_buildQueryAttended();
			$pagination = $this->getAttendedPagination();

			if ($pop)
			{
				$this->attended = $this->_getList($query);
			}
			else
			{
				$this->attended = $this->_getList($query, $pagination->limitstart, $pagination->limit);
			}
		}

		$this->attended = $this->_categories($this->attended);
		$this->attended = $this->_getPlacesLeft($this->attended);
		$this->attended = $this->_getPrices($this->attended);

		return $this->attended;
	}

	/**
	 * Method to get the Venues
	 *
	 * @return array
	 */
	public function getVenues()
	{
		$pop = JRequest::getBool('pop');

		// Lets load the content if it doesn't already exist
		if (empty($this->venues))
		{
			$query = $this->_buildQueryVenues();
			$pagination = $this->getVenuesPagination();

			if ($pop)
			{
				$this->venues = $this->_getList($query);
			}
			else
			{
				$this->venues = $this->_getList($query, $pagination->limitstart, $pagination->limit);
			}
		}

		return $this->venues;
	}

	/**
	 * Total nr of events
	 *
	 * @return integer
	 */
	public function getTotalEvents()
	{
		// Lets load the total nr if it doesn't already exist
		if (empty($this->total_events))
		{
			$query = $this->_buildQueryEvents();
			$this->total_events = $this->_getListCount($query);
		}

		return $this->total_events;
	}

	/**
	 * Total nr of events
	 *
	 * @return integer
	 */
	public function getTotalAttending()
	{
		// Lets load the total nr if it doesn't already exist
		if (empty($this->total_attending))
		{
			$query = $this->_buildQueryAttending();
			$this->total_attending = $this->_getListCount($query);
		}

		return $this->total_attending;
	}

	/**
	 * Total nr of events
	 *
	 * @return integer
	 */
	public function getTotalAttended()
	{
		// Lets load the total nr if it doesn't already exist
		if (empty($this->total_attended))
		{
			$query = $this->_buildQueryAttended();
			$this->total_attended = $this->_getListCount($query);
		}

		return $this->total_attended;
	}

	/**
	 * Total nr of events
	 *
	 * @return integer
	 */
	public function getTotalVenues()
	{
		// Lets load the total nr if it doesn't already exist
		if (empty($this->total_venues))
		{
			$query = $this->_buildQueryVenues();
			$this->total_venues = $this->_getListCount($query);
		}

		return $this->total_venues;
	}

	/**
	 * Method to get a pagination object for the events
	 *
	 * @return integer
	 */
	public function getEventsPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->pagination_events))
		{
			jimport('joomla.html.pagination');
			$this->pagination_events = new RedeventAjaxPagination(
				$this->getTotalEvents(), $this->getState('limitstart_events'), $this->getState('limit')
			);
		}

		return $this->pagination_events;
	}

	/**
	 * Method to get a pagination object for the venues
	 *
	 * @return integer
	 */
	public function getVenuesPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->pagination_venues))
		{
			jimport('joomla.html.pagination');
			$this->pagination_venues = new RedeventAjaxPagination(
				$this->getTotalVenues(), $this->getState('limitstart_venues'), $this->getState('limit')
			);
		}

		return $this->pagination_venues;
	}

	/**
	 * Method to get a pagination object for the attending events
	 *
	 * @return integer
	 */
	public function getAttendingPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->pagination_attending))
		{
			jimport('joomla.html.pagination');
			$this->pagination_attending = new RedeventAjaxPagination(
				$this->getTotalAttending(), $this->getState('limitstart_attending'), $this->getState('limit')
			);
		}

		return $this->pagination_attending;
	}

	/**
	 * Method to get a pagination object for the attended events
	 *
	 * @return integer
	 */
	public function getAttendedPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->pagination_attended))
		{
			jimport('joomla.html.pagination');
			$this->pagination_attended = new RedeventAjaxPagination(
				$this->getTotalAttended(), $this->getState('limitstart_attending'), $this->getState('limit')
			);
		}

		return $this->pagination_attended;
	}

	/**
	 * Build the query
	 *
	 * @return string
	 */
	protected function _buildQueryEvents()
	{
		$query = $this->_buildQueryEventsSelect();

		// Get the WHERE and ORDER BY clauses for the query
		$query = $this->_buildEventListWhere($query);
		$query = $this->_buildEventListOrderBy($query);

		return $query;
	}

	/**
	 * Build the query
	 *
	 * @return string
	 */
	protected function _buildQueryAttending()
	{
		$query = $this->_buildQueryEventsSelect();
		$query->where('r.cancelled = 0');

		// Get the WHERE and ORDER BY clauses for the query
		$query = $this->_buildEventListAttendingWhere($query);
		$query = $this->_buildEventListOrderBy($query);

		return $query;
	}

	/**
	 * Build the query
	 *
	 * @return string
	 */
	protected function _buildQueryAttended()
	{
		$query = $this->_buildQueryEventsSelect();

		// Get the WHERE and ORDER BY clauses for the query
		$query = $this->_buildEventListAttendedWhere($query);
		$query = $this->_buildEventListOrderBy($query);

		return $query;
	}

	/**
	 * build base select and joins for sessions queries
	 *
	 * @return JDatabaseQuery
	 */
	protected function _buildQueryEventsSelect()
	{
		$db      = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('x.dates, x.enddates, x.times, x.endtimes, x.registrationend, x.id AS xref, x.maxattendees, x.maxwaitinglist, x.published');
		$query->select('a.id, a.title, a.created, a.datdescription, a.registra, a.unregistra, a.course_code');
		$query->select('l.venue, l.city, l.state, l.url, l.id as locid, l.street, l.country');
		$query->select('c.name AS catname, c.id AS catid');
		$query->select('x.featured');
		$query->select('r.id AS attendee_id');
		$query->select('CASE WHEN CHAR_LENGTH(x.title) THEN CONCAT_WS(\' - \', a.title, x.title) ELSE a.title END as full_title');
		$query->select('CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug');
		$query->select('CASE WHEN CHAR_LENGTH(x.alias) THEN CONCAT_WS(\':\', x.id, x.alias) ELSE x.id END as xslug');
		$query->select('CASE WHEN CHAR_LENGTH(l.alias) THEN CONCAT_WS(\':\', l.id, l.alias) ELSE l.id END as venueslug');
		$query->select('CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as categoryslug');
		$query->from('#__redevent_event_venue_xref AS x');
		$query->join('LEFT', '#__redevent_register AS r ON r.xref = x.id');
		$query->join('LEFT', '#__redevent_events AS a ON a.id = x.eventid');
		$query->join('LEFT', '#__redevent_venues AS l ON l.id = x.venueid');
		$query->join('LEFT', '#__redevent_event_category_xref AS xcat ON xcat.event_id = a.id');
		$query->join('LEFT', '#__redevent_categories AS c ON c.id = xcat.category_id');
		$query->group('x.id');

		return $query;
	}

	/**
	 * Build the query
	 *
	 * @return string
	 */
	protected function _buildQueryVenues()
	{
		$allowed = RedeventUserAcl::getInstance()->getAllowedForEventsVenues();

		$db      = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('l.id, l.venue, l.city, l.state, l.url, l.published');
		$query->select('CASE WHEN CHAR_LENGTH(l.alias) THEN CONCAT_WS(\':\', l.id, l.alias) ELSE l.id END as venueslug');
		$query->from('#__redevent_venues AS l');
		$query->group('l.id');
		$query->order('l.venue ASC');

		if ($allowed && count($allowed))
		{
			$query->where('l.id IN (' . implode(',', $allowed) . ') ');
		}
		else
		{
			$query->where('0');
		}

		return $query;
	}

	/**
	 * Build the order clause
	 *
	 * @param   JDatabaseQuery  $query  query
	 *
	 * @return string
	 */
	protected function _buildEventListOrderBy($query)
	{
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
	 * @return string
	 */
	protected function _buildEventListWhere($query)
	{
		$app = JFactory::getApplication();

		// Get the paramaters of the active menu item
		$params = $app->getParams();

		$where = array();

		$where[] = 'x.published > -1';

		$acl = RedeventUserAcl::getInstance();

		if (!$acl->superuser())
		{
			$xrefs = $acl->getCanEditXrefs();
			$xrefs = @array_merge($acl->getXrefsCanViewAttendees(), $xrefs);
			$xrefs = @array_unique($xrefs);

			if ($xrefs && count($xrefs))
			{
				$where[] = ' x.id IN (' . implode(",", $xrefs) . ')';
			}
			else
			{
				$where[] = '0';
			}
		}

		if ($params->get('showopendates', 1) == 0)
		{
			$where[] = ' x.dates IS NOT NULL AND x.dates > 0 ';
		}

		if ($params->get('shownonbookable', 1) == 0)
		{
			$where[] = ' a.registra > 0 ';
		}

		/*
		 * If we have a filter, and this is enabled... lets tack the AND clause
		* for the filter onto the WHERE clause of the item query.
		*/
		if ($params->get('filter_text'))
		{
			$filter = $app->input->getString('filter', '', 'request');
			$filter_type = $app->input->getWord('filter_type', '', 'request');

			if ($filter)
			{
				// Clean filter variables
				$filter = JString::strtolower($filter);
				$filter = $this->_db->Quote('%' . $this->_db->escape($filter, true) . '%', false);
				$filter_type = JString::strtolower($filter_type);

				switch ($filter_type)
				{
					case 'title':
						$where[] = ' LOWER( a.title ) LIKE ' . $filter;
						break;

					case 'venue':
						$where[] = ' LOWER( l.venue ) LIKE ' . $filter;
						break;

					case 'city':
						$where[] = ' LOWER( l.city ) LIKE ' . $filter;
						break;

					case 'type':
						$where[] = ' LOWER( c.name ) LIKE ' . $filter;
						break;
				}
			}
		}

		if ($app->input->getInt('filter_event'))
		{
			$where[] = ' a.id = ' . $app->input->getInt('filter_event');
		}

		$query->where(implode(' AND ', $where));

		return $query;
	}

	/**
	 * Build the where clause
	 *
	 * @return string
	 */
	protected function _buildEventsOptionsWhere()
	{
		$mainframe = JFactory::getApplication();

		// Get the paramaters of the active menu item
		$params = $mainframe->getParams();

		$task = JRequest::getWord('task');

		$where = array();

		// First thing we need to do is to select only needed events
		if ($task == 'archive')
		{
			$where[] = ' x.published = -1 ';
		}
		else
		{
			$where[] = ' x.published > -1 ';
		}

		$acl = RedeventUserAcl::getInstance();

		if (!$acl->superuser())
		{
			$xrefs = $acl->getCanEditXrefs();
			$xrefs = array_merge($acl->getXrefsCanViewAttendees(), $xrefs);
			$xrefs = array_unique($xrefs);

			if ($xrefs && count($xrefs))
			{
				$where[] = ' x.id IN (' . implode(",", $xrefs) . ')';
			}
			else
			{
				$where[] = '0';
			}
		}

		if ($params->get('showopendates', 1) == 0)
		{
			$where[] = ' x.dates IS NOT NULL AND x.dates > 0 ';
		}

		if ($params->get('shownonbookable', 1) == 0)
		{
			$where[] = ' a.registra > 0 ';
		}

		/*
		 * If we have a filter, and this is enabled... lets tack the AND clause
		* for the filter onto the WHERE clause of the item query.
		*/
		if ($params->get('filter_text'))
		{
			$filter = JRequest::getString('filter', '', 'request');
			$filter_type = JRequest::getWord('filter_type', '', 'request');

			if ($filter)
			{
				// Clean filter variables
				$filter = JString::strtolower($filter);
				$filter = $this->_db->Quote('%' . $this->_db->escape($filter, true) . '%', false);
				$filter_type = JString::strtolower($filter_type);

				switch ($filter_type)
				{
					case 'title':
						$where[] = ' LOWER( a.title ) LIKE ' . $filter;
						break;

					case 'venue':
						$where[] = ' LOWER( l.venue ) LIKE ' . $filter;
						break;

					case 'city':
						$where[] = ' LOWER( l.city ) LIKE ' . $filter;
						break;

					case 'type':
						$where[] = ' LOWER( c.name ) LIKE ' . $filter;
						break;
				}
			}
		}

		$where = ' WHERE ' . implode(' AND ', $where);

		return $where;
	}

	/**
	 * Build the where clause
	 *
	 * @param   JDatabaseQuery  $query  query object
	 *
	 * @return string
	 */
	protected function _buildEventListAttendingWhere($query)
	{
		$user = JFactory::getUser();

		$query->where('x.published > -1');

		// Upcoming !
		$now = strftime('%Y-%m-%d %H:%M');
		$query->where('(x.dates = 0 OR (CASE WHEN x.times THEN CONCAT(x.dates," ",x.times) ELSE x.dates END) > ' . $this->_db->Quote($now) . ')');

		// Then if the user is attending the event
		$query->where('r.uid = ' . $this->_db->Quote($user->id));

		return $query;
	}

	/**
	 * Build the where clause
	 *
	 * @param   JDatabaseQuery  $query  query object
	 *
	 * @return string
	 */
	protected function _buildEventListAttendedWhere($query)
	{
		$user = JFactory::getUser();

		$query->where('x.published > -1');

		// Upcoming !
		$now = strftime('%Y-%m-%d %H:%M');
		$query->where('x.dates > 0 AND (CASE WHEN x.times THEN CONCAT(x.dates," ",x.times) ELSE x.dates END) <= ' . $this->_db->Quote($now));

		// Then if the user is attending the event
		$query->where('r.uid = ' . $this->_db->Quote($user->id));

		return $query;
	}

	/**
	 * Get events as options
	 *
	 * @return mixed
	 */
	public function getEventsOptions()
	{
		// Get the WHERE and ORDER BY clauses for the query
		$where = $this->_buildEventsOptionsWhere();

		// Get Events from Database
		$query = ' SELECT a.id AS value, a.title as text '
		. ' FROM #__redevent_event_venue_xref AS x'
		. ' LEFT JOIN #__redevent_events AS a ON a.id = x.eventid'
		. ' LEFT JOIN #__redevent_venues AS l ON l.id = x.venueid'
		. ' LEFT JOIN #__redevent_event_category_xref AS xcat ON xcat.event_id = a.id'
		. ' LEFT JOIN #__redevent_categories AS c ON c.id = xcat.category_id'
		. $where
		. ' GROUP BY (a.id) '
		. ' ORDER BY a.title ';

		$this->_db->setQuery($query);
		$res = $this->_db->loadObjectList();

		return $res;
	}
}
