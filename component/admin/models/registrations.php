<?php
/**
 * @version 1.0 $Id$
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
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

/**
 * EventList Component registrations Model
 *
 * @package Joomla
 * @subpackage redEVENT
 * @since		0.9
 */
class RedEventModelRegistrations extends JModel
{
	/**
	 * Events data array
	 *
	 * @var array
	 */
	var $_data = null;

	/**
	 * Events total
	 *
	 * @var integer
	 */
	var $_total = null;

	/**
	 * Events total
	 *
	 * @var integer
	 */
	var $_event = null;

	/**
	 * Pagination object
	 *
	 * @var object
	 */
	var $_pagination = null;

	/**
	 * Constructor
	 *
	 * @since 0.9
	 */
	function __construct()
	{
		parent::__construct();

		$mainframe = &JFactory::getApplication();

		$option = JRequest::getCmd('option');

		$limit		  = $mainframe->getUserStateFromRequest( $option.'limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart = $mainframe->getUserStateFromRequest( $option.'limitstart', 'limitstart', 0, 'int' );

		$filter_order		  = $mainframe->getUserStateFromRequest( $option.'.registrations.filter_order', 'filter_order', 'r.uregdate', 'cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $option.'.registrations.filter_order_Dir',	'filter_order_Dir',	'desc', 'word' );

		$filter_confirmed = $mainframe->getUserStateFromRequest( $option.'.registrations.filter_confirmed', 'filter_confirmed', 0, 'int' );
		$filter_waiting   = $mainframe->getUserStateFromRequest( $option.'.registrations.filter_waiting',   'filter_waiting'  , 0, 'int' );
		$filter_cancelled = $mainframe->getUserStateFromRequest( $option.'.registrations.filter_cancelled', 'filter_cancelled', 0, 'int' );

		$search = $mainframe->getUserStateFromRequest($option . '.registrations.search', 'search', '', 'string');
		$this->setState('search', $search);

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
		$this->setState('filter_order', $filter_order);
		$this->setState('filter_order_Dir',   $filter_order_Dir);
		$this->setState('filter_confirmed', $filter_confirmed);
		$this->setState('filter_waiting',   $filter_waiting);
		$this->setState('filter_cancelled', $filter_cancelled);

		//set unlimited if export or print action | task=export or task=print
		$this->setState('unlimited', JRequest::getString('task'));
	}

	/**
	 * Method to get categories item data
	 *
	 * @access public
	 * @return array
	 */
	function getData()
	{
		// Lets load the content if it doesn't already exist
		$query = $this->_buildQuery();

		if ($this->getState('unlimited') == '') {
			$this->_db->setQuery($query, $this->getState('limitstart'), $this->getState('limit'));
			$this->_data = $this->_db->loadObjectList();
		} else {
			$this->_db->setQuery($query);
			$this->_data = $this->_db->loadObjectList();
		}
		return $this->_data;
	}

	/**
	 * Method to get the total nr of the attendees
	 *
	 * @access public
	 * @return integer
	 */
	function getTotal()
	{
		// Lets load the content if it doesn't already exist
		$query = $this->_buildQuery();
		$this->_total = $this->_getListCount($query);

		return $this->_total;
	}

	/**
	 * Method to get a pagination object for the events
	 *
	 * @access public
	 * @return integer
	 */
	function getPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_pagination))
		{
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination( $this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
		}

		return $this->_pagination;
	}

	/**
	 * Method to build the query for the attendees
	 *
	 * @access private
	 * @return integer
	 * @since 0.9
	 */
	function _buildQuery()
	{
		// Get the ORDER BY clause for the query
		$orderby	= $this->_buildContentOrderBy();
		$where		= $this->_buildContentWhere();

		$query = ' SELECT r.*, r.id as attendee_id, u.username, u.name, e.id AS eventid, u.email '
		       . ', s.answer_id, r.waitinglist, r.confirmdate, r.confirmed, s.id AS submitter_id, s.price, s.currency, pg.name as pricegroup, fo.activatepayment, p.paid, p.status '
		       . ', e.course_code, e.title, x.dates, x.times, v.venue, x.maxattendees '
		       . ', auth.username AS creator '
		       . ' FROM #__redevent_register AS r '
		       . ' LEFT JOIN #__redevent_sessions_pricegroups AS spg ON spg.id = r.sessionpricegroup_id '
		       . ' LEFT JOIN #__redevent_pricegroups AS pg ON pg.id = spg.pricegroup_id '
		       . ' LEFT JOIN #__redevent_event_venue_xref AS x ON r.xref = x.id '
		       . ' LEFT JOIN #__redevent_venues AS v ON x.venueid = v.id '
		       . ' LEFT JOIN #__redevent_events AS e ON x.eventid = e.id '
		       . ' LEFT JOIN #__users AS u ON r.uid = u.id '
		       . ' LEFT JOIN #__users AS auth ON auth.id = e.created_by '
		       . ' LEFT JOIN #__rwf_submitters AS s ON r.sid = s.id '
		       . ' LEFT JOIN #__rwf_forms AS fo ON fo.id = s.form_id '
		       . ' LEFT JOIN (SELECT MAX(id) as id, submit_key FROM #__rwf_payment GROUP BY submit_key) AS latest_payment ON latest_payment.submit_key = s.submit_key'
		       . ' LEFT JOIN #__rwf_payment AS p ON p.id = latest_payment.id '
		       . $where
		       . ' GROUP BY r.id '
		       . $orderby;
		return $query;
	}

