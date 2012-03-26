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
	protected $_data = null;

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
		$this->setState('filter_order',     JRequest::getCmd('filter_order', 'x.dates'));
		$this->setState('filter_order_dir', JRequest::getCmd('filter_order_Dir', 'ASC'));
		
		$this->setState('filter',      $mainframe->getUserStateFromRequest('com_redevent.'.$this->getName().'.filter',      'filter', '', 'string'));
		$this->setState('filter_type', $mainframe->getUserStateFromRequest('com_redevent.'.$this->getName().'.filter_type', 'filter_type', '', 'string'));
			
		$this->setState('filter_event',    $mainframe->getUserStateFromRequest('com_redevent.'.$this->getName().'.filter_event',    'filter_event', 0, 'int'));
		$this->setState('filter_category', $mainframe->getUserStateFromRequest('com_redevent.'.$this->getName().'.filter_category', 'filter_category', 0, 'int'));
		$this->setState('filter_venue',    $mainframe->getUserStateFromRequest('com_redevent.'.$this->getName().'.filter_venue',    'filter_venue',    0, 'int'));
	}


	/**
	 * set limit
	 * @param int value
	 */
	function setLimit($value)
	{
		$this->setState('limit', (int) $value);
	}

	/**
	 * set limitstart
	 * @param int value
	 */
	function setLimitStart($value)
	{
		$this->setState('limitstart', (int) $value);
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
				// put a limit for print pagination
				//$this->setLimit(5);
			}
			$pagination = $this->getPagination();
			$this->_data = $this->_getList( $query, $pagination->limitstart, $pagination->limit );
			$this->_data = $this->_categories($this->_data);
      $this->_data = $this->_getPlacesLeft($this->_data);
      $this->_data = $this->_getPrices($this->_data);
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
		$acl = &UserAcl::getInstance();
		
		$gids = $acl->getUserGroupsIds();
		if (!is_array($gids) || !count($gids)) {
			$gids = array(0);
		}
		$gids = implode(',', $gids);
		
		

		//Get Events from Database
		$query = 'SELECT x.dates, x.enddates, x.times, x.endtimes, x.registrationend, x.id AS xref, ' 
		    . ' x.maxattendees, x.maxwaitinglist, x.course_credit, x.featured, x.icaldetails, x.icalvenue, x.title as session_title, '
        . ' CASE WHEN CHAR_LENGTH(x.title) THEN CONCAT_WS(\' - \', a.title, x.title) ELSE a.title END as full_title, '
		    . ' a.id, a.title, a.created, a.datdescription, a.registra, a.datimage, a.summary, '
				. ' l.venue, l.city, l.state, l.url, l.street, l.country, '
				. ' c.catname, c.id AS catid,'
        . ' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug, '
        . ' CASE WHEN CHAR_LENGTH(x.alias) THEN CONCAT_WS(\':\', x.id, x.alias) ELSE x.id END as xslug, '
        . ' CASE WHEN CHAR_LENGTH(l.alias) THEN CONCAT_WS(\':\', l.id, l.alias) ELSE l.id END as venueslug, '
        . ' CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as categoryslug '
        ;
		// add the custom fields
		foreach ((array) $customs as $c)
		{
			$query .= ', a.custom'. $c->id;
		}
		// add the custom fields
		foreach ((array) $xcustoms as $c)
		{
			$query .= ', x.custom'. $c->id;
		}
		
    $query .= ' FROM #__redevent_event_venue_xref AS x'
		        . ' INNER JOIN #__redevent_events AS a ON a.id = x.eventid'
		        . ' INNER JOIN #__redevent_venues AS l ON l.id = x.venueid'
		        . ' LEFT JOIN #__redevent_venue_category_xref AS xvcat ON l.id = xvcat.venue_id'
		        . ' LEFT JOIN #__redevent_venues_categories AS vc ON xvcat.category_id = vc.id'
            . ' INNER JOIN #__redevent_event_category_xref AS xcat ON xcat.event_id = a.id'
	          . ' INNER JOIN #__redevent_categories AS c ON c.id = xcat.category_id '
	          . ' LEFT JOIN #__redevent_groups_venues AS gv ON gv.venue_id = l.id AND gv.group_id IN ('.$gids.')'
	          . ' LEFT JOIN #__redevent_groups_venues_categories AS gvc ON gvc.category_id = vc.id AND gvc.group_id IN ('.$gids.')'
	          . ' LEFT JOIN #__redevent_groups_categories AS gc ON gc.category_id = c.id AND gc.group_id IN ('.$gids.')'
		        ;
		
		$query .= $where
		       . ' AND (l.private = 0 OR gv.id IS NOT NULL) '
		       . ' AND (c.private = 0 OR gc.id IS NOT NULL) '
		       . ' AND (vc.private = 0 OR vc.private IS NULL OR gvc.id IS NOT NULL) '
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
		$filter_order		  = $this->getState('filter_order');
		$filter_order_dir	= $this->getState('filter_order_dir');
			
		if (preg_match("/field([0-9]+)/", $filter_order, $regs)) {
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
		$mainframe = &JFactory::getApplication();

		$user		= & JFactory::getUser();
		$gid		= (int) $user->get('aid');

		// Get the paramaters of the active menu item
		$params 	= & $mainframe->getParams();

		$task 		= JRequest::getWord('task');
		
		$where = array();
		
		// First thing we need to do is to select only needed events
		if ($task == 'archive') {
			$where[] = ' x.published = -1 ';
		} else {
			$where[] = ' x.published = 1 ';
		}
				
		// Second is to only select events assigned to category the user has access to
		$where[] = ' c.access <= '.$gid;
		
		/*
		 * If we have a filter, and this is enabled... lets tack the AND clause
		 * for the filter onto the WHERE clause of the item query.
		 */
		if ($params->get('filter_text'))
		{
			$filter 		  = $this->getState('filter');
			$filter_type 	= $this->getState('filter_type');

			if ($filter)
			{
				// clean filter variables
				$filter 		= JString::strtolower($filter);
				$filter			= $this->_db->Quote( '%'.$this->_db->getEscaped( $filter, true ).'%', false );
				$filter_type 	= JString::strtolower($filter_type);

				switch ($filter_type)
				{
					case 'title' :
						$where[] = ' LOWER( a.title ) LIKE '.$filter;
						break;

					case 'venue' :
						$where[] = ' LOWER( l.venue ) LIKE '.$filter;
						break;

					case 'city' :
						$where[] = ' LOWER( l.city ) LIKE '.$filter;
						break;
						
					case 'type' :
						$where[] = '  LOWER( c.catname ) LIKE '.$filter;
						break;
				}
			}
		}
		
    if ($filter_venue = $this->getState('filter_venue'))
    {
    	$where[] = ' l.id = ' . $this->_db->Quote($filter_venue);    	
    }
    
		if ($ev = $this->getState('filter_event')) 
		{
			$where[] = 'a.id = '.$this->_db->Quote($ev);
		}
	    
		if ($cat = $this->getState('filter_category')) 
		{		
    	$category = $this->getCategory((int) $cat);
    	if ($category) {
				$where[] = '(c.id = '.$this->_db->Quote($category->id) . ' OR (c.lft > ' . $this->_db->Quote($category->lft) . ' AND c.rgt < ' . $this->_db->Quote($category->rgt) . '))';
    	}
		}
	
		$sstate = $params->get( 'session_state', '0' );
		if ($sstate == 1)
		{
			$now = strftime('%Y-%m-%d %H:%M');
			$where[] = '(CASE WHEN x.times THEN CONCAT(x.dates," ",x.times) ELSE x.dates END) > '.$this->_db->Quote($now);
		} 
		else if ($sstate == 2) {
			$where[] = 'x.dates = 0';
		}
		
		return ' WHERE '.implode(' AND ', $where);
	}
	
