<?php
/**
 * @version 1.0 $Id: details.php 3056 2010-01-20 11:50:16Z julien $
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
 * redEvent Component registration Model
 *
 * @package Joomla
 * @subpackage redevent
 * @since		2.0
 */
class RedEventModelRegistration extends JModel
{
	/**
	 * event session id
	 * @var int
	 */
	var $_xref = 0;
	
	/**
	 * data
	 * @var object
	 */
	var $_xrefdata = null;
	
	/**
	 * registration submit_key
	 * @var string
	 */
	var $_submit_key;
	/**
	 * caching redform fields for this submit_key
	 * @var array
	 */
	var $_rf_fields;
	/**
	 * caching registration answers from redform
	 * @var array
	 */
	var $_rf_answers;
	
	var $_prices = null;
	
	/**
	 * array of attending register id
	 */
	private $_attendees = null;
	
	function __construct($xref = 0, $config = array())
	{
		parent::__construct($config);
		if ($xref) {
			$this->setXref($xref);
		}
		else {
			$this->setXref(JRequest::getInt('xref', 0));
		}
	}
	
	function setXref($xref_id)
	{
		$this->_xref = (int) $xref_id;
	}
	
	function setSubmitKey($submit_key)
	{
		if ($submit_key && $this->_submit_key != $submit_key) 
		{
			$this->_submit_key = $submit_key;
			$this->_rf_answers = null;
			$this->_rf_fields  = null;
		}
	}
	
	/**
	 * create a new attendee
	 * 
	 * @param object user performing the registration
	 * @param int $sid associated redform submitter id
	 * @param string $submit_key associated redform submit key
	 * @param int $pricegroup_id
	 * @return boolean|object attendee row or false if failed
	 */
	function register($user, $sid, $submit_key, $pricegroup_id)
	{
		$config  = redEventHelper::config();
		$session = &$this->getSessionDetails();
		
		if (!$sid) {
			$this->setError(JText::_('COM_REDEVENT_REGISTRATION_UPDATE_XREF_REQUIRED'));
			return false;
		}
		
		$obj = $this->getTable('Redevent_register', '');
		$obj->loadBySid($sid);
		$obj->sid        = $sid;
		$obj->xref       = $this->_xref;
		$obj->pricegroup_id = $pricegroup_id;
		$obj->submit_key = $submit_key;
		$obj->uid        = $user ? $user->get('id') : 0;
		$obj->uregdate 	 = gmdate('Y-m-d H:i:s');
		$obj->uip        = $config->get('storeip', '1') ? getenv('REMOTE_ADDR') : 'DISABLED';
		
		if (!$obj->check()) {
			$this->setError($obj->getError());
			return false;
		}
		
		if (!$obj->store()) {
			$this->setError($obj->getError());
			return false;
		}
		
		if ($session->activate == 0) // no activation 
		{
			$this->confirm($obj->id);
		}
		return $obj;
	}
		
	/**
	 * to update a registration
	 * 
	 * @param int $sid associated redform submitter id
	 * @param string $submit_key associated redform submit key
	 * @param int $pricegroup_id
	 * @return boolean|object attendee row or false if failed
	 */
	function update($sid, $submit_key, $pricegroup_id)
	{
		if (!$sid) {
			$this->setError(JText::_('COM_REDEVENT_REGISTRATION_UPDATE_XREF_REQUIRED'));
			return false;
		}
		
		$obj = $this->getTable('Redevent_register', '');
		$obj->loadBySid($sid);
		$obj->sid        = $sid;
		$obj->pricegroup_id = $pricegroup_id;
		$obj->submit_key = $submit_key;
		
		if (!$obj->check()) {
			$this->setError($obj->getError());
			return false;
		}
		
		if (!$obj->store()) {
			$this->setError($obj->getError());
			return false;
		}
		return $obj;		
	}
	
	/**
	 * confirm a registration
	 * 
	 * @param int $rid register id
	 * @return boolean true on success
	 */
	function confirm($rid)
	{
		$attendee = new REattendee($rid);
		
		// first, changed status to confirmed
		if (!$attendee->confirm()) 
		{
			$this->setError($attendee->getError());
			return false;
		}		
		
		return true;
	}
		
