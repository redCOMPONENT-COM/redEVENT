<?php
/**
 * @version 1.0 $Id: eventlist.php 1180 2009-10-13 18:43:13Z julien $
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

require_once('baseeventslist.php');
/**
 * redEVENT Component search Model
 *
 * @package Joomla
 * @subpackage redEVENT
 * @since		2.0
 */
class RedeventModelSearch extends RedeventModelBaseEventList
{
	/**
	 * the query
	 */
	var $_query = null;

	var $_filter = null;
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
		$limit       	= $mainframe->getUserStateFromRequest('com_redevent.search.limit', 'limit', $params->def('display_num', 0), 'int');
		$limitstart		= JRequest::getVar('limitstart', 0, '', 'int');
			
		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);

		// Get the filter request variables
		$this->setState('filter_order', JRequest::getCmd('filter_order', 'x.dates'));
		$this->setState('filter_order_dir', JRequest::getCmd('filter_order_Dir', 'ASC'));
	}

	/**
	 * Build the where clause
	 *
	 * @access private
	 * @return string
	 */
	function _buildWhere()
	{
		$app = &JFactory::getApplication();

		// Get the paramaters of the active menu item
		$params 	= & $app->getParams();
		$task 		= JRequest::getWord('task');
    $customs = $app->getUserStateFromRequest('com_redevent.filter.customs', 'filtercustom', array(), 'array');
    
		$where = array();
	
    foreach ((array) $customs as $key => $custom)
    {
      if ($custom != '') 
      {
      	if (is_array($custom)) {
      		$custom = implode("/n", $custom);
      	}
        $where[] = ' c'.$key.'.value LIKE ' . $this->_db->Quote('%'.$custom.'%');
      }
    }
    
		// First thing we need to do is to select only needed events
		if ($task == 'archive') {
			$where[] = ' x.published = -1';
		} else {
			$where[] = ' x.published = 1';
		}

		$filter = $this->getFilter();
		if (!$filter || empty($filter)) {
			$filter = array('0');
		}
		$where = array_merge($where, $filter);
		
    if (count($where)) {
    	$where = ' WHERE '. implode(' AND ', $where);
    }
    else {
    	$where = '';
    }
		return $where;
	}
	
	/**
	 * return array of filters for where part of sql query
	 * 
	 * @return array
	 */
	function getFilter()
	{
		if (empty($this->_filter))
		{
			// Get the paramaters of the active menu item		
			$mainframe = &Jfactory::getApplication();
			$post = JRequest::get('request');
					
	    $filter_continent = $mainframe->getUserStateFromRequest('com_redevent.search.filter_continent', 'filter_continent', null, 'string');
	    $filter_country   = $mainframe->getUserStateFromRequest('com_redevent.search.filter_country',   'filter_country', null, 'string');
	    $filter_state     = $mainframe->getUserStateFromRequest('com_redevent.search.filter_state',     'filter_state', null, 'string');
	    $filter_city      = $mainframe->getUserStateFromRequest('com_redevent.search.filter_city',      'filter_city', null, 'string');
	    $filter_venue     = $mainframe->getUserStateFromRequest('com_redevent.search.filter_venue',     'filter_venue', null, 'int');
	   
	    $filter_date          = $mainframe->getUserStateFromRequest('com_redevent.search.filter_date',          'filter_date',          '', 'string');
	    $filter_venuecategory = $mainframe->getUserStateFromRequest('com_redevent.search.filter_venuecategory', 'filter_venuecategory', 0, 'int');
	    $filter_category      = $mainframe->getUserStateFromRequest('com_redevent.search.filter_category',      'filter_category',      0, 'int');
	    $filter_event         = $mainframe->getUserStateFromRequest('com_redevent.search.filter_event',         'filter_event',         0, 'int');
	        
	    $customs              = $mainframe->getUserStateFromRequest('com_redevent.filter.customs', 'filtercustom', array(), 'array');

	    $filter 		      = JRequest::getString('filter', '', 'request');
			$filter_type 	    = JRequest::getWord('filter_type', '', 'request');
			
		
	    	
	    $where = array();
	    
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
	    	}
	    }
	    // filter date
	    if ($filter_date) {
	    	if (strtotime($filter_date)) {
	    		$where[] = ' (\''.$filter_date.'\' BETWEEN (x.dates) AND (x.enddates) OR \''.$filter_date.'\' = x.dates)';
	    	}
	    }
		
	    if ($filter_venue)
	    {
	    	$where[] = ' l.id = ' . $this->_db->Quote($filter_venue);    	
	    }
	    else if (!is_null($filter_city) && $filter_city != "0") {
	    	$where[] = ' l.city = ' . $this->_db->Quote($filter_city);
	    }	    
	    else if (!is_null($filter_state) && $filter_state != "0") {
	    	$where[] = ' l.state = ' . $this->_db->Quote($filter_state);
	    }	 
	    // filter country
	    else if (!is_null($filter_country) && $filter_country != "0") {
	    	$where[] = ' l.country = ' . $this->_db->Quote($filter_country);
	    }	    
	    else if (!is_null($filter_continent) && $filter_continent != "0") {
	      $where[] = ' c.continent = ' . $this->_db->Quote($filter_continent);
	    }
	    
	    // filter category
	    if ($filter_category) {
	    	$category = $this->getCategory((int) $filter_category);
	    	if ($category) {
					$where[] = '(c.id = '.$this->_db->Quote($category->id) . ' OR (c.lft > ' . $this->_db->Quote($category->lft) . ' AND c.rgt < ' . $this->_db->Quote($category->rgt) . '))';
	    	}
	    }
	    // filter venue category
	    if ($filter_venuecategory) {
	    	$category = $this->getVenueCategory((int) $filter_venuecategory);
	    	if ($category) {
					$where[] = '(vc.id = '.$this->_db->Quote($category->id) . ' OR (vc.lft > ' . $this->_db->Quote($category->lft) . ' AND vc.rgt < ' . $this->_db->Quote($category->rgt) . '))';
	    	}
	    }
	    
	    if ($filter_event)
	    {
	    	$where[] = ' a.id = ' . $this->_db->Quote($filter_event);    	
	    }
	    
	    //custom fields
	    foreach ((array) $customs as $key => $custom)
	    {
	      if ($custom != '') 
	      {
	      	if (is_array($custom)) {
	      		$custom = implode("/n", $custom);
	      	}
	        $where[] = ' c'.$key.'.value LIKE ' . $this->_db->Quote('%'.$custom.'%');
	      }
	    }
	    
	    $this->_filter = $where;
		}
    return $this->_filter;
	}
	  
  function getCountryOptions()
  {
    $mainframe = &JFactory::getApplication();
  	$filter_continent = $mainframe->getUserState('com_redevent.search.filter_continent');
  	
    $query = ' SELECT DISTINCT c.iso2 as value, c.name as text '
           . ' FROM #__redevent_event_venue_xref AS x'
           . ' INNER JOIN #__redevent_venues AS v ON v.id = x.venueid'
           . ' INNER JOIN #__redevent_countries as c ON c.iso2 = v.country '
           ;
    if ($filter_continent) {
      $query .= ' WHERE c.continent = ' . $this->_db->Quote($filter_continent);
    }
    $query .= ' ORDER BY c.name ';
    $this->_db->setQuery($query);
    return $this->_db->loadObjectList();
  }
	
	function getStateOptions()
	{
    $mainframe = &JFactory::getApplication();
  	$filter_country = $mainframe->getUserState('com_redevent.search.filter_country');
  	
		$query = ' SELECT DISTINCT v.state as value, v.state as text '
           . ' FROM #__redevent_event_venue_xref AS x'
           . ' INNER JOIN #__redevent_venues AS v ON v.id = x.venueid'
           . ' LEFT JOIN #__redevent_countries as c ON c.iso2 = v.country '
           ;
    if (!empty($country)) {
    	$query .= ' WHERE v.country = ' . $this->_db->Quote($filter_country);
    }
    $query .= ' ORDER BY v.state ';           
    $this->_db->setQuery($query);
    return $this->_db->loadObjectList();
	}
	
	function getCityOptions()
	{
    $mainframe = &JFactory::getApplication();
  	$country = $mainframe->getUserState('com_redevent.search.filter_country');
		$state =   $mainframe->getUserState('com_redevent.search.filter_state');
		
		$query = ' SELECT DISTINCT v.city as value, v.city as text '
           . ' FROM #__redevent_event_venue_xref AS x'
           . ' INNER JOIN #__redevent_venues AS v ON v.id = x.venueid'
           . ' LEFT JOIN #__redevent_countries as c ON c.iso2 = v.country '
           ;
    $where = array();
    if (!empty($country)) {
    	$where[] = ' v.country = ' . $this->_db->Quote($country);
    }
    if (!empty($state)) {
    	$where[] = ' v.state = ' . $this->_db->Quote($state);
    }
    if (count($where)) {
    	$query .= ' WHERE '. implode(' AND ', $where);
    }
    $query .= ' ORDER BY v.city ';           
    $this->_db->setQuery($query);
    return $this->_db->loadObjectList();
	}
	
	
	/**
	 * get a venue category
	 * @param int id
	 * @return object
	 */
	function getVenuesOptions()
	{
		$app = &JFactory::getApplication();
		$vcat = JRequest::getVar('filter_venuecategory');
		$city =   $app->getUserState('com_redevent.search.filter_city');
		$country =   $app->getUserState('com_redevent.search.filter_country');
		
		$acl = &UserAcl::getInstance();		
		$gids = $acl->getUserGroupsIds();
		if (!is_array($gids) || !count($gids)) {
			$gids = array(0);
		}
		$gids = implode(',', $gids);
		
		$query = ' SELECT v.id AS value, '
           . ' CASE WHEN CHAR_LENGTH(v.city) THEN CONCAT_WS(\' - \', v.venue, v.city) ELSE v.venue END as text '
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
		$where[] = ' (vcat.private = 0 OR gvc.id IS NOT NULL) ';
		
    if (count($where)) {
    	$query .= ' WHERE '. implode(' AND ', $where);
    }
    $query .= ' ORDER BY v.venue ';
		$this->_db->setQuery($query);
		$res = $this->_db->loadObjectList();
		return $res;
	}
	
	/**
	 * get list of events as options, according to category, venue, and venue category criteria
	 * @return unknown_type
	 */
	function getEventsOptions()
	{
		$app = &JFactory::getApplication();
    $filter_venuecategory = JRequest::getVar('filter_venuecategory');
    $filter_category = JRequest::getVar('filter_category');
		$filter_venue = JRequest::getVar('filter_venue');
		$task 		= JRequest::getWord('task');
			
		//Get Events from Database
		$query  = ' SELECT a.id AS value, a.title AS text '
		        . ' FROM #__redevent_event_venue_xref AS x'
		        . ' INNER JOIN #__redevent_events AS a ON a.id = x.eventid'
		        . ' INNER JOIN #__redevent_venues AS l ON l.id = x.venueid'
		        . ' LEFT JOIN #__redevent_venue_category_xref AS xvcat ON l.id = xvcat.venue_id'
		        . ' LEFT JOIN #__redevent_venues_categories AS vc ON xvcat.category_id = vc.id'
            . ' INNER JOIN #__redevent_event_category_xref AS xcat ON xcat.event_id = a.id'
	          . ' INNER JOIN #__redevent_categories AS c ON c.id = xcat.category_id'
		        ;
	
		
		$where = array();		
		// First thing we need to do is to select only needed events
		if ($task == 'archive') {
			$where[] = ' x.published = -1';
		} else {
			$where[] = ' x.published = 1';
		}
		// filter category
    if ($filter_category) {
    	$category = $this->getCategory((int) $filter_category);
			$where[] = '(c.id = '.$this->_db->Quote($category->id) . ' OR (c.lft > ' . $this->_db->Quote($category->lft) . ' AND c.rgt < ' . $this->_db->Quote($category->rgt) . '))';
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
    
    if (count($where)) {
    	$query .= ' WHERE '. implode(' AND ', $where);
    }
    $query .= ' GROUP BY a.id ';
    
		$this->_db->setQuery($query);
		$res = $this->_db->loadObjectList();
		return $res;
	}

	/**
	 * get list of events as options, according to category, venue, and venue category criteria
	 * @return unknown_type
	 */
	function getCategoriesOptions()
	{
		$app = &JFactory::getApplication();
    $filter_venuecategory = JRequest::getVar('filter_venuecategory');
		$filter_venue = JRequest::getVar('filter_venue');
		$task 		= JRequest::getWord('task');
		
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

	/**
	 * get a venue category
	 * @param int id
	 * @return object
	 */
	function getVenueCategory($id)
	{		
		$query = ' SELECT vc.id, vc.name, vc.lft, vc.rgt '
		       . ' FROM #__redevent_venues_categories as vc '
		       . ' WHERE vc.id = '. $this->_db->Quote($id)
		            ;
		$this->_db->setQuery($query);
		$res = $this->_db->loadObject();
		return $res;
	}
}
?>