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
 * EventList Component attendees Model
 *
 * @package Joomla
 * @subpackage EventList
 * @since		0.9
 */
class RedEventModelAttendees extends JModel
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
	 * Events id
	 *
	 * @var int
	 */
	var $_eventid = null;

	var $_xref = null;
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

		//set unlimited if export or print action | task=export or task=print
		$this->setState('unlimited', JRequest::getString('task'));

		$eventid = JRequest::getInt('eventid');
		$this->setId($eventid);
		
		$xref = JRequest::getInt('xref');
		$this->setXref($xref);


	}

	/**
	 * Method to set the category identifier
	 *
	 * @access	public
	 * @param	int Category identifier
	 */
	function setId($eventid)
	{
		// Set id and wipe data
		$this->_eventid	    = $eventid;
		$this->_data 	= null;
	}
	
	function setXref($xref)
	{
		// Set id and wipe data
		$this->_xref	    = $xref;
		$this->_data 	= null;
	}

	/**
	 * Method to get categories item data
	 *
	 * @access public
	 * @return array
	 */
	function getData() {
		$db = JFactory::getDBO();
		// Lets load the content if it doesn't already exist
		$query = $this->_buildQuery();

		if ($this->getState('unlimited') == '') {
			$db->setQuery($query, $this->getState('limitstart'), $this->getState('limit'));
			$this->_data = $db->loadObjectList('answer_id');
		} else {
			$db->setQuery($query);
			$this->_data = $db->loadObjectList('answer_id');
		}
		return $this->_data;
	}
	
	/**
	 * Method to get the total nr of the attendees
	 *
	 * @access public
	 * @return integer
	 */
	function getTotal() {
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
		// get redform form and fields to show
		$q = ' SELECT e.redform_id, e.showfields '
		   . ' FROM #__redevent_events AS e '
		   . ' WHERE e.id = '. $this->_db->Quote($this->_eventid)
		   ;
		$this->_db->setQuery($q, 0, 1);
		$res = $this->_db->loadObject();
		
		if ($res) {
			$fields = explode(',', $res->showfields);
			$rfields = array();
			foreach ($fields as $f) {
				$rfields[] = 'f.field_'.trim($f);
			}
			$rfields = ', '.implode(',', $rfields);
			$join_rwftable  = ' LEFT JOIN #__rwf_forms_'.$res->redform_id.' AS f ON s.answer_id = f.id ';
		}
		
		// Get the ORDER BY clause for the query
		$orderby	= $this->_buildContentOrderBy();
		$where		= $this->_buildContentWhere();

		$query = 'SELECT r.*, u.username, u.name, a.id AS eventid, u.gid, u.email, s.answer_id, s.waitinglist, s.confirmdate, s.confirmed, s.id AS submitter_id, p.status '
		       . $rfields
		       . ' FROM #__redevent_register AS r '
		       . ' LEFT JOIN #__redevent_event_venue_xref AS x ON r.xref = x.id '
		       . ' LEFT JOIN #__redevent_events AS a ON x.eventid = a.id '
		       . ' LEFT JOIN #__users AS u ON r.uid = u.id '
		       . ' INNER JOIN #__rwf_submitters AS s ON r.submit_key = s.submit_key '
		       . ' LEFT JOIN #__rwf_payment AS p ON p.submit_key = s.submit_key '
		       . $join_rwftable
		       . $where
		       . $orderby;
		return $query;
	}
	
	function getRedFormFrontFields()
	{
		// get redform form and fields to show
		$q = ' SELECT e.showfields '
		   . ' FROM #__redevent_events AS e '
		   . ' WHERE e.id = '. $this->_db->Quote($this->_eventid)
		   ;
		$this->_db->setQuery($q, 0, 1);
		$res = $this->_db->loadResult();
		
		if (empty($res)) {
			return null;
		}
				
		// get redform form and fields to show
		$q = ' SELECT f.id, f.field '
		   . ' FROM #__rwf_fields AS f '
		   . ' WHERE f.id IN ('. $this->_db->Quote($res).')'
		   ;
		$this->_db->setQuery($q);
		$res = $this->_db->loadObjectList();
		return $res;
	}
	
	/**
	 * Method to build the orderby clause of the query for the attendees
	 *
	 * @access private
	 * @return integer
	 * @since 0.9
	 */
	function _buildContentOrderBy() {
		return ' ORDER BY s.confirmed DESC, s.confirmdate, r.uregdate, s.waitinglist';
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
		global $mainframe, $option;

		$filter 			= $mainframe->getUserStateFromRequest( $option.'.attendees.filter', 'filter', '', 'int' );
		// $search 			= $mainframe->getUserStateFromRequest( $option.'.attendees.search', 'search', '', 'string' );
		// $search 			= $this->_db->getEscaped( trim(JString::strtolower( $search ) ) );

		$where = array();

		// $where[] = 'r.event = '.$this->_eventid;
         
		if (0) {
			/*
			* Search name
			*/
			if ($search && $filter == 1) {
				$where[] = ' LOWER(u.name) LIKE \'%'.$search.'%\' ';
			}
	
			/*
			* Search username
			*/
			if ($search && $filter == 2) {
				$where[] = ' LOWER(u.username) LIKE \'%'.$search.'%\' ';
			}
		}
		if (JRequest::getInt('filter', false) && JRequest::getInt('filter', 0) > 0) $where[] = ' x.id = '.JRequest::getInt('filter');
		else if (!is_null($this->_eventid) && $this->_eventid > 0) $where[] = ' x.eventid = '.$this->_eventid;
		else if (!is_null($this->_xref) && $this->_xref > 0) $where[] = 'x.id = '.$this->_xref;

		$where 		= ( count( $where ) ? ' WHERE ' . implode( ' AND ', $where ) : '' );

		return $where;
	}

	/**
	 * Get event data
	 *
	 * @access public
	 * @return object
	 * @since 0.9
	 */
	function getEvent()
	{
		$query = 'SELECT eventid, title, x.dates, redform_id , x.id AS xref
				FROM #__redevent_events e
				LEFT JOIN #__redevent_event_venue_xref x
				ON x.eventid = e.id';
		if (JRequest::getInt('filter', false) && JRequest::getInt('filter', 0) > 0) $query .= ' WHERE x.id = '.JRequest::getInt('filter');
		else if (!is_null($this->_eventid) && $this->_eventid > 0) $query .= ' WHERE e.id = '.$this->_eventid;
		else if (!is_null($this->_xref) && $this->_xref > 0) $query .= ' WHERE x.id = '.$this->_xref;
		

		$this->_db->setQuery( $query );
		$_event = $this->_db->loadObject();

		return $_event;
	}

	/**
	 * Delete registered users
	 *
	 * @access public
	 * @return true on success
	 * @since 0.9
	 */
	function remove($cid = array())
	{
		if (count( $cid ))
		{
			$ids = implode(',', $cid);
			$xref = (int)$xref;
			
			if (substr($ids, -1) == ',') $ids = substr($ids, 0, -1);
			
			$query = 'DELETE FROM #__redevent_register WHERE id IN ('. $ids .') ';
			$this->_db->setQuery( $query );
			
			if (!$this->_db->query()) {
				RedeventError::raiseError( 1001, $this->_db->getErrorMsg() );
				return false;
			}
		}
		return true;
	}
	
	/**
	 * Get form ID belonging to a submitter
	 *
	 * @access public
	 * @return true on success
	 * @since 0.9
	 */
	function getSubmitterFormId($cid = array(), $event)
	{
		if (count( $cid ))
		{
			$ids = implode(',', $cid);
			$event = (int)$event;
			
			$query = 'SELECT s.form_id 
					FROM #__redevent_register r, #__rwf_submitters s
					WHERE r.submitter_id = s.answer_id
					AND r.id IN ('. $ids .') AND event = '.$event." LIMIT 1";

			$this->_db->setQuery( $query );
			JRequest::setVar('form_id', $this->_db->loadResult());
			if ($this->_db->getErrorNum() > 0) {
				RedeventError::raiseError( 1001, $this->_db->getErrorMsg() );
			}
		}
	}
	
	function getDateTimeLocation() {
		$db = JFactory::getDBO();
		$q = 'SELECT x.*, v.venue
			FROM #__redevent_event_venue_xref x
			LEFT JOIN #__redevent_venues v
			ON v.id = x.venueid ';
		if (!is_null($this->_eventid) && $this->_eventid > 0) $q .= ' WHERE x.eventid = '.$this->_eventid;
		else if (!is_null($this->_xref) && $this->_xref > 0) $q .= ' WHERE x.id = '.$this->_xref;
		$q .= '	ORDER BY v.venue, x.dates';
		$db->setQuery($q);
		return $db->loadObjectList();
	}
	
	/**
	 * Add an attendee
	 */
	function getAddAttendee() {
		$row = $this->getTable('redevent_register', '');
		$store = array();
		$store['submit_key'] = JRequest::getVar('submit_key');
		$store['xref'] = JRequest::getInt('xref');
		$store['uregdate'] = gmdate('Y-m-d H:i:s');
		$store['uid'] = JRequest::getInt('user_id', 0);
		$row->bind($store);
		$row->check();
		$row->store();
	}
	
	/**
	 * confirm attendees
	 * 
	 * @param $cid array of attendees id to confirm
	 * @return boolean true on success
	 */
	function confirmattendees($cid = array())
  {
    if (count( $cid ))
    {
      $ids = implode(',', $cid);
            
      $query = 'UPDATE #__rwf_submitters SET confirmed = 1 WHERE answer_id IN ('. $ids .') AND xref = '. $this->_db->Quote($this->_xref);
      $this->_db->setQuery( $query );
      
      if (!$this->_db->query()) {
        RedeventError::raiseError( 1001, $this->_db->getErrorMsg() );
        return false;
      }
    }
    return true;
  }
  

  /**
   * unconfirm attendees
   * 
   * @param $cid array of attendees id to unconfirm
   * @return boolean true on success
   */
  function unconfirmattendees($cid = array())
  {
    if (count( $cid ))
    {
      $ids = implode(',', $cid);
            
      $query = 'UPDATE #__rwf_submitters SET confirmed = 0 WHERE answer_id IN ('. $ids .') AND xref = '. $this->_db->Quote($this->_xref);
      $this->_db->setQuery( $query );
      
      if (!$this->_db->query()) {
        RedeventError::raiseError( 1001, $this->_db->getErrorMsg() );
        return false;
      }
    }
    return true;
  }
}
?>