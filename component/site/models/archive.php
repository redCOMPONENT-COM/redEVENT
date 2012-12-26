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
 * Redevents Component events list Model
 *
 * @package Joomla
 * @subpackage Redevent
 * @since		2.5
 */
class RedeventModelArchive extends RedeventModelBaseEventList
{
	
	function __construct()
	{		
		parent::__construct();
		
		$mainframe = & JFactory::getApplication();
		
		$filter 		  = $mainframe->getUserStateFromRequest('com_redevent.simplelist.filter', 'filter', '', 'string');
		$filter_type 	= $mainframe->getUserStateFromRequest('com_redevent.simplelist.filter_type', 'filter_type', '', 'string');
    $customs      = $mainframe->getUserStateFromRequest('com_redevent.simplelist.filter_customs', 'filtercustom', array(), 'array');
    
    // Get the filter request variables
    $this->setState('filter_order',     JRequest::getCmd('filter_order', 'x.dates'));
    $this->setState('filter_order_dir', JRequest::getCmd('filter_order_Dir', $mainframe->getParams('com_redevent')->get('archive_ordering', 'ASC')));
    
		$this->setState('filter',         $filter);
		$this->setState('filter_type',    $filter_type);
		$this->setState('filter_customs', $customs);
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
		$gid		= max($user->getAuthorisedViewLevels());

		// Get the paramaters of the active menu item
		$params 	= & $mainframe->getParams();

		$task 		= JRequest::getWord('task');
		
		$where = array();
		$where[] = ' x.published = -1';
				
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
		
		// more filters
		if ($state = JRequest::getVar('state', '', 'request', 'string')) {
			$where[] = ' STRCMP(l.state, '.$this->_db->Quote($state).') = 0 ';
		}		
		if ($country = JRequest::getVar('country', '', 'request', 'string')) {
			$where[] = ' STRCMP(l.country, '.$this->_db->Quote($country).') = 0 ';
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
    
    $day_limit = trim($params->get('display_limit')) == '' ? false : (int) $params->get('display_limit');
    if ($day_limit) {
			$limit = strftime('%Y-%m-%d %H:%M', strtotime("- $day_limit days"));
			$where[] = '(CASE WHEN x.times THEN CONCAT(x.dates," ",x.times) ELSE x.dates END) > '.$this->_db->Quote($limit);
    }
		
		return ' WHERE '.implode(' AND ', $where);
	}
	
}
