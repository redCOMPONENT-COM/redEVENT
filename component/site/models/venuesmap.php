<?php
/**
 * @version 1.0 $Id: venues.php 321 2009-06-25 09:26:36Z julien $
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
 * EventList Component Venuesmap Model
 *
 * @package Joomla
 * @subpackage redEVENT
 * @since		0.9
 */
class RedEventModelVenuesmap extends JModel
{
	/**
	 * Venues data array
	 *
	 * @var array
	 */
	var $_data = null;

	/**
	 * Constructor
	 *
	 * @since 0.9
	 */
	function __construct()
	{
		parent::__construct();

		$mainframe = &JFactory::getApplication();

		// Get the paramaters of the active menu item
		$params 	= & $mainframe->getParams('com_redevent');
	}

	/**
	 * Method to get the Venues
	 *
	 * @access public
	 * @return array
	 */
	function &getData( )
	{
		$mainframe = &JFactory::getApplication();

		$menu		=& JSite::getMenu();
		$item    	= $menu->getActive();
		$params		=& $menu->getParams($item->id);

		$elsettings 	=  & redEVENTHelper::config();

		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$query = $this->_buildQuery();
			
      // Get a reference to the global cache object.
      $cache = & JFactory::getCache('redevent');
      
      $this->_data = $cache->call( array( 'RedeventModelVenuesmap', '_getResultList' ), $query );

			$k = 0;
			for($i = 0; $i <  count($this->_data); $i++)
			{
				$venue =& $this->_data[$i];
								
        //Create image information
        $venue->limage = redEVENTImage::flyercreator($venue->locimage);

				//Generate Venuedescription
				if (!empty ($venue->locdescription)) {
					//execute plugins
					$venue->locdescription = JHTML::_('content.prepare', $venue->locdescription);
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
					$venue->countryimg = REOutput::getFlag( $venue->country );
				}
				
				//create target link
				$venue->targetlink = JRoute::_(RedeventHelperRoute::getVenueEventsRoute($venue->slug));
		
				$k = 1 - $k;
			}

		}

		return $this->_data;
	}
	
	/**
	 * Build the query
	 *
	 * @access private
	 * @return string
	 */
	function _buildQuery()
	{
		$app = & JFactory::getApplication();
    $vcat = $app->getUserState('com_redevent.venuesmap.vcat');
    $cat = $app->getUserState('com_redevent.venuesmap.cat');
    $customs = $app->getUserState('com_redevent.venuesmap.filter_customs');
    
    $params = $app->getParams();
        
		$acl = &UserAcl::getInstance();		
		$gids = $acl->getUserGroupsIds();
		if (!is_array($gids) || !count($gids)) {
			$gids = array(0);
		}
		$gids = implode(',', $gids);
		
		//check archive task
		$task 	= JRequest::getVar('task', '', '', 'string');
		if($task == 'archive') {
			$eventstate = ' AND x.published = -1';
		} else {
			$eventstate = ' AND x.published = 1';
		}
		
		//get events
		$query = 'SELECT v.*, COUNT(x.id) AS assignedevents,'
						. ' CASE WHEN CHAR_LENGTH(v.alias) THEN CONCAT_WS(\':\', v.id, v.alias) ELSE v.id END as slug'
						. ' FROM #__redevent_venues as v'
						. ' LEFT JOIN #__redevent_venue_category_xref AS xvcat ON xvcat.venue_id = v.id '
            . ' LEFT JOIN #__redevent_venues_categories AS vcat ON vcat.id = xvcat.category_id '        
            . ' LEFT JOIN #__redevent_groups_venues AS gv ON gv.venue_id = v.id '
            . ' LEFT JOIN #__redevent_groups_venues_categories AS gvc ON gvc.category_id = vcat.id '
						;
		if ($params->get('show_empty_venues', 0)) {
		  $query .= ' LEFT JOIN #__redevent_event_venue_xref AS x ON x.venueid = v.id'. $eventstate;
		  $query .= ' LEFT JOIN #__redevent_events AS e ON x.eventid = e.id';
		}
		else {
      $query .= ' INNER JOIN #__redevent_event_venue_xref AS x ON x.venueid = v.id'. $eventstate;	
		  $query .= ' INNER JOIN #__redevent_events AS e ON x.eventid = e.id';		
		}
	
    if ($cat)
    {
      $query .= ' INNER JOIN #__redevent_event_category_xref AS xcat ON xcat.event_id = x.eventid '
              . ' INNER JOIN #__redevent_categories AS cat ON cat.id = xcat.category_id '
              . ' INNER JOIN #__redevent_categories AS topcat ON cat.lft BETWEEN topcat.lft AND topcat.rgt '
              ;
    }
		if ($vcat)
		{
			$query .= ' INNER JOIN #__redevent_venues_categories AS top ON vcat.lft BETWEEN top.lft AND top.rgt '
							;
		}
		// where
		$query .= ' WHERE v.published = 1 '
		        . '   AND (v.private = 0 OR gv.group_id IN ('.$gids.')) '
		        . '   AND (vcat.private = 0 OR vcat.private IS NULL OR gvc.group_id IN ('.$gids.')) '
		        ;
		        
    if ($cat)
    {
      $query .= ' AND topcat.id = ' . $this->_db->Quote($cat);
    }   
	  if ($vcat)
    {
      $query .= ' AND top.id = ' . $this->_db->Quote($vcat);
    }		
    foreach ((array) $customs as $key => $custom)
    {
      if ($custom != '' ) {
      	if (is_array($custom)) {
      		$custom = implode("/n", $custom);
      	}
        $query .= ' AND custom'.$key.' LIKE ' . $this->_db->Quote('%'.$custom.'%');
      }
    }
    
		$query .= ' GROUP BY v.id'
						. ' ORDER BY v.venue'
						;

		return $query;
	}
	
  /**
   * used by the caching function
   *
   * @param string $query
   * @return array
   */
  function _getResultList($query)
  {
    $db = & JFactory::getDBO();
    
    $db->setQuery($query);

    return ($db->loadObjectList());
  }
	
  /**
   * get venues countries
   * @return array
   */
	function getCountries()
	{
		$venues = $this->getData();
		$countries = array();
		foreach ((array)$venues AS $v)
		{			
			$countries[] = $v->country;
		}
		if (!count($countries)) {
			return array();
		}
		$countries = array_unique($countries);
		
	  $countrycoords = redEVENTHelperCountries::getCountrycoordArray();

	  $res = array();
	  foreach ($countries as $c)
	  {
	  	$country = new stdclass();
	  	$country->name      = redEVENTHelperCountries::getCountryName($c);
	  	$country->flag      = redEVENTHelperCountries::getCountryFlag($c);
	  	$country->flagurl   = redEVENTHelperCountries::getIsoFlag($c);
	  	$country->latitude  = redEVENTHelperCountries::getLatitude($c);
	  	$country->longitude = redEVENTHelperCountries::getLongitude($c);
	  	$res[] = $country;
	  }
		return $res;
	}
	
	function getCustomFilters()
	{
		$query = ' SELECT f.* FROM #__redevent_fields AS f '
           . ' WHERE f.published = 1 AND f.searchable = 1 AND f.object_key = '. $this->_db->Quote("redevent.event")
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
	
}
