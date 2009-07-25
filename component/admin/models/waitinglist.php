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
class RedEventModelWaitinglist extends JModel {
	
	private $xref = null;
	private $eventid = null;
	private $event_data = null;
	private $move_on = null;
	private $move_off = null;
	private $move_on_ids = array();
	private $move_off_ids = array();
	private $mailer = null;
	
	/**
	 * Constructor
	 *
	 * @since 0.9
	 */
	function __construct()
	{
		parent::__construct();

	}
	
	public function setXrefId($id) {
		$this->xref = $id;
		/* Get the eventdata */
		$this->getEventData();
	}
	
	public function setEventId($id) {
		$this->eventid = $id;
	}
	
	/* Cleans up the array */
	private function clean() {
		$this->event_data = null;
		$this->move_on = null;
		$this->move_off = null;
		$this->move_on_ids = null;
		$this->move_off_ids = null;
		$this->mailer = null;
	}
	
	public function UpdateWaitingList() 
	{
		// nothing to do if there is no max
		if (empty($this->event_data->maxattendees)) {
			return true;
		}
		
		/* If there is an event ID set, update all waitinglists for that event */
		if (!is_null($this->eventid)) {
			$xrefids = $this->getXrefIds();
			foreach ($xrefids AS $key => $xref) {
				$this->setXrefId($xref);
				$this->ProcessWaitingList();
				$this->clean();
			}
		}
		else {
			$this->ProcessWaitingList();
		}
		
	}
	
	/**
	 * Process waitinglist
	 */
	 private function ProcessWaitingList() {
		/* Get attendee total first */
		$this->getWaitingList();
		
		/* Check if there are too many ppl going to the event */
		if (isset($this->waitinglist[0])) {
			if ($this->event_data->maxattendees < $this->waitinglist[0]->total) {
				/* Need to move people on the waitinglist */
				$this->move_on = $this->waitinglist[0]->total - $this->event_data->maxattendees;
				$this->MoveOnWaitingList();
			}
			else if ($this->event_data->maxattendees > $this->waitinglist[0]->total) {
				/* Need to move people off the waitinglist */
				$this->move_off = $this->event_data->maxattendees - $this->waitinglist[0]->total;
				$this->MoveOffWaitingList();
			}
		}
		/* Nobody going yet, maximum number of attendees can go off the waitinglist */
		else if (isset($this->waitinglist[1])) {
			/* Need to move people off the waitinglist */
			$this->move_off = $this->event_data->maxattendees;
			$this->MoveOffWaitingList();
		}
		/* Mail the attendees of their new status */
		if (count($this->move_on_ids) > 0) {
			/* Mail attendees they have been moved on the waitlinglist */
			$this->SendMail('on');
		}
		
		if (count($this->move_off_ids) > 0) {
			/* Mail attendees they have been moved off the waitlinglist */
			$this->SendMail('off');
		}
	 }
	
	/**
	 * Get the xref IDs for an event
	 */
	private function getXrefIds() {
		$db = JFactory::getDBO();
		$q = "SELECT id FROM #__redevent_event_venue_xref WHERE eventid = ".$this->eventid;
		$db->setQuery($q);
		return $db->loadResultArray();
	}
	
	/**
	 * Load the number of people that are confirmed and if they are on or off
	 * the waitinglist
	 */
	public function getWaitingList() {
		$db = JFactory::getDBO();
		$q = "SELECT waitinglist, COUNT(id) AS total
			FROM #__rwf_submitters
			WHERE xref = ".$this->xref."
			AND confirmed = 1
			GROUP BY waitinglist";
		$db->setQuery($q);
		$this->waitinglist = $db->loadObjectList('waitinglist');
	}
	
	/**
	 * Move people off the waitinglist
	 */
	private function MoveOffWaitingList() {
		$db = JFactory::getDBO();
		$q = "SELECT answer_id
			FROM #__rwf_submitters
			WHERE xref = ".$this->xref."
			AND waitinglist = 1
			AND confirmed = 1
			ORDER BY confirmdate
			LIMIT ".$this->move_off;
		$db->setQuery($q);
		$this->move_off_ids = $db->loadResultArray();
		
		$q = "UPDATE #__rwf_submitters
			SET waitinglist = 0
			WHERE xref = ".$this->xref."
			AND waitinglist = 1
			AND confirmed = 1
			ORDER BY confirmdate
			LIMIT ".$this->move_off;
		$db->setQuery($q);
		$db->query();
	}
	
