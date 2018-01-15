<?php
/**
 * @package     Redevent.Plugin
 * @subpackage  Ibc.Sessionsdump
 *
 * @copyright   Copyright (C) 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

/**
 * Class DumpModel
 *
 * @since  3.0
 */
class DumpModel
{
	/**
	 * @var JDatabase
	 */
	private $db;

	/**
	 * DumpModel constructor.
	 */
	public function __construct()
	{
		$this->db = JFactory::getDbo();
	}

	/**
	 * Get items
	 *
	 * @return array|mixed
	 */
	public function getItems()
	{
		$sessions = $this->getSessions();
		$sessions = $this->addCategories($sessions);
		$sessions = $this->addAttendees($sessions);
		$sessions = $this->addPrices($sessions);

		// Filter sessions from "Webinarer" and "Events"
		$sessions = array_filter(
				$sessions,
				function($session)
				{
					return (!in_array("Webinarer", $session->categories) && !in_array("Events", $session->categories));
				}
		);

		return $sessions;
	}

	/**
	 * Get sessions
	 *
	 * @return mixed
	 */
	private function getSessions()
	{
		$query = $this->db->getQuery(true)
			->select('e.title, x.id, x.dates, x.enddates, x.times, x.endtimes, x.eventid')
			->select('e.published AS event_state, x.published AS session_state')
			->select('x.custom5, x.custom8')
			->select('v.venue')
			->select('k.id AS reditem_id')
			->from('#__redevent_event_venue_xref AS x')
			->innerJoin('#__redevent_events AS e ON e.id = x.eventid')
			->innerJoin('#__redevent_venues AS v ON v.id = x.venueid')
			->leftJoin('#__reditem_types_kursus_1 AS k ON k.event = e.id')
			// Only published and archived (active and inactive)
			->where('e.published <> 0')
			->where('x.published <> 0')
			->order('x.published = 1 DESC, x.dates = 0 DESC, x.dates DESC, e.title ASC');

		$this->db->setQuery($query);

		return $this->db->loadObjectList('id');
	}

	/**
	 * adds categories property to event rows
	 *
	 * @param   array  $sessions  sessions of events
	 *
	 * @return array
	 */
	private function addCategories($sessions)
	{
		$ids = array_keys($sessions);

		$query = $this->db->getQuery(true)
			->select('session.id AS session_id, c.name')
			->from('#__redevent_categories as c')
			->join('INNER', '#__redevent_event_category_xref as x ON x.category_id = c.id')
			->join('INNER', '#__redevent_event_venue_xref as session ON session.eventid = x.event_id')
			->where('session.id IN (' . implode(', ', $ids) . ')');

		$this->db->setQuery($query);
		$res = $this->db->loadObjectList();

		foreach ($res as $row)
		{
			if (empty($sessions[$row->session_id]->categories))
			{
				$sessions[$row->session_id]->categories = array();
			}

			$sessions[$row->session_id]->categories[] = $row->name;
		}

		// Sort categories
		foreach ($sessions as $session)
		{
			$categories = $session->categories;
			sort($categories);
			$session->categories = $categories;
		}

		return $sessions;
	}

	/**
	 * adds registered (int) and waiting (int) properties to rows.
	 *
	 * @param   array  $sessions  sessions
	 *
	 * @return array
	 */
	private function addAttendees($sessions)
	{
		foreach ((array) $sessions as $k => $r)
		{
			$q = ' SELECT r.waitinglist, COUNT(r.id) AS total '
				. ' FROM #__redevent_register AS r '
				. ' WHERE r.xref = ' . $this->db->Quote($r->id)
				// . ' AND r.confirmed = 1 '
				. ' AND r.cancelled = 0 '
				. ' GROUP BY r.waitinglist ';

			$this->db->setQuery($q);
			$res = $this->db->loadObjectList('waitinglist');

			$sessions[$k]->registered = (isset($res[0]) ? $res[0]->total : 0);
			$sessions[$k]->waiting = (isset($res[1]) ? $res[1]->total : 0);
		}

		return $sessions;
	}

	/**
	 * adds prices to rows.
	 *
	 * @param   array  $rows  rows
	 *
	 * @return array
	 */
	private function addPrices($rows)
	{
		if (!$rows)
		{
			return $rows;
		}

		$db = $this->db;

		$ids = array_keys($rows);

		$query = ' SELECT sp.*, p.name, p.alias, p.image, p.tooltip, f.currency, '
			. ' CASE WHEN CHAR_LENGTH(p.alias) THEN CONCAT_WS(\':\', p.id, p.alias) ELSE p.id END as slug '
			. ' FROM #__redevent_sessions_pricegroups AS sp '
			. ' INNER JOIN #__redevent_pricegroups AS p on p.id = sp.pricegroup_id '
			. ' INNER JOIN #__redevent_event_venue_xref AS x on x.id = sp.xref '
			. ' INNER JOIN #__redevent_events AS e on e.id = x.eventid '
			. ' LEFT JOIN #__rwf_forms AS f on e.redform_id = f.id '
			. ' WHERE sp.xref IN (' . implode(",", $ids) . ')'
			. ' ORDER BY p.ordering ASC ';

		$db->setQuery($query);
		$res = $db->loadObjectList();

		// Sort this out
		$prices = array();

		foreach ((array) $res as $p)
		{
			if (!isset($prices[$p->xref]))
			{
				$prices[$p->xref] = array($p);
			}
			else
			{
				$prices[$p->xref][] = $p;
			}
		}

		// Add to rows
		foreach ($rows as $k => $r)
		{
			if (isset($prices[$r->id]))
			{
				$rows[$k]->prices = $prices[$r->id];
			}
			else
			{
				$rows[$k]->prices = null;
			}
		}

		return $rows;
	}
}

