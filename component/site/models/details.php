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
 * EventList Component Details Model
 *
 * @package Joomla
 * @subpackage EventList
 * @since		0.9
 */
class RedeventModelDetails extends JModel
{
	/**
	 * Details data in details array
	 *
	 * @var array
	 */
	protected $_details = null;

	protected $_xreflinks = null;

	/**
	 * registeres in array
	 *
	 * @var array
	 */
	protected $_registers = null;
	
	protected $_id = null;

	/**
	 * Constructor
	 *
	 * @since 0.9
	 */
	function __construct()
	{
		parent::__construct();

		$id = JRequest::getInt('id');
		$this->setId((int)$id);
		$xref = JRequest::getInt('xref');
		$this->setXref((int)$xref);
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
		$this->_xref			= $xref;
	}

	/**
	 * Method to get event data for the Detailsview
	 *
	 * @access public
	 * @return array
	 * @since 0.9
	 */
	function getDetails( )
	{
		/*
		 * Load the Category data
		 */
		if ($this->_loadDetails())
		{
			$user	= & JFactory::getUser();

			// Is the category published?
			if (!count($this->_details->categories))
			{
				RedeventError::raiseError( 404, JText::_("CATEGORY NOT PUBLISHED") );
			}

			// Do we have access to each category ?
			foreach ($this->_details->categories as $cat)
			{
				if ($cat->access > $user->get('aid'))
				{
					JError::raiseError( 403, JText::_("ALERTNOTAUTH") );
				}
			}

		}

		return $this->_details;
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
		if (empty($this->_details))
		{
			// Get the WHERE clause
			$where	= $this->_buildDetailsWhere();

			$query = 'SELECT a.id AS did, x.id AS xref, a.title, a.datdescription, a.meta_keywords, a.meta_description, a.datimage, a.registra, a.unregistra,' 
					. ' x.*, a.created_by, a.redform_id, x.maxwaitinglist, x.maxattendees, a.juser, a.show_names, a.showfields, '
					. ' a.submission_type_email, a.submission_type_external, a.submission_type_phone,'
					. ' v.venue,'
					. ' u.name AS creator_name, u.email AS creator_email, '
					. " a.confirmation_message, x.course_price, IF (x.course_credit = 0, '', x.course_credit) AS course_credit, a.course_code, a.submission_types, c.catname, c.published, c.access,"
	        . ' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug, '
	        . ' CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as categoryslug '
					. ' FROM #__redevent_events AS a'
					. ' LEFT JOIN #__redevent_event_venue_xref AS x ON x.eventid = a.id'
					. ' LEFT JOIN #__redevent_venues AS v ON x.venueid = v.id'
	        . ' LEFT JOIN #__redevent_event_category_xref AS xcat ON xcat.event_id = a.id'
	        . ' LEFT JOIN #__redevent_categories AS c ON c.id = xcat.category_id'
					. ' LEFT JOIN #__users AS u ON a.created_by = u.id '
					. $where
					;
    		$this->_db->setQuery($query);
			$this->_details = $this->_db->loadObject();
			if ($this->_details) {
        $this->_details = $this->_getEventCategories($this->_details);				
			}
			return (boolean) $this->_details;
		}
		return true;
	}
	
 
  /**
   * Load all venues and their signup links
   */
  public function getXrefLinks() 
  {
  	if (empty($this->_xreflinks))
  	{
	    $q = ' SELECT e.*, IF (x.course_credit = 0, "", x.course_credit) AS course_credit, x.course_price, '
	       . ' x.id AS xref, x.dates, x.enddates, x.times, x.endtimes, v.venue, x.venueid, x.details, x.registrationend, '
	       . ' x.external_registration_url, '
	       . ' v.city AS location, '
	       . ' v.country, v.locimage, '
	       . ' UNIX_TIMESTAMP(x.dates) AS unixdates, '
	       . ' CASE WHEN CHAR_LENGTH(v.alias) THEN CONCAT_WS(":", v.id, v.alias) ELSE v.id END as venueslug '
	       . ' FROM #__redevent_events AS e '
	       . ' INNER JOIN #__redevent_event_venue_xref AS x ON x.eventid = e.id '
	       . ' INNER JOIN #__redevent_venues AS v ON x.venueid = v.id '
	       . ' LEFT JOIN #__redevent_event_category_xref AS xcat ON xcat.event_id = e.id '
	       . ' LEFT JOIN #__redevent_categories AS c ON xcat.category_id = c.id '
	       . ' WHERE x.published = 1 '
	       . ' AND e.id = '.$this->_db->Quote($this->_id)
	       . ' GROUP BY x.id '
	       . ' ORDER BY x.dates ASC, x.times ASC '
	       ;
	    $this->_db->setQuery($q);
	    $rows = $this->_db->loadObjectList();
	    	  	
	    foreach ((array)$rows as $k => $r) {
	      $query = ' SELECT c.id, c.catname, c.image, '
	             . ' CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(":", c.id, c.alias) ELSE c.id END as slug '
	             . ' FROM #__redevent_categories AS c '
	             . ' INNER JOIN #__redevent_event_category_xref AS xcat ON xcat.category_id = c.id '
	             . ' WHERE xcat.event_id = ' . $this->_db->Quote($r->id)
	             . ' ORDER BY c.lft '
	             ;
	      $this->_db->setQuery($query);
	      $rows[$k]->categories = $this->_db->loadObjectList();
	    }
	    $this->_xreflinks = $rows;
  	}
  	return $this->_xreflinks;
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
	 * Method to check if the user is already registered
	 *
	 * @access	public
	 * @return	array
	 * @since	0.9
	 */
	function getUsercheck()
	{
		// Initialize variables
		$user 		= & JFactory::getUser();
		$userid		= (int) $user->get('id', 0);

		//usercheck
		$query = 'SELECT uid'
				. ' FROM #__redevent_register'
				. ' WHERE uid = '.$userid
				. ' AND xref = '.$this->_xref
				;
		$this->_db->setQuery( $query );
		return $this->_db->loadResult();
	}

	/**
	 * Method to get the registered users
	 *
	 * @access	public
	 * @return	object
	 * @since	0.9
	 * @todo Complete CB integration
	 */
	function getRegisters($all_fields = false, $admin = false) 
	{
		// make sure the init is done
		$this->getDetails();
		
	  if (!$this->_details->registra && !$admin) {
	    return null;
	  }
	  
		$db = JFactory::getDBO();

		// first, get all submissions			
		$query = ' SELECT r.*, s.waitinglist, s.confirmed, s.confirmdate, s.submit_key '
						. ' FROM #__redevent_register AS r '
						. ' INNER JOIN #__rwf_submitters AS s ON r.submit_key = s.submit_key '
						. ' LEFT JOIN #__users AS u ON r.uid = u.id '
						. ' WHERE s.xref = ' . $this->_xref
            . ' AND s.confirmed = 1'
						;
		$db->setQuery($query);
		$submitters = $db->loadObjectList('submit_key');
		
		if ($submitters === null)
		{
			$msg = JText::_('ERROR GETTING ATTENDEES');
			$this->setError($msg);
			RedeventError::raiseWarning(5, $msg);
			return null;
		}
		else if (empty($submitters)) {
			// no submitters
			return null;
		}
		
		/* At least 1 redFORM field must be selected to show the user data from */
		if ((!empty($this->_details->showfields) || $admin) && $this->_details->redform_id > 0) 
		{
			$fields = $this->getFormFields($all_fields);
			
			if (!$fields) 
			{
				RedeventError::raiseWarning('error', JText::_('Cannot load fields').$db->getErrorMsg());
				return null;
			}			
			
			if (count($fields)) 
			{
				$table_fields = array();
				$fields_names = array();
				foreach ($fields as $key => $field) {
					$table_fields[] = 'a.field_'. $field->id;
					$fields_names['field_'. $field->id] = $field->field;
				}
				
				$query  = ' SELECT ' . implode(', ', $table_fields)
				        . ' , s.submit_key, s.id '
				        . ' FROM #__redevent_register AS r '
				        . ' INNER JOIN #__rwf_submitters AS s ON r.submit_key = s.submit_key '
				        . ' INNER JOIN #__rwf_forms_' . $fields[0]->form_id . ' AS a ON s.answer_id = a.id '
				        . ' WHERE s.xref = ' . $this->_xref
				        . ' AND s.confirmed = 1'
				        . ' ORDER BY s.confirmdate';
				        ;
				$db->setQuery($query);
				if (!$db->query()) {
					RedeventError::raiseWarning('error', JText::_('Cannot load registered users').' '.$db->getErrorMsg());
					return null;
				}			
				$answers = $db->loadObjectList();
			}
			else {
				$answers = array();
			}
			
		  // add the answers to submitters list
		  $registers = array();
      foreach ($answers as $answer) 
      {
        if (!isset($submitters[$answer->submit_key])) 
        {
        	$msg = JText::_('ERROR REGISTRATION WITHOUT SUBMITTER') . ': ' . $answer->id;
        	$this->setError($msg);
        	RedeventError::raiseWarning(10, $msg);
        	return null;
        }
        // build the object
        $register = new stdclass();
        $register->id = $answer->id;
        $register->submitter = $submitters[$answer->submit_key];
        $register->answers = $answer;
        $register->fields = $fields_names;
        unset($register->answers->id); // just the fields
        unset($register->answers->submit_key); // just the fields
        $registers[] = $register;
      }
      return $registers;
		}
		return null;
	}
	
	/**
	 * returns the fields to be shown in attendees list
	 * 
	 * @param boolean get all fields
	 * @return array;
	 */
	function getFormFields($all_fields = false)
	{
		// make sure the init is done
		$this->getDetails();
		
		if (empty($this->_details->showfields)) {
			return false;
		}
		// load form fields
		$q = ' SELECT id, field, form_id '
			 . ' FROM #__rwf_fields j '
			 . ' WHERE form_id = '. $this->_db->Quote($this->_details->redform_id)
			 . ($all_fields ? '' : '   AND j.id in ('.$this->_details->showfields. ')')
			 . '   AND j.published = 1 '
			 . ' ORDER BY ordering ';
		$this->_db->setQuery($q);
		
		return $this->_db->loadObjectList();
	}	
	
	/**
	 * Saves the registration to the database
	 *
	 * Contact person is defined as:
	 * If logged in, this is the contact person used for multiple signups
	 * If not logged in, the e-mail address of the submitted form is used
	 */
	public function userregister() 
	{
		global $mainframe;
		
		/* Get the global settings */
		$elsettings = redEVENTHelper::config();
		
		/* Get the event unique ID */
		$event 		= (int) $this->_id;
		$xref 		= (int) $this->_xref;
		
		/* Get the submitter key */
		$submit_key = JRequest::getVar('submit_key');
		
		/* Get the event settings */
		$eventsettings = $this->getDetails();
		
		/* Load redEVENT setttings */
		$elsettings = redEVENTHelper::config();
		$tzoffset	= $mainframe->getCfg('offset');
		
		/* Determine contact person */
		$user = JFactory::getUser();
		
		/* Set a user ID for validating */
		$uid = (int) $user->get('id');
		
		/* Create the object to store the data in redEVENT */
		$obj = new stdClass();
		$obj->xref 		= $xref;
		$obj->uid   	= (int)$uid;
		$obj->uregdate 	= gmdate('Y-m-d H:i:s');
		$obj->uip   	= $elsettings->storeip ? getenv('REMOTE_ADDR') : 'DISABLED';
		/* Submit key for identifying submissions */
		$obj->submit_key = $submit_key;
		
		/* Store the user registration */
		if (!$this->_db->insertObject('#__redevent_register', $obj)) {
			$this->setError(JText::_('Error in registration') . ': ' . $this->_db->getErrorMsg());
			return false;
		}
						
		/* All is good */
		return true;
	}
	
	/**
	 * Deletes a registered user
	 *
	 * @access public
	 * @return true on success
	 * @since 0.7
	 * @todo Fix as it is broken now
	 */
	function delreguser() 
	{
		$db = & JFactory::getDBO();
		$user =  & JFactory::getUser();
		$userid = $user->get('id');
		$xref = JRequest::getInt('xref');
    $submitter_id = JRequest::getInt('sid');
		
		if ($userid < 1) {
			JError::raiseError( 403, JText::_('ALERTNOTAUTH') );
			return;
		}
		
		// first, check if the user is allowed to unregister from this
		// he must be the one that submitted the form, plus the unregistration must be allowed
		$q = ' SELECT s.*, r.uid, e.unregistra '
        . ' FROM #__rwf_submitters AS s '
        . ' INNER JOIN #__redevent_register AS r ON r.submit_key = s.submit_key '
        . ' INNER JOIN #__redevent_event_venue_xref AS x ON x.id = r.xref '
        . ' INNER JOIN #__redevent_events AS e ON x.eventid = e.id '
        . ' WHERE s.id = ' . $db->Quote($submitter_id)
		    ;
		$db->setQuery($q);
		$submitterinfo = $db->loadObject();
		
		// or be allowed to manage attendees
		$manager = $this->getManageAttendees();
		
		if (($submitterinfo->uid <> $userid || $submitterinfo->unregistra == 0) && !$manager) {
      RedeventError::raiseWarning('1', JText::_('Cannot delete registration').' '.$db->getErrorMsg());
      return false;			
		}
		
		// Now that we made sure, we can delete the submitter and corresponding form values
    /* Delete the redFORM entry first */
    /* Submitter answers first*/
    $q =  ' DELETE s, f '
        . ' FROM #__rwf_submitters AS s '
        . ' INNER JOIN #__rwf_forms_'.$submitterinfo->form_id .' AS f ON f.id = s.answer_id '
        . ' WHERE s.id = ' . $db->Quote($submitter_id)
        ;
    $db->setQuery($q);
    if ( !$db->query() ) {
      RedeventError::raiseWarning('2', JText::_('Error deleting redform registration').' '.$db->getErrorMsg());
      return false;     
    }
        
    /* Now remove the redevent registration */
    /* if there is no more submitter associated to register submit_key, we can delete the record */
    $q = ' SELECT COUNT(*) FROM #__rwf_submitters WHERE submit_key = ' . $db->Quote($submitterinfo->submit_key);
    $db->setQuery($q);
    $res = $db->loadResult();
		if ($res === null) {
      RedeventError::raiseWarning('3', JText::_('Error counting remaining associated submission').' '.$db->getErrorMsg());
      return false;     			
		}
		
		if ($res == 0) { // no more records associated in redform
			$q = "DELETE FROM #__redevent_register WHERE submit_key = " . $db->Quote($submitterinfo->submit_key);
			$db->setQuery($q);
			if (!$db->query()) {
				RedeventError::raiseWarning('error', JText::_('Cannot delete registration in redevent').' '.$db->getErrorMsg());
				return false;
			}
		}
		return true;
	}
	
	/**
	 * Get a list of venues
	 */
	public function getVenues() {
		$db = JFactory::getDBO();
		$q = "SELECT *
			FROM #__redevent_venues v
			LEFT JOIN #__redevent_event_venue_xref x
			ON v.id = x.venueid
			WHERE x.eventid IN (".$this->_details->did.")";
		$db->setQuery($q);
		return $db->loadObjectList('id');
	}
	
	/**
	 * Get a list of venue/date relations
	 */
	public function getVenueDates() 
	{
		$db = JFactory::getDBO();
		$q = ' SELECT * '
		    .' FROM #__redevent_event_venue_xref x '
		    .' WHERE x.eventid = '.$this->_db->Quote($this->_details->did)
        .'   AND x.published = 1 '
		    .' ORDER BY x.dates ASC, x.times ASC ';
		$db->setQuery($q);
		return $db->loadObjectList('id');
	}
	

  /**
   * adds categories property to event row
   *
   * @param object event
   * @return object
   */
  function _getEventCategories($row)
  {
  	$query =  ' SELECT c.id, c.catname, c.access, '
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
  
  function getManageAttendees()
  {
  	$auth =& JFactory::getACL();
        
    $auth->addACL('com_redevent', 'manageattendees', 'users', 'super administrator');
    $auth->addACL('com_redevent', 'manageattendees', 'users', 'administrator');
    $auth->addACL('com_redevent', 'manageattendees', 'users', 'manager');  	
    
  	$user = & JFactory::getUser();
  	
  	if ($user->authorize('com_redevent', 'manageattendees')) {
  		return true;
  	}
  	$acl = UserAcl::getInstance();
  	return $acl->canEditXref($this->_xref);
  }
  
  function notifyManagers()
  {
  	jimport('joomla.mail.helper');
  	$app    = &JFactory::getApplication();
  	$params = $app->getParams('com_redevent');
		$tags   = new redEVENT_tags();
  	
  	$event = $this->getDetails();
  	
  	$recipients = array();
  	
  	// default recipients
  	$default = $params->get('registration_default_recipients');
  	if (!empty($default)) 
  	{
  		if (strstr($default, ';')) {
  			$addresses = explode(";", $default);
  		}
  		else {
  			$addresses = explode(",", $default);
  		}
  		foreach ($addresses as $a) 
  		{
  			$a = trim($a);
	  		if (JMailHelper::isEmailAddress($a)) {
	  			$recipients[] = array('email' => $a, 'name' => '');
	  		}  			
  		}
  	}
  	
  	// creator
  	if ($params->get('registration_notify_creator', 1)) {
  		if (JMailHelper::isEmailAddress($event->creator_email)) {
  			$recipients[] = array('email' => $event->creator_email, 'name' => $event->creator_name);
  		}
  	}
  	
  	// group recipients
  	$gprecipients = $this->_getXrefRegistrationRecipients();
  	foreach ($gprecipients AS $r)
  	{
  		$recipients[] =  array('email' => $r->email, 'name' => $r->name);	
  	}
  	
  	if (!count($recipients)) {
  		return true;
  	}
  	
  	$mailer = & JFactory::getMailer();
  	
  	foreach ($recipients as $r)
  	{
  		$mailer->addAddress($r['email'], $r['name']);
  	}
  	
  	$mailer->setSubject($tags->ReplaceTags($params->get('registration_notification_subject')));
  	$mailer->MsgHTML($tags->ReplaceTags($params->get('registration_notification_body')));
  	if (!$mailer->send())
  	{
  		RedeventHelperLog::simplelog(JText::_('REDEVENT_ERROR_REGISTRATION_MANAGERS_NOTIFICATION_FAILED'));
  		$this->setError(JText::_('REDEVENT_ERROR_REGISTRATION_MANAGERS_NOTIFICATION_FAILED'));
  		return false;
  	}
  	return true;
  }
  
  function _getXrefRegistrationRecipients()
  {
  	$event = $this->getDetails();
  	
		$query = ' SELECT u.name, u.email '
					 . ' FROM #__redevent_event_venue_xref AS x '
					 . ' INNER JOIN #__redevent_groups AS g ON x.groupid = g.id '
					 . ' INNER JOIN #__redevent_groupmembers AS gm ON gm.group_id = g.id '
					 . ' INNER JOIN #__users AS u ON gm.member = u.id '
					 . ' WHERE x.id = '. $this->_db->Quote($event->xref)
					 . '   AND gm.receive_registrations = 1 '
					 ;
		$this->_db->setQuery($query);
		$xref_group_recipients = $this->_db->loadObjectList();
		return $xref_group_recipients;
  }
}
?>