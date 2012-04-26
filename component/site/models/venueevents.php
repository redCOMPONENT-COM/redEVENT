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
 * EventList Component Venueevents Model
 *
 * @package Joomla
 * @subpackage redEVENT
 * @since		0.9
 */
class RedeventModelVenueevents extends RedeventModelBaseEventList
{
	/**
	 * venue data array
	 *
	 * @var array
	 */
	var $_venue = null;

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
		
    $customs      = $mainframe->getUserStateFromRequest('com_redevent.categoryevents.filter_customs', 'filtercustom', array(), 'array');
		$this->setState('filter_customs', $customs);
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

	/**
	 * Method to build the WHERE clause
	 *
	 * @access private
	 * @return array
	 */
	function _buildWhere( )
	{
		$mainframe = &JFactory::getApplication();
		
		$user		=& JFactory::getUser();
		$gid		= (int) max($user->getAuthorisedViewLevels());

		// Get the paramaters of the active menu item
		$params 	= & $mainframe->getParams('com_redevent');
		
		$task 		= JRequest::getWord('task');

		$where = array();
		
		// First thing we need to do is to select only the requested events
		if ($task == 'archive') {
			$where[] = ' x.published = -1';
		} else {
			$where[] = ' x.published = 1';
		}
		
		/* Check if a venue ID is set */
		if ($this->_id > 0) $where[] = ' x.venueid = '.$this->_id;
		
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
					
					case 'type' :
						$where[] = ' LOWER( c.catname ) LIKE '.$filter;
						break;
				}
			}
		}
	    
		if ($ev = $this->getState('filter_event')) 
		{
			$where[] = 'a.id = '.$this->_db->Quote($ev);
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
	 * Method to get the Venue
	 *
	 * @access public
	 * @return array
	 */
	function getVenue( )
	{
		$user		= & JFactory::getUser();
		//Location holen
		$query = 'SELECT *, v.id AS venueid, '
        . ' CASE WHEN CHAR_LENGTH(v.alias) THEN CONCAT_WS(\':\', v.id, v.alias) ELSE v.id END as slug '
				. ' FROM #__redevent_venues AS v'
				. ' WHERE v.id = '.$this->_id;

		$this->_db->setQuery( $query );
		$_venue = $this->_db->loadObject();
			
		if ($_venue->private)
		{
			$acl = &UserAcl::getInstance();
			$cats = $acl->getManagedVenues();
			if (!is_array($cats) || !in_array($_venue->id, $cats)) {
				JError::raiseError(403, JText::_('COM_REDEVENT_ACCESS_NOT_ALLOWED'));
			}
		}			
		$_venue->attachments = REAttach::getAttachments('venue'.$_venue->id, max($user->getAuthorisedViewLevels()));		

		return $_venue;
	}
}
