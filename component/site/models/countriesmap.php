<?php
/**
 * @version 1.1 $Id: view.html.php 407 2007-09-21 16:03:39Z schlu $
 * @package Joomla
 * @subpackage EventList
 * @copyright (C) 2005 - 2008 Christoph Lukes
 * @license GNU/GPL, see LICENSE.php
 * EventList is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * EventList is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with EventList; if not, write to the Free Software
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
class RedeventModelCountriesmap extends JModel
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
      
			$this->_data = $cache->call( array( 'RedeventModelCountriesmap', '_getResultList' ), $query );
      
			$countrycoords = redEVENTHelperCountries::getCountrycoordArray();
			
			$k = 0;
			for($i = 0; $i <  count($this->_data); $i++)
			{
				$country =& $this->_data[$i];
								
				$country->flag = ELOutput::getFlag( $country->iso2 );
				$country->flagurl = ELOutput::getFlagUrl( $country->iso2 );
				$country->latitude = $countrycoords[$country->iso2][0];
        $country->longitude = $countrycoords[$country->iso2][1];
				
				//create target link
				$country->targetlink = JRoute::_(JURI::base().'index.php?option=com_redevent&view=countryevents&filter_country='.$country->iso2);
		
				$k = 1 - $k;
			}

		}
		
		return $this->_data;
	}
	
	function _getResultList($query)
	{
		$db = & JFactory::getDBO();
		
		$db->setQuery($query);

		return ($db->loadObjectList());
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
		
		//get categories
		$query = 'SELECT c.*, COUNT( x.id ) AS assignedevents,'
				. ' CONCAT_WS(\':\', c.id, c.iso2) as slug'
				. ' FROM #__redevent_countries as c'
				. ' INNER JOIN #__redevent_venues as v ON v.country = c.iso2'
				. ' INNER JOIN #__redevent_event_venue_xref AS x ON x.venueid = v.id'
				. ' WHERE v.published = 1'
        . '   AND v.latitude <> 0 AND v.longitude <> 0 '
				. $eventstate
				. ' GROUP BY c.id'
        . ' ORDER BY c.name'
				;

		return $query;
	}
}
?>