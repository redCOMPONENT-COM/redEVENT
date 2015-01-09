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
 * EventList Component Archive Model
 *
 * @package Joomla
 * @subpackage redEVENT
 * @since		0.9
 */
class RedeventModelArchive extends RModel
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
	 * Events id
	 *
	 * @var int
	 */
	var $_id = null;

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

		$limit		= $mainframe->getUserStateFromRequest( $option.'.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart = $mainframe->getUserStateFromRequest( $option.'.limitstart', 'limitstart', 0, 'int' );

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);

		$array = JRequest::getVar('cid',  0, '', 'array');
		$this->setId((int)$array[0]);

	}

	/**
	 * Method to set the category identifier
	 *
	 * @access	public
	 * @param	int Category identifier
	 */
	function setId($id)
	{
		// Set id and wipe data
		$this->_id	    = $id;
		$this->_data 	= null;
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
		if (empty($this->_data))
		{
			$query = $this->_buildQuery();
			$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
			$this->_data = $this->_additionals($this->_data);
		}

		return $this->_data;
	}

	/**
	 * Method to get the total number
	 *
	 * @access public
	 * @return integer
	 */
	function getTotal()
	{
		// Lets load the content if it doesn't already exist
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
	 * Method to get the query for the events
	 *
	 * @access public
	 * @return string
	 */
	function _buildQuery()
	{
		// Get the WHERE and ORDER BY clauses for the query
		$where		= $this->_buildContentWhere();
		$orderby	= $this->_buildContentOrderBy();
		$query = 'SELECT a.*, cat.checked_out AS cchecked_out, cat.name AS catname, u.email, u.name AS author, x.id AS xref'
					. ' FROM #__redevent_events AS a'
					. ' LEFT JOIN #__redevent_event_venue_xref AS x ON x.eventid = a.id'
					. ' LEFT JOIN #__redevent_venues AS loc ON x.venueid = loc.id'
          . ' LEFT JOIN #__redevent_event_category_xref AS xcat ON xcat.event_id = a.id'
					. ' LEFT JOIN #__redevent_categories AS cat ON cat.id = xcat.category_id'
					. ' LEFT JOIN #__users AS u ON u.id = a.created_by'
					. $where
					. ' GROUP BY a.id'
					. $orderby
					;
		return $query;
	}

	/**
	 * Method to get the orderby clause for the events
	 *
	 * @access public
	 * @return string
	 */
	function _buildContentOrderBy()
	{
		$mainframe = &JFactory::getApplication();
		$option = JRequest::getCmd('option');

		$filter_order		= $mainframe->getUserStateFromRequest( $option.'.archive.filter_order', 		'filter_order', 	'x.dates', 'cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $option.'.archive.filter_order_Dir',	'filter_order_Dir',	'', 'word' );

		$orderby 	= ' ORDER BY '.$filter_order.' '.$filter_order_Dir.', x.dates';

		return $orderby;
	}

	/**
	 * Method to get the where clause for the events
	 *
	 * @access public
	 * @return string
	 */
	function _buildContentWhere()
	{
		$mainframe = &JFactory::getApplication();
		$option = JRequest::getCmd('option');

		$filter 			= $mainframe->getUserStateFromRequest( $option.'.archive.filter', 'filter', '', 'int' );
		$search 			= $mainframe->getUserStateFromRequest( $option.'.archive.search', 'search', '', 'string' );
		$search 			= $this->_db->getEscaped( trim(JString::strtolower( $search ) ) );

		$where = array('a.published = -1');

		if ($search && $filter == 1) {
			$where[] = ' LOWER(a.title) LIKE \'%'.$this->_db->getEscaped($search).'%\' ';
		}

		if ($search && $filter == 2) {
			$where[] = ' LOWER(loc.venue) LIKE \'%'.$this->_db->getEscaped($search).'%\' ';
		}

		if ($search && $filter == 3) {
			$where[] = ' LOWER(loc.city) LIKE \'%'.$this->_db->getEscaped($search).'%\' ';
		}

		if ($search && $filter == 4) {
			$where[] = ' LOWER(cat.name) LIKE \'%'.$this->_db->getEscaped($search).'%\' ';
		}


		$where 		= ( count( $where ) ? ' WHERE ' . implode( ' AND ', $where ) : '' );

		return $where;
	}

	/**
	 * Get the editor name
	 *
	 * @access private
	 * @param array $rows
	 * @return array
	 */
	function _additionals($rows)
	{
		for ($i=0, $n=count($rows); $i < $n; $i++) {

			// Get editor name
			$query = 'SELECT name'
					. ' FROM #__users'
					. ' WHERE id = '.$rows[$i]->modified_by
					;
			$this->_db->SetQuery( $query );

			$rows[$i]->editor = $this->_db->loadResult();
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
		$user 	= & JFactory::getUser();
		$userid	= (int) $user->get('id');

		if (count( $cid ))
		{
			$cids = implode( ',', $cid );

			$query = 'UPDATE #__redevent_events'
					. ' SET published = '.(int) $publish
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
	 * Method to remove a event
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	0.9
	 */
	function delete($cid = array())
	{
		if (count( $cid ))
		{
			$cids = implode( ',', $cid );
			$query = 'DELETE FROM #__redevent_events'
					. ' WHERE id IN ( '.$cids.' )';

			$this->_db->setQuery( $query );

			if(!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}

		return true;
	}

	/**
	 * Retrieve a list of events, venues and times
	 */
	public function getArchiveEventVenues()
	{
	  $events_id = array();
	  foreach ((array) $this->getData() as $e) {
	    $events_id[] = $e->id;
	  }
	  if (empty($events_id)) {
	    return false;
	  }

		$q = ' SELECT count(r.id) AS regcount, x.*, v.venue, v.city '
		   . ' FROM #__redevent_event_venue_xref AS x '
       . ' LEFT JOIN #__redevent_venues AS v ON x.venueid = v.id '
       . ' LEFT JOIN #__redevent_register AS r ON r.xref = x.id'
       . ' WHERE x.published = -1 '
       . '   AND x.eventid IN ('. implode(', ', $events_id) .')'
       . ' GROUP BY x.id '
       . ' ORDER BY x.dates, v.venue '
       ;
		$this->_db->setQuery($q);
		$datetimes = $this->_db->loadObjectList();
		$ardatetimes = array();
		foreach ($datetimes as $key => $datetime) {
			$ardatetimes[$datetime->eventid][] = $datetime;
		}
		return $ardatetimes;
	}
}
