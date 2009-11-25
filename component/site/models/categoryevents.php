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

require_once('baseeventslist.php');

/**
 * EventList Component Categoryevents Model
 *
 * @package Joomla
 * @subpackage EventList
 * @since		0.9
 */
class RedeventModelCategoryevents extends RedeventModelBaseEventList {

	/**
	 * category data array
	 *
	 * @var array
	 */
	var $_category = null;

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
		$params 	= & $mainframe->getParams();

		//get the number of events from database
		$limit       	= $mainframe->getUserStateFromRequest('com_redevent.categoryevents.limit', 'limit', $params->def('display_num', 0), 'int');
		$limitstart		= JRequest::getInt('limitstart');

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);

		// Get the filter request variables
		$this->setState('filter_order', JRequest::getCmd('filter_order', 'x.dates'));
		$this->setState('filter_order_dir', JRequest::getCmd('filter_order_Dir', 'ASC'));
	}

	/**
	 * Method to set the category id
	 *
	 * @access	public
	 * @param	int	category ID number
	 */
	function setId($id)
	{
		// Set new category ID and wipe data
		$this->_id			= $id;
		$this->_data		= null;
	}
	
	function setXref($xref)
	{
		// Set new category ID and wipe data
		$this->_xref			= $xref;
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
    $where    = $this->_buildCategoryWhere();
    $orderby  = $this->_buildCategoryOrderBy();
		$customs  = $this->getCustomFields();

    //Get Events from Database
    $query = 'SELECT a.id, a.datimage, x.dates, x.enddates, x.times, x.endtimes, x.id AS xref, x.registrationend, x.id AS xref, x.maxattendees, x.maxwaitinglist, '
        . ' a.title, a.datdescription, a.created, a.registra, l.venue, l.city, l.state, l.url, c.catname, c.id AS catid, '
        . ' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug, '
        . ' CASE WHEN CHAR_LENGTH(l.alias) THEN CONCAT_WS(\':\', l.id, l.alias) ELSE l.id END as venueslug, '
        . ' CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as categoryslug '
        ;
		// add the custom fields
		foreach ((array) $customs as $c)
		{
			$query .= ', c'. $c->id .'.value AS custom'. $c->id;
		}
    $query .= ' FROM #__redevent_events AS a'
        . ' INNER JOIN #__redevent_event_venue_xref AS x on x.eventid = a.id'
        . ' INNER JOIN #__redevent_venues AS l ON l.id = x.venueid'
        . ' INNER JOIN #__redevent_event_category_xref AS xcat ON xcat.event_id = a.id'
        . ' INNER JOIN #__redevent_categories AS c ON c.id = xcat.category_id'
		    ;
		
		// add the custom fields tables
		foreach ((array) $customs as $c)
		{
			$query .= ' LEFT JOIN #__redevent_fields_values AS c'. $c->id .' ON c'. $c->id .'.object_id = a.id';
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
	function _buildCategoryOrderBy()
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
	function _buildCategoryWhere( )
	{
		global $mainframe;

		$user		= & JFactory::getUser();
		$gid		= (int) $user->get('aid');
		$category = & $this->getCategory();

		// Get the paramaters of the active menu item
		$params 	= & $mainframe->getParams();

		$task 		= JRequest::getWord('task');

		$where = array();
		
		$where[] = '(c.id = '.$this->_db->Quote($category->id) . ' OR (c.lft > ' . $this->_db->Quote($category->lft) . ' AND c.rgt < ' . $this->_db->Quote($category->rgt) . '))';
		
		// First thing we need to do is to select only the requested events
		if ($task == 'archive') {
			$where[] = ' x.published = -1';
		} else {
			$where[] = ' x.published = 1 ';
		}

		// Second is to only select events assigned to category the user has access to
		$where[] = ' c.access <= '.$gid;

		/*
		 * If we have a filter, and this is enabled... lets tack the AND clause
		 * for the filter onto the WHERE clause of the content item query.
		 */
		if ($params->get('filter'))
		{
      $filter     = $mainframe->getUserStateFromRequest('com_redevent.categoryevents.filter', 'filter', '', 'string');
      $filter_type  = $mainframe->getUserStateFromRequest('com_redevent.categoryevents.filter_type', 'filter_type', '', 'string');

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
		}
		return ' WHERE ' . implode(' AND ', $where);
	}

	/**
	 * Method to get the Category
	 *
	 * @access public
	 * @return integer
	 */
	function getCategory( ) 
	{
		if (!$this->_category) {
		$query = 'SELECT *,'
				.' CASE WHEN CHAR_LENGTH(alias) THEN CONCAT_WS(\':\', id, alias) ELSE id END as slug'
				.' FROM #__redevent_categories'
				.' WHERE id = '.$this->_id;

		$this->_db->setQuery( $query );
		$this->_category = $this->_db->loadObject();
		}
		
		return $this->_category;
	}
	
}
?>