	/**
	 * Method to build the orderby clause of the query for the attendees
	 *
	 * @access private
	 * @return integer
	 * @since 0.9
	 */
	function _buildContentOrderBy()
	{
		$mainframe = &JFactory::getApplication();
		$option = JRequest::getCmd('option');

		$filter_order		  = $this->getState('filter_order');
		$filter_order_Dir	= $this->getState('filter_order_Dir');
		switch ($filter_order)
		{
			case 'e.title':
				return ' ORDER BY CONCAT(e.title, x.title) '.$filter_order_Dir.', r.uregdate DESC';
			default:
				return ' ORDER BY '.$filter_order.' '.$filter_order_Dir.', r.confirmdate DESC';
		}

	}

	/**
	 * Method to build the where clause of the query for the attendees
	 *
	 * @access private
	 * @return string
	 * @since 0.9
	 */
	function _buildContentWhere()
	{
		$mainframe = &JFactory::getApplication();
		$option = JRequest::getCmd('option');

		$where = array();

		switch ($this->getState('filter_confirmed', 0))
		{
			case 1:
				$where[] = ' r.confirmed = 1 ';
				break;
			case 2:
				$where[] = ' r.confirmed = 0 ';
				break;
		}
		switch ($this->getState('filter_waiting', 0))
		{
			case 1:
				$where[] = ' r.waitinglist = 0 ';
				break;
			case 2:
				$where[] = ' r.waitinglist = 1 ';
				break;
		}
		switch ($this->getState('filter_cancelled', 0))
		{
			case 0:
				$where[] = ' r.cancelled = 0 ';
				break;
			case 1:
				$where[] = ' r.cancelled = 1 ';
				break;
		}

		if ($search = $this->getState('search'))
		{
			$where_search = array(
				'u.username LIKE "%' . $search . '%"',
				'u.name LIKE "%' . $search . '%"',
				'u.email LIKE "%' . $search . '%"',
				'r.submit_key LIKE "%' . $search . '%"',
			);
			$where[] = '(' . implode(' OR ', $where_search) . ')';
		}

		$where = ( count( $where ) ? ' WHERE ' . implode( ' AND ', $where ) : '' );

		return $where;
	}


	/**
	 * Delete registered users
	 *
	 * @access public
	 * @return true on success
	 * @since 2.5
	 */
	public function remove($cid = array())
	{
		if (!count($cid)) {
			return true;
		}
		/**
		 * track xrefs attendees are being cancelled from
		 * @var array
		 */
		$xrefs = array();
		foreach ($cid as $register_id)
		{
			$db = &JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('e.redform_id,r.xref AS xref_id');
			$query->from('#__redevent_register AS r');
			$query->join('INNER', '#__redevent_event_venue_xref AS x ON x.id = r.xref');
			$query->join('INNER', '#__redevent_events AS e ON e.id = x.eventid');
			$query->where('r.id = '.(int) $register_id);
			$db->setQuery($query);
			$res = $db->loadObject();
			$xrefs[] = $res->xref_id;

			$query = ' DELETE s, f, r '
			. ' FROM #__redevent_register AS r '
			. ' LEFT JOIN #__rwf_submitters AS s ON r.sid = s.id '
			. ' LEFT JOIN #__rwf_forms_'.$res->redform_id .' AS f ON f.id = s.answer_id '
			. ' WHERE r.id = '.$register_id
			. '   AND r.cancelled = 1 ';
			;
			$this->_db->setQuery( $query );

			if (!$this->_db->query()) {
				RedeventError::raiseError( 1001, $this->_db->getErrorMsg() );
				return false;
			}
		}
		// now update waiting list for all updated sessions
		foreach ($xrefs as $xref)
		{
			$model_wait = JModel::getInstance('waitinglist', 'RedeventModel');
			$model_wait->setXrefId($xref);
			if (!$model_wait->UpdateWaitingList()) {
				$this->setError($model_wait->getError());
				return false;
			}
		}
		return true;
	}

	/**
	 * toggle registrations on and off the wainting list
	 * @param array $cid register_ids
	 * @param boolean $on set true to put on waiting list, false to take off
	 */
	public function togglewaiting($cid, $on)
	{
		if (!count($cid)) {
			return true;
		}
		// we need to group by xref
		$db = &JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('r.id AS rid, e.redform_id,r.xref AS xref_id');
		$query->from('#__redevent_register AS r');
		$query->join('INNER', '#__redevent_event_venue_xref AS x ON x.id = r.xref');
		$query->join('INNER', '#__redevent_events AS e ON e.id = x.eventid');
		$query->where('r.id IN ('.implode(',', $cid).')');
		$db->setQuery($query);
		$res = $db->loadObjectList();

		// let's group
		$xrefs = array();
		foreach ($res as $r) {
			@$xrefs[$r->xref_id][] = $r->rid;
		}
		// let's do the thing
		foreach ($xrefs as $xref => $rids)
		{
			$model = JModel::getInstance('waitinglist', 'RedeventModel');
			$model->setXrefId($xref);
			if ($on)
			{
				$res = $model->putOnWaitingList($rids);
			}
			else
			{
				$res = $model->putOffWaitingList($rids);
			}
			if (!$res) {
				$this->setError($model->getError());
				return false;
			}
		}
		return true;
	}
}
