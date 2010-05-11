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
		global $mainframe;

		// Get the paramaters of the active menu item
		$params 	= & $mainframe->getParams();

		$task 		= JRequest::getWord('task');
		
		$where = array();
		
		// First thing we need to do is to select only needed events
		if ($task == 'archive') {
			$where[] = ' x.published = -1';
		} else {
			$where[] = ' x.published = 1';
		}

		$filter 		      = JRequest::getString('filter', '', 'request');
		$filter_type 	    = JRequest::getWord('filter_type', '', 'request');
    $filter_continent = JRequest::getVar('filter_continent', '', 'string');
    $filter_country   = JRequest::getVar('filter_country', '', 'string');
    $filter_city      = JRequest::getVar('filter_city', '', 'string');
		$filter_venue     = JRequest::getVar('filter_venue', 0, '', 'int');
    $filter_date      = JRequest::getVar('filter_date', '', 'string');
    $filter_venuecategory = JRequest::getVar('filter_venuecategory', 0, 'int');
    $filter_category  = JRequest::getVar('filter_category', 0, 'int');
    $filter_event     = JRequest::getVar('filter_event', 0, 'int');
    
    // no result if no filter:
    if ( !($filter || $filter_continent || $filter_country || $filter_city || $filter_date || $filter_category || $filter_venuecategory || $filter_venue || $filter_event) ) {
    	return ' WHERE 0 ';
    }

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
    // filter country
    if ($filter_continent) {
      $where[] = ' c.continent = ' . $this->_db->Quote($filter_continent);
    }
    // filter country
    if ($filter_country) {
    	$where[] = ' l.country = ' . $this->_db->Quote($filter_country);
    }
    // filter city
    if ($filter_city) {
    	$where[] = ' l.city = ' . $this->_db->Quote($filter_city);
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
    if ($filter_event)
    {
    	$where[] = ' a.id = ' . $this->_db->Quote($filter_event);    	
    }
    if (count($where)) {
    	$where = ' WHERE '. implode(' AND ', $where);
    }
    else {
    	$where = '';
    }
		return $where;
	}
	  
  function getCountryOptions()
  {
    global $mainframe;
  	$filter_continent = JRequest::getVar('filter_continent', '', 'string');
  	
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
	
	function getCityOptions()
	{
		$country = JRequest::getString('filter_country', '', 'request');
		$query = ' SELECT DISTINCT v.city as value, v.city as text '
           . ' FROM #__redevent_event_venue_xref AS x'
           . ' INNER JOIN #__redevent_venues AS v ON v.id = x.venueid'
           . ' LEFT JOIN #__redevent_countries as c ON c.iso2 = v.country '
           ;
    if (!empty($country)) {
    	$query .= ' WHERE v.country = ' . $this->_db->Quote($country);
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
		
		$query = ' SELECT v.id AS value, '
           . ' CASE WHEN CHAR_LENGTH(v.city) THEN CONCAT_WS(\' - \', v.venue, v.city) ELSE v.venue END as text '
		       . ' FROM #__redevent_venues AS v '
		       . ' LEFT JOIN #__redevent_venue_category_xref AS xcat ON xcat.venue_id = v.id '
		       . ' LEFT JOIN #__redevent_venues_categories AS vcat ON vcat.id = xcat.category_id '
		       ;
    if ($vcat) {
    	$category = $this->getCategory($vcat);
			$query .= ' WHERE (vcat.id = '.$this->_db->Quote($category->id) . ' OR (vcat.lft > ' . $this->_db->Quote($category->lft) . ' AND vcat.rgt < ' . $this->_db->Quote($category->rgt) . '))';
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
			
		//Get Events from Database
		$query  = ' SELECT c.id '
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
		$app = &JFactory::getApplication();
    $filter_venuecategory = JRequest::getVar('filter_venuecategory');
    $filter_category = JRequest::getVar('filter_category');
    
		$query = ' SELECT id, catname, lft, rgt '
		       . ' FROM #__redevent_categories '
		       . ' WHERE id = '. $this->_db->Quote($id)
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
		$query = ' SELECT id, name, lft, rgt '
		       . ' FROM #__redevent_venues_categories '
		       . ' WHERE id = '. $this->_db->Quote($id)
		            ;
		$this->_db->setQuery($query);
		$res = $this->_db->loadObject();
		return $res;
	}
}
?>