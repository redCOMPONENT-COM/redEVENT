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
 * Class DumpHelper
 *
 * @since  3.0
 */
class DumpHelper
{
	/**
	 * Sort rows
	 *
	 * @param   array  &$rows  rows
	 *
	 * @return void
	 */
	public static function sortRows(&$rows)
	{
		// Sort sessions
		usort(
			$rows,
			function($a, $b)
			{
				// First by active status
				if ($a->active !== $b->active)
				{
					return $a->active ? -1 : 1;
				}

				// Then by category
				if ($diff = strcmp($a->categories[0], $b->categories[0]))
				{
					return $diff;
				}

				// Then by date
				// First check they are valid
				if (!(RedeventHelperDate::isValidDate($a->dates[0]) && RedeventHelperDate::isValidDate($b->dates[0])))
				{
					return RedeventHelperDate::isValidDate($a->dates[0]) ? -1 : 1;
				}

				// Then compare valid dates
				if ($diff = strtotime($a->dates[0] . ' ' . $a->times[0]) - strtotime($b->dates[0] . ' ' . $b->times[0]))
				{
					return $diff;
				}

				// Then name
				return strcmp($a->title, $b->title);
			}
		);
	}

	/**
	 * Format dates
	 *
	 * @param   object  $row  row
	 *
	 * @return array
	 */
	public static function formatDates($row)
	{
		$dates = array();

		foreach ($row->dates as $date)
		{
			if (!RedeventHelperDate::isValidDate($date))
			{
				$dates[] = 'Open date';
				continue;
			}

			$date = new DateTime($date);

			$dates[] = $date->format('d-m-Y');
		}

		return $dates;
	}

	/**
	 * Group sessions
	 *
	 * @param   array  $sessions  sessions
	 *
	 * @return array
	 */
	public static function groupSessions($sessions)
	{
		$grouped = array();

		// Group sessions by event
		foreach ($sessions as $s)
		{
			if (empty($grouped[$s->eventid]))
			{
				$grouped[$s->eventid] = new Tablerow;
			}

			$grouped[$s->eventid]->add($s);
		}

		self::sortRows($grouped);

		return $grouped;
	}

	/**
	 * Count active sessions
	 *
	 * @param   array  $rows  rows
	 *
	 * @return mixed
	 */
	public static function countActive($rows)
	{
		return array_reduce(
				$rows,
				function($carry, $item) {
					return $item->active ? $carry + 1: $carry;
				}
		);
	}
}
