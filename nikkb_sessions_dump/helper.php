<?php

class DumpHelper
{
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
				if (!(redEVENTHelper::isValidDate($a->dates[0]) && redEVENTHelper::isValidDate($b->dates[0])))
				{
					return redEVENTHelper::isValidDate($a->dates[0]) ? -1 : 1;
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

	public static function formatDates($row)
	{
		$dates = array();

		foreach ($row->dates as $date)
		{
			if (!redEVENTHelper::isValidDate($date))
			{
				$dates[] = 'Open date';
				continue;
			}

			$date = new DateTime($date);

			$dates[] =  $date->format('d-m-Y');
		}

		return $dates;
	}

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
