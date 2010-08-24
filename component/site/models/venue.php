<?php
/**
 * @version 1.0 $Id: view.html.php 360 2009-06-30 07:49:16Z julien $
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
 * EventList Component Editvenue Model
 *
 * @package Joomla
 * @subpackage EventList
 * @since		0.9
 */
class RedeventModelVenue extends JModel
{
	/**
	 * Venue data in Venue array
	 *
	 * @var array
	 */
	var $_venue = null;

	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	function __construct()
	{
		parent::__construct();

		$id = JRequest::getInt('id');
		$this->setId($id);
	}

	/**
	 * Method to set the Venue id
	 *
	 * @access	public
	 * @param	int	Venue ID number
	 */
	function setId($id)
	{
		// Set new venue ID
		$this->_id			= $id;
	}

	/**
	 * Logic to get the venue
	 *
	 * @return array
	 */
	function &getData(  )
	{
		global $mainframe;
		
		if (empty($this->_venue))
		{
			if ($this->_id) 
			{
				// Load the Event data
	      $query = ' SELECT v.id, v.venue, v.url, v.street, v.plz, v.city, v.state, v.country, v.locdescription, v.locimage, v.latitude, v.longitude, '
				  . ' COUNT( a.id ) AS assignedevents,'
	        . ' CASE WHEN CHAR_LENGTH(v.alias) THEN CONCAT_WS(\':\', v.id, v.alias) ELSE v.id END as slug'
	        . ' FROM #__redevent_venues as v'
	        . ' LEFT JOIN #__redevent_event_venue_xref AS a ON a.venueid = v.id AND a.published = 1'
	        . ' WHERE v.id = ' . $this->_db->Quote($this->_id)
	        . ' GROUP BY v.id '
	        ;
				$this->_db->setQuery($query);
				$this->_venue = $this->_db->loadObject();
				
				$venue = $this->_db->loadObject();
	
				//Create image information
				$venue->limage = redEVENTImage::flyercreator($venue->locimage);
	
				//Generate Venuedescription
				if (empty ($venue->locdescription)) {
					$venue->locdescription = JText::_( 'NO DESCRIPTION' );
				} else {
					//execute plugins
					$venue->text  = $venue->locdescription;
					$venue->title   = $venue->venue;
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
				$venue->targetlink = JRoute::_(RedeventHelperRoute::getVenueEventsRoute($venue->slug));
	
				$venue->categories = $this->_getVenueCategories($this->_id);
				
				$this->_venue = $venue;
			}
		}
		
		return $this->_venue;
	}

	/**
	 * logic to get the venue
	 *
	 * @access private
	 * @return array
	 */
	function _loadVenue( )
	{
		if (empty($this->_venue)) {

			$this->_venue =& JTable::getInstance('redevent_venues', '');
			$this->_venue->load( $this->_id );

			return $this->_venue;
		}
		return true;
	}
	

  /**
   * adds categories property to event row
   *
   * @param object event
   * @return object
   */
  function _getVenueCategories($venueid)
  {
    $query =  ' SELECT c.id, c.name, c.access, '
          . ' CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as slug '
          . ' FROM #__redevent_venues_categories as c '
          . ' INNER JOIN #__redevent_venue_category_xref as x ON x.category_id = c.id '
          . ' WHERE c.published = 1 '
          . '   AND x.venue_id = ' . $this->_db->Quote($venueid)
          . ' ORDER BY c.lft'
          ;
    $this->_db->setQuery( $query );

    $rows = $this->_db->loadObjectList();

    return $rows;   
  }
}
?>