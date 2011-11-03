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

		$mainframe = &JFactory::getApplication();

		$id = JRequest::getInt('id');
		$this->setId((int)$id);
		// for the toggles
		$this->setState('filter_category', $this->_id);
		
		// Get the paramaters of the active menu item
		$params 	= & $mainframe->getParams();

		//get the number of events from database
		$limit       	= $mainframe->getUserStateFromRequest('com_redevent.categoryevents.limit', 'limit', $params->def('display_num', 0), 'int');
		$limitstart		= JRequest::getInt('limitstart');

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);

		// Get the filter request variables
		$this->setState('filter_order',     JRequest::getCmd('filter_order', 'x.dates'));
		$this->setState('filter_order_dir', JRequest::getCmd('filter_order_Dir', 'ASC'));
		
    $customs      = $mainframe->getUserStateFromRequest('com_redevent.categoryevents.filter_customs', 'filtercustom', array(), 'array');
		$this->setState('filter_customs', $customs);
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
		$xcustoms = $this->getXrefCustomFields();
		
		$acl = &UserAcl::getInstance();
		$gids = $acl->getUserGroupsIds();
		if (!is_array($gids) || !count($gids)) {
			$gids = array(0);
		}
		$gids = implode(',', $gids);

    //Get Events from Database
    $query = 'SELECT a.id, a.datimage, x.dates, x.enddates, x.times, x.endtimes, '
        . ' x.id AS xref, x.registrationend, x.id AS xref, x.maxattendees, '
        . ' x.maxwaitinglist, x.featured, x.icaldetails, x.icalvenue, x.title as session_title, '
        . ' CASE WHEN CHAR_LENGTH(x.title) THEN CONCAT_WS(\' - \', a.title, x.title) ELSE a.title END as full_title, '
        . ' a.title, a.datdescription, a.created, a.registra, l.venue, l.city, l.state, l.url, c.catname, c.id AS catid, a.summary, x.course_credit, '
        . ' l.street, l.country, '
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
    $query .= ' FROM #__redevent_events AS a'
        . ' INNER JOIN #__redevent_event_venue_xref AS x on x.eventid = a.id'
        . ' INNER JOIN #__redevent_venues AS l ON l.id = x.venueid'
        . '  LEFT JOIN #__redevent_venue_category_xref AS xvcat ON l.id = xvcat.venue_id'
        . '  LEFT JOIN #__redevent_venues_categories AS vc ON xvcat.category_id = vc.id'
        . ' INNER JOIN #__redevent_event_category_xref AS xcat ON xcat.event_id = a.id'
        . ' INNER JOIN #__redevent_categories AS c ON c.id = xcat.category_id'
        // acl
        . ' LEFT JOIN #__redevent_groups_venues AS gv ON gv.venue_id = l.id AND gv.group_id IN ('.$gids.')'
        . ' LEFT JOIN #__redevent_groups_venues_categories AS gvc ON gvc.category_id = vc.id AND gvc.group_id IN ('.$gids.')'
        . ' LEFT JOIN #__redevent_groups_categories AS gc ON gc.category_id = c.id AND gc.group_id IN ('.$gids.')'
		    ;
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
	
		if (preg_match("/field([0-9]+)/", $filter_order, $regs)) {
			$filter_order = 'c'. $regs[1] .'.value';
		}
		
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
				}
			}
		}
		$where[] = ' (l.private = 0 OR gv.id IS NOT NULL) ';
		$where[] = ' (c.private = 0 OR gc.id IS NOT NULL) ';
		$where[] = ' (vc.private = 0 OR vc.private IS NULL OR gvc.id IS NOT NULL) ';
	
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
    
		if ($ev = $this->getState('filter_event')) 
		{
			$where[] = 'a.id = '.$this->_db->Quote($ev);
		}
		
    $customs = $this->getState('filter_customs');	
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
			$user		= & JFactory::getUser();
			$query = 'SELECT *,'
					.' CASE WHEN CHAR_LENGTH(alias) THEN CONCAT_WS(\':\', id, alias) ELSE id END as slug'
					.' FROM #__redevent_categories'
					.' WHERE id = '.$this->_id;
	
			$this->_db->setQuery( $query );
			$this->_category = $this->_db->loadObject();
		
			if ($this->_category->private)
			{
				$acl = &UserAcl::getInstance();
				$cats = $acl->getManagedCategories();
				if (!is_array($cats) || !in_array($this->_category->id, $cats)) {
					JError::raiseError(403, JText::_('COM_REDEVENT_ACCESS_NOT_ALLOWED'));
				}
			}
			$this->_category->attachments = REAttach::getAttachments('category'.$this->_category->id, $user->get('aid'));		
		}
		
		return $this->_category;
	}
	
}
?>