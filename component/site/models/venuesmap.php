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
 * @subpackage EventList
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

		global $mainframe;

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
		global $mainframe;

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
				if (empty ($venue->locdescription)) {
					$venue->locdescription = JText::_( 'NO DESCRIPTION' );
				} else {
					//execute plugins
					$venue->text	= $venue->locdescription;
					$venue->title 	= $venue->venue;
					JPluginHelper::importPlugin('content');
					$results = $mainframe->triggerEvent( 'onPrepareContent', array( &$venue, &$params, 0 ));
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
				$venue->targetlink = JRoute::_('index.php?view=venueevents&id='.$venue->slug);
		
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
    $customs = $app->getUserState('com_redevent.venuesmap.customs');
    $params = $app->getParams();
        
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
						;
		if ($params->get('show_empty_venues', 0)) {
		  $query .= ' LEFT JOIN #__redevent_event_venue_xref AS x ON x.venueid = v.id'. $eventstate;
		}
		else {
      $query .= ' INNER JOIN #__redevent_event_venue_xref AS x ON x.venueid = v.id'. $eventstate;			
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
			$query .= ' INNER JOIN #__redevent_venue_category_xref AS xvcat ON xvcat.venue_id = v.id '
              . ' INNER JOIN #__redevent_venues_categories AS vcat ON vcat.id = xvcat.category_id '
							. ' INNER JOIN #__redevent_venues_categories AS top ON vcat.lft BETWEEN top.lft AND top.rgt '
							;
		}
		foreach ((array) $customs as $key => $custom)
		{
			if ($custom != '') {
			  $query .= ' LEFT JOIN #__redevent_fields_values AS custom'.$key.' ON custom'.$key.'.object_id = x.eventid AND custom'.$key.'.field_id = ' . $this->_db->Quote($key);
			}
		}
		// where
		$query .= ' WHERE v.published = 1 '
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
      if ($custom != '') {
      	if (is_array($custom)) {
      		$custom = implode("/n", $custom);
      	}
        $query .= ' AND custom'.$key.'.value LIKE ' . $this->_db->Quote('%'.$custom.'%');
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
	
	function getCountries()
	{
		$venues = $this->getData();
		$countries = array();
		foreach ((array)$venues AS $v)
		{
			if (!in_array($v->country, $countries)) {
				$countries[] = $this->_db->Quote($v->country);
			}
		}
		if (!count($countries)) {
			return array();
		}
		
		$query = ' SELECT * FROM #__redevent_countries WHERE iso2 IN (' . implode(', ', $countries) . ') ';
		$this->_db->setQuery($query);
		$rows = $this->_db->loadObjectList();
		
		
	  $countrycoords = redEVENTHelper::getCountrycoordArray();
      
	  for($i = 0; $i <  count($rows); $i++)
	  {
	  	$country =& $rows[$i];

	  	$country->flag = ELOutput::getFlag( $country->iso2 );
	  	$country->flagurl = ELOutput::getFlagUrl( $country->iso2 );
	  	$country->latitude = $countrycoords[$country->iso2][0];
	  	$country->longitude = $countrycoords[$country->iso2][1];
	  }
		return $rows;
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
?>