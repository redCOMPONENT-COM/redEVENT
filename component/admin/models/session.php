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
 * EventList Component session Model
 *
 * @package Joomla
 * @subpackage EventList
 * @since		0.9
 */
class RedEventModelSession extends JModel
{
	/**
	 * Session id (xref)
	 *
	 * @var int
	 */
	var $_id = null;
	
	/**
	 * Event data array
	 *
	 * @var array
	 */
	var $_data = null;

	/**
	 * Categories data array
	 *
	 * @var array
	 */
	var $_categories = null;
	
	/**
	 * Xrefs custom fields data array
	 *
	 * @var array
	 */
	var $_xrefcustomfields = null;

	/**
	 * Constructor
	 *
	 * @since 0.9
	 */
	function __construct()
	{
		parent::__construct();
		
		
    $array = JRequest::getVar('cid', array(), '', 'array');
    if (count($array)) {
    	$xref = $array[0];
    }
    else {
			$xref = JRequest::getVar('xref', 0, 'request', 'int');     	
    }      
		$this->setId($xref);
	}

	/**
	 * Method to set the identifier
	 *
	 * @access	public
	 * @param	int event identifier
	 */
	function setId($id)
	{
		// Set event id and wipe data
		$this->_id	    = $id;
		$this->_data	= null;
	}
	
	/**
	 * Method to checkin/unlock the item
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	0.9
	 */
	function checkin()
	{
		if ($this->_id)
		{
			$event = & JTable::getInstance('redevent_event_venue_xref', '');
			return $event->checkin($this->_id);
		}
		return false;
	}

	/**
	 * Method to checkout/lock the item
	 *
	 * @access	public
	 * @param	int	$uid	User ID of the user checking the item out
	 * @return	boolean	True on success
	 * @since	0.9
	 */
	function checkout($uid = null)
	{
		if ($this->_id)
		{
			// Make sure we have a user id to checkout the event with
			if (is_null($uid)) {
				$user	=& JFactory::getUser();
				$uid	= $user->get('id');
			}
			// Lets get to it and checkout the thing...
			$event = & JTable::getInstance('redevent_event_venue_xref', '');
			return $event->checkout($uid, $this->_id);
		}
		return false;
	}

	/**
	 * Tests if the event is checked out
	 *
	 * @access	public
	 * @param	int	A user id
	 * @return	boolean	True if checked out
	 * @since	0.9
	 */
	function isCheckedOut( $uid=0 )
	{
		if ($this->_loadData())
		{
			if ($uid) {
				return ($this->_data->checked_out && $this->_data->checked_out != $uid);
			} else {
				return $this->_data->checked_out;
			}
		} elseif ($this->_id < 1) {
			return false;
		} else {
			RedeventError::raiseWarning( 0, 'Unable to Load Data');
			return false;
		}
	}
		
	/**
	 * Retrieve a list of venues
	 */
	public function getVenues() 
	{
		$db = JFactory::getDBO();
		$q = "SELECT id, venue
			FROM #__redevent_venues
			ORDER BY venue";
		$db->setQuery($q);
		return $db->loadObjectList();
	}
		
  /**
   * get custom fields
   *
   * @return objects array
   */
  function getXrefCustomfields()
  {
  	$xref = JRequest::getVar('xref', 0, 'request', 'int');  
    $query = ' SELECT f.* '
           . ' FROM #__redevent_fields AS f '
           . ' WHERE f.object_key = '. $this->_db->Quote("redevent.xref")
           . ' ORDER BY f.ordering '
           ;
    $this->_db->setQuery($query);
    $result = $this->_db->loadObjectList();    
  
    if (!$result) {
      return array();
    }
    $fields = array();
    $data = $this->getXref();
    foreach ($result as $c)
    {
      $field =& redEVENTHelper::getCustomField($c->type);
      $field->bind($c);
      $prop = 'custom'.$c->id;
      if ($data && isset($data->$prop)) {
      	$field->value = $data->$prop;
      } 
      $fields[] = $field;
    }
    return $fields;     
  }
  
  /**
   * return xref from request
   *
   * @return unknown
   */
  function getXref()
  {
  	$xref = $this->_id;  	
  	
  	if ($xref) 
  	{  		
			$customs = $this->_getXCustomFields();
		
    	$query = ' SELECT x.*, v.venue, r.id as recurrence_id, r.rrule, rp.count ';
			// add the custom fields
			foreach ((array) $customs as $c)
			{
				$query .= ', x.custom'. $c->id;
			}
			
  	  $query .= ' FROM #__redevent_event_venue_xref AS x '
  	       . ' LEFT JOIN #__redevent_venues AS v on v.id = x.venueid '
           . ' LEFT JOIN #__redevent_repeats AS rp on rp.xref_id = x.id '
           . ' LEFT JOIN #__redevent_recurrences AS r on r.id = rp.recurrence_id '
           ;
			
  	  $query .= ' WHERE x.id = '. $this->_db->Quote($xref);
  	  
      $this->_db->setQuery($query);
  		$object = $this->_db->loadObject();
  		$object->rrules = RedeventHelperRecurrence::getRule($object->rrule);
  	}
  	else {
      $object = JTable::getInstance('RedEvent_eventvenuexref', '');
  		$object->id    = null;
  		$object->venue = 0;
      $object->recurrence_id = 0;
      $object->rrule = '';
      $object->count = 0;
  		$object->rrules = RedeventHelperRecurrence::getRule();
  	}
  	return $object;
  }
  