	function getSessionDetails()
	{
		if (empty($this->_xrefdata))
		{
			if (empty($this->_xref)) {
				$this->setError(JText::_('COM_REDEVENT_missing_xref_for_session'));
				return false;
			}
			$query = 'SELECT a.id AS did, x.id AS xref, a.title, a.datdescription, a.meta_keywords, a.meta_description, a.datimage, '
			    . ' a.registra, a.unregistra, a.activate, a.notify, a.redform_id as form_id, '
			    . ' a.enable_activation_confirmation, a.notify_confirm_body, a.notify_confirm_subject, a.notify_subject, a.notify_body, ' 
			    . ' a.notify_off_list_subject, a.notify_off_list_body, a.notify_on_list_subject, a.notify_on_list_body, '
					. ' x.*, a.created_by, a.redform_id, x.maxwaitinglist, x.maxattendees, a.juser, a.show_names, a.showfields, '
					. ' a.submission_type_email, a.submission_type_external, a.submission_type_phone,'
					. ' v.venue,'
					. ' u.name AS creator_name, u.email AS creator_email, '
					. ' a.confirmation_message, a.review_message, '
					. " IF (x.course_credit = 0, '', x.course_credit) AS course_credit, a.course_code, a.submission_types, c.catname, c.published, c.access,"
			    . ' CASE WHEN CHAR_LENGTH(x.title) THEN CONCAT_WS(\' - \', a.title, x.title) ELSE a.title END as full_title, '
	        . ' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug, '
	        . ' CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as categoryslug '
					. ' FROM #__redevent_events AS a'
					. ' LEFT JOIN #__redevent_event_venue_xref AS x ON x.eventid = a.id'
					. ' LEFT JOIN #__redevent_venues AS v ON x.venueid = v.id'
	        . ' LEFT JOIN #__redevent_event_category_xref AS xcat ON xcat.event_id = a.id'
	        . ' LEFT JOIN #__redevent_categories AS c ON c.id = xcat.category_id'
					. ' LEFT JOIN #__users AS u ON a.created_by = u.id '
					. ' WHERE x.id = '.$this->_xref
					;
    	$this->_db->setQuery($query);
			$this->_xrefdata = $this->_db->loadObject();
			if ($this->_xrefdata) {
        $this->_xrefdata = $this->_getEventCategories($this->_xrefdata);				
			}
		}
		return $this->_xrefdata;
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
  
  function cancel($submit_key)
  {
  	$session = &$this->getSessionDetails();
  	
		if (!empty( $submit_key ))
		{						
			$query = ' DELETE s, f, r '
        . ' FROM #__redevent_register AS r '
        . ' INNER JOIN #__rwf_submitters AS s ON r.sid = s.id '
        . ' INNER JOIN #__rwf_forms_'.$session->redform_id .' AS f ON f.id = s.answer_id '
        . ' WHERE r.submit_key = '.$this->_db->Quote($submit_key);
        ;
			$this->_db->setQuery( $query );
			
			if (!$this->_db->query()) {
				redeventError::raiseError( 1001, $this->_db->getErrorMsg() );
				return false;
			}
		}
		return true;
  	
  }  

	/**
	 * Send e-mail confirmations
	 * 
	 * @param string submit key
	 * @return boolean true on success
	 */
	public function sendNotificationEmail($submit_key) 
	{
		/* Load database connection */
		$db = JFactory::getDBO();
		
		/* Get registration settings */
		$q = "SELECT r.id
			FROM #__redevent_register r
			WHERE submit_key = ".$db->Quote($submit_key);
		$db->setQuery($q);
		$registrations = $db->loadResultArray();
		
		if (!$registrations || !count($registrations)) {
			JError::raiseError(0, JText::sprintf('COM_REDEVENT_notification_registration_not_found_for_key_s', $submit_key));
			return false;
		}
		
		foreach ($registrations as $rid)
		{
			$attendee = new REattendee($rid);
			if (!$attendee->sendNotificationEmail()) {
				$this->setError($attendee->getError());
				return false;
			}
		}
		return true;
	}
	

  function notifyManagers($submit_key, $unreg = false, $reg_id = 0)
  {
		/* Load database connection */
		$db = JFactory::getDBO();
		
		if ($reg_id)
		{
			$registrations = array($reg_id);
		}
		else 
		{
			/* Get registration settings */
			$q = "SELECT r.id
				FROM #__redevent_register r
				WHERE submit_key = ".$db->Quote($submit_key);
			$db->setQuery($q);
			$registrations = $db->loadResultArray();
			
			if (!$registrations || !count($registrations)) {
				JError::raiseError(0, JText::sprintf('COM_REDEVENT_notification_registration_not_found_for_key_s', $submit_key));
				return false;
			}
		}
		
		foreach ($registrations as $rid)
		{
			$attendee = new REattendee($rid);
			if (!$attendee->notifyManagers($unreg)) {
				$this->setError($attendee->getError());
				return false;
			}
		}
		return true;
  }
  	
	function getRegistration($submitter_id)
	{
		$query =' SELECT s.*, r.uid, r.xref, r.pricegroup_id, e.unregistra '
        . ' FROM #__rwf_submitters AS s '
        . ' INNER JOIN #__redevent_register AS r ON r.sid = s.id '
        . ' INNER JOIN #__redevent_event_venue_xref AS x ON x.id = r.xref '
        . ' INNER JOIN #__redevent_events AS e ON x.eventid = e.id '
        . ' WHERE s.id = ' . $this->_db->Quote($submitter_id)
		    ;
		$this->_db->setQuery($query);
		$registration = $this->_db->loadObject();
		
		if (!$registration) {
			$this->setError(JText::_('COM_REDEVENT_REGISTRATION_NOT_VALID'));
			return false;
		}
		  
		$query = ' SELECT * '
		       . ' FROM #__rwf_forms_'. $registration->form_id
		       . ' WHERE id = '. $registration->answer_id
		            ;
		$this->_db->setQuery($query);
		$registration->answers = $this->_db->loadObject();
		return $registration;
	}
	
	/**
	 * get price according to pricegroup
	 * 
	 * @param unknown_type $pricegroup
	 */
	function getRegistrationPrice($pricegroup)
	{
		if (is_null($this->_prices))
		{
			$event = $this->getSessionDetails();
			$query = ' SELECT * ' 
			       . ' FROM #__redevent_sessions_pricegroups ' 
			       . ' WHERE xref = ' . $event->xref
			       . ' ORDER BY price DESC '
			       ;
			$this->_db->setQuery($query);
			$res = $this->_db->loadObjectList();
			$this->_prices = $res ? $res : array();
		}
		
		if (!count($this->_prices)) {
			return 0;
		}
		foreach ($this->_prices as $p)
		{
			if ($p->pricegroup_id == $pricegroup)
			{
				return $p->price;
				break;
			}
		}
		//pricegroup not found... not good at all ! 
		$this->setError(JText::_('COM_REDEVENT_Pricegroup_not_found'));
		return false;
	}

  /**
   * get current session prices
   * 
   * @return array
   */
  function getPricegroups()
  {
		$event = $this->getSessionDetails();
  	$query = ' SELECT sp.*, p.name, p.alias, p.tooltip, f.currency, '
	         . ' CASE WHEN CHAR_LENGTH(p.alias) THEN CONCAT_WS(\':\', p.id, p.alias) ELSE p.id END as slug ' 
  	       . ' FROM #__redevent_sessions_pricegroups AS sp '
  	       . ' INNER JOIN #__redevent_pricegroups AS p on p.id = sp.pricegroup_id '
  	       . ' INNER JOIN #__redevent_event_venue_xref AS x on x.id = sp.xref '
  	       . ' INNER JOIN #__redevent_events AS e on e.id = x.eventid '
  	       . ' LEFT JOIN #__rwf_forms AS f on e.redform_id = f.id '
  	       . ' WHERE sp.xref = ' . $this->_db->Quote($event->xref)
  	       . ' ORDER BY p.ordering ASC '
  	       ;
  	$this->_db->setQuery($query);
  	$res = $this->_db->loadObjectList();   	
  	return $res;
  }
  
  
  /**
  * Cancel a registration
  *
  * @access public
  * @param int $register_id
  * @return boolean true on success
  * @since 2.0
  */
  function cancelregistration($register_id, $xref)
  {
	  $user =  & JFactory::getUser();
	  $userid = $user->get('id');
  	$acl = UserAcl::getInstance();
	  
	  if ($userid < 1) {
			JError::raiseError( 403, JText::_('COM_REDEVENT_ALERTNOTAUTH') );
			return;
	  }
	  		
	  		// first, check if the user is allowed to unregister from this
	  // he must be the one that submitted the form, plus the unregistration must be allowed
	  $q = ' SELECT s.*, r.uid, e.unregistra, x.dates, x.times, x.registrationend  '
	          . ' FROM #__rwf_submitters AS s '
	          . ' INNER JOIN #__redevent_register AS r ON r.sid = s.id '
	          . ' INNER JOIN #__redevent_event_venue_xref AS x ON x.id = r.xref '
	          . ' INNER JOIN #__redevent_events AS e ON x.eventid = e.id '
	          . ' WHERE r.id = ' . $this->_db->Quote($register_id)
	  		    ;
		$this->_db->setQuery($q);
	  $submitterinfo = $this->_db->loadObject();
	  
	  // or be allowed to manage attendees
	  $manager = $acl->canManageAttendees($xref);
	  
	  if (!redEVENTHelper::canUnregister($xref) && !$manager) {
	  	$this->setError(JText::_('COM_REDEVENT_UNREGISTRATION_NOT_ALLOWED'));
	  	return false;
	  }
	  
	  if (($submitterinfo->uid <> $userid || $submitterinfo->unregistra == 0) && !$manager)
	  {
			$this->setError(JText::_('COM_REDEVENT_UNREGISTRATION_NOT_ALLOWED'));
			return false;
	  }
	  
	  
	  // Now that we made sure, we can delete the submitter and corresponding form values
	  /* Delete the redFORM entry first */
	  /* Submitter answers first*/
	  $q = ' UPDATE #__redevent_register AS r '
	     . ' SET r.cancelled = 1 '
	     . ' WHERE r.id = ' . $this->_db->Quote($register_id)
	  ;
	  $this->_db->setQuery($q);
	  if ( !$this->_db->query() ) {
	  	$this->setError(JText::_('COM_REDEVENT_ERROR_CANNOT_DELETE_REGISTRATION'));
	  	return false;
	  }
	  return true;
  }
}