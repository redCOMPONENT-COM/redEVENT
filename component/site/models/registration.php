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
	
	var $mailer = null;
	
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
	 * @param int $sid associated redform submitter id
	 * @param string $submit_key associated redform submit key
	 * @param int $pricegroup_id
	 * @return boolean|object attendee row or false if failed
	 */
	function register($sid, $submit_key, $pricegroup_id)
	{
		$user    = &JFactory::getUser();
		$config  = redEventHelper::config();
		$session = &$this->getSessionDetails();
		
		$status = redEVENTHelper::canRegister($session->xref);
		if (!$status->canregister) {
			$this->setError($status->status);
			return false;			
		}
		
		if ($sid)
		{
			$obj = $this->getTable('Redevent_register', '');
			$obj->loadBySid($sid);
			$obj->sid        = $sid;
			$obj->xref       = $this->_xref;
			$obj->pricegroup_id = $pricegroup_id;
			$obj->submit_key = $submit_key;
			$obj->uid        = $user->get('id');
			$obj->uregdate 	 = gmdate('Y-m-d H:i:s');
			$obj->uip        = $config->storeip ? getenv('REMOTE_ADDR') : 'DISABLED';
			
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
	}
	
	/**
	 * confirm a registration
	 * 
	 * @param int $rid register id
	 * @return boolean true on success
	 */
	function confirm($rid)
	{
		// first, changed status to confirmed
		$query = ' UPDATE #__redevent_register '
		       . ' SET confirmed = 1, confirmdate = ' .$this->_db->Quote(gmdate('Y-m-d H:i:s'))
		       . ' WHERE id = ' . $rid;
		$this->_db->setQuery($query);
		$res = $this->_db->query();
		
		if (!$res) {
			$this->setError(JText::_('COM_REDEVENT_REGISTRATION_FAILED_CONFIRM_REGISTRATION'));
			return false;
		}
		
		// now, handle waiting list
		$session = &$this->getSessionDetails();
		if ($session->maxattendees == 0) { // no waiting list
			return true;
		}
		$attendees = $this->_getAttendees();
		if (count($attendees) > $session->maxattendees) 
		{
			// put this attendee on WL
			$query = ' UPDATE #__redevent_register '
			       . ' SET waitinglist = 1'
		  	     . ' WHERE id = ' . $rid;
			$this->_db->setQuery($query);
			$res = $this->_db->query();
			
			if (!$res) {
				$this->setError(JText::_('COM_REDEVENT_REGISTRATION_FAILED_ADDING_TO_WAITING_LIST'));
				return false;
			}
			
			// send waiting list email
			$this->_sendWaitinglistStatusEmail($rid, 1);
		}
		else {
			$this->_attendees[] = $rid;
			
			// send attending email
			$this->_sendWaitinglistStatusEmail($rid, 0);
		}
		
		return true;
	}
	
	/**
	 * returns array of ids of currently attending (confirmed, not on wl, not cancelled) register_id
	 * 
	 * @return array;
	 */
	private function _getAttendees()
	{
		if (is_null($this->_attendees))
		{
			$query = ' SELECT r.id ' 
			       . ' FROM #__redevent_register AS r ' 
			       . ' WHERE r.xref = ' . $this->_xref
			       . '   AND r.confirmed = 1 '
			       . '   AND r.cancelled = 0 '
			       . '   AND r.waitinglist = 0 '
			       ;
			$this->_db->setQuery($query);
			$this->_attendees = $this->_db->loadResultArray();
		}
		return $this->_attendees;
	}
	
	/**
	 * send waiting list status emails
	 * 
	 * @param int $rid register id
	 * @param int $waiting status: 0 for attending, 1 for waiting
	 * @return boolean true on success
	 */
	function _sendWaitinglistStatusEmail($rid, $waiting = 0)
	{
		$session = &$this->getSessionDetails();
		
		$query = ' SELECT sid ' 
		       . ' FROM #__redevent_register AS r ' 
		       . ' WHERE id = ' . $rid;
		$this->_db->setQuery($query);
		$sid = $this->_db->loadResult();
		
		if (empty($this->taghelper)) {
			$this->taghelper = new redEVENT_tags();
			$this->taghelper->setXref($this->_xref);
		}
				
		if ($waiting == 0) {
			$body    = nl2br($this->taghelper->ReplaceTags($session->notify_off_list_body));
			$subject = $this->taghelper->ReplaceTags($session->notify_off_list_subject);
		}
		else {
			$body = nl2br($this->taghelper->ReplaceTags($session->notify_on_list_body));
			$subject = $this->taghelper->ReplaceTags($session->notify_on_list_subject);
		}
		
		// update image paths in body
		$body = ELOutput::ImgRelAbs($body);
		
		$mailer = JFactory::getMailer();
		
		$rfcore = new RedFormCore();
		$emails = $rfcore->getSubmissionContactEmail(array($sid));
		$email = current($emails);
		
		/* Add the email address */
		$mailer->AddAddress($email['email'], $email['fullname']);
			
		/* Mail submitter */
		$htmlmsg = '<html><head><title></title></title></head><body>'.$body.'</body></html>';
		$mailer->setBody($htmlmsg);
		$mailer->setSubject($subject);
		$mailer->IsHTML(true);
			
		/* Send the mail */
		if (!$mailer->Send()) {
			RedeventHelperLog::simpleLog(JText::_('COM_REDEVENT_REGISTRATION_FAILED_SENDING_WAITING_LIST_STATUS_EMAIL'));
			return false;
		}
		
		return true;
	}
	
	function getSessionDetails()
	{
		if (empty($this->_xrefdata))
		{
			if (empty($this->_xref)) {
				$this->setError(JText::_('missing xref for session'));
				return false;
			}
			$query = 'SELECT a.id AS did, x.id AS xref, a.title, a.datdescription, a.meta_keywords, a.meta_description, a.datimage, '
			    . ' a.registra, a.unregistra, a.activate, a.notify, a.redform_id as form_id, '
			    . ' a.notify_confirm_body, a.notify_confirm_subject, ' 
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
  
  /**
   * adds registered (int) and waiting (int) properties to rows.
   * 
   * @return array 
   */
  function getRegistrationsCount() 
  {
  	$session = &$this->getSessionDetails();
  	
		$query = ' SELECT waitinglist, COUNT(id) AS total '
		       . ' FROM #__redevent_register '
		       . ' WHERE xref = '.$session->xref
		       . ' AND confirmed = 1 '
		       . '   AND r.cancelled = 0 '
		       . ' GROUP BY waitinglist'
		       ;
		$this->_db->setQuery($query);
		$res = $this->_db->loadObjectList('waitinglist');
    return array('attending' => (isset($res[0]) ? $res[0]->total : 0), 'waiting' => (isset($res[1]) ? $res[1]->total : 0));
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
	 */
	public function sendNotificationEmail($submit_key) 
	{
		$mainframe = & JFactory::getApplication();
		
		/* Load database connection */
		$db = JFactory::getDBO();
		
		/* Determine contact person */
		$user = JFactory::getUser();
		
		/* Get the global settings */
		$elsettings = redEVENTHelper::config();
		
		/* Get registration settings */
		$q = "SELECT *
			FROM #__redevent_register r
			WHERE submit_key = ".$db->Quote($submit_key);
		$db->setQuery($q);
		$registration = $db->loadObject();
		
		if (!$registration) {
			JError::raiseError(0, JText::sprintf('notification: registration not found for key %s', $submit_key));
			return false;
		}
		
		/* Get settings for event */
		$q = "SELECT e.title, e.notify_subject, e.notify_body, e.notify, x.maxattendees, e.activate,
					e.juser, e.confirmation_message, e.redform_id, e.submission_type_formal_offer, e.submission_type_formal_offer_subject,
					e.datdescription, e.id AS eventid
			FROM #__redevent_events e
			LEFT JOIN #__redevent_event_venue_xref x
			ON x.eventid = e.id
			WHERE x.id = ".$registration->xref."
			";
		$db->setQuery($q);
		$eventsettings = $db->loadObject();
		
		/* Get a list of fields that are of type email/username/fullname */
		$q = "SELECT f.id, f.field, f.fieldtype 
			FROM #__rwf_fields f
			WHERE f.published = 1
			AND f.form_id = ".$eventsettings->redform_id."
			AND f.fieldtype in ('email', 'username', 'fullname')
			GROUP BY f.fieldtype";
		$db->setQuery($q);
		$selectfields = $db->loadObjectList('fieldtype');

		/* Get the username and e-mail from the redFORM database */
		$getfields = array($db->nameQuote('s.id'));
		foreach ((array) $selectfields as $selectfield) {
			$getfields[] = $db->nameQuote('f.field_'. $selectfield->id);
		}
		
		
		/* Get list of attendees */
		$q = ' SELECT r.id as rid, '. implode(', ', $getfields)
		   . ' FROM #__redevent_register as r '
		   . ' INNER JOIN #__rwf_submitters AS s ON s.id = r.sid '
		   . ' INNER JOIN '. $db->nameQuote('#__rwf_forms_'.$eventsettings->redform_id).' AS f ON s.answer_id = f.id '
		   . ' WHERE r.submit_key = '.$db->Quote($registration->submit_key)
		   ;
		$db->setQuery($q);
		$useremails = $db->loadObjectList();
				
		$attendees = array();
		foreach ($useremails as $attendeeinfo)
		{
			$attendee = new REattendee($attendeeinfo->rid);
			if (isset($selectfields['fullname'])) {
				$property = 'field_'. $selectfields['fullname']->id;
				$attendee->setFullname($attendeeinfo->$property);
			}
      if (isset($selectfields['username'])) {
        $property = 'field_'. $selectfields['username']->id;
        $attendee->setUsername($attendeeinfo->$property);
      }
      if (isset($selectfields['email'])) {
        $property = 'field_'. $selectfields['email']->id;
        $attendee->setEmail($attendeeinfo->$property);
      }
      
      $attendees[] = $attendee;
		}
						        
		if ($user->id > 1) {
			/* user is logged in thus contact person */
		}
		else 
		{
			/* Register the user in Joomla if chosen*/
			if ($eventsettings->juser) 
			{
				// use info from first attendee to create a new user
				$attendee = $attendees[0];
				
				if (strlen($attendee->getUsername()) > 0 && strlen($attendee->getEmail()) > 0) 
				{
					/* Check if the user already exists in Joomla with this e-mail address */
					$query = "SELECT id
							FROM #__users
							WHERE email = ".$db->Quote($attendee->getEmail())."
							LIMIT 1";
					$db->setQuery($query);
					$found_id = $db->loadResult();
					
					if ($found_id) {
						$uid = $found_id;
					}
					else 
					{
						/* Load the User helper */
						jimport('joomla.user.helper');
						
            if (!$attendee->getFullname()) {
            	$attendee->setFullname($attendee->getUsername());
            }
						
						// Get required system objects
						$user 		= JFactory::getUser(0);
						$pathway 	= $mainframe->getPathway();
						$config		= JFactory::getConfig();
						$authorize	= JFactory::getACL();
						$document   = JFactory::getDocument();
						$password   = JUserHelper::genRandomPassword();
						$usersConfig = JComponentHelper::getParams( 'com_users' );
						$newUsertype = 'Registered';
						
						// Set some initial user values
						$user->set('id', 0);
            $user->set('name', $attendee->getFullname());
            $user->set('username', $attendee->getUsername());
            $user->set('email', $attendee->getEmail());
						$user->set('usertype', $newUsertype);
						$user->set('gid', $authorize->get_group_id( '', $newUsertype, 'ARO' ));
						$user->set('password', md5($password));
						
						// TODO: Should this be JDate?
						$user->set('registerDate', date('Y-m-d H:i:s'));
						
						// If there was an error with registration, set the message and display form
						if (!$user->save())
						{
							RedeventError::raiseWarning('', JText::_($user->getError()));
							/* We cannot save the user, need to delete already stored user data */
							
							/* Delete the redFORM entry first */
							/* Submitter records */
							$q = "DELETE FROM #__rwf_submitters
								WHERE submit_key = ".$db->Quote($submit_key);
							$db->setQuery($q);
							$db->query();
							
							/* All cleaned up, return false */
							return false;
						}
						else
						{
							/** update registration with user id **/
							$q = ' UPDATE #__redevent_register '
							   . ' SET uid = '. $db->Quote($user->id)
							   . ' WHERE submit_key = '.$db->Quote($submit_key);
							$db->setQuery($q);
							$db->query();							
							
							// send mail with account details
							/* Load the mailer */
				      $this->Mailer();
				    
				      /* Add the email address */
				      $this->mailer->AddAddress($user->email, $user->name);

				      /* Get the activation link */
				      $activatelink = '<a href="'.JRoute::_(JURI::root().'index.php?option=com_redevent&controller=registration&task=activate&confirmid='.str_replace(".", "_", $registration->uip).'x'.$registration->xref.'x'.$user->id.'x'.$registration->id.'x'.$submit_key).'">'.JText::_('Activate').'</a>';
				      /* Mail attendee */
				      $htmlmsg = '<html><head><title></title></title></head><body>';
				      $htmlmsg .= $eventsettings->notify_body;

				      $htmlmsg .= '<br /><br />';
				      $reginfo = nl2br(JText::_('INFORM_USERNAME'));
				      $reginfo = str_replace('[fullname]', $user->name, $reginfo);
				      $reginfo = str_replace('[username]', $user->username, $reginfo);
				      $reginfo = str_replace('[password]', $password, $reginfo);
				      $htmlmsg .= $reginfo;

				      $htmlmsg .= '</body></html>';
				      
				      $tags = new redEVENT_tags();
				      $tags->setXref($registration->xref);
				      $tags->setSubmitkey($submit_key);
				      
				      $htmlmsg = $tags->ReplaceTags($htmlmsg);
				      $htmlmsg = str_replace('[activatelink]', $activatelink, $htmlmsg);
				      $htmlmsg = str_replace('[fullname]', $user->name, $htmlmsg);
				      
							// convert urls
							$htmlmsg = ELOutput::ImgRelAbs($htmlmsg);
							
				      $this->mailer->setBody($htmlmsg);
				      $this->mailer->setSubject($tags->ReplaceTags($eventsettings->notify_subject));

				      /* Count number of messages sent */
				      if (!$this->mailer->Send()) {
				      	RedeventHelperLog::simpleLog('Error sending notify message to submitter');
				      }
				      /* Clear the mail details */
				      $this->mailer->ClearAddresses();
        
							/* Check if the user needs to be added to Community Builder */
							if ($elsettings->comunsolution == 1) 
							{
								$q = "INSERT INTO #__comprofiler (id, user_id, avatarapproved, approved, confirmed, banned)
									VALUES (".$uid.", ".$uid.", 1, 1, 1, 0)";
								$db->setQuery($q);
								if (!$db->query()) RedeventError::raiseWarning('', JText::_($db->getErrorMsg()));
							}
  						return $registration;
						}
					}
				}
			}
		}
		
		/**
		 * Send a submission mail to the attendee and/or contact person 
		 * This will only work if the contact person has an e-mail address
		 **/		
		if (isset($eventsettings->notify) && $eventsettings->notify) 
		{
			/* Load the mailer */
			$this->Mailer();
      
			$tags = new redEVENT_tags();
			$tags->setXref($registration->xref);
			$tags->setSubmitkey($submit_key);
			
			/* Now send some mail to the attendants */
			foreach ($attendees as $attendee) 
			{
				if ($attendee->getEmail()) 
				{
					/* Check if we have all the fields */
					if (!$attendee->getUsername()) $attendee->setUsername($attendee->getEmail());
					if (!$attendee->getFullname()) $attendee->setFullname($attendee->getUsername());
		
					/* Add the email address */
					$this->mailer->AddAddress($attendee->getEmail(), $attendee->getFullname());
					
					/* build activation link */
					// TODO: use the route helper !
					$rid = $attendee->getId();
					$url = JRoute::_( JURI::root().'index.php?option=com_redevent&controller=registration&task=activate'
					     . '&confirmid='.str_replace(".", "_", $registration->uip)
					     .              'x'.$registration->xref
					     .              'x'.$registration->uid
					     .              'x'.$rid
					     .              'x'.$submit_key );
					$activatelink = '<a href="'.$url.'">'.JText::_('Activate').'</a>';
					$cancellink = JRoute::_(JURI::root().'index.php?option=com_redevent&task=cancelreg'
					                        .'&rid='.$rid.'&xref='.$registration->xref.'&submit_key='.$submit_key);
					
					/* Mail attendee */
					$htmlmsg = '<html><head><title></title></title></head><body>';
					$htmlmsg .= $eventsettings->notify_body;
					$htmlmsg .= '</body></html>';
					
					$htmlmsg = $tags->ReplaceTags($htmlmsg);
					$htmlmsg = str_replace('[activatelink]', $activatelink, $htmlmsg);
					$htmlmsg = str_replace('[cancellink]', $cancellink, $htmlmsg);
					$htmlmsg = str_replace('[fullname]', $attendee->getFullname(), $htmlmsg);
					
					// convert urls
					$htmlmsg = ELOutput::ImgRelAbs($htmlmsg);
					
					$this->mailer->setBody($htmlmsg);
					$this->mailer->setSubject($tags->ReplaceTags($eventsettings->notify_subject));
		
					/* Count number of messages sent */
					if (!$this->mailer->Send()) {
						RedeventHelperLog::simpleLog('Error sending notify message to submitted attendants');
					}
		
					/* Clear the mail details */
					$this->mailer->ClearAllRecipients();
				}
				else {
					/* Not sending mail as there is no e-mail address */
				}
			}
		}
		return $registration;
	}
	

  function notifyManagers($submit_key, $unreg = false, $reg_sid = 0)
  {
  	$this->setSubmitKey($submit_key);
  	$session = &$this->getSessionDetails();
  	
  	jimport('joomla.mail.helper');
  	$app    = &JFactory::getApplication();
  	$params = $app->getParams('com_redevent');
		$tags   = new redEVENT_tags();
		$tags->setXref($this->_xref);
		$tags->setSubmitkey($submit_key);
		if ($reg_sid) {
			$tags->addOptions(array('sids' => array($reg_sid)));
		}
  	
  	$event = $this->getSessionDetails();
  	
  	// we will use attendee details for 'from' field
  	$contact = $this->getRegistrationContactPerson($submit_key);
  	
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
  		if (JMailHelper::isEmailAddress($r->email)) {
  			$recipients[] =  array('email' => $r->email, 'name' => $r->name);	
  		}
  	}
  	
  	// redform recipients
  	$rfrecipients = $this->getRFRecipients();
  	foreach ((array) $rfrecipients as $r)
  	{
  		if (JMailHelper::isEmailAddress($r)) {
  			$recipients[] =  array('email' => $r, 'name' => '');	
  		}
  	}
  	
  	if (!count($recipients)) {
  		return true;
  	}
  	
  	$mailer = & JFactory::getMailer();
  	if ($contact && $params->get('allow_email_aliasing', 1)) {
	  	$sender = array($contact->getEmail(), $contact->getFullname());
		}
		else { // default to site settings
			$sender = array($app->getCfg('mailfrom'), $app->getCfg('sitename'));
		}
		$mailer->setSender($sender);
		$mailer->addReplyTo($sender);
		
  	foreach ($recipients as $r)
  	{
  		$mailer->addAddress($r['email'], $r['name']);
  	}
  	
  	$mail = '<HTML><HEAD>
		<STYLE TYPE="text/css">
		<!--
		  table.formanswers , table.formanswers td, table.formanswers th
			{
			    border-color: darkgrey;
			    border-style: solid;
			    text-align:left;
			}			
			table.formanswers
			{
			    border-width: 0 0 1px 1px;
			    border-spacing: 0;
			    border-collapse: collapse;
			    padding: 5px;
			}			
			table.formanswers td, table.formanswers th
			{
			    margin: 0;
			    padding: 4px;
			    border-width: 1px 1px 0 0;
			}		  
		-->
		</STYLE>
		</head>
		<BODY bgcolor="#FFFFFF">
		'.$tags->ReplaceTags($unreg ? $params->get('unregistration_notification_body') : $params->get('registration_notification_body')).'
		</body>
		</html>';
  	
		// convert urls
		$mail = ELOutput::ImgRelAbs($mail);
		
		if (!$unreg && $params->get('registration_notification_attach_rfuploads', 1))
		{
			// files submitted through redform
			$files = $this->getRFFiles();
			$filessize = 0;
			foreach ($files as $f)
			{
				$filessize += filesize($f);
			}
			
			if ($filessize < $params->get('registration_notification_attach_rfuploads_maxsize', 1500) * 1000) 
			{
				foreach ($files as $f) {
					$mailer->addAttachment($f);
				}
			}
		}
						
  	$mailer->setSubject($tags->ReplaceTags($unreg ? $params->get('unregistration_notification_subject') : $params->get('registration_notification_subject')));
  	$mailer->MsgHTML($mail);
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
  	$event = $this->getSessionDetails();
  	
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
  
	/**
	 * Initialise the mailer object to start sending mails
	 */
	private function Mailer() 
	{
		if (empty($this->mailer))
		{
			$mainframe = & JFactory::getApplication();
			jimport('joomla.mail.helper');
			/* Start the mailer object */
			$this->mailer = JFactory::getMailer();
			$this->mailer->isHTML(true);
			$this->mailer->From = $mainframe->getCfg('mailfrom');
			$this->mailer->FromName = $mainframe->getCfg('sitename');
			$this->mailer->AddReplyTo(array($mainframe->getCfg('mailfrom'), $mainframe->getCfg('sitename')));
		}
		return $this->mailer;
	}
	
	function getRegistration($submitter_id)
	{
		$query =' SELECT s.*, r.uid, r.pricegroup_id, e.unregistra '
        . ' FROM #__rwf_submitters AS s '
        . ' INNER JOIN #__redevent_register AS r ON r.sid = s.id '
        . ' INNER JOIN #__redevent_event_venue_xref AS x ON x.id = r.xref '
        . ' INNER JOIN #__redevent_events AS e ON x.eventid = e.id '
        . ' WHERE s.id = ' . $this->_db->Quote($submitter_id)
		    ;
		$this->_db->setQuery($query);
		$registration = $this->_db->loadObject();
		
		if (!$registration) {
			$this->setError(JText::_('REGISTRATION NOT VALID'));
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
	 * returns contact person for submitted registration
	 * first, looks for a user id, otherwise looks into redform fields
	 * 
	 * @param string $submit_key
	 * @return object REAttendee
	 */
	function getRegistrationContactPerson($submit_key)
	{		
		$this->setSubmitKey($submit_key);
		$attendee = new REattendee();
		
		// first, take info from joomla user is a uid is set
		$query = ' SELECT u.name, u.email '
		       . ' FROM #__redevent_register AS r '
		       . ' LEFT JOIN #__users AS u on r.uid = u.id '
		       . ' WHERE r.submit_key = '. $this->_db->Quote($submit_key)
		       . '   AND uid > 0 '
		       ;
		$this->_db->setQuery($query, 0, 1);
		$res = $this->_db->loadObject();
		
		if ($res)
		{
			$attendee->setEmail($res->email);
			$attendee->setFullname($res->name);
		}
		else // uid not set, so get the info from submission directly
		{
	  	$fields  = $this->getRFFields();
	  	$answers = $this->getRFAnswers();
			foreach ($fields as $f)
			{
				$property = 'field_'.$f->id;
				switch($f->fieldtype)
				{
					case 'email':
						$attendee->setEmail($answers[0]->fields->$property);
						break;
					case 'fullname':
						$attendee->setFullname($answers[0]->fields->$property);
						break;
					case 'username':
						$attendee->setUsername($answers[0]->fields->$property);
						break;
				}
			}
		}
		$email = $attendee->getEmail();
		if (empty($email)) {
			return false;
		}
		return $attendee;
	}
	
	/**
	 * return redform fields for this event
	 * 
	 * @return array
	 */
	function getRFFields()
	{
		if (empty($this->_rf_fields)) 
		{
			$event = $this->getSessionDetails();
			$rfcore  = new redformcore();
	  	$this->_rf_fields  = $rfcore->getFields($event->redform_id);			
		}
		return $this->_rf_fields;
	}
	
	/**
	 * returns answers array for current submit_key
	 * 
	 * @return array
	 */
	function getRFAnswers()
	{
		if (empty($this->_rf_answers)) 
		{
			$rfcore  = new redformcore();
	  	$this->_rf_answers  = $rfcore->getAnswers($this->_submit_key);			
		}
		return $this->_rf_answers;		
	}
	
	/**
	 * return selected redform recipients emails if any
	 * 
	 * @return string
	 */
	function getRFRecipients()
	{
		$fields  = $this->getRFFields();
	
		foreach ($fields as $f)
		{
			$property = 'field_'.$f->id;
			if ($f->fieldtype == 'recipients')
			{
				$answers = $this->getRFAnswers();
				$email = explode('~~~', $answers[0]->fields->$property);
				return $email;
			}
		}
		return false;
	}	
	
	/**
	 * return redform submitted files path if any
	 * 
	 * @return array
	 */
	protected function getRFFiles()
	{
		$files = array();
		$fields  = $this->getRFFields();
		$answers = $this->getRFAnswers();
		
		foreach ($fields as $f)
		{
			$property = 'field_'.$f->id;
			if ($f->fieldtype == 'fileupload')
			{
				foreach ($answers as $a)
				{
					$path = $a->fields->$property;
					if (!empty($path) && file_exists($path)) {
						$files[] = $path;
					}
				}
			}
		}
		return $files;
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
		$this->setError(JText::_('Pricegroup not found'));
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
			JError::raiseError( 403, JText::_('ALERTNOTAUTH') );
			return;
	  }
	  
	  if (!redEVENTHelper::canUnregister($xref)) {
	  	$this->setError(JText::_('COM_REDEVENT_UNREGISTRATION_NOT_ALLOWED'));
	  	return false;
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