  /**
   * return list of venues as options
   *
   * @return array
   */
  function getVenuesOptions()
  {
		$query = ' SELECT id AS value, '
		       . ' CASE WHEN CHAR_LENGTH(city) THEN CONCAT_WS(\' - \', venue, city) ELSE venue END as text '
  	       . ' FROM #__redevent_venues AS v'
  	       . ' ORDER BY venue, city '
  	       ;
    $this->_db->setQuery($query);
    return $this->_db->loadObjectList();    
  }

  /**
   * return list of groups as options
   *
   * @return array
   */
  function getGroupsOptions()
  {
		$query = ' SELECT id AS value, '
		       . ' name as text '
  	       . ' FROM #__redevent_groups '
  	       . ' ORDER BY name '
  	       ;
    $this->_db->setQuery($query);
    return $this->_db->loadObjectList();    
  }
  
  /**
   * save xref data
   *
   * @param array $data
   * @return boolean true on success
   */
  function savexref($data)
  {
  	$id = (int) $data['id'];

  	$object = & JTable::getInstance('RedEvent_eventvenuexref', '');
  	
  	if ($id) {
  		$object->load($id);
  	}
  	
  	if (!$object->bind($data)) {
  		$this->setError($object->getError());
  		return false;
  	}  	
  
    if (!$object->check()) {
      $this->setError($object->getError());
      return false;
    }
    
    if (!$object->store(true)) {
      $this->setError($object->getError());
      return false;
    }
        
    // we need to save the recurrence too
    $recurrence = & JTable::getInstance('RedEvent_recurrences', '');
    if (!$data['recurrenceid'])
    {
      $rrule = RedeventHelperRecurrence::parsePost($data);
      if (!empty($rrule))
      {
	      // new recurrence
	      $recurrence->rrule = $rrule;
	      if (!$recurrence->store()) 
	      {
	        $this->setError($recurrence->getError());
	        return false;        
	      }
	      
	      // add repeat record
	      $repeat = & JTable::getInstance('RedEvent_repeats', '');
	      $repeat->set('xref_id', $object->id);
	      $repeat->set('recurrence_id', $recurrence->id);
	      $repeat->set('count', 0);      
	      if (!$repeat->store()) {
	        $this->setError($repeat->getError());
	        return false;        
	      }
      }
    }
    else 
    {
      if ($data['repeat'] == 0) // only update if it's the first xref.
      {
        $recurrence->load($data['recurrenceid']);
        // reset the status
        $recurrence->ended = 0;
        // TODO: maybe add a check to have a choice between updating rrule or not...
        $rrule = RedeventHelperRecurrence::parsePost($data);
        $recurrence->rrule = $rrule;
        if (!$recurrence->store()) {
          $this->setError($recurrence->getError());
          return false;        
        }
      }
    }
    if ($recurrence->id) {
    	redEVENTHelper::generaterecurrences($recurrence->id);
    }
    
    /** roles **/
    // first remove current rows
    $query = ' DELETE FROM #__redevent_sessions_roles ' 
           . ' WHERE xref = ' . $this->_db->Quote($object->id);
    $this->_db->setQuery($query);     
    if (!$this->_db->query()) {
    	$this->setError($this->_db->getErrorMsg());
    	return false;
    }
    
    // then recreate them if any
    foreach ((array) $data['rrole'] as $k => $r)
    {    	
    	if (!($data['rrole'][$k] && $data['urole'][$k])) {
    		continue;
    	}
      $new = & JTable::getInstance('RedEvent_sessions_roles', '');
      $new->set('xref',    $object->id);
      $new->set('role_id', $r);
      $new->set('user_id', $data['urole'][$k]);
      if (!($new->check() && $new->store())) {
      	$this->setError($new->getError());
      	return false;
      }
    }
    /** roles END **/
    
    /** prices **/
    // first remove current rows
    $query = ' DELETE FROM #__redevent_sessions_pricegroups ' 
           . ' WHERE xref = ' . $this->_db->Quote($object->id);
    $this->_db->setQuery($query);     
    if (!$this->_db->query()) {
    	$this->setError($this->_db->getErrorMsg());
    	return false;
    }
    
    // then recreate them if any
    foreach ((array) $data['pricegroup'] as $k => $r)
    {    	
    	if (!($data['pricegroup'][$k])) {
    		continue;
    	}
      $new = & JTable::getInstance('RedEvent_sessions_pricegroups', '');
      $new->set('xref',    $object->id);
      $new->set('pricegroup_id', $r);
      $new->set('price', $data['price'][$k]);
      if (!($new->check() && $new->store())) {
      	$this->setError($new->getError());
      	return false;
      }
    }
    /** prices END **/
    
    return $object->id;
  }
  
