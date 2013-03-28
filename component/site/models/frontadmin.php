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
	 * caching for events
	 *
	 * @var array
	 */
	protected $events = null;
	protected $pagination_events = null;
	protected $total_events = null;

	/**
	 * Method to get the Events
	 *
	 * @return array
	 */
	public function getEvents()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->events))
		{
			$query = $this->_buildQueryEvents();
			$pagination = $this->getEventsPagination();
			$this->events = $this->_getList($query, $pagination->limitstart, $pagination->limit);
			$this->events = $this->_categories($this->events);
			$this->events = $this->_getPlacesLeft($this->events);
		}

		return $this->events;
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
		if (empty($this->pagination_events))
		{
			jimport('joomla.html.pagination');
			$this->pagination_events = new JPagination($this->getTotalEvents(), $this->getState('limitstart_events'), $this->getState('limit'));
		}

		return $this->pagination_events;
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
	 * returns events as options for filter
	 *
	 * @return array
	 */
	public function getEventsOptions()
	{
		return array();
	}

	/**
	 * returns sessions as options for filter
	 *
	 * @return array
	 */
	public function getSessionsOptions()
	{
		return array();
	}

	/**
	 * returns sessions as options for filter
	 *
	 * @return array
	 */
	public function getVenuesOptions()
	{
		return array();
	}

	/**
	 * returns sessions as options for filter
	 *
	 * @return array
	 */
	public function getCategoriesOptions()
	{
		return array();
	}

	/**
	 * Build the events query
	 *
	 * @access private
	 * @return string
	 */
	protected function _buildQueryEvents()
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
		$query = $this->_buildEventListWhere($query);

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
	protected function _buildEventListWhere(JDatabaseQuery $query)
	{
		$mainframe = JFactory::getApplication();

		$user = JFactory::getUser();

		// Get the paramaters of the active menu item
		$params = $mainframe->getParams();

		$task = JRequest::getWord('task');

		$where = array();

		$where[] = ' x.published > -1 ';

		$acl = UserAcl::getInstance();

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
			$filter      = JRequest::getString('filter', '', 'request');
			$filter_type = JRequest::getWord('filter_type', '', 'request');

			if ($filter)
			{
				// Clean filter variables
				$filter = JString::strtolower($filter);
				$filter = $this->_db->Quote('%' . $this->_db->getEscaped($filter, true) . '%', false);
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
						$where[] = ' LOWER( c.catname ) LIKE ' . $filter;
						break;
				}
			}
		}

		if (JRequest::getInt('filter_event'))
		{
			$where[] = ' a.id = ' . JRequest::getInt('filter_event');
		}

		$query->where(implode(' AND ', $where));

		return $query;
	}
}
