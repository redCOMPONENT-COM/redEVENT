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
 * EventList Component Venueevents Model
 *
 * @package Joomla
 * @subpackage EventList
 * @since		0.9
 */
class RedeventModelVenueevents extends JModel
{
	/**
	 * Events data array
	 *
	 * @var array
	 */
	var $_data = null;

	/**
	 * venue data array
	 *
	 * @var array
	 */
	var $_venue = null;

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

		global $mainframe;

		$id = JRequest::getInt('id');
		$this->setId((int)$id);
		
		$xref = JRequest::getInt('xref');
		$this->setXref((int)$xref);

		// Get the paramaters of the active menu item
		$params 	= & $mainframe->getParams('com_redevent');

		//get the number of events from database
		$limit       	= $mainframe->getUserStateFromRequest('com_redevent.venueevents.limit', 'limit', $params->def('display_num', 0), 'int');
		$limitstart		= JRequest::getInt('limitstart');

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);

		// Get the filter request variables
		$this->setState('filter_order', JRequest::getCmd('filter_order', 'x.dates'));
		$this->setState('filter_order_dir', JRequest::getCmd('filter_order_Dir', 'ASC'));
	}

	/**
	 * Method to set the venue id
	 *
	 * @access	public
	 * @param	int	venue ID number
	 */
	function setId($id)
	{
		// Set new venue ID and wipe data
		$this->_id			= $id;
		$this->_data		= null;
	}
	
	function setXref($xref)
	{
		// Set new venue ID and wipe data
		$this->_xref		= $xref;
	}

	/**
	 * Method to get the events
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
        $this->_data = $this->_getEventsCategories($this->_data);
			} else {
				$this->_data = $this->_getList( $query, $this->getState('limitstart'), $this->getState('limit') );
        $this->_data = $this->_getEventsCategories($this->_data);
			}
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

		//Get Events from Database
		$query = 'SELECT a.id, a.title, a.datdescription, a.created, '
		    . ' x.dates, x.enddates, x.times, x.endtimes, x.id AS xref, '
		    . ' l.venue, l.city, l.state, l.url, l.latitude, l.longitude, '
		    . ' c.catname, c.id AS catid,'
        . ' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug, '
        . ' CASE WHEN CHAR_LENGTH(l.alias) THEN CONCAT_WS(\':\', l.id, l.alias) ELSE l.id END as venueslug, '
        . ' CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as categoryslug '
				. ' FROM #__redevent_events AS a'
				. ' LEFT JOIN #__redevent_event_venue_xref AS x ON a.id = x.eventid'
				. ' LEFT JOIN #__redevent_venues AS l ON l.id = x.venueid'
        . ' LEFT JOIN #__redevent_event_category_xref AS xcat ON xcat.event_id = a.id'
        . ' LEFT JOIN #__redevent_categories AS c ON c.id = xcat.category_id'
				. $where
				. ' GROUP BY x.id '
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
		$filter_order		= $this->getState('filter_order');
		$filter_order_dir	= $this->getState('filter_order_dir');

		$orderby 	= ' ORDER BY '.$filter_order.' '.$filter_order_dir.', x.dates, x.times';

		return $orderby;
	}

	/**
	 * Method to build the WHERE clause
	 *
	 * @access private
	 * @return array
	 */
	function _buildContentWhere( )
	{
		global $mainframe;

		$user		=& JFactory::getUser();
		$gid		= (int) $user->get('aid');

		// Get the paramaters of the active menu item
		$params 	= & $mainframe->getParams('com_redevent');
		
		$task 		= JRequest::getWord('task');

		// First thing we need to do is to select only the requested events
		if ($task == 'archive') {
			$where = ' WHERE x.published = -1';
		} else {
			$where = ' WHERE x.published = 1';
		}
		
		/* Check if a venue ID is set */
		if ($this->_id > 0) $where .= ' AND x.venueid = '.$this->_id;
		
		// Second is to only select events assigned to category the user has access to
		$where .= ' AND c.access <= '.$gid;

		/*
		 * If we have a filter, and this is enabled... lets tack the AND clause
		 * for the filter onto the WHERE clause of the content item query.
		 */
		if ($params->get('filter'))
		{
      $filter     = $mainframe->getUserStateFromRequest('com_redevent.venueevents.filter', 'filter', '', 'string');
      $filter_type  = $mainframe->getUserStateFromRequest('com_redevent.venueevents.filter_type', 'filter_type', '', 'string');

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
					
					case 'type' :
						$where .= ' AND LOWER( c.catname ) LIKE '.$filter;
						break;
				}

			}
		}
		return $where;
	}

	/**
	 * Method to get the Venue
	 *
	 * @access public
	 * @return array
	 */
	function getVenue( )
	{
		//Location holen
		$query = 'SELECT *, v.id AS id, x.id AS xref, '
        . ' CASE WHEN CHAR_LENGTH(v.alias) THEN CONCAT_WS(\':\', v.id, v.alias) ELSE v.id END as slug '
				.' FROM #__redevent_venues v'
				.' LEFT JOIN #__redevent_event_venue_xref AS x'
				.' ON v.id = x.venueid';
		if ($this->_xref) {
				$query .= ' WHERE x.id ='. $this->_xref;
		}
		else $query .= ' WHERE v.id = '.$this->_id;

		$this->_db->setQuery( $query );
		$_venue = $this->_db->loadObject();

		return $_venue;
	}
	
  /**
   * adds categories property to event rows
   *
   * @param array $rows of events
   * @return array
   */
  function _getEventsCategories($rows)
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
}
?>