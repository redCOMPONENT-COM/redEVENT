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

/**
 * EventList Component Venues Model
 *
 * @package Joomla
 * @subpackage EventList
 * @since		0.9
 */
class RedeventModelVenues extends JModel
{
  /**
   * limit venues to a certain category
   * @var object
   */
  var $_category;
  
	/**
	 * Venues data array
	 *
	 * @var array
	 */
	var $_data = null;

	/**
	 * Venues total
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
	
    if ($params->get('categoryid', 0)) {
      $this->setCategory($params->get('categoryid', 0));
    }
    
		//get the number of events from database
		$limit			= JRequest::getInt('limit', $params->get('display_venues_num'));
		$limitstart		= JRequest::getInt('limitstart');

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
	}

  /**
   * set the category
   * 
   * @param int id
   * @return boolean
   */
  function setCategory($id)
  {   
    $sub = ' SELECT id, lft, rgt FROM #__redevent_venues_categories WHERE id = '. $this->_db->Quote((int) $id);
    $this->_db->setQuery($sub);
    $obj = $this->_db->loadObject();
    if (!$obj) {
      JError::raiseWarning(0, JText::_('VENUE CATEGORY NOT FOUND'));
    }
    else {
      $this->_category = $obj;
      $this->_data = null;
    }
    return true;
  }
  
	/**
	 * Method to get the Venues
	 *
	 * @access public
	 * @return array
	 */
	function &getData( )
	{
		global $mainframe;

		$menu		=& JSite::getMenu();
		$item    	= $menu->getActive();
		$params		=& $menu->getParams($item->id);

		$elsettings 	=  & redEVENTHelper::config();

		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$query = $this->_buildQuery();
			$this->_data = $this->_getList( $query, $this->getState('limitstart'), $this->getState('limit') );

			$k = 0;
			for($i = 0; $i <  count($this->_data); $i++)
			{
				$venue =& $this->_data[$i];

				//Create image information
				$venue->limage = redEVENTImage::flyercreator($venue->locimage);

				//Generate Venuedescription
				if (empty ($venue->locdescription)) {
					$venue->locdescription = JText::_( 'NO DESCRIPTION' );
				} else {
					//execute plugins
					$venue->text	= $venue->locdescription;
					$venue->title 	= $venue->venue;
					JPluginHelper::importPlugin('content');
					$results = $mainframe->triggerEvent( 'onPrepareContent', array( &$venue, array(), 0 ));
					$venue->locdescription = $venue->text;
				}

				//build the url
				if(!empty($venue->url) && strtolower(substr($venue->url, 0, 7)) != "http://") {
					$venue->url = 'http://'.$venue->url;
    		    }

				//prepare the url for output
				if (strlen(htmlspecialchars($venue->url, ENT_QUOTES)) > 35) {
					$venue->urlclean = substr( htmlspecialchars($venue->url, ENT_QUOTES), 0 , 35).'...';
				} else {
					$venue->urlclean = htmlspecialchars($venue->url, ENT_QUOTES);
				}

    		    //create flag
				if ($venue->country) {
					$venue->countryimg = ELOutput::getFlag( $venue->country );
				}
				
				//create target link
				$task 	= JRequest::getVar('task', '', '', 'string');
				
				if ($task == 'archive') {
					$venue->targetlink = JRoute::_(RedeventHelperRoute::getVenueEventsRoute($venue->slug, 'archive'));
				} else {
					$venue->targetlink = JRoute::_(RedeventHelperRoute::getVenueEventsRoute($venue->slug));
				}
		
				$k = 1 - $k;
			}

		}

		return $this->_data;
	}

	/**
	 * Total nr of Venues
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
			$this->_pagination = new JPagination( $this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
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
		//check archive task
		$task 	= JRequest::getVar('task', '', '', 'string');
		if($task == 'archive') {
			$eventstate = ' AND x.published = -1';
		} else {
			$eventstate = ' AND x.published = 1';
		}
				
    $mainframe = & JFactory::getApplication();

    // Get the paramaters of the active menu item
    $params   = & $mainframe->getParams('com_redevent');
    if ($params->get('display_all_venues', 0) == 0) {
      $filter = ' AND x.eventid IS NOT NULL ';
    }
    else {
      $filter = '';
    }
    if ($this->_category) {
      $filter .= ' AND c.lft BETWEEN '. $this->_db->Quote($this->_category->lft) .' AND '. $this->_db->Quote($this->_category->rgt);
    }
		
		$acl = &UserAcl::getInstance();		
		$gids = $acl->getUserGroupsIds();
		if (!is_array($gids) || !count($gids)) {
			$gids = array(0);
		}
		$gids = implode(',', $gids);
		
		//get venues
		$query = 'SELECT v.*, v.id as venueid, COUNT( x.eventid ) AS assignedevents,'
        . ' CASE WHEN CHAR_LENGTH(v.alias) THEN CONCAT_WS(\':\', v.id, v.alias) ELSE v.id END as slug '
				. ' FROM #__redevent_venues as v'
				. ' LEFT JOIN #__redevent_event_venue_xref AS x ON v.id = x.venueid '. $eventstate
				. ' LEFT JOIN #__redevent_venue_category_xref AS xc ON xc.venue_id = v.id '
        . ' LEFT JOIN #__redevent_venues_categories AS c ON c.id = xc.category_id '
        
        . ' LEFT JOIN #__redevent_groups_venues AS gv ON gv.venue_id = v.id '
        . ' LEFT JOIN #__redevent_groups_venues_categories AS gvc ON gvc.category_id = c.id '
        
				. ' WHERE v.published = 1'
        . '   AND (v.private = 0 OR gv.group_id IN ('.$gids.')) '
        . '   AND (c.private = 0 OR c.private IS NULL OR gvc.group_id IN ('.$gids.')) '
				. $filter
				. ' GROUP BY v.id'
				. ' ORDER BY v.venue'
				;
		return $query;
	}
}
?>