<?php
/**
 * @version 1.0 $Id: eventlist.php 1180 2009-10-13 18:43:13Z julien $
 * @package Joomla
 * @subpackage redEVENT
 * @copyright redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license GNU/GPL, see LICENSE.php
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

// no direct access
defined('_JEXEC') or die ('Restricted access');

jimport('joomla.application.component.model');
jimport('joomla.html.pagination');

/**
 * Redevents Component my events Model
 *
 * @package Joomla
 * @subpackage Redevent
 * @since   2.0
*/
class RedeventModelMyevents extends RedeventModelBaseeventlist
{
	/**
	 * Events data array
	 *
	 * @var array
	 */
	protected $_events = null;
	/**
	 * Events total
	 *
	 * @var integer
	 */
	protected $_total_events = null;
	/**
	 * Pagination object
	 *
	 * @var object
	 */
	protected $_pagination_events = null;

	protected $_venues = null;

	protected $_total_venues = null;

	/**
	 * Pagination object
	 *
	 * @var object
	 */
	protected $_pagination_venues = null;


	protected $_attending = null;

	protected $_total_attending = null;

	protected $_pagination_attending = null;

	protected $_attended = null;

	protected $_total_attended = null;

	protected $_pagination_attended = null;


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

		//get the number of events from database
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
	 * @access public
	 * @return array
	 */
	public function & getEvents()
	{
		$pop = JRequest::getBool('pop');

		// Lets load the content if it doesn't already exist
		if (empty($this->_events))
		{
			$query = $this->_buildQueryEvents();
			$pagination = $this->getEventsPagination();

			if ($pop)
			{
				$this->_events = $this->_getList($query);
			}
			else
			{
				$this->_events = $this->_getList($query, $pagination->limitstart, $pagination->limit);
			}

			$this->_events = $this->_categories($this->_events);
			$this->_events = $this->_getPlacesLeft($this->_events);
		}

		return $this->_events;
	}

	/**
	 * Method to get the Events user is attending
	 *
	 * @access public
	 * @return array
	 */
	public function & getAttending()
	{
		$pop = JRequest::getBool('pop');

		// Lets load the content if it doesn't already exist
		if (empty($this->_attending))
		{
			$query = $this->_buildQueryAttending();
			$pagination = $this->getAttendingPagination();

			if ($pop)
			{
				$this->_attending = $this->_getList($query);
			}
			else
			{
				$this->_attending = $this->_getList($query, $pagination->limitstart, $pagination->limit);
			}
		}

		$this->_attending = $this->_categories($this->_attending);
		$this->_attending = $this->_getPlacesLeft($this->_attending);
		$this->_attending = $this->_getPrices($this->_attending);

		return $this->_attending;
	}

	/**
	 * Method to get the Events user attended
	 *
	 * @access public
	 * @return array
	 */
	public function & getAttended()
	{
		$pop = JRequest::getBool('pop');

		// Lets load the content if it doesn't already exist
		if (empty($this->_attended))
		{
			$query = $this->_buildQueryAttended();
			$pagination = $this->getAttendedPagination();

			if ($pop)
			{
				$this->_attended = $this->_getList($query);
			}
			else
			{
				$this->_attended = $this->_getList($query, $pagination->limitstart, $pagination->limit);
			}
		}

		$this->_attended = $this->_categories($this->_attended);
		$this->_attended = $this->_getPlacesLeft($this->_attended);
		$this->_attended = $this->_getPrices($this->_attended);

		return $this->_attended;
	}

	/**
	 * Method to get the Venues
	 *
	 * @access public
	 * @return array
	 */
	public function & getVenues()
	{
		$pop = JRequest::getBool('pop');

		// Lets load the content if it doesn't already exist
		if (empty($this->_venues))
		{
			$query = $this->_buildQueryVenues();
			$pagination = $this->getVenuesPagination();

			if ($pop)
			{
				$this->_venues = $this->_getList($query);
			}
			else
			{
				$this->_venues = $this->_getList($query, $pagination->limitstart, $pagination->limit);
			}
		}

		return $this->_venues;
	}

	/**
	 * Total nr of events
	 *
	 * @access public
	 * @return integer
	 */
	public function getTotalEvents()
	{
		// Lets load the total nr if it doesn't already exist
		if (empty($this->_total_events))
		{
			$query = $this->_buildQueryEvents();
			$this->_total_events = $this->_getListCount($query);
		}

		return $this->_total_events;
	}

