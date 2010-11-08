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
 * redEVENT Component Eventhelper Model
 *
 * @package Joomla
 * @subpackage redEVENT
 * @since		2.0
 */
class RedeventModelEventhelper extends JModel
{
	/**
	 * event data caching
	 *
	 * @var array
	 */
	protected $_event = null;
	
	protected $_id   = null;
	protected $_xref = null;

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
	 * Method to set the details id
	 *
	 * @access	public
	 * @param	int	details ID number
	 */

	function setId($id)
	{
		// Set new details ID and wipe data
		$this->_id			= $id;
		$this->_event		= null;
	}
	
	/**
	 * Method to set the details id
	 *
	 * @access	public
	 * @param	int	details ID number
	 */

	function setXref($xref)
	{
		// Set new details ID and wipe data
		$this->_xref  = $xref;
		$this->_event = null;
	}

	/**
	 * Method to get event data for the Detailsview
	 *
	 * @access public
	 * @return array
	 * @since 0.9
	 */
	function getData()
	{
		/*
		 * Load the Category data
		 */
		if ($this->_loadDetails())
		{
			$user	= & JFactory::getUser();

			// Is the category published?
			if (!count($this->_event->categories))
			{
				RedeventError::raiseError( 404, JText::_("CATEGORY NOT PUBLISHED") );
			}

			// Do we have access to each category ?
			foreach ($this->_event->categories as $cat)
			{
				if ($cat->access > $user->get('aid'))
				{
					JError::raiseError( 403, JText::_("ALERTNOTAUTH") );
				}
			}

		}

		return $this->_event;
	}

	/**
	 * Method to load required data
	 *
	 * @access	private
	 * @return	array
	 * @since	0.9
	 */
	function _loadDetails()
	{
		if (empty($this->_event))
		{
			$user	= & JFactory::getUser();
			// Get the WHERE clause
			$where	= $this->_buildDetailsWhere();

			$query = 'SELECT a.*, a.id AS did, x.id AS xref, a.title, a.datdescription, a.meta_keywords, a.meta_description, a.datimage, a.registra, a.unregistra, a.summary, ' 
					. ' x.*, a.created_by, a.redform_id, x.maxwaitinglist, x.maxattendees, a.juser, a.show_names, a.showfields, '
					. ' a.submission_type_email, a.submission_type_external, a.submission_type_phone, a.review_message, '
					. ' v.venue, v.city AS location, v.country, v.locimage, v.street, v.plz, v.state, v.locdescription as venue_description, v.map, '
					. ' v.city, v.latitude, v.longitude, '
					. ' u.name AS creator_name, u.email AS creator_email, '
					. ' f.formname, '
					. " a.confirmation_message, x.course_price, IF (x.course_credit = 0, '', x.course_credit) AS course_credit, a.course_code, a.submission_types, c.catname, c.published, c.access,"
	        . ' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug, '
	        . ' CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as categoryslug, '
	        . ' CASE WHEN CHAR_LENGTH(v.alias) THEN CONCAT_WS(":", v.id, v.alias) ELSE v.id END as venueslug '
					. ' FROM #__redevent_events AS a'
					. ' LEFT JOIN #__redevent_event_venue_xref AS x ON x.eventid = a.id'
					. ' LEFT JOIN #__redevent_venues AS v ON x.venueid = v.id'
	        . ' LEFT JOIN #__redevent_event_category_xref AS xcat ON xcat.event_id = a.id'
	        . ' LEFT JOIN #__redevent_categories AS c ON c.id = xcat.category_id'
	        . ' LEFT JOIN #__rwf_forms AS f ON f.id = a.redform_id '
					. ' LEFT JOIN #__users AS u ON a.created_by = u.id '
					. $where
					;
    	$this->_db->setQuery($query);
			$this->_event = $this->_db->loadObject();
			if ($this->_event) {
        $this->_details = $this->_getEventCategories($this->_event);			
				$this->_details->attachments = REAttach::getAttachments('event'.$this->_details->did, $user->get('aid'));		
			}
			return (boolean) $this->_event;
		}
		return true;
	}	
 
	/**
	 * Method to build the WHERE clause of the query to select the details
	 *
	 * @access	private
	 * @return	string	WHERE clause
	 * @since	0.9
	 */
	function _buildDetailsWhere()
	{
		$where = '';
		if ($this->_xref) $where = ' WHERE x.id = '.$this->_xref;
		else if ($this->_id) $where = ' WHERE x.eventid = '.$this->_id;

		return $where;
	}
	
  /**
   * adds categories property to event row
   *
   * @param object event
   * @return object
   */
  function _getEventCategories($row)
  {
  	$query =  ' SELECT c.id, c.catname, c.access, c.image, '
			  	. ' CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as slug '
			  	. ' FROM #__redevent_categories as c '
			  	. ' INNER JOIN #__redevent_event_category_xref as x ON x.category_id = c.id '
			  	. ' WHERE c.published = 1 '
			  	. '   AND x.event_id = ' . $this->_db->Quote($row->did)
			  	. ' ORDER BY c.ordering'
			  	;
  	$this->_db->setQuery( $query );

  	$row->categories = $this->_db->loadObjectList();

    return $row;   
  }
  
  function getPlacesLeft()
  {
  	$session = &$this->getData();
		$q = ' SELECT COUNT(r.id) AS total '
		   . ' FROM #__redevent_register AS r '
		   . ' WHERE r.xref = '. $this->_db->Quote($this->_xref)
		   . '   AND r.confirmed = 1 '
		   . '   AND r.waitinglist = 0 '
		   . ' GROUP BY r.waitinglist '
		   ;
    $this->_db->setQuery($q);
    $res = $this->_db->loadResult();
    $left = $session->maxattendees - $res;
    return ($left > 0 ? $left : 0);
  }
  
  function getWaitingPlacesLeft()
  {
  	$session = &$this->getData();
		$q = ' SELECT COUNT(r.id) AS total '
		   . ' FROM #__redevent_register AS r '
		   . ' WHERE r.xref = '. $this->_db->Quote($this->_xref)
		   . '   AND r.confirmed = 1 '
		   . '   AND r.waitinglist = 1 '
		   . ' GROUP BY r.waitinglist '
		   ;
    $this->_db->setQuery($q);
    $res = $this->_db->loadResult();
    $left = $session->maxwaitinglist - $res;
    return ($left > 0 ? $left : 0);  	
  }
}
?>