/**
	 * Build the where clause
	 *
	 * @access private
	 * @return string
	 */
	function _buildEventsOptionsWhere()
	{
		$mainframe = &JFactory::getApplication();

		$user		= & JFactory::getUser();
		$gid		= (int) $user->get('aid');

		// Get the paramaters of the active menu item
		$params 	= & $mainframe->getParams();

		$task 		= JRequest::getWord('task');
		
		$where = array();
		
		// First thing we need to do is to select only needed events
		if ($task == 'archive') {
			$where[] = ' x.published = -1 ';
		} else {
			$where[] = ' x.published = 1 ';
		}
				
		// Second is to only select events assigned to category the user has access to
		$where[] = ' c.access <= '.$gid;
		
		/*
		 * If we have a filter, and this is enabled... lets tack the AND clause
		 * for the filter onto the WHERE clause of the item query.
		 */
		if ($params->get('filter_text'))
		{
			$filter 		  = $this->getState('filter');
			$filter_type 	= $this->getState('filter_type');

			if ($filter)
			{
				// clean filter variables
				$filter 		= JString::strtolower($filter);
				$filter			= $this->_db->Quote( '%'.$this->_db->getEscaped( $filter, true ).'%', false );
				$filter_type 	= JString::strtolower($filter_type);

				switch ($filter_type)
				{
					case 'title' :
						$where[] = ' LOWER( a.title ) LIKE '.$filter;
						break;

					case 'venue' :
						$where[] = ' LOWER( l.venue ) LIKE '.$filter;
						break;

					case 'city' :
						$where[] = ' LOWER( l.city ) LIKE '.$filter;
						break;
						
					case 'type' :
						$where[] = '  LOWER( c.catname ) LIKE '.$filter;
						break;
				}
			}
		}
		
    if ($filter_venue = $this->getState('filter_venue'))
    {
    	$where[] = ' l.id = ' . $this->_db->Quote($filter_venue);    	
    }
	    
		if ($cat = $this->getState('filter_category')) 
		{		
    	$category = $this->getCategory((int) $cat);
    	if ($category) {
				$where[] = '(c.id = '.$this->_db->Quote($category->id) . ' OR (c.lft > ' . $this->_db->Quote($category->lft) . ' AND c.rgt < ' . $this->_db->Quote($category->rgt) . '))';
    	}
		}
	
		$sstate = $params->get( 'session_state', '0' );
		if ($sstate == 1)
		{
			$now = strftime('%Y-%m-%d %H:%M');
			$where[] = '(CASE WHEN x.times THEN CONCAT(x.dates," ",x.times) ELSE x.dates END) > '.$this->_db->Quote($now);
		} 
		else if ($sstate == 2) {
			$where[] = 'x.dates = 0';
		}
		
		return ' WHERE '.implode(' AND ', $where);
	}
	
	/**
   * adds categories property to event rows
   *
   * @param array $rows of events
   * @return array
   */
  function _categories($rows)
  {
		$acl = &UserAcl::getInstance();		
		$gids = $acl->getUserGroupsIds();
		if (!is_array($gids) || !count($gids)) {
			$gids = array(0);
		}
		$gids = implode(',', $gids);
		
    for ($i=0, $n=count($rows); $i < $n; $i++) 
    {
      $query =  ' SELECT c.id, c.catname, c.color, '
              . ' CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as slug '
              . ' FROM #__redevent_categories as c '
              . ' INNER JOIN #__redevent_event_category_xref as x ON x.category_id = c.id '
	            . '  LEFT JOIN #__redevent_groups_categories AS gc ON gc.category_id = c.id '
              . ' WHERE c.published = 1 '
              . '   AND x.event_id = ' . $this->_db->Quote($rows[$i]->id)
              . '   AND (c.private = 0 OR gc.group_id IN ('.$gids.')) '
              . ' GROUP BY c.id '
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
			   . ' AND r.cancelled = 0 '
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
   * adds registered (int) and waiting (int) properties to rows.
   * 
   * @return array 
   */
  function _getPrices($rows) 
  {
  	if (!$rows) {
  		return $rows;
  	}
    $db = JFactory::getDBO();
    $ids = array();
    foreach ($rows as $k => $r) 
    {
    	$ids[$r->xref] = $k;
    }
  	$query = ' SELECT sp.*, p.name, p.alias, p.image, p.tooltip, f.currency, '
	         . ' CASE WHEN CHAR_LENGTH(p.alias) THEN CONCAT_WS(\':\', p.id, p.alias) ELSE p.id END as slug ' 
  	       . ' FROM #__redevent_sessions_pricegroups AS sp '
  	       . ' INNER JOIN #__redevent_pricegroups AS p on p.id = sp.pricegroup_id '
  	       . ' INNER JOIN #__redevent_event_venue_xref AS x on x.id = sp.xref '
  	       . ' INNER JOIN #__redevent_events AS e on e.id = x.eventid '
  	       . ' LEFT JOIN #__rwf_forms AS f on e.redform_id = f.id '
  	       . ' WHERE sp.xref IN (' . implode(",", array_keys($ids)).')'
  	       . ' ORDER BY p.ordering ASC '
  	       ;
  	$db->setQuery($query);
  	$res = $db->loadObjectList();
  	
  	// sort this out
  	$prices = array();
  	foreach ((array)$res as $p)
  	{
  		if (!isset($prices[$p->xref])) {
  			$prices[$p->xref] = array($p);
  		}
  		else {
  			$prices[$p->xref][] = $p;
  		}
  	}
  	
  	// add to rows
    foreach ($rows as $k => $r) 
    {
    	if (isset($prices[$r->xref])) {
    		$rows[$k]->prices = $prices[$r->xref];
    	}
    	else {
    		$rows[$k]->prices = null;
    	}
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
	  	$query = ' SELECT f.id, f.name, f.in_lists, f.searchable, f.ordering, f.tips '
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
	  	$query = ' SELECT f.id, f.name, f.in_lists, f.searchable, f.ordering, f.tips '
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
	  			$res[$f->id] = $f;
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
  
	/**
	 * return filter for event custom fields
	 */
	function getCustomFilters()
	{
		$query = ' SELECT f.* FROM #__redevent_fields AS f '
           . ' WHERE f.published = 1 '
           . '   AND f.searchable = 1 '
//           . '   AND f.object_key = '. $this->_db->Quote("redevent.event")
           . ' ORDER BY f.ordering ASC '
           ;
    $this->_db->setQuery($query);
    $rows = $this->_db->loadObjectList();
    
    $filters = array();
    foreach ($rows as $r) {
    	$field = redEVENTcustomHelper::getCustomField($r->type);
    	$field->bind($r);
    	$filters[] = $field;
    }
    return $filters;
	}

	/**
	 * get list of categories as options, according to acl
	 * 
	 * @return array
	 */
	function getCategoriesOptions()
	{
		$app = &JFactory::getApplication();
    $filter_venuecategory = JRequest::getVar('filter_venuecategory');
		$filter_venue         = JRequest::getVar('filter_venue');
		$task 		            = JRequest::getWord('task');
		
		$acl = &UserAcl::getInstance();		
		$gids = $acl->getUserGroupsIds();
		if (!is_array($gids) || !count($gids)) {
			$gids = array(0);
		}
		$gids = implode(',', $gids);
			
		//Get Events from Database
		$query  = ' SELECT c.id '
		        . ' FROM #__redevent_event_venue_xref AS x'
		        . ' INNER JOIN #__redevent_events AS a ON a.id = x.eventid'
		        . ' INNER JOIN #__redevent_venues AS l ON l.id = x.venueid'
		        . ' LEFT JOIN #__redevent_venue_category_xref AS xvcat ON l.id = xvcat.venue_id'
		        . ' LEFT JOIN #__redevent_venues_categories AS vc ON xvcat.category_id = vc.id'
            . ' INNER JOIN #__redevent_event_category_xref AS xcat ON xcat.event_id = a.id'
	          . ' INNER JOIN #__redevent_categories AS c ON c.id = xcat.category_id'
	          
	          . ' LEFT JOIN #__redevent_groups_venues AS gv ON gv.venue_id = l.id AND gv.group_id IN ('.$gids.')'
	          . ' LEFT JOIN #__redevent_groups_venues_categories AS gvc ON gvc.category_id = vc.id AND gvc.group_id IN ('.$gids.')'
	          . ' LEFT JOIN #__redevent_groups_categories AS gc ON gc.category_id = c.id AND gc.group_id IN ('.$gids.')'
		        ;	
		
		$where = array();		
		// First thing we need to do is to select only needed events
		if ($task == 'archive') {
			$where[] = ' x.published = -1';
		} else {
			$where[] = ' x.published = 1';
		}
		
    // filter category
    if ($filter_venuecategory) {
    	$category = $this->getVenueCategory((int) $filter_venuecategory);
			$where[] = '(vc.id = '.$this->_db->Quote($category->id) . ' OR (vc.lft > ' . $this->_db->Quote($category->lft) . ' AND vc.rgt < ' . $this->_db->Quote($category->rgt) . '))';
    }
    if ($filter_venue)
    {
    	$where[] = ' l.id = ' . $this->_db->Quote($filter_venue);    	
    }
    //acl
		$where[] = ' (l.private = 0 OR gv.id IS NOT NULL) ';
		$where[] = ' (c.private = 0 OR gc.id IS NOT NULL) ';
		$where[] = ' (vc.private = 0 OR vc.private IS NULL OR gvc.id IS NOT NULL) ';
    
    if (count($where)) {
    	$query .= ' WHERE '. implode(' AND ', $where);
    }
    $query .= ' GROUP BY c.id ';
    
		$this->_db->setQuery($query);
		$res = $this->_db->loadResultArray();
		
		return redEVENTHelper::getEventsCatOptions(true, false, $res);
	}
	

	/**
	 * get venues options

	 * @return array
	 */
	function getVenuesOptions()
	{
		$app = &JFactory::getApplication();
		$vcat    = JRequest::getVar('filter_venuecategory');
		$city    = JRequest::getVar('filter_city');
		$country = JRequest::getVar('filter_country');
		
		$acl = &UserAcl::getInstance();		
		$gids = $acl->getUserGroupsIds();
		if (!is_array($gids) || !count($gids)) {
			$gids = array(0);
		}
		$gids = implode(',', $gids);
		
		$query = ' SELECT DISTINCT v.id AS value, '
           . ' CASE WHEN CHAR_LENGTH(v.city) AND v.city <> v.venue THEN CONCAT_WS(\' - \', v.venue, v.city) ELSE v.venue END as text '
		       . ' FROM #__redevent_venues AS v '
		       . ' LEFT JOIN #__redevent_venue_category_xref AS xcat ON xcat.venue_id = v.id '
		       . ' LEFT JOIN #__redevent_venues_categories AS vcat ON vcat.id = xcat.category_id '
		       
		       . ' LEFT JOIN #__redevent_groups_venues AS gv ON gv.venue_id = v.id AND gv.group_id IN ('.$gids.')'
		       . ' LEFT JOIN #__redevent_groups_venues_categories AS gvc ON gvc.category_id = vcat.id AND gvc.group_id IN ('.$gids.')'
		       ;
		$where = array();
    if ($vcat) {
    	$category = $this->getCategory($vcat);
			$where[] = ' (vcat.id = '.$this->_db->Quote($category->id) . ' OR (vcat.lft > ' . $this->_db->Quote($category->lft) . ' AND vcat.rgt < ' . $this->_db->Quote($category->rgt) . '))';
    }
    if ($city) {
    	$where[] = ' v.city = '.$this->_db->Quote($city);
    }
    if ($country) {
    	$where[] = ' v.country = '.$this->_db->Quote($country);
    }
    //acl
		$where[] = ' (v.private = 0 OR gv.id IS NOT NULL) ';
		$where[] = ' (vcat.id IS NULL OR vcat.private = 0 OR gvc.id IS NOT NULL) ';
		
    if (count($where)) {
    	$query .= ' WHERE '. implode(' AND ', $where);
    }
    $query .= ' ORDER BY v.venue ';
		$this->_db->setQuery($query);
		$res = $this->_db->loadObjectList();
		return $res;
	}
	
	function getEventsOptions()
	{
		// Get the WHERE and ORDER BY clauses for the query
		$where		= $this->_buildEventsOptionsWhere();
		$customs = $this->getCustomFields();
		$xcustoms = $this->getXrefCustomFields();
		$acl = &UserAcl::getInstance();
		
		$gids = $acl->getUserGroupsIds();
		if (!is_array($gids) || !count($gids)) {
			$gids = array(0);
		}
		$gids = implode(',', $gids);
		
		

		//Get Events from Database
		$query = 'SELECT a.id AS value, a.title AS text '
        ;
		// add the custom fields
		foreach ((array) $customs as $c)
		{
			$query .= ', a.custom'. $c->id;
		}
		// add the custom fields
		foreach ((array) $xcustoms as $c)
		{
			$query .= ', x.custom'. $c->id;
		}
		
    $query .= ' FROM #__redevent_event_venue_xref AS x'
		        . ' INNER JOIN #__redevent_events AS a ON a.id = x.eventid'
		        . ' INNER JOIN #__redevent_venues AS l ON l.id = x.venueid'
		        . ' LEFT JOIN #__redevent_venue_category_xref AS xvcat ON l.id = xvcat.venue_id'
		        . ' LEFT JOIN #__redevent_venues_categories AS vc ON xvcat.category_id = vc.id'
            . ' INNER JOIN #__redevent_event_category_xref AS xcat ON xcat.event_id = a.id'
	          . ' INNER JOIN #__redevent_categories AS c ON c.id = xcat.category_id '
	          . ' LEFT JOIN #__redevent_groups_venues AS gv ON gv.venue_id = l.id AND gv.group_id IN ('.$gids.')'
	          . ' LEFT JOIN #__redevent_groups_venues_categories AS gvc ON gvc.category_id = vc.id AND gvc.group_id IN ('.$gids.')'
	          . ' LEFT JOIN #__redevent_groups_categories AS gc ON gc.category_id = c.id AND gc.group_id IN ('.$gids.')'
		        ;
		
		$query .= $where
		       . ' AND (l.private = 0 OR gv.id IS NOT NULL) '
		       . ' AND (c.private = 0 OR gc.id IS NOT NULL) '
		       . ' AND (vc.private = 0 OR vc.private IS NULL OR gvc.id IS NOT NULL) '
		       . ' GROUP BY (a.id) '
		       . ' ORDER BY a.title, x.title ASC '
				   ;
		$this->_db->setQuery($query);
		return $this->_db->loadObjectList();
	}
	
	/**
	 * get a category
	 * @param int id
	 * @return object
	 */
	function getCategory($id)
	{		
		$query = ' SELECT c.id, c.catname, c.lft, c.rgt '
		       . ' FROM #__redevent_categories AS c '
		       . ' WHERE c.id = '. $this->_db->Quote($id)
		            ;
		$this->_db->setQuery($query);
		$res = $this->_db->loadObject();
		return $res;
	}
}
