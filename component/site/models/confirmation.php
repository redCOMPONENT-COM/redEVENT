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
class RedeventModelConfirmation extends JModel
{
	public function getDetails() {
		$db = JFactory::getDBO();
		
		/* Load registration details */
		$q = "SELECT *
			FROM #__redevent_register r
			LEFT JOIN #__redevent_event_venue_xref x
			ON r.xref = x.id
			LEFT JOIN #__redevent_events e
			ON e.id = x.eventid
			WHERE submit_key = ".$db->Quote(JRequest::getVar('submit_key'));
		$db->setQuery($q);
		$details['event'] = $db->loadObject();
		
		/* Load venue details */
		$q = "SELECT *
			FROM #__redevent_venues
			WHERE id = (SELECT venueid FROM #__redevent_event_venue_xref WHERE id = ".$details['event']->xref.")";
		$db->setQuery($q);
		$details['venue'] = $db->loadObject();
		
		/* Load all subscribers */
		$q = "SELECT *
			FROM #__rwf_submitters
			WHERE submit_key = ".$db->Quote(JRequest::getVar('submit_key'));
		$db->setQuery($q);
		$details['submitters'] = $db->loadObjectList();
		
		/* Load the fields */
		$q = "SELECT field, REPLACE(LOWER(field), ' ', '') AS rawfield
			FROM #__rwf_fields
			WHERE form_id = ".$details['event']->redform_id;
		$db->setQuery($q);
		$details['fields'] = $db->loadObjectList('rawfield');
		
		/* Load all the answers */
		foreach ($details['submitters'] AS $key => $submitter) {
			$q = "SELECT *
				FROM #__rwf_forms_".$submitter->form_id."
				WHERE id = ".$submitter->answer_id;
			$db->setQuery($q);
			$details['answers'][$key] = $db->loadObject();
		}
		
		return $details;
	}
	
	/**
	 * Send e-mail confirmations
	 */
	public function getMailConfirmation() {
		global $mainframe;
		
		/* Load database connection */
		$db = JFactory::getDBO();
		
		/* Determine contact person */
		$user = JFactory::getUser();
		
		/* Get the global settings */
		$elsettings = redEVENTHelper::config();
		
		/* Get registration settings */
		$q = "SELECT *
			FROM #__redevent_register r
			WHERE submit_key = ".$db->Quote(JRequest::getVar('submit_key'));
		$db->setQuery($q);
		$registration = $db->loadObject();
		
		/* Get settings for event */
		$q = "SELECT title, notify_subject, notify_body, notify, maxattendees, activate,
					juser, confirmation_message, redform_id, submission_type_formal_offer, submission_type_formal_offer_subject,
					datdescription, redform_id, e.id AS eventid
			FROM #__redevent_events e
			LEFT JOIN #__redevent_event_venue_xref x
			ON x.eventid = e.id
			WHERE x.id = ".$registration->xref."
			";
		$db->setQuery($q);
		$eventsettings = $db->loadObject();
		
		/* Get a list of fields that are of type email/username/fullname */
		$q = "SELECT ".$db->nameQuote('field').", fieldtype 
			FROM #__rwf_fields f, #__rwf_values v
			WHERE f.id = v.field_id
			AND f.published = 1
			AND f.form_id = ".$eventsettings->redform_id."
			AND fieldtype in ('email', 'username', 'fullname')
			GROUP BY fieldtype";
		$db->setQuery($q);
		$selectfields = $db->loadObjectList();

		/* Get the username and e-mail from the redFORM database */
		$getfields = '';
		$last = end(array_keys($selectfields));
		foreach ($selectfields as $key => $selectfield) {
			$q = "SELECT LOWER(REPLACE(".$db->Quote($selectfield->field).", ' ',''))";
			$db->setQuery($q);
			$replacefield = $db->loadResult();
			$getfields .= $db->nameQuote($replacefield)." AS ".$selectfield->fieldtype;
			if ($last != $key) $getfields .= ",";
		}
		
		/* Get list of attendees */
		$q = "SELECT id, ".$getfields." 
			FROM #__rwf_forms_".$eventsettings->redform_id."
			WHERE id IN (SELECT answer_id FROM #__rwf_submitters WHERE submit_key = ".$db->Quote($registration->submit_key).")";
		$db->setQuery($q);
		$useremails = $db->loadObjectList();
		
		/* Set up user object */
		$user->set('email', $useremails[0]->email);
		if (isset($useremails[0]->username)) $user->set('username', str_replace(" ", "", $useremails[0]->username));
		else $user->set('username', $useremails[0]->email);
		if (isset($useremails[0]->fullname)) $user->set('name', $useremails[0]->fullname);
		else $user->set('name', $user->get('username'));
		
		/* Add the submission ID */
		$user->set('answer_id', $useremails[0]->id);
		
		if ($user->id > 1) {
			/* user is logged in thus contact person */
		}
		else {
			/* Register the user in Joomla if chosen*/
			if ($eventsettings->juser) {
				if (strlen($user->username) > 0 && strlen($user->email) > 0) {
					/* Check if the user already exists in Joomla with this e-mail address */
					$query = "SELECT id
							FROM #__users
							WHERE email = ".$db->Quote($user->email)."
							LIMIT 1";
					$db->setQuery($query);
					$found_id = $db->loadResult();
					if ($found_id) {
						$uid = $found_id;
					}
					else {
						/* Load the User helper */
						jimport('joomla.user.helper');
						
						// Get required system objects
						$user 		= clone(JFactory::getUser());
						$pathway 	= $mainframe->getPathway();
						$config		= JFactory::getConfig();
						$authorize	= JFactory::getACL();
						$document   = JFactory::getDocument();
						$password   = JUserHelper::genRandomPassword();
						$usersConfig = JComponentHelper::getParams( 'com_users' );
						$newUsertype = 'Registered';
						
						// Set some initial user values
						$user->set('id', 0);
						$user->set('usertype', $newUsertype);
						$user->set('gid', $authorize->get_group_id( '', $newUsertype, 'ARO' ));
						$user->set('password', md5($password));
						
						// TODO: Should this be JDate?
						$user->set('registerDate', date('Y-m-d H:i:s'));
						
						// If there was an error with registration, set the message and display form
						if (!$user->save()){
							JError::raiseWarning('', JText::_($user->getError()));
							/* We cannot save the user, need to delete already stored user data */
							
							/* Delete the redFORM entry first */
							/* Submitter records */
							$q = "DELETE FROM #__rwf_submitters
								WHERE submit_key = ".JRequest::getVar('submit_key');
							$db->setQuery($q);
							$db->query();
							
							/* All cleaned up, return false */
							return false;
						}
						else {
							/* Check if the user needs to be added to Community Builder */
							if ($elsettings->comunsolution == 1) {
								$q = "INSERT INTO #__comprofiler (id, user_id, avatarapproved, approved, confirmed, banned)
									VALUES (".$uid.", ".$uid.", 1, 1, 1, 0)";
								$db->setQuery($q);
								if (!$db->query()) JError::raiseWarning('', JText::_($db->getErrorMsg()));
							}
						}
					}
				}
			}
		}
		
		/**
		 * Send a submission mail to the attendantee and/or contactperson 
		 * This will only work if the contactperson has an e-mail address
		 **/
		 
		if (isset($eventsettings->notify) && $eventsettings->notify && !empty($user->email)) {
			/* Load the mailer */
			$this->Mailer();
			
			/* Add the email address */
			$this->mailer->AddAddress($user->email, $user->name);
			
			/* Get the activation link */
			$activatelink = '<a href="'.JRoute::_(JURI::root().'index.php?task=confirm&option=com_redevent&confirmid='.str_replace(".", "_", $registration->uip).'x'.$registration->xref.'x'.$registration->uid.'x'.$user->get('answer_id').'x'.JRequest::getVar('submit_key')).'">'.JText::_('Activate').'</a>';
			
			/* Mail attendee */
			$htmlmsg = '<html><head><title></title></title></head><body>';
			$htmlmsg .= str_replace('[activatelink]', $activatelink, $eventsettings->notify_body);
			
			
			/* Check if user was registered */
			if (isset($password)) {
				$htmlmsg .= '<br /><br />';
				$reginfo = nl2br(JText::_('INFORM_USERNAME'));
				$reginfo = str_replace('[fullname]', $user->name, $reginfo);
				$reginfo = str_replace('[username]', $user->username, $reginfo);
				$reginfo = str_replace('[password]', $password, $reginfo);
				$htmlmsg .= $reginfo;
			}
			
			$htmlmsg .= '</body></html>';
			$tags = new redEVENT_tags;
			$this->mailer->setBody($tags->ReplaceTags($htmlmsg));
			$this->mailer->setSubject($tags->ReplaceTags($eventsettings->notify_subject));
			
			/* Count number of messages sent */
			$this->mailer->Send();
			
			/* Clear the mail details */
			$this->mailer->ClearAddresses();
			
			/* Now send some mail to the attendants */
			if (JRequest::getInt('notify_attendants', false)) {
				foreach ($useremails as $key => $useremail) {
					if (isset($useremail->email) && !empty($useremail->email)) {
						
						/* Check if we have all the fields */
						if (!isset($useremail->username)) $useremail->username = $useremail->email;
						if (!isset($useremail->fullname)) $useremail->fullname = $useremail->username;
						
						/* Add the email address */
						$this->mailer->AddAddress($useremail->email, $useremail->fullname);
						
						/* Mail attendee */
						$htmlmsg = '<html><head><title></title></title></head><body>';
						$htmlmsg .= str_replace('[activatelink]', $activatelink, $eventsettings->notify_body);
						
						
						/* Check if user was registered */
						if (isset($password)) {
							$htmlmsg .= '<br /><br />';
							$reginfo = nl2br(JText::_('INFORM_USERNAME'));
							$reginfo = str_replace('[fullname]', $useremail->fullname, $reginfo);
							$reginfo = str_replace('[username]', $useremail->username, $reginfo);
							$reginfo = str_replace('[password]', $password, $reginfo);
							$htmlmsg .= $reginfo;
						}
						
						$htmlmsg .= '</body></html>';
						
						$this->mailer->setBody($htmlmsg);
						$this->mailer->Subject = $eventsettings->notify_subject;
						
						/* Count number of messages sent */
						$this->mailer->Send();
						
						/* Clear the mail details */
						$this->mailer->ClearAddresses();
					}
					else { 
						/* Not sending mail as there is no e-mail address */
					}
				}
			}
		}
		
		return $registration;
	}
	
	/**
     * Initialise the mailer object to start sending mails
     */
	private function Mailer() {
		global $mainframe;
		jimport('joomla.mail.helper');
		/* Start the mailer object */
		$this->mailer = JFactory::getMailer();
		$this->mailer->isHTML(true);
		$this->mailer->From = $mainframe->getCfg('mailfrom');
		$this->mailer->FromName = $mainframe->getCfg('sitename');
		$this->mailer->AddReplyTo(array($mainframe->getCfg('mailfrom'), $mainframe->getCfg('sitename')));
	}
	
	/**
	 * Cancel confirmation
	 */
	public function getCancelConfirmation() {
		$db = JFactory::getDBO();
		$submit_key = JRequest::getVar('submit_key');
		
		/* Get the answer ID's to delete */
		$q = "SELECT id, answer_id, form_id
			FROM #__rwf_submitters
			WHERE submit_key = ".$db->Quote($submit_key);
		$db->setQuery($q);
		$attendees_ids = $db->loadObjectList();
		
		/* Remove the answers */
		foreach ($attendees_ids as $key => $attendee) {
			$q = "DELETE FROM #__rwf_forms_".$attendee->form_id."
				WHERE id = ".$attendee->answer_id;
			$db->setQuery($q);
			$db->query();
		}
		
		/* Delete redFORM entry */
		$q = "DELETE FROM #__rwf_submitters
			WHERE submit_key = ".$db->Quote($submit_key);
		$db->setQuery($q);
		$db->query();
		
		/* Delete redEVENT entry */
		$q = "DELETE FROM #__redevent_register
			WHERE submit_key = ".$db->Quote($submit_key);
		$db->setQuery($q);
		$db->query();
	}
	
	/**
	 * Check the submit key
	 */
	public function getCheckSubmitKey() {
		$db = JFactory::getDBO();
		$submit_key = JRequest::getVar('submit_key');
		
		/* Load registration details */
		$q = "SELECT submit_key
			FROM #__redevent_register r
			WHERE submit_key = ".$db->Quote($submit_key);
		$db->setQuery($q);
		$result = $db->loadResult();
		if ($result == $submit_key) return true;
		else return false;
	}
}
?>