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
class RedeventModelMyattended extends RedeventModelBasesessionlist
{
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
		$limitstart_attended 	= $mainframe->input->get('limitstart', 0, '', 'int');

		$this->setState('list.limit', $limit);
		$this->setState('list.start', $limitstart_attended);

		// Get the filter request variables
		$this->setState('filter_order', $mainframe->input->getCmd('filter_order', 'x.dates'));
		$this->setState('filter_order_dir', $mainframe->input->getCmd('filter_order_Dir', 'ASC'));
	}

	/**
	 * Method to get the Events user attended
	 *
	 * @return array
	 */
	public function getItems()
	{
		$pop = JFactory::getApplication()->input->getBool('pop');

		// Lets load the content if it doesn't already exist
		if (empty($this->data))
		{
			$query = $this->buildQuery();
			$pagination = $this->getPagination();

			if ($pop)
			{
				$this->data = $this->_getList($query);
			}
			else
			{
				$this->data = $this->_getList($query, $pagination->limitstart, $pagination->limit);
			}
		}

		$this->data = $this->_categories($this->data);
		$this->data = $this->_getPlacesLeft($this->data);
		$this->data = $this->_getPrices($this->data);

		return $this->data;
	}

	/**
	 * Build the query
	 *
	 * @return JDatabaseQuery
	 */
	protected function buildQuery()
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

		$query->select('x.dates, x.enddates, x.allday, x.times, x.endtimes, x.registrationend');
		$query->select('a.id, a.title, a.created, a.datdescription, a.registra, a.unregistra, a.course_code');
		$query->select('l.venue, l.city, l.state, l.url, l.id as locid, l.street, l.country');
		$query->select('c.name AS catname, c.id AS catid');
		$query->select('x.featured, x.id AS xref, x.maxattendees, x.maxwaitinglist, x.published');
		$query->select('r.id AS attendee_id, r.sid, r.submit_key');
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
	protected function _buildEventListAttendedWhere($query)
	{
		$user = JFactory::getUser();

		$query->where('r.cancelled = 0');
		$query->where('r.waitinglist = 0');

		// Upcoming !
		$now = strftime('%Y-%m-%d %H:%M');
		$query->where('x.dates > 0 AND (CASE WHEN x.times THEN CONCAT(x.dates," ",x.times) ELSE x.dates END) <= ' . $this->_db->Quote($now));

		// Then if the user is attending the event
		$query->where('r.uid = ' . $this->_db->Quote($user->id));

		return $query;
	}

	/**
	 * Method to get a pagination object for the events
	 *
	 * @return integer
	 */
	public function getPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->pagination))
		{
			$this->pagination = new RedeventAjaxPagination(
				$this->getTotal(), $this->getState('list.start'), $this->getState('list.limit')
			);
		}

		return $this->pagination;
	}
}
