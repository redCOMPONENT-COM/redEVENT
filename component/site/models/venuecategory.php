<?php
/**
 * @version 1.0 $Id: categoryevents.php 1084 2009-10-05 19:11:57Z julien $
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
 * redevent Component venue category events Model
 *
 * @package Joomla
 * @subpackage redevent
 * @since		2.0
 */
class RedeventModelVenuecategory extends RedeventModelBaseEventList {

	/**
	 * category data array
	 *
	 * @var array
	 */
	var $_category = null;

	/**
	 * Constructor
	 *
	 * @since 2.0
	 */
	function __construct()
	{
		parent::__construct();

		$mainframe = &JFactory::getApplication();

		$id = JRequest::getInt('id');
		$this->setId((int)$id);
		
		// Get the paramaters of the active menu item
		$params 	= & $mainframe->getParams();

		//get the number of events from database
		$limit       	= $mainframe->getUserStateFromRequest('com_redevent.venuecategory.limit', 'limit', $params->def('display_num', 0), 'int');
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

		$customs = $this->getCustomFields();
		$xcustoms = $this->getXrefCustomFields();
		
    //Get Events from Database
    $query = 'SELECT a.id, a.datimage, x.dates, x.enddates, x.times, x.endtimes, x.id AS xref, x.registrationend, x.id AS xref, x.maxattendees, x.maxwaitinglist, '
        . ' a.title, a.datdescription, a.created, a.registra, l.venue, l.city, l.state, l.url, x.course_credit, x.featured, l.street, l.country, '
        . ' CASE WHEN CHAR_LENGTH(x.title) THEN CONCAT_WS(\' - \', a.title, x.title) ELSE a.title END as full_title, '
        . ' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug, '
        . ' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', x.id, x.alias) ELSE x.id END as xslug, '
        . ' CASE WHEN CHAR_LENGTH(l.alias) THEN CONCAT_WS(\':\', l.id, l.alias) ELSE l.id END as venueslug '
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
        
		$query .= ' FROM #__redevent_events AS a'
        . ' INNER JOIN #__redevent_event_category_xref AS xcat ON xcat.event_id = a.id'
        . ' INNER JOIN #__redevent_categories AS c ON c.id = xcat.category_id '
        . ' INNER JOIN #__redevent_event_venue_xref AS x on x.eventid = a.id'
        . ' INNER JOIN #__redevent_venues AS l ON l.id = x.venueid'
        . ' INNER JOIN #__redevent_venue_category_xref AS vref ON vref.venue_id = l.id'
        . ' INNER JOIN #__redevent_venues_categories AS vcat ON vref.category_id = vcat.id'
        
        . ' LEFT JOIN #__redevent_groups_venues AS gv ON gv.venue_id = l.id '
        . ' LEFT JOIN #__redevent_groups_venues_categories AS gvc ON gvc.category_id = vcat.id '
        . ' LEFT JOIN #__redevent_groups_categories AS gc ON gc.category_id = c.id '
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
		$mainframe = &JFactory::getApplication();

		$user		= & JFactory::getUser();
		$gid		= (int) $user->get('aid');
		$category = & $this->getCategory();

		// Get the paramaters of the active menu item
		$params 	= & $mainframe->getParams();
		
		$acl = &UserAcl::getInstance();		
		$gids = $acl->getUserGroupsIds();
		if (!is_array($gids) || !count($gids)) {
			$gids = array(0);
		}
		$gids = implode(',', $gids);

		$task 		= JRequest::getWord('task');

		$where = array();
		
		$where[] = ' vcat.lft BETWEEN ' . $this->_db->Quote($category->lft) . ' AND ' . $this->_db->Quote($category->rgt);
		
		// First thing we need to do is to select only the requested events
		if ($task == 'archive') {
			$where[] = ' x.published = -1';
		} else {
			$where[] = ' x.published = 1 ';
		}

		// Second is to only select events assigned to category the user has access to
		$where[] = ' vcat.access <= '.$gid;
		
    //acl
		$where[] = ' (c.private = 0 OR gc.group_id IN ('.$gids.')) ';
		$where[] = ' (l.private = 0 OR gv.group_id IN ('.$gids.')) ';
		$where[] = ' (vcat.private = 0 OR vcat.private IS NULL OR gvc.group_id IN ('.$gids.')) ';
		
		/*
		 * If we have a filter, and this is enabled... lets tack the AND clause
		 * for the filter onto the WHERE clause of the content item query.
		 */
		if ($params->get('filter_text'))
		{
      $filter     = $mainframe->getUserStateFromRequest('com_redevent.venuecategory.filter', 'filter', '', 'string');
      $filter_type  = $mainframe->getUserStateFromRequest('com_redevent.venuecategory.filter_type', 'filter_type', '', 'string');

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
		if (!$this->_category) 
		{
			$query = 'SELECT *,'
					.' CASE WHEN CHAR_LENGTH(alias) THEN CONCAT_WS(\':\', id, alias) ELSE id END as slug'
					.' FROM #__redevent_venues_categories'
					.' WHERE id = '.$this->_id;
	
			$this->_db->setQuery( $query );
			$this->_category = $this->_db->loadObject();
			
			if ($this->_category->private)
			{
				$acl = &UserAcl::getInstance();
				$cats = $acl->getManagedVenuesCategories();
				if (!is_array($cats) || !in_array($this->_category->id, $cats)) {
					JError::raiseError(403, JText::_('COM_REDEVENT_ACCESS_NOT_ALLOWED'));
				}
			}			
		}
		
		return $this->_category;
	}
	
}
?>