  /**
   * remove xref if there is no attendees
   *
   * @param int xref_id
   * @return boolean result true on success
   */
  function removexref($id)
  {
  	// do not delete xref if there are attendees
  	$query = ' SELECT COUNT(*) FROM #__redevent_register WHERE xref = '. $this->_db->Quote((int)$id);
  	$this->_db->setQuery($query);
  	if ($this->_db->loadResult()) {
  		$this->setError(JText::_('COM_REDEVENT_CANNOT_DELETE_XREF_HAS_REGISTRATIONS'));
  		return false;
  	}
  	
  	
  	$q = "DELETE FROM #__redevent_event_venue_xref WHERE id =". $this->_db->Quote((int)$id);
    $this->_db->setQuery($q);
    if (!$this->_db->query()) {
      $this->setError(JText::_('COM_REDEVENT_DB_ERROR_DELETING_XREF'));
      return false;
    }
    
    // delete corresponding roles
    $q = "DELETE FROM #__redevent_sessions_roles WHERE xref =". $this->_db->Quote((int)$id);
    $this->_db->setQuery($q);
    if (!$this->_db->query()) {
      $this->setError(JText::_('COM_REDEVENT_DB_ERROR_DELETING_XREF_ROLES'));
      return false;
    }
    
    // delete corresponding record in repeats table in case of recurrences
    $q = "DELETE FROM #__redevent_repeats WHERE xref_id =". $this->_db->Quote((int)$id);
    $this->_db->setQuery($q);
    if (!$this->_db->query()) {
      $this->setError(JText::_('COM_REDEVENT_DB_ERROR_DELETING_XREF_REPEAT'));
      return false;
    }
    
    return true;
  }
  
  /**
   * returns all custom fields for xrefs
   * 
   * @return array
   */
  function _getXCustomFields()
  {
  	if (empty($this->_xrefcustomfields))
  	{
	  	$query = ' SELECT f.id, f.name, f.in_lists, f.searchable '
	  	       . ' FROM #__redevent_fields AS f'
	  	       . ' WHERE f.published = 1'
	  	       . '   AND f.object_key = '. $this->_db->Quote('redevent.xref')
	  	       . ' ORDER BY f.ordering ASC '
	  	       ;
	  	$this->_db->setQuery($query);
	  	$this->_xrefcustomfields = $this->_db->loadObjectList();
  	}
  	return $this->_xrefcustomfields;
  }
  
  function getRolesOptions()
  {
  	$query = ' SELECT id AS value, name AS text ' 
  	       . ' FROM #__redevent_roles ' 
  	       . ' ORDER BY ordering ASC ';
  	$this->_db->setQuery($query);
  	$res = $this->_db->loadObjectList();
  	return $res;
  }
  
  function getPricegroupsOptions()
  {
  	$query = ' SELECT id AS value, name AS text ' 
  	       . ' FROM #__redevent_pricegroups ' 
  	       . ' ORDER BY ordering ASC ';
  	$this->_db->setQuery($query);
  	$res = $this->_db->loadObjectList();
  	return $res;
  }
  
  function getSessionRoles()
  {
  	$query = ' SELECT sr.* ' 
  	       . ' FROM #__redevent_sessions_roles AS sr ' 
  	       . ' INNER JOIN #__redevent_roles AS r ON r.id = sr.role_id '
  	       . ' WHERE sr.xref = ' . $this->_db->Quote($this->_id)
  	       . ' ORDER BY r.ordering '
  	       ;
  	$this->_db->setQuery($query);
  	$res = $this->_db->loadObjectList();
  	return $res;
  }
  
  function getSessionPrices()
  {
  	$query = ' SELECT r.* ' 
  	       . ' FROM #__redevent_sessions_pricegroups AS r ' 
  	       . ' INNER JOIN #__redevent_pricegroups AS pg ON pg.id = r.pricegroup_id '
  	       . ' WHERE xref = ' . $this->_db->Quote($this->_id)
  	       . ' ORDER BY pg.ordering ';
  	$this->_db->setQuery($query);
  	$res = $this->_db->loadObjectList();
  	return $res;
  }
}
