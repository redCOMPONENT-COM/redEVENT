<?php
/**
 * @package    Redeventb2b.site
 * @copyright  Copyright (C) 2008 - 2016 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevents Component events list Model
 *
 * @package  Redevent
 * @since    2.5
 */
class Redeventb2bModelBookedsessions extends RedeventModelBasesessionlist
{
	protected $booked = null;

	protected $pagination_booked = null;

	protected $total_booked = null;

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
			$this->pagination_booked = new RedeventAjaxPagination(
				$this->getTotalBookings(), $this->getState('bookings_limitstart'), $this->getState('limit')
			);
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
	 * return organization name
	 *
	 * @return boolean
	 */
	public function getOrganization()
	{
		$organisationId = $this->getState('filter_organization');

		if (!$organisationId)
		{
			return false;
		}

		$db      = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('o.name')
			->from('#__redmember_organization AS o')
			->where('o.id = ' . (int) $organisationId);

		$db->setQuery($query);
		$res = $db->loadResult();

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

		$query->select('x.dates, x.enddates, x.allday, x.times, x.endtimes, x.registrationend');
		$query->select('x.session_language, x.id AS xref, x.maxattendees, x.maxwaitinglist, x.published');
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
		$query->join('LEFT', $db->quoteName('#__languages') . ' AS lg ON lg.lang_code = a.language');

		// Join over
		$query->join('INNER', '#__redevent_register AS r ON r.xref = x.id');
		$query->join('INNER', '#__redmember_users AS rmu ON rmu.joomla_user_id = r.uid');
		$query->join('INNER', '#__redmember_user_organization_xref AS rmuo ON rmuo.user_id = rmu.joomla_user_id');
		$query->join('INNER', '#__users AS u ON u.id = rmu.joomla_user_id');
		$query->where('rmuo.organization_id = ' . (int) $this->getState('filter_organization'));
		$query->where('r.cancelled = 0');

		$session_state = array();

		if ($this->getState('filter_bookings_state') == -1)
		{
			$query->where('x.published = -1');
		}
		else
		{
			$query->where('x.published = 1');
		}

		if ($this->getState('filter_person'))
		{
			$matching = array();
			$matching[] = 'u.name LIKE (' . $db->Quote('%' . $this->getState('filter_person') . '%') . ')';
			$matching[] = 'u.username LIKE (' . $db->Quote('%' . $this->getState('filter_person') . '%') . ')';
			$matching[] = 'u.email LIKE (' . $db->Quote('%' . $this->getState('filter_person') . '%') . ')';
			$query->where('(' . implode(' OR ', $matching) . ')');
		}

		if ($this->getState('filter_event'))
		{
			$query->where('x.eventid = ' . $this->getState('filter_event'));
		}

		if ($this->getState('filter_category'))
		{
			$query->where('xcat.category_id = ' . $this->getState('filter_category'));
		}

		if ($this->getState('filter_venue'))
		{
			$query->where('x.venueid = ' . $this->getState('filter_venue'));
		}

		if ($from = $this->getState('filter_from') && RedeventHelperDate::isValidDate($this->getState('filter_from')))
		{
			$query->where('DATE(x.dates) >= ' . $db->quote($this->getState('filter_from')));
		}

		if ($to = $this->getState('filter_to') && RedeventHelperDate::isValidDate($this->getState('filter_to')))
		{
			$query->where('x.dates > 0 AND DATE(x.dates) <= ' . $db->quote($this->getState('filter_to')));
		}

		// Language filter
		if ($this->getState('filter.language'))
		{
			$query->where('(c.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ') OR c.language IS NULL)');
		}

		// Only bookings associated to organisation, or not if new
		$query->where('r.origin = ' . $db->q('b2b'));
		$query->where('(r.organisation_id = 0 OR r.organisation_id = ' . $this->getState('filter_organization') . ')');

		$filter_order = $this->getState('filter_order');
		$filter_order_dir = $this->getState('filter_order_dir');

		$query->order($filter_order . ' ' . $filter_order_dir . ', x.dates, x.times');

		return $query;
	}

	protected function populateState()
	{
		parent::populateState();

		$app = JFactory::getApplication();

		$this->setState('filter_bookings_state', $app->input->get('filter_bookings_state', 1));
		$this->setState('filter_organization', $app->input->getInt('orgId'));

		$this->setState(
			'filter_from',    $app->getUserStateFromRequest('com_redevent.' . $this->getName() . '.filter_from',
			'filter_from',    '', 'string')
		);
		$this->setState(
			'filter_to',    $app->getUserStateFromRequest('com_redevent.' . $this->getName() . '.filter_to',
			'filter_to',    '', 'string')
		);
	}
}
