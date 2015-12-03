<?php

class DumpHelper
{
	public static function sortSessions(&$sessions)
	{
		// Sort sessions
		usort(
			$sessions,
			function($a, $b)
			{
				// First by category
				if ($diff = strcmp($a->categories[0], $b->categories[0]))
				{
					return $diff;
				}

				// Then by date
				// First check they are valid
				if (!(redEVENTHelper::isValidDate($a->dates) && redEVENTHelper::isValidDate($b->dates)))
				{
					return redEVENTHelper::isValidDate($a->dates) ? -1 : 1;
				}

				// Then compare valid dates
				if ($diff = strtotime($a->dates . ' ' . $a->times) - strtotime($b->dates . ' ' . $b->times))
				{
					return $diff;
				}

				// Then name
				return strcmp($a->title, $b->title);
			}
		);
	}

	public static function formatDate($session)
	{
		if (!redEVENTHelper::isValidDate($session->dates))
		{
			return 'Open date';
		}

		$date = new DateTime($session->dates);

		return $date->format('d-m-Y');
	}

	public static function buildLink($session)
	{
		$target = $session->custom13;

		if (strstr($target, 'http') !== false)
		{
			return $target;
		}

		if (strpos($target, '/') !== 0)
		{
			$target = "/" . $target;
		}

		return 'https://kurser.ibc.dk' . $target;
	}

	public static function getState($session)
	{
		if ($session->event_state == -1 || $session->session_state == -1)
		{
			return 'archived';
		}

		if ($session->event_state == 0 || $session->session_state == 0)
		{
			return 'unpublished';
		}

		return 'published';
	}
}
