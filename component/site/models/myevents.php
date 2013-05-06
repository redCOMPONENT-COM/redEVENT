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

require_once('baseeventslist.php');

/**
 * Redevents Component my events Model
 *
 * @package Joomla
 * @subpackage Redevent
 * @since   2.0
*/
class RedeventModelMyevents extends RedeventModelBaseEventList
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

	protected $_venues = null;

	protected $_total_venues = null;

	protected $_attending = null;

	protected $_total_attending = null;

	/**
	 * Pagination object
	 *
	 * @var object
	 */
	protected $_pagination_events = null;

	/**
	 * Pagination object
	 *
	 * @var object
	 */
	protected $_pagination_venues = null;

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

		$this->setState('limit', $limit);
		$this->setState('limitstart_events', $limitstart_events);
		$this->setState('limitstart_venues', $limitstart_venues);
		$this->setState('limitstart_attending', $limitstart_attending);

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
	 * Build the query
	 *
	 * @access private
	 * @return string
	 */
	protected function _buildQueryEvents()
	{
		// Get the WHERE and ORDER BY clauses for the query
		$where = $this->_buildEventListWhere();
		$orderby = $this->_buildEventListOrderBy();

		//Get Events from Database
		$query = 'SELECT x.dates, x.enddates, x.times, x.endtimes, x.registrationend, x.id AS xref, x.maxattendees, x.maxwaitinglist, x.published, '
		. ' a.id, a.title, a.created, a.datdescription, a.registra, a.course_code, '
		. ' l.venue, l.city, l.state, l.url, l.id as locid, '
		. ' c.catname, c.id AS catid, '
		. ' CASE WHEN CHAR_LENGTH(x.title) THEN CONCAT_WS(\' - \', a.title, x.title) ELSE a.title END as full_title, '
		. ' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug, '
		. ' CASE WHEN CHAR_LENGTH(l.alias) THEN CONCAT_WS(\':\', l.id, l.alias) ELSE l.id END as venueslug, '
		. ' CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as categoryslug '
		. ' FROM #__redevent_event_venue_xref AS x'
		. ' LEFT JOIN #__redevent_events AS a ON a.id = x.eventid'
		. ' LEFT JOIN #__redevent_venues AS l ON l.id = x.venueid'
		. ' LEFT JOIN #__redevent_event_category_xref AS xcat ON xcat.event_id = a.id'
		. ' LEFT JOIN #__redevent_categories AS c ON c.id = xcat.category_id'
		. $where
		. ' GROUP BY (x.id) '
		. $orderby
		;

		return $query;
	}

	/**
	 * Build the query
	 *
	 * @access private
	 * @return string
	 */
	protected function _buildQueryAttending()
	{
		// Get the WHERE and ORDER BY clauses for the query
		$where = $this->_buildEventListAttendingWhere();
		$orderby = $this->_buildEventListOrderBy();

		//Get Events from Database
		$query = 'SELECT x.dates, x.enddates, x.times, x.endtimes, x.registrationend, x.id AS xref, x.maxattendees, x.maxwaitinglist, '
		. ' a.id, a.title, a.created, a.datdescription, a.registra, '
		. ' l.venue, l.city, l.state, l.url, l.id as locid, l.street, l.country, '
		. ' c.catname, c.id AS catid,'
		. ' x.featured, '
		. ' CASE WHEN CHAR_LENGTH(x.title) THEN CONCAT_WS(\' - \', a.title, x.title) ELSE a.title END as full_title, '
		. ' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug, '
		. ' CASE WHEN CHAR_LENGTH(x.alias) THEN CONCAT_WS(\':\', x.id, x.alias) ELSE x.id END as xslug, '
		. ' CASE WHEN CHAR_LENGTH(l.alias) THEN CONCAT_WS(\':\', l.id, l.alias) ELSE l.id END as venueslug, '
		. ' CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as categoryslug '
		. ' FROM #__redevent_event_venue_xref AS x'
		. ' INNER JOIN #__redevent_register AS r ON r.xref = x.id '
		. ' LEFT JOIN #__redevent_events AS a ON a.id = x.eventid'
		. ' LEFT JOIN #__redevent_venues AS l ON l.id = x.venueid'
		. ' LEFT JOIN #__redevent_event_category_xref AS xcat ON xcat.event_id = a.id'
		. ' LEFT JOIN #__redevent_categories AS c ON c.id = xcat.category_id'
		. $where
		. ' GROUP BY (x.id) '
		. $orderby
		;

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
		$allowed = UserAcl::getInstance()->getAllowedForEventsVenues();

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
	protected function _buildEventListOrderBy()
	{
		$filter_order = $this->getState('filter_order');
		$filter_order_dir = $this->getState('filter_order_dir');

		$orderby = ' ORDER BY '.$filter_order.' '.$filter_order_dir.', x.dates, x.times';

		return $orderby;
	}

	/**
	 * Build the where clause
	 *
	 * @access private
	 * @return string
	 */
	protected function _buildEventListWhere()
	{
		$mainframe = JFactory::getApplication();

		$user = JFactory::getUser();
		$gid = (int) max($user->getAuthorisedViewLevels());

		// Get the paramaters of the active menu item
		$params = $mainframe->getParams();

		$task = JRequest::getWord('task');

		$where = array();

		$where[] = ' x.published > -1 ';

		$acl = UserAcl::getInstance();
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

		$where = ' WHERE '. implode(' AND ', $where);

		return $where;
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

		$acl = UserAcl::getInstance();

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
	protected function _buildEventListAttendingWhere()
	{
		$mainframe = JFactory::getApplication();

		$user = JFactory::getUser();

		// Get the paramaters of the active menu item
		$params = & $mainframe->getParams();

		$task = JRequest::getWord('task');

		// First thing we need to do is to select only needed events
		if ($task == 'archive')
		{
			$where = ' WHERE x.published = -1';
		}
		else
		{
			$where = ' WHERE x.published = 1';
		}

		// then if the user is attending the event
		$where .= ' AND r.uid = '.$this->_db->Quote($user->id);

		return $where;
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
