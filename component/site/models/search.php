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

		$filter_continent = $mainframe->getUserStateFromRequest('com_redevent.search.filter_continent', 'filter_continent', null, 'string');
		$filter_country   = $mainframe->getUserStateFromRequest('com_redevent.search.filter_country',   'filter_country', null, 'string');
		$filter_state     = $mainframe->getUserStateFromRequest('com_redevent.search.filter_state',     'filter_state', null, 'string');
		$filter_city      = $mainframe->getUserStateFromRequest('com_redevent.search.filter_city',      'filter_city', null, 'string');
		$filter_venue     = $mainframe->getUserStateFromRequest('com_redevent.search.filter_venue',     'filter_venue', null, 'int');
		 
		$filter_date_from     = $mainframe->getUserStateFromRequest('com_redevent.search.filter_date_from',          'filter_date_from',          '', 'string');
		$filter_date_to       = $mainframe->getUserStateFromRequest('com_redevent.search.filter_date_to',          'filter_date_to',          '', 'string');
		$filter_venuecategory = $mainframe->getUserStateFromRequest('com_redevent.search.filter_venuecategory', 'filter_venuecategory', 0, 'int');
		$filter_category      = $mainframe->getUserStateFromRequest('com_redevent.search.filter_category',      'filter_category',      $params->get('category', 0), 'int');
		$filter_event         = $mainframe->getUserStateFromRequest('com_redevent.search.filter_event',         'filter_event',         0, 'int');

		$customs              = $mainframe->getUserStateFromRequest('com_redevent.search.filter_customs', 'filtercustom', array(), 'array');

		$filter 		      = JRequest::getString('filter', '', 'request');
		$filter_type 	    = JRequest::getWord('filter_type', '', 'request');
			
		// saving state
		$this->setState('filter_continent',     $filter_continent);
		$this->setState('filter_country',       $filter_country);
		$this->setState('filter_state',         $filter_state);
		$this->setState('filter_city',          $filter_city);
		$this->setState('filter_venue',         $filter_venue);
		$this->setState('filter_date_from',     $filter_date_from);
		$this->setState('filter_date_to',       $filter_date_to);
		$this->setState('filter_venuecategory', $filter_venuecategory);
		$this->setState('filter_category',      $filter_category);
		$this->setState('filter_event',         $filter_event);
		$this->setState('filter_customs',       $customs);
		$this->setState('filter',               $filter);
		$this->setState('filter_type',          $filter_type);
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
		$customs = $this->getState('filter_customs');

		$where = array();

		foreach ((array) $customs as $key => $custom)
		{
			if ($custom != '')
			{
				if (is_array($custom)) {
					$custom = implode("/n", $custom);
				}
				$where[] = ' custom'.$key.' LIKE ' . $this->_db->Quote('%'.$custom.'%');
			}
		}

		// First thing we need to do is to select only needed events
		if ($task == 'archive') {
			$where[] = ' x.published = -1';
		} else {
			$where[] = ' x.published = 1';
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

		$filter = $this->getFilter();
		if ( $params->get('requires_filter', 0) && (!$filter || empty($filter)) ) {
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
			$params    = & $mainframe->getParams();
			$post = JRequest::get('request');
				
			$filter_continent = $this->getState('filter_continent');
			$filter_country   = $this->getState('filter_country');
			$filter_state     = $this->getState('filter_state');
			$filter_city      = $this->getState('filter_city');
			$filter_venue     = $this->getState('filter_venue');

			$filter_date_from     = $this->getState('filter_date_from');
			$filter_date_to       = $this->getState('filter_date_to');
			$filter_venuecategory = $this->getState('filter_venuecategory');
			$filter_category      = $this->getState('filter_category');
			$filter_event         = $this->getState('filter_event');
			 
			$customs              = $this->getState('filtercustom');
			 
			$filter 		      = $this->getState('filter');
			$filter_type 	    = $this->getState('filter_type');
			 
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
			if (strtotime($filter_date_from)) {
				$date = $this->_db->Quote(strftime('%F', strtotime($filter_date_from)));
				$where[] = " CASE WHEN (x.enddates) THEN $date <= x.enddates ELSE $date <= x.dates END ";
			}
			if (strtotime($filter_date_to)) {
				$date = $this->_db->Quote(strftime('%F', strtotime($filter_date_to)));
				$where[] = " $date >= x.dates ";
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
					$where[] = ' custom'.$key.' LIKE ' . $this->_db->Quote('%'.$custom.'%');
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
	 * get list of events as options, according to category, venue, and venue category criteria
	 * @return unknown_type
	 */
	function getEventsOptions()
	{
		$app = &JFactory::getApplication();
		$params = & $app->getParams();
		$filter_venuecategory = JRequest::getVar('filter_venuecategory');
		$filter_category = JRequest::getVar('filter_category', $params->get('category', 0));
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
