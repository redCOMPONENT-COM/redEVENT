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
 * EventList Component Event Model
 *
 * @package Joomla
 * @subpackage EventList
 * @since		0.9
 */
class RedEventModelEvent extends JModel
{
	/**
	 * Event id
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

		$cid = JRequest::getVar( 'cid', array(0), '', 'array' );
		JArrayHelper::toInteger($cid, array(0));
		$this->setId($cid[0]);
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
	 * Logic for the event edit screen
	 *
	 */
	function &getData()
	{

		if ($this->_loadData())
		{

		}
		else  $this->_initData();

		return $this->_data;
	}

	/**
	 * Method to load content event data
	 *
	 * @access	private
	 * @return	boolean	True on success
	 * @since	0.9
	 */
	function _loadData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$query = 'SELECT e.*, v.venue'
					. ' FROM #__redevent_events AS e'
					. ' LEFT JOIN #__redevent_event_venue_xref AS x ON x.eventid = e.id'
					. ' LEFT JOIN #__redevent_venues AS v ON v.id = x.venueid'
					. ' WHERE e.id = '.$this->_id
					;
			$this->_db->setQuery($query);
			$this->_data = $this->_db->loadObject();
			
			if ($this->_data) {
			  $categories = & $this->getEventCategories();
			  $this->_data->categories_ids = array_keys($categories);
			}
			return (boolean) $this->_data;
		}
		return true;
	}

	/**
	 * Method to get the category data
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	0.9
	 */
	function &getEventCategories()
	{
		$query = ' SELECT c.id, c.catname '
				. ' FROM #__redevent_categories as c '
				. ' INNER JOIN #__redevent_event_category_xref as x ON x.category_id = c.id '
				. ' WHERE x.event_id = ' . $this->_db->Quote($this->_id)
				;
		$this->_db->setQuery( $query );

		$this->_categories = $this->_db->loadObjectList('id');

		return $this->_categories;
	}
	
	/**
	 * Get a option list of all categories
	 */
	public function getCategories() 
	{
	 $query = ' SELECT c.id, c.catname, (COUNT(parent.catname) - 1) AS depth '
           . ' FROM #__redevent_categories AS c, '
           . ' #__redevent_categories AS parent '
           . ' WHERE c.lft BETWEEN parent.lft AND parent.rgt '
           . ' GROUP BY c.id '
           . ' ORDER BY c.lft;'
           ;
    $this->_db->setQuery($query);
    
    $results = $this->_db->loadObjectList();
    
    $options = array();
    foreach((array) $results as $cat)
    {
      $options[] = JHTML::_('select.option', $cat->id, str_repeat('>', $cat->depth) . ' ' . $cat->catname);
    }
		return $options;
	}
	
	/**
	 * Method to initialise the event data
	 *
	 * @access	private
	 * @return	boolean	True on success
	 * @since	0.9
	 */
	function _initData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$event = new stdClass();
			$event->id							= 0;
			$event->locid						= 0;
			$event->categories			= null;
      $event->categories_ids      = null;
			$event->dates						= null;
			$event->enddates					= null;
			$event->times						= null;
			$event->endtimes					= null;
			$event->title						= null;
			$event->alias						= null;
			$event->created						= null;
			$event->author_ip					= null;
			$event->created_by					= null;
			$event->published					= 1;
			$event->registra					= 1;
			$event->unregistra					= 0;
			$event->datdescription				= null;
			$event->meta_keywords				= null;
			$event->meta_description			= null;
			$event->datimage					= JText::_('SELECTIMAGE');
			$event->venue						= JText::_('SELECTVENUE');
			$event->maxattendees				= 0;
			$event->maxwaitinglist				= 0;
			$event->notify_on_list_subject 		= null;
			$event->notify_on_list_body 		= null;
			$event->notify_off_list_subject	 	= null;
			$event->notify_off_list_body 		= null;
			$event->notify_confirm_subject 		= null;
			$event->notify_confirm_body 		= null;
			$event->juser						= false;
			$event->notify						= false;
			$event->notify_subject 				= null;
			$event->notify_body 				= null;
			$event->review_message 				= null;
			$event->confirmation_message 		= null;
			$event->redform_id					= null;
			$event->activate					= null;
			$event->show_names					= 0;
			$event->showfields					= '';
			$event->course_credit				= 0;
			$event->course_price				= 0;
			$event->course_code					= 0;
			$event->submission_types			= null;
			$event->submission_type_email		= null;
			$event->submission_type_external	= null;
			$event->submission_type_phone		= null;
			$event->max_multi_signup			= 1;
			$event->submission_type_formal_offer				= null;
			$event->submission_type_formal_offer_subject		= null;
			$event->submission_type_formal_offer_body		= null;
			$event->submission_type_email_body		= null;
			$event->submission_type_email_pdf		= null;
			$event->submission_type_formal_offer_pdf = null;
			$event->submission_type_webform = null;
			$event->submission_type_email_subject = null;
			$event->submission_type_webform_formal_offer = null;
			$event->show_submission_type_webform_formal_offer = 0;
			$event->send_pdf_form = 0;
			$event->pdf_form_data = 0;
			$event->paymentaccepted = null;
			$event->paymentprocessing = null;
			$this->_data						= $event;
			return (boolean) $this->_data;
		}
		return true;
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
			$event = & JTable::getInstance('redevent_events', '');
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
			$event = & JTable::getInstance('redevent_events', '');
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
	 * Method to store the event
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function store($data)
	{
		global $mainframe;

		$elsettings = ELAdmin::config();
		$user		= & JFactory::getUser();

		$tzoffset 	= $mainframe->getCfg('offset');

		$row =& JTable::getInstance('redevent_events', '');
		
		// Bind the form fields to the table
		if (!$row->bind($data)) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		// Check/sanitize the metatags
		$row->meta_description = htmlspecialchars(trim(addslashes($row->meta_description)));
		if (JString::strlen($row->meta_description) > 255) {
			$row->meta_description = JString::substr($row->meta_description, 0, 254);
		}

		$row->meta_keywords = htmlspecialchars(trim(addslashes($row->meta_keywords)));
		if (JString::strlen($row->meta_keywords) > 200) {
			$row->meta_keywords = JString::substr($row->meta_keywords, 0, 199);
		}

		//Check if image was selected
		jimport('joomla.filesystem.file');
		$format 	= JFile::getExt('JPATH_SITE/images/redevent/events/'.$row->datimage);

		$allowable 	= array ('gif', 'jpg', 'png');
		if (in_array($format, $allowable)) {
			$row->datimage = $row->datimage;
		} else {
			$row->datimage = '';
		}

		// sanitise id field
		$row->id = (int) $row->id;

		$nullDate	= $this->_db->getNullDate();

		// Are we saving from an item edit?
		if ($row->id) {
			$row->modified 		= gmdate('Y-m-d H:i:s');
			$row->modified_by 	= $user->get('id');
		} else {
			$row->modified 		= $nullDate;
			$row->modified_by 	= '';

			//get IP, time and userid
			$row->created 			= gmdate('Y-m-d H:i:s');

			$row->author_ip 		= $elsettings->storeip ? getenv('REMOTE_ADDR') : 'DISABLED';
			$row->created_by		= $user->get('id');
		}

		// Make sure the data is valid
		if (!$row->check($elsettings)) {
			$this->setError($row->getError());
			return false;
		}

		// Store the table to the database
		if (!$row->store(true)) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		
		// update the event category xref
		// first, delete current rows for this event
    $query = ' DELETE FROM #__redevent_event_category_xref WHERE event_id = ' . $this->_db->Quote($row->id);
    $this->_db->setQuery($query);
    if (!$this->_db->query()) {
      $this->setError($this->_db->getErrorMsg());
      return false;    	
    }
		// insert new ref
		foreach ((array) $data['categories'] as $cat_id) {
		  $query = ' INSERT INTO #__redevent_event_category_xref (event_id, category_id) VALUES (' . $this->_db->Quote($row->id) . ', '. $this->_db->Quote($cat_id) . ')';
		  $this->_db->setQuery($query);
	    if (!$this->_db->query()) {
	      $this->setError($this->_db->getErrorMsg());
	      return false;     
	    }		  
		}  
	    // custom fields
    // first delete records for this object
    $query = ' DELETE fv FROM #__redevent_fields_values as fv '
           . ' INNER JOIN #__redevent_fields as f ON f.id = fv.field_id '
           . ' WHERE fv.object_id = ' . $this->_db->Quote($row->id)
           . '   AND f.object_key = ' . $this->_db->Quote('redevent.event')
           ;
    $this->_db->setQuery($query);
    if (!$this->_db->query()) {
      $this->setError($this->_db->getErrorMsg());
      return false;     
    }
    
    // input new values
    foreach ($data as $key => $value)
    {
      if (strstr($key, "custom"))
      {
        $fieldid = (int) substr($key, 6);
        $field = & $this->getTable('Redevent_customfieldvalue','');
        $field->object_id = $row->id;
        $field->field_id = $fieldid;
        if (is_array($value)) {
          $value = implode("\n", $value);
        }
        $field->value = $value;
        
        if (!$field->check()) {
          $this->setError($field->getError());
          return false;         
        }
        if (!$field->store()) {
          $this->setError($field->getError());
          return false;         
        }       
      }
    }
    
		return $row->id;
	}
	
	/**
	 * Check if redFORM is installed
	 */
	public function getCheckredFORM() {
		$db = JFactory::getDBO();
		$q = "SELECT id FROM #__components
			WHERE link = 'option=com_redform'";
		$db->setQuery($q);
		$result = $db->loadResult();
		if ($result > 0) return true;
		else return false;
	}
	
	/**
	 * Function to retrieve the form fields
	 */
	function getFormFields() {
		$db = JFactory::getDBO();
		$q = "SELECT id, field
			FROM #__rwf_fields
			WHERE form_id = ".$this->_data->redform_id."
			AND published = 1
			ORDER BY ordering";
		$db->setQuery($q);
		if ($db->query()) return $db->loadObjectList('id');
		else return false;
	}
	
	/**
	 * Function to retrieve the redFORM forms
	 */
	function getRedForms() {
		$db = JFactory::getDBO();
		$q = "SELECT id, formname
			FROM #__rwf_forms
			WHERE published = 1
			ORDER BY formname";
		$db->setQuery($q);
		if ($db->query()) return $db->loadObjectList('id');
		else return false;
	}
	
	/**
	 * Retrieve a list of venues
	 */
	public function getVenues() {
		$db = JFactory::getDBO();
		$q = "SELECT id, venue
			FROM #__redevent_venues
			ORDER BY venue";
		$db->setQuery($q);
		return $db->loadObjectList();
	}
	
	/**
	 * Retrieve a list of events, venues and times
	 */
	public function getEventVenue() {
		$db = JFactory::getDBO();
		$q = "SELECT x.*
			FROM #__redevent_event_venue_xref x
			WHERE eventid = ".$this->_id."
			ORDER BY dates";
		$db->setQuery($q);
		$datetimes = $db->loadObjectList();
		$ardatetimes = array();
		foreach ($datetimes as $key => $datetime) {
			$ardatetimes[$datetime->venueid][] = $datetime;
		}
		return $ardatetimes;
	}
	
  /**
   * Retrieve a list of events, venues and times
   */
  public function getXrefs() 
  {
    $db = & $this->_db;
    $q = ' SELECT x.*, v.venue '
       . ' FROM #__redevent_event_venue_xref AS x '
       . ' INNER JOIN #__redevent_venues AS v ON x.venueid = v.id '
       . ' WHERE eventid = '.$this->_id
       . ' ORDER BY dates';
    $db->setQuery($q);
    return $db->loadObjectList();
  }
  
  /**
   * get custom fields
   *
   * @return objects array
   */
  function getCustomfields()
  {
    $query = ' SELECT f.*, fv.value '
           . ' FROM #__redevent_fields AS f '
           . ' LEFT JOIN #__redevent_fields_values AS fv ON fv.field_id = f.id AND fv.object_id = '.(int) $this->_id
           . ' WHERE f.object_key = '. $this->_db->Quote("redevent.event")
           . ' ORDER BY f.ordering '
           ;
    $this->_db->setQuery($query);
    $result = $this->_db->loadObjectList();    
  
    if (!$result) {
      return array();
    }
    $fields = array();
    foreach ($result as $c)
    {
      $field =& redEVENTHelper::getCustomField($c->type);
      $field->bind($c);
      $fields[] = $field;
    }
    return $fields;     
  }

  /**
   * get custom fields
   *
   * @return objects array
   */
  function getXrefCustomfields()
  {
  	$xref = JRequest::getVar('xref', 0, 'request', 'int');  
    $query = ' SELECT f.*, fv.value '
           . ' FROM #__redevent_fields AS f '
           . ' LEFT JOIN #__redevent_fields_values AS fv ON fv.field_id = f.id AND fv.object_id = '.(int) $xref
           . ' WHERE f.object_key = '. $this->_db->Quote("redevent.xref")
           . ' ORDER BY f.ordering '
           ;
    $this->_db->setQuery($query);
    $result = $this->_db->loadObjectList();    
  
    if (!$result) {
      return array();
    }
    $fields = array();
    foreach ($result as $c)
    {
      $field =& redEVENTHelper::getCustomField($c->type);
      $field->bind($c);
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
  	$xref = JRequest::getVar('xref', 0, 'request', 'int');  	
  	
  	if ($xref) 
  	{  		
			$customs = $this->_getXCustomFields();
		
    	$query = ' SELECT x.*, v.venue, r.id as recurrence_id, r.rrule, rp.count ';
			// add the custom fields
			foreach ((array) $customs as $c)
			{
				$query .= ', c'. $c->id .'.value AS custom'. $c->id;
			}
			
  	  $query .= ' FROM #__redevent_event_venue_xref AS x '
  	       . ' LEFT JOIN #__redevent_venues AS v on v.id = x.venueid '
           . ' LEFT JOIN #__redevent_repeats AS rp on rp.xref_id = x.id '
           . ' LEFT JOIN #__redevent_recurrences AS r on r.id = rp.recurrence_id '
           ;
			// add the custom fields tables
			foreach ((array) $customs as $c)
			{
				$query .= ' LEFT JOIN #__redevent_fields_values AS c'. $c->id .' ON c'. $c->id .'.object_id = x.id';
			}
			
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
  
    if (!$object->store()) {
      $this->setError($object->getError());
      return false;
    }
    
  	// custom fields
    // first delete records for this object
    $query = ' DELETE fv FROM #__redevent_fields_values as fv '
           . ' INNER JOIN #__redevent_fields as f ON f.id = fv.field_id '
           . ' WHERE fv.object_id = ' . $this->_db->Quote($object->id)
           . '   AND f.object_key = ' . $this->_db->Quote('redevent.xref')
           ;
    $this->_db->setQuery($query);
    if (!$this->_db->query()) {
      $this->setError($this->_db->getErrorMsg());
      return false;     
    }
    
    // input new values
    foreach ($data as $key => $value)
    {
      if (strstr($key, "custom"))
      {
        $fieldid = (int) substr($key, 6);
        $field = & $this->getTable('Redevent_customfieldvalue','');
        $field->object_id = $object->id;
        $field->field_id = $fieldid;
        if (is_array($value)) {
          $value = implode("\n", $value);
        }
        $field->value = $value;
        
        if (!$field->check()) {
          $this->setError($field->getError());
          return false;         
        }
        if (!$field->store()) {
          $this->setError($field->getError());
          return false;         
        }       
      }
    }
    
    // we need to save the recurrence too
    $recurrence = & JTable::getInstance('RedEvent_recurrences', '');
    if (!$data['recurrenceid'])
    {
      $rrule = RedeventHelperRecurrence::parsePost($data);
      // new recurrence
      $recurrence->rrule = $rrule;
      if (!$recurrence->store()) {
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
    redEVENTHelper::generaterecurrences($recurrence->id);
    
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
  		$this->setError(JText::_('CANNOT DELETE XREF HAS REGISTRATIONS'));
  		return false;
  	}
  	
  	
  	$q = "DELETE FROM #__redevent_event_venue_xref WHERE id =". $this->_db->Quote((int)$id);
    $this->_db->setQuery($q);
    if (!$this->_db->query()) {
      $this->setError(JText::_('DB ERROR DELETING XREF'));
      return false;
    }
    
    // delete corresponding record in repeats table in case of recurrences
    $q = "DELETE FROM #__redevent_repeats WHERE xref_id =". $this->_db->Quote((int)$id);
    $this->_db->setQuery($q);
    if (!$this->_db->query()) {
      $this->setError(JText::_('DB ERROR DELETING XREF REPEAT'));
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
}
?>