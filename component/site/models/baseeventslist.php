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
	 * custom fields data array
	 *
	 * @var array
	 */
	var $_customfields = null;
	
	/**
	 * xref custom fields data array
	 *
	 * @var array
	 */
	var $_xrefcustomfields = null;
	
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
		$limit       	= $mainframe->getUserStateFromRequest('com_redevent.limit', 'limit', $params->def('display_num', 0), 'int');
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
				$pagination = $this->getPagination();
				$this->_data = $this->_getList( $query, $pagination->limitstart, $pagination->limit );
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
			$total = $this->getTotal();
			$limit = $this->getState('limit');
			$limitstart = $this->getState('limitstart');
			if ($limitstart > $total) {
				$limitstart = floor($total / $limit) * $limit;
				$this->setState('limitstart', $limitstart);
			}
			$this->_pagination = new JPagination( $total, $limitstart, $limit );
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
		$customs = $this->getCustomFields();
		$xcustoms = $this->getXrefCustomFields();

		//Get Events from Database
		$query = 'SELECT x.dates, x.enddates, x.times, x.endtimes, x.registrationend, x.id AS xref, x.maxattendees, x.maxwaitinglist, x.course_credit, x.course_price,'
		    . ' a.id, a.title, a.created, a.datdescription, a.registra, a.datimage, '
				. ' l.venue, l.city, l.state, l.url,'
				. ' c.catname, c.id AS catid,'
        . ' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug, '
        . ' CASE WHEN CHAR_LENGTH(l.alias) THEN CONCAT_WS(\':\', l.id, l.alias) ELSE l.id END as venueslug, '
        . ' CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as categoryslug '
        ;
		// add the custom fields
		foreach ((array) $customs as $c)
		{
			$query .= ', c'. $c->id .'.value AS custom'. $c->id;
		}
		// add the custom fields
		foreach ((array) $xcustoms as $c)
		{
			$query .= ', c'. $c->id .'.value AS custom'. $c->id;
		}
		
    $query .= ' FROM #__redevent_event_venue_xref AS x'
		        . ' INNER JOIN #__redevent_events AS a ON a.id = x.eventid'
		        . ' INNER JOIN #__redevent_venues AS l ON l.id = x.venueid'
		        . ' LEFT JOIN #__redevent_venue_category_xref AS xvcat ON l.id = xvcat.venue_id'
		        . ' LEFT JOIN #__redevent_venues_categories AS vc ON xvcat.category_id = vc.id'
            . ' INNER JOIN #__redevent_event_category_xref AS xcat ON xcat.event_id = a.id'
	          . ' INNER JOIN #__redevent_categories AS c ON c.id = xcat.category_id'
		        ;
		
		// add the custom fields tables
		foreach ((array) $customs as $c)
		{
			$query .= ' LEFT JOIN #__redevent_fields_values AS c'. $c->id .' ON c'. $c->id .'.object_id = a.id AND c'. $c->id .'.field_id = '. $c->id;
		}
		// add the custom fields tables
		foreach ((array) $xcustoms as $c)
		{
			$query .= ' LEFT JOIN #__redevent_fields_values AS c'. $c->id .' ON c'. $c->id .'.object_id = x.id AND c'. $c->id .'.field_id = '. $c->id;
		}
		
		$query .= $where
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
			
		if (ereg("field([0-9]+)", $filter_order, $regs)) {
			$filter_order = 'c'. $regs[1] .'.value';
		}
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
			$filter 		= $mainframe->getUserStateFromRequest('com_redevent.simplelist.filter', 'filter', '', 'string');
			$filter_type 	= $mainframe->getUserStateFromRequest('com_redevent.simplelist.filter_type', 'filter_type', '', 'string');

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
		foreach ((array) $rows as $k => $r)
  	{
			$q = ' SELECT r.waitinglist, COUNT(r.id) AS total '
			   . ' FROM #__redevent_register AS r '
			   . ' WHERE r.xref = '. $this->_db->Quote($r->xref)
			   . ' AND r.confirmed = 1 '
			   . ' GROUP BY r.waitinglist '
			   ;
			$this->_db->setQuery($q);
			$res = $this->_db->loadObjectList('waitinglist');
			$rows[$k]->registered = (isset($res[0]) ? $res[0]->total : 0) ;
			$rows[$k]->waiting = (isset($res[1]) ? $res[1]->total : 0) ;
		}
		return $rows;
	}
  
  /**
   * adds custom fields to rows.
   * 
   * @return array 
   */
  function _getEventsCustoms($rows) 
  {
    foreach ((array) $rows as $k => $r) 
    {
    	$query = ' SELECT f.name, custom.value '
    	       . ' FROM #__redevent_fields_values AS custom'
						 . ' INNER JOIN #__redevent_fields AS f ON custom.field_id = f.id'
						 . ' WHERE custom.object_id = '. $this->_db->Quote($r->id)
						 . '   AND f.in_lists = 1'
						 . '   AND f.published = 1'
						 . '   AND f.object_key = '. $this->_db->Quote('redevent.event')
  	         . ' ORDER BY f.ordering ASC '
    	       ;
    	$this->_db->setQuery($query);
    	$res = $this->_db->loadObjectList();
    	
   		$rows[$k]->customs = $res;
    }
    return $rows;
  }
  
  /**
   * returns all custom fields for events
   * 
   * @return array
   */
  function getCustomFields()
  {
  	if (empty($this->_customfields))
  	{
	  	$query = ' SELECT f.id, f.name, f.in_lists, f.searchable, f.ordering '
	  	       . ' FROM #__redevent_fields AS f'
	  	       . ' WHERE f.published = 1'
	  	       . '   AND f.object_key = '. $this->_db->Quote('redevent.event')
	  	       . ' ORDER BY f.ordering ASC '
	  	       ;
	  	$this->_db->setQuery($query);
	  	$this->_customfields = $this->_db->loadObjectList();
  	}
  	return $this->_customfields;
  }

  /**
   * returns all custom fields for xrefs
   * 
   * @return array
   */
  function getXrefCustomFields()
  {
  	if (empty($this->_xrefcustomfields))
  	{
	  	$query = ' SELECT f.id, f.name, f.in_lists, f.searchable, f.ordering '
	  	       . ' FROM #__redevent_fields AS f'
	  	       . ' WHERE f.published = 1'
	  	       . '   AND f.object_key = '. $this->_db->Quote('redevent.xref')
	  	       . ' ORDER BY f.ordering ASC '
	  	       ;
	  	$this->_db->setQuery($query);
	  	$this->_xrefcustomfields = $this->_db->loadObjectList();
  	}
  	return $this->_xrefcustomfields;
  }

  /**
   * returns custom fields to be shown in lists
   * 
   * @return array
   */
  function getListCustomFields()
  {
  	$res = array();

  	$fields = array_merge((array) $this->getCustomFields(), (array) $this->getXrefCustomFields());
  
  	if (!empty($fields)) 
  	{  		
	  	uasort($fields, array('RedeventModelBaseEventList', '_cmpCustomFields'));
	  	foreach ((array)$fields as $f)
	  	{
	  		if ($f->in_lists) {
	  			$res[] = $f;
	  		}
	  	}
  	}
  	return $res;
  }

  function _cmpCustomFields($a, $b)
  {
    return $a->ordering - $b->ordering;
  }
  
  /**
   * returns searchable custom fields
   * 
   * @return array
   */
  function getSearchableCustomFields()
  {
  	$fields = $this->getCustomFields();
  	$res = array();
  	foreach ((array)$fields as $f)
  	{
  		if ($f->searchable) {
  			$res[] = $f;
  		}
  	}
  	return $res;
  }
}
?>