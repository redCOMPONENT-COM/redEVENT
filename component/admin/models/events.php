<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

use Redevent\Model\AbstractEventsModel;

/**
 * redEVENT Component events Model
 *
 * @package  Redevent.admin
 * @since    2.0
 */
class RedeventModelEvents extends AbstractEventsModel
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_events';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'events_limit';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitstartField = 'auto';

	/**
	 * archive past xrefs
	 *
	 * @param   array  $event_ids  events ids to archive.
	 *
	 * @return array
	 */
	public function archivePast($event_ids = array())
	{
		if (!count($event_ids))
		{
			return array('sessions' => 0, 'events' => 0);
		}

		// First archive past sessions
		$query = $this->_db->getQuery(true)
			->update('#__redevent_event_venue_xref AS x')
			->set('x.published = -1')
			->where('DATE_SUB(NOW(), INTERVAL 1 DAY) > (IF (x.enddates, x.enddates, x.dates))')
			->where('x.eventid IN (' . implode(', ', $event_ids) . ')');

		$this->_db->setQuery($query);
		$this->_db->execute();

		$archivedSessions = $this->_db->getAffectedRows();

		// Then archive events that don't have published sessions any more
		$query = $this->_db->getQuery(true)
			->update('#__redevent_events AS e')
			->join('LEFT', '#__redevent_event_venue_xref AS x ON x.eventid = e.id AND x.published <> -1')
			->set('e.published = -1')
			->where('x.id IS NULL')
			->where('e.id IN (' . implode(', ', $event_ids) . ')');

		$this->_db->setQuery($query);
		$this->_db->execute();

		$archivedEvents = $this->_db->getAffectedRows();

		return array('sessions' => $archivedSessions, 'events' => $archivedEvents);
	}

	/**
	 * Retrieve a list of events, venues and times
	 *
	 * @return array
	 */
	public function getEventVenues()
	{
		$items = $this->getItems();

		if (empty($items))
		{
			return false;
		}

		$events_id = JArrayHelper::getColumn($items, 'id');

		$db = $this->_db;
		$query = $db->getQuery(true);

		$query->select('x.eventid, COUNT(*) AS total');
		$query->select('SUM(CASE WHEN x.published = 1 THEN 1 ELSE 0 END) as published');
		$query->select('SUM(CASE WHEN x.published = 0 THEN 1 ELSE 0 END) as unpublished');
		$query->select('SUM(CASE WHEN x.published = -1 THEN 1 ELSE 0 END) as archived');
		$query->select('SUM(CASE WHEN x.featured = 1 THEN 1 ELSE 0 END) as featured');
		$query->from('#__redevent_event_venue_xref AS x');
		$query->where('x.eventid IN (' . implode(', ', $events_id) . ')');
		$query->group('x.eventid');

		$db->setQuery($query);
		$sessionStats = $db->loadObjectList();

		$eventSessionsStats = array();

		foreach ((array) $sessionStats as $stat)
		{
			$eventSessionsStats[$stat->eventid] = $stat;
		}

		return $eventSessionsStats;
	}
}
