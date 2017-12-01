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
class RedeventModelMyattending extends RedeventModelBasesessionlist
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
		$limitstart_attending 	= $mainframe->input->get('limitstart', 0, '', 'int');

		$this->setState('list.limit', $limit);
		$this->setState('list.start', $limitstart_attending);

		// Get the filter request variables
		$this->setState('filter_order', $mainframe->input->getCmd('filter_order', 'x.dates'));
		$this->setState('filter_order_dir', $mainframe->input->getCmd('filter_order_Dir', 'ASC'));
	}

	/**
	 * Method to get the Events user is attending
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

			$this->data = $this->_categories($this->data);
			$this->data = $this->_getPlacesLeft($this->data);
			$this->data = $this->_getPrices($this->data);
			$this->data = $this->addPaymentInfo($this->data);
		}

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
		$query->where('r.cancelled = 0');

		// Get the WHERE and ORDER BY clauses for the query
		$query = $this->_buildEventListAttendingWhere($query);

		$filter_order = $this->getState('filter_order');
		$filter_order_dir = $this->getState('filter_order_dir');

		$query->order($filter_order . ' ' . $filter_order_dir . ', x.dates, x.times');

		return $query;
	}

	/**
	 * build base select and joins for sessions queries
	 *
	 * @return JDatabaseQuery
	 */
	protected function _buildQueryEventsSelect()
	{
		$query = parent::buildSelectFrom();
		$query->select('l.id as locid');
		$query->select('r.id AS attendee_id, r.sid, r.submit_key');
		$query->join('LEFT', '#__redevent_register AS r ON r.xref = x.id');

		return $query;
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
		$query->where('(x.dates IS NULL OR (CASE WHEN x.times THEN CONCAT(x.dates," ",x.times) ELSE x.dates END) > ' . $this->_db->Quote($now) . ')');

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
