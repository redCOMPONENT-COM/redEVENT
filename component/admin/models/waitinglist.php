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

/**
 * EventList Component Event Model
 *
 * @package Joomla
 * @subpackage redEVENT
 * @since		0.9
 */
class RedeventModelWaitinglist extends RModel {

	private $xref = null;
	private $eventid = null;
	var $event_data = null;
	private $move_on = null;
	private $move_off = null;
	private $move_on_ids = array();
	private $move_off_ids = array();
	private $mailer = null;
	private $taghelper = null;

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
	 * set xref
	 *
	 * @param int $id
	 */
	public function setXrefId($id)
	{
		$this->xref = $id;
		/* Get the eventdata */
		$this->getEventData();
	}

	/**
	 * set event id
	 *
	 * @param int $id
	 */
	public function setEventId($id)
	{
		$this->eventid = $id;
	}

	/* Cleans up the array */
	private function clean()
	{
		$this->event_data = null;
		$this->move_on = null;
		$this->move_off = null;
		$this->move_on_ids = null;
		$this->move_off_ids = null;
		$this->mailer = null;
	}

	/**
	 * update the waiting list
	 *
	 * @return boolean true on success
	 */
	public function UpdateWaitingList()
	{
    $this->getEventData();

		/* If there is an event ID set, update all waitinglists for that event */
		if (!is_null($this->eventid))
		{
			$xrefids = $this->getXrefIds();
			foreach ($xrefids AS $key => $xref)
			{
				$this->setXrefId($xref);
				$this->ProcessWaitingList();
				$this->clean();
			}
		}
		else {
			if (!$this->ProcessWaitingList()) {
				return false;
			}
		}
		return true;
	}



	/**
	 * Process waitinglist
	 *
	 * @return boolean true on success
	 */
	private function ProcessWaitingList()
	{
		/* Get attendee total first */
		$this->getWaitingList();

		/* Check if there are too many ppl going to the event */
		if (isset($this->waitinglist[0]))
		{
			if ($this->event_data->maxattendees == 0)
			{
				if (isset($this->waitinglist[1]))
				{
					// No more limit, and still user on waiting list !
					$this->move_off = $this->waitinglist[1]->total;
					$this->MoveOffWaitingList();
				}
			}
			else if ($this->event_data->maxattendees < $this->waitinglist[0]->total)
			{
				/* Need to move people on the waitinglist */
				$this->move_on = $this->waitinglist[0]->total - $this->event_data->maxattendees;
				$this->MoveOnWaitingList();
			}
			else if ($this->event_data->maxattendees > $this->waitinglist[0]->total)
			{
				/* Need to move people off the waitinglist */
				$this->move_off = $this->event_data->maxattendees - $this->waitinglist[0]->total;
				$this->MoveOffWaitingList();
			}
		}
		/* Nobody going yet, maximum number of attendees can go off the waitinglist */
		else if (isset($this->waitinglist[1]))
		{
			/* Need to move people off the waitinglist */
			$this->move_off = $this->event_data->maxattendees;
			$this->MoveOffWaitingList();
		}
		return true;
	}

	/**
	 * Get the xref IDs for an event
	 *
	 * @return array
	 */
	private function getXrefIds()
	{
		$db = $this->_db;
		$q = "SELECT id FROM #__redevent_event_venue_xref WHERE eventid = ".$this->eventid;
		$db->setQuery($q);
		return $db->loadResultArray();
	}

	/**
	 * Load the number of people that are confirmed and if they are on or off
	 * the waitinglist
	 *
	 * @return array indexed by 0|1 (attending | waiting)
	 */
	public function getWaitingList()
	{
		$db = $this->_db;
		$q = ' SELECT r.waitinglist, COUNT(r.id) AS total '
		   . ' FROM #__redevent_register AS r '
		   . ' WHERE r.xref = '.$this->xref
		   . '   AND r.confirmed = 1 '
		   . '   AND r.cancelled = 0 '
		   . ' GROUP BY r.waitinglist ';
		$db->setQuery($q);
		$this->waitinglist = $db->loadObjectList('waitinglist');
		return $this->waitinglist;
	}

