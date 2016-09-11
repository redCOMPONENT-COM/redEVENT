<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * RedEvent Model Bundle
 *
 * @package  Redevent.admin
 * @since    3.2.0
 */
class RedeventModelBundle extends RModelAdmin
{
	/**
	 * Get bundle events
	 *
	 * @param   int  $id  bundle id
	 *
	 * @return array
	 */
	public function getEvents($id)
	{
		$query = $this->_db->getQuery(true)
			->select('e.id AS event_id, e.title, s.session_id')
			->from('#__redevent_bundle_event AS be')
			->innerJoin('#__redevent_events AS e On e.id = be.event_id')
			->leftJoin('#__redevent_bundle_event_session AS s ON s.bundle_event_id = be.id')
			->leftJoin('#__redevent_event_venue_xref AS x ON x.id = s.session_id')
			->where('be.bundle_id = ' . $id)
			->order('e.title, x.dates');

		$this->_db->setQuery($query);

		if (!$res = $this->_db->loadObjectList())
		{
			return false;
		}

		$events = array();

		foreach ($res as $row)
		{
			if (!isset($events[$row->event_id]))
			{
				$obj = new stdclass;
				$obj->id = $row->event_id;
				$obj->title = $row->title;
				$obj->sessions = array();

				$events[$row->event_id] = $obj;
			}

			if ($row->session_id)
			{
				$session = RedeventEntitySession::load($row->session_id);
				$data = array(
					'id' => $session->id,
					'formatted_start_date' => $session->getFormattedStartDate(),
					'venue' => $session->getVenue()->venue,
				);

				$events[$row->event_id]->sessions[] = $data;
			}
		}

		$events = array_values($events);

		return $events;
	}

	/**
	 * Save bundle events (and optional sessions)
	 *
	 * @param   int       $bundle_id  bundle id
	 * @param   Object[]  $events     eventd to add
	 *
	 * @return void
	 */
	public function saveEvents($bundle_id, $events)
	{
		// First purge current records
		$query = $this->_db->getQuery(true)
			->delete('#__redevent_bundle_event')
			->where('bundle_id = ' . $bundle_id);

		$this->_db->setQuery($query);
		$this->_db->execute();

		// Then add new
		foreach ($events as $event)
		{
			$all_dates = empty($event->sessions) ? 1 : 0;

			$query = $this->_db->getQuery(true)
				->insert('#__redevent_bundle_event')
				->columns('bundle_id, event_id, all_dates')
				->values($bundle_id . ', ' . $event->id . ', ' . $all_dates);

			$this->_db->setQuery($query);
			$this->_db->execute();

			$bundleEventId = $this->_db->insertid();

			if ($all_dates)
			{
				continue;
			}

			$query = $this->_db->getQuery(true)
				->insert('#__redevent_bundle_event_session')
				->columns('bundle_event_id, session_id');

			foreach ($event->sessions as $session_id)
			{
				$query->values($bundleEventId . ', ' . $session_id);
			}

			$this->_db->setQuery($query);
			$this->_db->execute();
		}
	}
}
