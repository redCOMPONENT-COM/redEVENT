<?php
/**
 * @version 1.0 $Id: eventlist.php 1027 2009-09-27 21:50:56Z julien $
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
 * Base class foe events lists models
 *
 * @package Joomla
 * @subpackage redevent
 * @since		2.0
 */
class RedeventModelBaseEventList extends JModel
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

		$mainframe = & JFactory::getApplication();

		// Get the paramaters of the active menu item
		$params 	= & $mainframe->getParams('com_redevent');

		//get the number of events from database
		$limit       	= $mainframe->getUserStateFromRequest('com_redevent.eventlist.limit', 'limit', $params->def('display_num', 0), 'int');
		$limitstart		= JRequest::getVar('limitstart', 0, '', 'int');
			        
		// In case limit has been changed, adjust it
    $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
		
		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);

		// Get the filter request variables
		$this->setState('filter_order', JRequest::getCmd('filter_order', 'x.dates'));
		$this->setState('filter_order_dir', JRequest::getCmd('filter_order_Dir', 'ASC'));
	}

	/**
	 * Method to get the Events
	 *
	 * @access public
	 * @return array
	 */
	function &getData( )
	{
		$pop	= JRequest::getBool('pop');

		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$query = $this->_buildQuery();

			if ($pop) {
				$this->_data = $this->_getList( $query );
			} else {
				$this->_data = $this->_getList( $query, $this->getState('limitstart'), $this->getState('limit') );
			}
			$this->_data = $this->_categories($this->_data);
      $this->_data = $this->_getPlacesLeft($this->_data);
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
		$where		= $this->_buildWhere();
		$orderby	= $this->_buildOrderBy();

		//Get Events from Database
		$query = 'SELECT x.dates, x.enddates, x.times, x.endtimes, x.registrationend, x.id AS xref, x.maxattendees, x.maxwaitinglist, '
		    . ' a.id, a.title, a.created, a.datdescription, a.registra, '
				. ' l.venue, l.city, l.state, l.url,'
				. ' c.catname, c.id AS catid,'
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
	 * Build the order clause
	 *
	 * @access private
	 * @return string
	 */
	function _buildOrderBy()
	{
		$filter_order		= $this->getState('filter_order');
		$filter_order_dir	= $this->getState('filter_order_dir');

		$orderby 	= ' ORDER BY '.$filter_order.' '.$filter_order_dir.', x.dates, x.times';

		return $orderby;
	}

	/**
	 * Build the where clause
	 *
	 * @access private
	 * @return string
	 */
	function _buildWhere()
	{
		global $mainframe;

		$user		= & JFactory::getUser();
		$gid		= (int) $user->get('aid');

		// Get the paramaters of the active menu item
		$params 	= & $mainframe->getParams();

		$task 		= JRequest::getWord('task');
		
		// First thing we need to do is to select only needed events
		if ($task == 'archive') {
			$where = ' WHERE x.published = -1';
		} else {
			$where = ' WHERE x.published = 1';
		}
				
		// Second is to only select events assigned to category the user has access to
		$where .= ' AND c.access <= '.$gid;

		/*
		 * If we have a filter, and this is enabled... lets tack the AND clause
		 * for the filter onto the WHERE clause of the item query.
		 */
		if ($params->get('filter'))
		{
			$filter 		= $mainframe->getUserStateFromRequest('com_redevent.eventlist.filter', 'filter', '', 'string');
			$filter_type 	= $mainframe->getUserStateFromRequest('com_redevent.eventlist.filter_type', 'filter_type', '', 'string');

			if ($filter)
			{
				// clean filter variables
				$filter 		= JString::strtolower($filter);
				$filter			= $this->_db->Quote( '%'.$this->_db->getEscaped( $filter, true ).'%', false );
				$filter_type 	= JString::strtolower($filter_type);

				switch ($filter_type)
				{
					case 'title' :
						$where .= ' AND LOWER( a.title ) LIKE '.$filter;
						break;

					case 'venue' :
						$where .= ' AND LOWER( l.venue ) LIKE '.$filter;
						break;

					case 'city' :
						$where .= ' AND LOWER( l.city ) LIKE '.$filter;
						break;
						
					case 'type' :
						$where .= ' AND LOWER( c.catname ) LIKE '.$filter;
						break;
				}
			}
		}
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
      $query =  ' SELECT c.id, c.catname, '
              . ' CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as slug '
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
   * adds registered (int) and waiting (int) properties to rows.
   * 
   * @return array 
   */
  function _getPlacesLeft($rows) 
  {
    $db = JFactory::getDBO();
    foreach ((array) $rows as $k => $r) 
    {
      $q = "SELECT waitinglist, COUNT(id) AS total
        FROM #__rwf_submitters
        WHERE xref = ".$r->xref."
        AND confirmed = 1
        GROUP BY waitinglist";
      $db->setQuery($q);
      $res = $db->loadObjectList('waitinglist');
      $rows[$k]->registered = (isset($res[0]) ? $res[0]->total : 0) ;
      $rows[$k]->waiting = (isset($res[1]) ? $res[1]->total : 0) ;
    }
    return $rows;
  }
}
?>