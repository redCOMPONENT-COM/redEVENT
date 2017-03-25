<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008-2015 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * redEVENT Component waiting list Model
 *
 * @package  Redevent.admin
 * @since    0.9
 */
class RedeventModelWaitinglist extends RModel
{
	private $xref = null;

	private $eventid = null;

	private $event_data = null;

	private $move_on = null;

	private $move_off = null;

	private $move_on_ids = array();

	private $move_off_ids = array();

	private $mailer = null;

	/**
	 * set xref
	 *
	 * @param   int  $id  xref id
	 *
	 * @return integer
	 */
	public function setXrefId($id)
	{
		$this->xref = $id;

		// Get the eventdata
		$this->getEventData();

		return $this->xref;
	}

	/**
	 * set event id
	 *
	 * @param   int  $id  event id
	 *
	 * @return void
	 */
	public function setEventId($id)
	{
		$this->eventid = $id;
	}

	/**
	 * Clean data
	 *
	 * @return void
	 */
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
		else
		{
			if (!$this->ProcessWaitingList())
			{
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
			elseif ($this->event_data->maxattendees < $this->waitinglist[0]->total)
			{
				/* Need to move people on the waitinglist */
				$this->move_on = $this->waitinglist[0]->total - $this->event_data->maxattendees;
				$this->MoveOnWaitingList();
			}
			elseif ($this->event_data->maxattendees > $this->waitinglist[0]->total)
			{
				/* Need to move people off the waitinglist */
				$this->move_off = $this->event_data->maxattendees - $this->waitinglist[0]->total;
				$this->MoveOffWaitingList();
			}
		}
		elseif (isset($this->waitinglist[1]))
		{
			/* Nobody going yet, maximum number of attendees can go off the waitinglist */
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
		$query = $this->_db->getQuery(true);

		$query->select('id')
			->from('#__redevent_event_venue_xref')
			->where('eventid = ' . $this->eventid);

		return $this->_db->loadColumn();
	}

	/**
	 * Load the number of people that are confirmed and if they are on or off
	 * the waitinglist
	 *
	 * @return array indexed by 0|1 (attending | waiting)
	 */
	public function getWaitingList()
	{
		$query = $this->_db->getQuery(true);

		$query->select('r.waitinglist, COUNT(r.id) AS total')
			->from('#__redevent_register AS r')
			->where('r.xref = ' . $this->xref)
			->where('r.confirmed = 1')
			->where('r.cancelled = 0')
			->group('r.waitinglist');

		$this->_db->setQuery($query);
		$this->waitinglist = $this->_db->loadObjectList('waitinglist');

		return $this->waitinglist;
	}

	/**
	 * Move people off the waitinglist
	 *
	 * @return boolean
	 */
	private function MoveOffWaitingList()
	{
		$query = $this->_db->getQuery(true);

		$query->select('id')
			->from('#__redevent_register')
			->where('xref = ' . $this->xref)
			->where('waitinglist = 1')
			->where('confirmed = 1')
			->where('cancelled = 0')
			->order('confirmdate');

		$this->_db->setQuery($query, 0, $this->move_off);
		$this->move_off_ids = $this->_db->loadColumn();

		if (!count($this->move_off_ids))
		{
			return true;
		}

		foreach ($this->move_off_ids as $rid)
		{
			$attendee = new RedeventAttendee($rid);

			if (!$attendee->toggleWaitingListStatus(0))
			{
				$this->setError($attendee->getError());

				return false;
			}
		}

		return true;
	}

	/**
	 * Move people on the waiting list
	 *
	 * @return boolean
	 */
	private function MoveOnWaitingList()
	{
		$query = $this->_db->getQuery(true);

		$query->select('id')
			->from('#__redevent_register')
			->where('xref = ' . $this->xref)
			->where('waitinglist = 0')
			->where('confirmed = 1')
			->where('cancelled = 0')
			->order('confirmdate DESC');

		$this->_db->setQuery($query, 0, $this->move_on);
		$this->move_on_ids = $this->_db->loadColumn();

		if (!count($this->move_on_ids))
		{
			return true;
		}

		foreach ($this->move_on_ids as $rid)
		{
			$attendee = new RedeventAttendee($rid);

			if (!$attendee->toggleWaitingListStatus(1))
			{
				$this->setError($attendee->getError());

				return false;
			}
		}

		return true;
	}

	/**
	 * Get the basic event information
	 *
	 * @return array
	 */
	private function getEventData()
	{
		if (empty($this->event_data))
		{
			if (!$this->xref)
			{
				$error = JText::_('COM_REDEVENT_xref_not_set_in_waitinglist_model');
				$this->setError($error);

				return false;
			}

			$query = $this->_db->getQuery(true);

			$query->select('x.maxattendees, x.maxwaitinglist')
				->select('t.notify_off_list_body, t.notify_off_list_subject')
				->select('t.notify_on_list_body, t.notify_on_list_subject')
				->select('t.redform_id')
				->from('#__redevent_event_venue_xref AS x')
				->join('INNER', '#__redevent_events e ON x.eventid = e.id')
				->join('INNER', '#__redevent_event_template AS t ON t.id =  e.template_id')
				->where('x.id = ' . $this->xref);

			$this->_db->setQuery($query);
			$this->event_data = $this->_db->loadObject();
		}

		return $this->event_data;
	}

	/**
	 * put people off from the waiting list
	 *
	 * @param   array  $register_ids  register ids to put off waiting
	 *
	 * @return boolean
	 */
	public function putOffWaitingList($register_ids)
	{
		if (!count($register_ids))
		{
			return true;
		}

		/* Get attendee total first */
		$this->getEventData();
		$this->getWaitingList();

		/* Check if there are too many ppl going to the event */
		$remaining = $this->event_data->maxattendees - $this->waitinglist[0]->total;

		// If there are places remaining, or no limit, put people off the list.
		if ($this->event_data->maxattendees == 0 || $remaining)
		{
			/* Need to move people on the waitinglist */
			// We can only take as many new people off the list as there are remaining places
			if ($this->event_data->maxattendees)
			{
				$this->move_off_ids = array_slice($register_ids, 0, $remaining);
			}
			else
			{
				$this->move_off_ids = $register_ids;
			}

			foreach ($this->move_off_ids as $rid)
			{
				$attendee = new RedeventAttendee($rid);

				if (!$attendee->toggleWaitingListStatus(0))
				{
					$this->setError($attendee->getError());

					return false;
				}
			}
		}
		else
		{
			$this->setError(JText::_('COM_REDEVENT_NOT_ENOUGH_PLACES_LEFT'));

			return false;
		}

		return true;
	}

	/**
	 * put people on the waiting list
	 *
	 * @param   array  $register_ids  register ids to put on waiting list
	 *
	 * @return boolean
	 */
	public function putOnWaitingList($register_ids)
	{
		/* Check if there are too many ppl going to the event */
		if (count($register_ids))
		{
			foreach ($register_ids as $rid)
			{
				$attendee = new RedeventAttendee($rid);

				if (!$attendee->toggleWaitingListStatus(1))
				{
					$this->setError($attendee->getError());

					return false;
				}
			}
		}
		else
		{
			return true;
		}

		return true;
	}
}
