<?php
/**
 * @package     Redevent.Frontend
 * @subpackage  Modules
 *
 * @copyright   Copyright (C) 2008 - 2020 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Class module helper
 *
 * @package     Redevent.Frontend
 * @subpackage  Modules
 * @since       2.0
 */
class Modredeventcalhelper
{
	/**
	 * Get items
	 *
	 * @param   string    $greqYear   requested year
	 * @param   string    $greqMonth  requested month
	 * @param   JRegistry $params     plugin params
	 *
	 * @return array
	 */
	public static function getdays($greqYear, $greqMonth, &$params)
	{
		$user = JFactory::getUser();

		$tz = new DatetimeZone(JFactory::getApplication()->getCfg('offset'));

		$monthstart = JFactory::getDate("$greqMonth/1/$greqYear", $tz)->format('Y-m-d', true);
		$monthend   = JFactory::getDate("$greqMonth/1/$greqYear next month", $tz)->format('Y-m-d', true);

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('x.dates, x.times, x.enddates,a.title')
			->select('DAYOFMONTH(x.dates) AS start_day, YEAR(x.dates) AS start_year, MONTH(x.dates) AS start_month')
			->select('l.venue')
			->select('CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug')
			->select('CASE WHEN CHAR_LENGTH(x.alias) THEN CONCAT_WS(\':\', x.id, x.alias) ELSE x.id END as xslug')
			->from('#__redevent_event_venue_xref AS x')
			->join('INNER', '#__redevent_events AS a ON a.id = x.eventid')
			->join('INNER', '#__redevent_event_category_xref AS xcat ON xcat.event_id = a.id')
			->join('INNER', '#__redevent_categories AS c ON c.id = xcat.category_id')
			->join('INNER', '#__redevent_venues AS l ON l.id = x.venueid')
			->where('x.published = 1')
			->where('a.published = 1')
			->where('l.published = 1')
			->where('c.access IN (' . implode(',', $user->getAuthorisedViewLevels()) . ')')
			->where(
				'(x.dates BETWEEN ' . $db->Quote($monthstart) . ' AND ' . $db->Quote($monthend)
				. ' OR (x.enddates AND x.enddates BETWEEN '
				. $db->Quote($monthstart) . ' AND ' . $db->Quote($monthend) . ') ) '
			)
			->group('x.id');

		if (JFactory::getApplication()->getLanguageFilter())
		{
			$query->where('(a.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ') OR a.language IS NULL)');
		}

		$catid = $params->get('catid');

		if (is_array($catid) && count($catid))
		{
			JArrayHelper::toInteger($catid);
			$query->where('c.id IN (' . implode(',', $catid) . ')');
		}

		$venid = $params->get('venid');

		if (is_array($venid) && count($venid))
		{
			JArrayHelper::toInteger($venid);
			$query->where('l.id IN (' . implode(',', $venid) . ')');
		}

		$db->setQuery($query);
		$events = $db->loadObjectList();

		// Group events per days
		$days_events = array();

		foreach ($events as $event)
		{
			// Cope with no end date set i.e. set it to same as start date
			if ($event->enddates == '0000-00-00' || is_null($event->enddates))
			{
				$eyear = $event->start_year;
				$emonth = $event->start_month;
				$eday = $event->start_day;
			}
			else
			{
				list($eyear, $emonth, $eday) = explode('-', $event->enddates);
			}

			// The two cases for roll over the year end with an event that goes across the year boundary.
			if ($greqYear < $eyear)
			{
				$emonth = $emonth + 12;
			}

			if ($event->start_year < $greqYear)
			{
				$event->start_month = $event->start_month - 12;
			}

			if (($greqYear >= $event->start_year) && ($greqYear <= $eyear)
				&& ($greqMonth >= $event->start_month) && ($greqMonth <= $emonth))
			{
				// Set end day for current month
				if ($emonth > $greqMonth)
				{
					$emonth = $greqMonth;

					$eday = JFactory::getDate($greqMonth . "/1/$greqYear", $tz)->format('t', true);
				}

				// Set start day for current month
				if ($event->start_month < $greqMonth)
				{
					$event->start_month = $greqMonth;
					$event->start_day = 1;
				}

				for ($day = $event->start_day; $day <= $eday; $day++)
				{
					if (!isset($days_events[$day]))
					{
						$days_events[$day] = array($event);
					}
					else
					{
						$days_events[$day][] = $event;
					}
				}
			}
		}

		return $days_events;
	}
}