	/**
	 * Move people on the waiting list
	 */
	private function MoveOnWaitingList() {
		$db = JFactory::getDBO();
		$q = "SELECT answer_id
			FROM #__rwf_submitters s
			WHERE xref = ".$this->xref."
			AND waitinglist = 0
			AND confirmed = 1
			ORDER BY confirmdate DESC
			LIMIT ".$this->move_on;
		$db->setQuery($q);
		$this->move_on_ids = $db->loadResultArray();
		
		$q = "UPDATE #__rwf_submitters
			SET waitinglist = 1
			WHERE xref = ".$this->xref."
			AND waitinglist = 0
			AND confirmed = 1
			ORDER BY confirmdate DESC
			LIMIT ".$this->move_on;
		$db->setQuery($q);
		$db->query();
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
	
	private function SendMail($type) {
		global $mainframe;
		
		if ($type == 'off') {
			$update_ids = $this->move_off_ids;
			$body = nl2br($this->event_data->notify_off_list_body);
			$subject = $this->event_data->notify_off_list_subject;
		}
		else if ($type == 'on') {
			$update_ids = $this->move_on_ids;
			$body = nl2br($this->event_data->notify_on_list_body);
			$subject = $this->event_data->notify_on_list_subject;
		}
		
		/* Get the DB */
		$db = JFactory::getDBO();
		
		/* Find out what the fieldname is for the email field */
		$q = "SELECT f.id, f.field, v.fieldtype 
			FROM #__rwf_fields f, #__rwf_values v
			WHERE f.id = v.field_id
			AND f.published = 1
			AND f.form_id = ".$this->event_data->redform_id."
			AND fieldtype in ('email')
			LIMIT 1";
		$db->setQuery($q);
		$selectfield = $db->loadResult();
		
		if ($selectfield) 
		{
			/* Inform the ids that they can attend the event */
			$subids = "id = ".implode(" OR id = ", $update_ids);
			$fieldname = 'field_'. $selectfield;
			$query = "SELECT ".$fieldname."
					FROM #__rwf_forms_".$this->event_data->redform_id."
					WHERE ".$subids;
			$db->setQuery($query);
			$addresses = $db->loadResultArray();
			
			/* Check if there are any addresses to be mailed */
			if (count($addresses) > 0) {
				/* Start mailing */
				$this->Mailer();
				foreach ($addresses as $key => $email) {
					/* Send a off mailinglist mail to the submitter if set */
					/* Add the email address */
					$this->mailer->AddAddress($email);
					
					/* Mail submitter */
					$htmlmsg = '<html><head><title></title></title></head><body>'.$body.'</body></html>';
					$this->mailer->setBody($htmlmsg);
					$this->mailer->setSubject($subject);
					
					/* Send the mail */
					if (!$this->mailer->Send()) {
						$mainframe->enqueueMessage(JText::_('THERE WAS A PROBLEM SENDING MAIL'));
						RedeventHelperLog::simpleLog('Error sending mail on/off waiting list');
					}
					
					/* Clear the mail details */
					$this->mailer->ClearAddresses();
				}
			}
		}
	}
	
	/**
	 * Get the basic event information
	 */
	private function getEventData() {
		$db = JFactory::getDBO();
		$q = "SELECT x.maxattendees, x.maxwaitinglist, e.notify_off_list_body,
			e.notify_on_list_body, e.notify_off_list_subject, e.notify_on_list_subject,
			e.redform_id
			FROM #__redevent_event_venue_xref x
			LEFT JOIN #__redevent_events e
			ON x.eventid = e.id
			WHERE x.id = ".$this->xref;
		$db->setQuery($q);
		$this->event_data = $db->loadObject();
	}
}
?>