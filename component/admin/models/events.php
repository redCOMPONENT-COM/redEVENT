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
 * EventList Component Events Model
 *
 * @package Joomla
 * @subpackage EventList
 * @since		0.9
 */
class RedEventModelEvents extends JModel
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

		global $mainframe, $option;

		$limit		= $mainframe->getUserStateFromRequest( $option.'limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart = $mainframe->getUserStateFromRequest( $option.'limitstart', 'limitstart', 0, 'int' );

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);

	}

	/**
	 * Method to get event item data
	 *
	 * @access public
	 * @return array
	 */
	function getData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$query = $this->_buildQuery();
			$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
      $this->_data = $this->_categories($this->_data);
		}

		return $this->_data;
	}

	/**
	 * Total nr of events
	 *
	 * @access public
	 * @return integer
	 */
	function getTotal()
	{
		// Lets load the total nr if it doesn't already exist
		if (empty($this->_total))
		{
			$query = $this->_buildQuery();
			$this->_total = $this->_getListCount($query);
		}

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
	 * Build the query
	 *
	 * @access private
	 * @return string
	 */
	function _buildQuery()
	{
		// Get the WHERE and ORDER BY clauses for the query
		$where		= $this->_buildContentWhere();
		$orderby	= $this->_buildContentOrderBy();

		$query = ' SELECT a.*, cat.checked_out AS cchecked_out, cat.catname, u.email, u.name AS author, u2.name as editor, x.id AS xref'
					. ' FROM #__redevent_events AS a'
          . ' LEFT JOIN #__redevent_event_category_xref AS xcat ON xcat.event_id = a.id'
					. ' LEFT JOIN #__redevent_categories AS cat ON cat.id = xcat.category_id'
					. ' LEFT JOIN #__redevent_event_venue_xref AS x ON x.eventid = a.id'
					. ' LEFT JOIN #__redevent_venues AS loc ON loc.id = x.venueid'
					. ' LEFT JOIN #__users AS u ON u.id = a.created_by'
          . ' LEFT JOIN #__users AS u2 ON u2.id = a.modified_by'
					. $where
					. ' GROUP BY a.id'
					. $orderby
					;
		return $query;
	}

	/**
	 * Build the order clause
	 *
	 * @access private
	 * @return string
	 */
	function _buildContentOrderBy()
	{
		global $mainframe, $option;

		$filter_order		= $mainframe->getUserStateFromRequest( $option.'.events.filter_order', 'filter_order', 'a.title', 'cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $option.'.events.filter_order_Dir', 'filter_order_Dir', '', 'word' );

		$orderby 	= ' ORDER BY '.$filter_order.' '.$filter_order_Dir.', a.title';

		return $orderby;
	}

	/**
	 * Build the where clause
	 *
	 * @access private
	 * @return string
	 */
	function _buildContentWhere()
	{
		global $mainframe, $option;

		$filter_state = $mainframe->getUserStateFromRequest( $option.'.filter_state', 'filter_state', '', 'word' );
		$filter       = $mainframe->getUserStateFromRequest( $option.'.filter', 'filter', '', 'int' );
		$search       = $mainframe->getUserStateFromRequest( $option.'.search', 'search', '', 'string' );
		$search       = $this->_db->getEscaped( trim(JString::strtolower( $search ) ) );

		$where = array();

		if ($filter_state) 
		{
			if ($filter_state == 'P') {
				$where[] = 'a.published = 1';
			} else if ($filter_state == 'U') {
				$where[] = 'a.published = 0';
			} else {
				$where[] = 'a.published >= 0';
			}
		} else {
			$where[] = 'a.published >= 0';
		}

		if ($search && $filter == 1) {
			$where[] = ' LOWER(a.title) LIKE \'%'.$search.'%\' ';
		}

		if ($search && $filter == 2) {
			$where[] = ' LOWER(loc.venue) LIKE \'%'.$search.'%\' ';
		}

		if ($search && $filter == 3) {
			$where[] = ' LOWER(loc.city) LIKE \'%'.$search.'%\' ';
		}

		if ($search && $filter == 4) {
			$where[] = ' LOWER(cat.catname) LIKE \'%'.$search.'%\' ';
		}

		$where 		= ( count( $where ) ? ' WHERE ' . implode( ' AND ', $where ) : '' );

		return $where;
	}
	
	/**
	 * adds categories property to event rows
	 *
	 * @param array $rows of events
	 * @return array
	 */
	function _categories($rows)
	{
		for ($i=0, $n=count($rows); $i < $n; $i++) {
			$query =  ' SELECT c.id, c.catname, c.checked_out '
							. ' FROM #__redevent_categories as c '
							. ' INNER JOIN #__redevent_event_category_xref as x ON x.category_id = c.id '
							. ' WHERE c.published = 1 '
							. '   AND x.event_id = ' . $this->_db->Quote($rows[$i]->id)
							. ' ORDER BY c.ordering'
							;
			$this->_db->setQuery( $query );

			$rows[$i]->categories = $this->_db->loadObjectList();
		}

    return $rows;		
	}

	/**
	 * Method to (un)publish a event
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function publish($cid = array(), $publish = 1)
	{
		$user 	=& JFactory::getUser();
		$userid = (int) $user->get('id');

		if (count( $cid ))
		{
			$cids = implode( ',', $cid );

			$query = 'UPDATE #__redevent_events'
				. ' SET published = '. (int) $publish
				. ' WHERE id IN ('. $cids .')'
				. ' AND ( checked_out = 0 OR ( checked_out = ' .$userid. ' ) )'
			;

			$this->_db->setQuery( $query );

			if (!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
			else {
				$query = 'UPDATE #__redevent_event_venue_xref'
					. ' SET published = '. (int) $publish
					. ' WHERE eventid IN ('. $cids .')'
				;
				$this->_db->setQuery( $query );

				if (!$this->_db->query()) {
					$this->setError($this->_db->getErrorMsg());
					return false;
				}
			}
		}
	}
	
	/**
	 * archive past xrefs
	 * 
	 * @param $event_ids
	 * @return unknown_type
	 */
	function archive($event_ids = array())
	{
		if (!count($event_ids)) {
			return true;
		}

		$db = & $this->_db;
		
    $nulldate = '0000-00-00';
      
		// update xref to archive
		$query = ' UPDATE #__redevent_event_venue_xref AS x '
		. ' SET x.published = -1 '
		. ' WHERE DATE_SUB(NOW(), INTERVAL 1 DAY) > (IF (x.enddates <> '.$nulldate.', x.enddates, x.dates))'
		. '   AND x.eventid IN (' . implode(', ', $event_ids) . ')'
		;
		$db->SetQuery( $query );
		$db->Query();

		// update events to archive (if no more published xref)
		$query = ' UPDATE #__redevent_events AS e '
		. ' LEFT JOIN #__redevent_event_venue_xref AS x ON x.eventid = e.id AND x.published <> -1 '
		. ' SET e.published = -1 '
		. ' WHERE x.id IS NULL '
		. '   AND e.id IN (' . implode(', ', $event_ids) . ')'
		;
		$db->SetQuery( $query );
		$db->Query();
		return true;
	}

	/**
	 * Method to remove a event
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	0.9
	 */
	function delete($cid = array())
	{
		$result = false;

		if (count( $cid ))
		{
			$cids = implode( ',', $cid );
			$query = 'DELETE FROM #__redevent_events'
					. ' WHERE id IN ('. $cids .')'
					;

			$this->_db->setQuery( $query );

			if(!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
			else {
				// delete corresponding event_venue
				$query = 'DELETE FROM #__redevent_event_venue_xref'
					. ' WHERE eventid IN ('. $cids .')';
					$this->_db->setQuery( $query );
				if(!$this->_db->query()) {
					$this->setError($this->_db->getErrorMsg());
					return false;
				}
				// delete corresponding event_category
				$query = 'DELETE FROM #__redevent_event_category_xref'
					. ' WHERE event_id IN ('. $cids .')';
					$this->_db->setQuery( $query );
				if(!$this->_db->query()) {
					$this->setError($this->_db->getErrorMsg());
					return false;
				}
			}
		}

		return true;
	}
	
	/**
	 * Retrieve a list of events, venues and times
	 */
	public function getEventVenues() 
	{
	  $events_id = array();
	  foreach ((array) $this->getData() as $e) {
	    $events_id[] = $e->id;
	  }
	  if (empty($events_id)) {
	    return false;
	  }
	  
		$db = JFactory::getDBO();
		$q = ' SELECT count(r.id) AS regcount, x.*, v.venue, v.city '
		   . ' FROM #__redevent_event_venue_xref AS x '
       . ' LEFT JOIN #__redevent_venues AS v ON x.venueid = v.id '
       . ' LEFT JOIN #__redevent_register AS r ON r.xref = x.id'
       . ' WHERE x.published > -1 '
       . '   AND x.eventid IN ('. implode(', ', $events_id) .')'
       . ' GROUP BY x.id '
       . ' ORDER BY x.dates, v.venue '
       ;
		$db->setQuery($q);
		$datetimes = $db->loadObjectList();
		
		$ardatetimes = array();
		foreach ((array) $datetimes as $key => $datetime) {
			$ardatetimes[$datetime->eventid][] = $datetime;
		}
		return $ardatetimes;
	}
}//Class end
?>