	/**
	 * Total nr of events
	 *
	 * @access public
	 * @return integer
	 */
	public function getTotalAttending()
	{
		// Lets load the total nr if it doesn't already exist
		if (empty($this->_total_attending))
		{
			$query = $this->_buildQueryAttending();
			$this->_total_attending = $this->_getListCount($query);
		}

		return $this->_total_attending;
	}

	/**
	 * Total nr of events
	 *
	 * @access public
	 * @return integer
	 */
	public function getTotalAttended()
	{
		// Lets load the total nr if it doesn't already exist
		if (empty($this->_total_attended))
		{
			$query = $this->_buildQueryAttended();
			$this->_total_attended = $this->_getListCount($query);
		}

		return $this->_total_attended;
	}

	/**
	 * Total nr of events
	 *
	 * @access public
	 * @return integer
	 */
	public function getTotalVenues()
	{
		// Lets load the total nr if it doesn't already exist
		if (empty($this->_total_venues))
		{
			$query = $this->_buildQueryVenues();
			$this->_total_venues = $this->_getListCount($query);
		}

		return $this->_total_venues;
	}

	/**
	 * Method to get a pagination object for the events
	 *
	 * @access public
	 * @return integer
	 */
	public function getEventsPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_pagination_events))
		{
			jimport('joomla.html.pagination');
			$this->_pagination_events = new REAjaxPagination($this->getTotalEvents(), $this->getState('limitstart_events'), $this->getState('limit'));
		}

		return $this->_pagination_events;
	}

	/**
	 * Method to get a pagination object for the venues
	 *
	 * @access public
	 * @return integer
	 */
	public function getVenuesPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_pagination_venues))
		{
			jimport('joomla.html.pagination');
			$this->_pagination_venues = new REAjaxPagination($this->getTotalVenues(), $this->getState('limitstart_venues'), $this->getState('limit'));
		}

		return $this->_pagination_venues;
	}

	/**
	 * Method to get a pagination object for the attending events
	 *
	 * @access public
	 * @return integer
	 */
	public function getAttendingPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_pagination_attending))
		{
			jimport('joomla.html.pagination');
			$this->_pagination_attending = new REAjaxPagination($this->getTotalAttending(), $this->getState('limitstart_attending'), $this->getState('limit'));
		}

		return $this->_pagination_attending;
	}

	/**
	 * Method to get a pagination object for the attended events
	 *
	 * @access public
	 * @return integer
	 */
	public function getAttendedPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_pagination_attended))
		{
			jimport('joomla.html.pagination');
			$this->_pagination_attended = new REAjaxPagination($this->getTotalAttended(), $this->getState('limitstart_attending'), $this->getState('limit'));
		}

		return $this->_pagination_attended;
	}

	/**
	 * Build the query
	 *
	 * @access private
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
		$query->select('c.catname, c.id AS catid');
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
	 * @access private
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
	 * @access private
	 * @return string
	 */
	protected function _buildEventListOrderBy($query)
	{
		$filter_order = $this->getState('filter_order');
		$filter_order_dir = $this->getState('filter_order_dir');

		$query->order($filter_order.' '.$filter_order_dir.', x.dates, x.times');

		return $query;
	}

	/**
	 * Build the where clause
	 *
	 * @access private
	 * @return string
	 */
	protected function _buildEventListWhere($query)
	{
		$mainframe = JFactory::getApplication();

		$user = JFactory::getUser();
		$gid = (int) max($user->getAuthorisedViewLevels());

		// Get the paramaters of the active menu item
		$params = $mainframe->getParams();

		$task = JRequest::getWord('task');

		$where = array();

		$where[] = ' x.published > -1 ';

		$acl = RedeventUserAcl::getInstance();
		if (!$acl->superuser())
		{
			$xrefs = $acl->getCanEditXrefs();
			$xrefs = @array_merge($acl->getXrefsCanViewAttendees(), $xrefs);
			$xrefs = @array_unique($xrefs);

			if ($xrefs && count($xrefs))
			{
				$where[] = ' x.id IN ('.implode(",", $xrefs).')';
			}
			else
			{
				$where[] = '0';
			}
		}

		if ($params->get('showopendates', 1) == 0) {
			$where[] = ' x.dates IS NOT NULL AND x.dates > 0 ';
		}

		if ($params->get('shownonbookable', 1) == 0) {
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
				// clean filter variables
				$filter = JString::strtolower($filter);
				$filter = $this->_db->Quote('%'.$this->_db->getEscaped($filter, true).'%', false);
				$filter_type = JString::strtolower($filter_type);

				switch($filter_type)
				{
					case 'title':
						$where[] = ' LOWER( a.title ) LIKE '.$filter;
						break;

					case 'venue':
						$where[] = ' LOWER( l.venue ) LIKE '.$filter;
						break;

					case 'city':
						$where[] = ' LOWER( l.city ) LIKE '.$filter;
						break;

					case 'type':
						$where[] = ' LOWER( c.catname ) LIKE '.$filter;
						break;
				}
			}
		}

		if (JRequest::getInt('filter_event'))
		{
			$where[] = ' a.id = '.JRequest::getInt('filter_event');
		}

		$query->where(implode(' AND ', $where));

		return $query;
	}

	/**
	 * Build the where clause
	 *
	 * @access private
	 * @return string
	 */
	protected function _buildEventsOptionsWhere()
	{
		$mainframe = JFactory::getApplication();

		$user = JFactory::getUser();
		$gid = (int) max($user->getAuthorisedViewLevels());

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
				$where[] = ' x.id IN ('.implode(",", $xrefs).')';
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
				// clean filter variables
				$filter = JString::strtolower($filter);
				$filter = $this->_db->Quote('%'.$this->_db->getEscaped($filter, true).'%', false);
				$filter_type = JString::strtolower($filter_type);

				switch($filter_type)
				{
					case 'title':
						$where[] = ' LOWER( a.title ) LIKE '.$filter;
						break;

					case 'venue':
						$where[] = ' LOWER( l.venue ) LIKE '.$filter;
						break;

					case 'city':
						$where[] = ' LOWER( l.city ) LIKE '.$filter;
						break;

					case 'type':
						$where[] = ' LOWER( c.catname ) LIKE '.$filter;
						break;
				}
			}
		}

		$where = ' WHERE '. implode(' AND ', $where);

		return $where;
	}

	/**
	 * Build the where clause
	 *
	 * @access private
	 * @return string
	 */
	protected function _buildEventListAttendingWhere($query)
	{
		$user = JFactory::getUser();

		$query->where('x.published > -1');

		// Upcoming !
		$now = strftime('%Y-%m-%d %H:%M');
		$query->where('(x.dates = 0 OR (CASE WHEN x.times THEN CONCAT(x.dates," ",x.times) ELSE x.dates END) > ' . $this->_db->Quote($now) . ')');

		// then if the user is attending the event
		$query->where('r.uid = '.$this->_db->Quote($user->id));

		return $query;
	}

	/**
	 * Build the where clause
	 *
	 * @access private
	 * @return string
	 */
	protected function _buildEventListAttendedWhere($query)
	{
		$user = JFactory::getUser();

		$query->where('x.published > -1');

		// Upcoming !
		$now = strftime('%Y-%m-%d %H:%M');
		$query->where('x.dates > 0 AND (CASE WHEN x.times THEN CONCAT(x.dates," ",x.times) ELSE x.dates END) <= ' . $this->_db->Quote($now));

		// then if the user is attending the event
		$query->where('r.uid = '.$this->_db->Quote($user->id));

		return $query;
	}

	public function getEventsOptions()
	{
		// Get the WHERE and ORDER BY clauses for the query
		$where = $this->_buildEventsOptionsWhere();

		//Get Events from Database
		$query = ' SELECT a.id AS value, a.title as text '
		. ' FROM #__redevent_event_venue_xref AS x'
		. ' LEFT JOIN #__redevent_events AS a ON a.id = x.eventid'
		. ' LEFT JOIN #__redevent_venues AS l ON l.id = x.venueid'
		. ' LEFT JOIN #__redevent_event_category_xref AS xcat ON xcat.event_id = a.id'
		. ' LEFT JOIN #__redevent_categories AS c ON c.id = xcat.category_id'
		. $where
		. ' GROUP BY (a.id) '
		. ' ORDER BY a.title '
		;
		$this->_db->setQuery($query);
		$res = $this->_db->loadObjectList();

		return $res;
	}
}
