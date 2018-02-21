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
class Redeventb2bModelEditmember extends RedeventModelBasesessionlist
{
	/**
	 * @var integer
	 */
	protected $uid = null;

	/**
	 * Redeventb2bModelFrontadmin constructor.
	 *
	 * @param   array  $config  config
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$app = JFactory::getApplication();
		$params = RedeventHelper::config();

		$this->uid = $app->input->get('uid', 0, 'int');
		$this->setState('uid', $this->uid);

		// Editmember
		$this->setState('booked_order',     JRequest::getCmd('booked_order', 'x.dates'));
		$this->setState('booked_order_dir', strtoupper(JRequest::getCmd('booked_order_dir', 'DESC')) == 'DESC' ? 'DESC' : 'ASC');

		$limit = $app->getUserStateFromRequest('com_redevent.limit', 'limit', $params->def('b2b_sessions_display_num', 15), 'int');
		$booked_limitstart = JRequest::getVar('booked_limitstart', 0, '', 'int');

		// In case limit has been changed, adjust it
		$booked_limitstart = ($limit != 0 ? (floor($booked_limitstart / $limit) * $limit) : 0);
		$this->setState('booked_limitstart', $booked_limitstart);

		$this->setState('previous_order',     JRequest::getCmd('previous_order', 'x.dates'));
		$this->setState('previous_order_dir', strtoupper(JRequest::getCmd('previous_order_dir', 'DESC')) == 'DESC' ? 'DESC' : 'ASC');

		$previous_limitstart = JRequest::getVar('previous_limitstart', 0, '', 'int');

		// In case limit has been changed, adjust it
		$previous_limitstart = ($limit != 0 ? (floor($previous_limitstart / $limit) * $limit) : 0);
		$this->setState('previous_limitstart', $previous_limitstart);
	}

	/**
	 * returns user info
	 *
	 * @param   int  $uid  user id
	 *
	 * @return object
	 *
	 * @todo: get info from redmember !!
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
			$this->pagination_booked = new RedeventAjaxPagination(
				$this->getTotalMemberBooked(), $this->getState('booked_limitstart'), $this->getState('limit')
			);
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
			$this->pagination_previous = new RedeventAjaxPagination(
				$this->getTotalMemberPrevious(), $this->getState('previous_limitstart'), $this->getState('limit')
			);
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
	 * Get member current bookings
	 *
	 * @return array|null
	 */
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

	/**
	 * Build query booked
	 *
	 * @return JDatabaseQuery
	 */
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

	/**
	 * Get member past bookings
	 *
	 * @return array|null
	 */
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

	/**
	 * Build query past bookings
	 *
	 * @return JDatabaseQuery
	 */
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

		// Language filter
		if ($this->getState('filter.language'))
		{
			$query->where('(c.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ') OR c.language IS NULL)');
		}

		$filter_order = $this->getState('filter_order');
		$filter_order_dir = $this->getState('filter_order_dir');

		$query->order($filter_order . ' ' . $filter_order_dir . ', x.dates, x.times');

		return $query;
	}
}