	/**
	 * Move people off the waitinglist
	 */
	private function MoveOffWaitingList()
	{
		$db = $this->_db;
		$q = "SELECT id
			FROM #__redevent_register
			WHERE xref = ".$this->xref."
			AND waitinglist = 1
			AND confirmed = 1
		  AND cancelled = 0
			ORDER BY confirmdate
			LIMIT ".$this->move_off;
		$db->setQuery($q);
		$this->move_off_ids = $db->loadResultArray();

		if (!count($this->move_off_ids)) {
			return true;
		}

		foreach ($this->move_off_ids as $rid)
		{
			$attendee = new RedeventAttendee($rid);
			if (!$attendee->toggleWaitingListStatus(0)) {
				$this->setError($attendee->getError());
				return false;
			}
		}
		return true;
	}

	/**
	 * Move people on the waiting list
	 */
	private function MoveOnWaitingList()
	{
		$db = $this->_db;
		$q = "SELECT id
			FROM #__redevent_register
			WHERE xref = ".$this->xref."
			AND waitinglist = 0
			AND confirmed = 1
		  AND cancelled = 0
			ORDER BY confirmdate DESC
			LIMIT ".$this->move_on;
		$db->setQuery($q);
		$this->move_on_ids = $db->loadResultArray();


		if (!count($this->move_on_ids)) {
			return true;
		}

		foreach ($this->move_on_ids as $rid)
		{
			$attendee = new RedeventAttendee($rid);
			if (!$attendee->toggleWaitingListStatus(1)) {
				$this->setError($attendee->getError());
				return false;
			}
		}
		return true;
	}

	/**
	 * Get the basic event information
	 */
	private function getEventData()
	{
	  if (empty($this->event_data))
	  {
	  	if (!$this->xref) {
	  		$error = JText::_('COM_REDEVENT_xref_not_set_in_waitinglist_model');
	  		JError::raiseWarning(0, $error);
	  		$this->setError($error);
	  		return false;
	  	}
  		$db = $this->_db;
  		$q = ' SELECT x.maxattendees, x.maxwaitinglist, '
  		   . ' e.notify_off_list_body, e.notify_on_list_body, e.notify_off_list_subject, e.notify_on_list_subject, '
  		   . ' e.redform_id '
  		   . ' FROM #__redevent_event_venue_xref x  '
  		   . ' LEFT JOIN #__redevent_events e ON x.eventid = e.id  '
  		   . ' WHERE x.id = '.$this->xref;
  		$db->setQuery($q);
  		$this->event_data = $db->loadObject();
	  }
	  return $this->event_data;
	}

	/**
	 * put people off from the waiting list
	 *
	 * @param array $answer_ids to put off waiting
	 */
	public function putOffWaitingList($register_ids)
	{
	  if (!count($register_ids)) {
	    return true;
	  }

	  /* Get attendee total first */
    $this->getEventData();
    $this->getWaitingList();

    /* Check if there are too many ppl going to the event */
    $remaining = $this->event_data->maxattendees - $this->waitinglist[0]->total;

    // if there are places remaining, or no limit, put people off the list.
    if ($this->event_data->maxattendees == 0 || $remaining)
    {
      /* Need to move people on the waitinglist */
      // we can only take as many new people off the list as there are remaining places
      if ($this->event_data->maxattendees) {
        $this->move_off_ids = array_slice($register_ids, 0, $remaining);
      }
      else {
        $this->move_off_ids = $register_ids;
      }

      foreach ($this->move_off_ids as $rid)
      {
      	$attendee = new RedeventAttendee($rid);
				if (!$attendee->toggleWaitingListStatus(0)) {
					$this->setError($attendee->getError());
					return false;
				}
      }
    }
    else {
      $this->setError(JText::_('COM_REDEVENT_NOT_ENOUGH_PLACES_LEFT'));
      return false;
      // event is full already
    }
    return true;
	}

  /**
   * put people on the waiting list
   *
   * @param array $answer_ids to put on waiting list
   */
  public function putOnWaitingList($register_ids)
  {
    /* Check if there are too many ppl going to the event */
    if (count($register_ids))
    {
      foreach ($register_ids as $rid)
      {
      	$attendee = new RedeventAttendee($rid);
				if (!$attendee->toggleWaitingListStatus(1)) {
					$this->setError($attendee->getError());
					return false;
				}
      }
    }
    else {
      return true;
      // event is full already
    }
    return true;
  }
}
