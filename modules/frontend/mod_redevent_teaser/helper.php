<?php
/**
 * THIS FILE IS BASED mod_eventlist_teaser from ezuri.de, BASED ON MOD_EVENTLIST_WIDE FROM SCHLU.NET
 * @package     Redevent.Frontend
 * @subpackage  Modules
 *
 * @copyright   Copyright (C) 2008 - 2020 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevent Module teaser helper
 *
 * @package     Redevent.Frontend
 * @subpackage  Modules
 * @since       1.0
 */
class ModRedeventTeaserHelper
{
	/**
	 * Method to get the events
	 *
	 * @param   array  $params  parameters
	 *
	 * @return array
	 */
	public static function getList(&$params)
	{
		$mainframe = JFactory::getApplication();

		$db = JFactory::getDBO();
		$user = JFactory::getUser();

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('a.*, x.eventid, x.id AS xref, x.dates, x.enddates, x.allday, x.times, x.endtimes')
			->select('l.venue, l.city, l.url , l.locimage, l.state')
			->select('CONCAT_WS(",", c.image) AS categories_images')
			->select('CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug')
			->select('CASE WHEN CHAR_LENGTH(x.alias) THEN CONCAT_WS(\':\', x.id, x.alias) ELSE x.id END as xslug')
			->select('CASE WHEN CHAR_LENGTH(l.alias) THEN CONCAT_WS(\':\', l.id, l.alias) ELSE l.id END as venueslug')
			->from('#__redevent_event_venue_xref AS x')
			->join('INNER', '#__redevent_events AS a ON a.id = x.eventid')
			->join('LEFT', '#__redevent_venues AS l ON l.id = x.venueid')
			->join('LEFT', '#__redevent_event_category_xref AS xcat ON xcat.event_id = a.id')
			->join('LEFT', '#__redevent_categories AS c ON c.id = xcat.category_id')
			->join('LEFT', '#__redevent_repeats AS r ON r.xref_id = x.id')
			->group('x.id');

		// All upcoming events
		if ($params->get('type', 1) == 1)
		{
			$query->where('x.dates >= CURDATE()')
				->where('x.published = 1')
				->order('x.dates, x.times');
		}

		// Archived events only
		if ($params->get('type', 1) == 2)
		{
			$query->where('x.published = 11')
				->order('x.dates DESC, x.times DESC');
		}

		// Currently running events only
		if ($params->get('type', 1) == 3)
		{
			$query->where('(x.dates = CURDATE() OR (x.enddates >= CURDATE() AND x.dates <= CURDATE()))')
				->where('x.published = 1')
				->order('x.dates, x.times');
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

		$state = JString::strtolower(trim($params->get('stateloc')));

		// Build state selection query statement
		if ($state)
		{
			$rawstate = explode(',', $state);

			foreach ($rawstate as $val)
			{
				if ($val)
				{
					$states[] = $db->quote(trim($val));
				}
			}

			JArrayHelper::toString($states);
			$query->where('(LOWER(l.state) IN (' . implode(',', $states) . ')');
		}

		if (JFactory::getApplication()->getLanguageFilter())
		{
			$query->where('(a.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ') OR a.language IS NULL)');
		}

		$db->setQuery($query, 0, (int) $params->get('count', '2'));
		$rows = $db->loadObjectList();
		$rows = self::_categories($rows);

		// Loop through the result rows and prepare data
		$i = 0;
		$lists = array();

		foreach ($rows as $k => $row)
		{
			// Create thumbnails if needed and receive imagedata
			$dimage = RedeventImage::modalimage($row->datimage, $row->title, intval($params->get('picture_size', 30)));
			$limage = RedeventImage::modalimage($row->locimage, $row->venue, intval($params->get('picture_size', 30)));

			// Cut title
			$length = mb_strlen($row->title, 'UTF-8');
			$title_length = $params->get('cuttitle', 35);

			if ($title_length && $length > $title_length)
			{
				$title = mb_substr($row->title, 0, $title_length, 'UTF-8') . '...';
			}
			else
			{
				$title = $row->title;
			}

			$lists[$i] = new stdclass;
			$lists[$i]->title = htmlspecialchars($title, ENT_COMPAT, 'UTF-8');
			$lists[$i]->venue = htmlspecialchars($row->venue, ENT_COMPAT, 'UTF-8');
			$lists[$i]->state = htmlspecialchars($row->state, ENT_COMPAT, 'UTF-8');
			$lists[$i]->city = htmlspecialchars($row->city, ENT_COMPAT, 'UTF-8');
			$lists[$i]->eventlink = $params->get('linkevent', 1) ? JRoute::_(RedeventHelperRoute::getDetailsRoute($row->slug, $row->xref)) : '';
			$lists[$i]->venuelink = $params->get('linkvenue', 1) ? JRoute::_(RedeventHelperRoute::getVenueEventsRoute($row->venueslug)) : '';
			$lists[$i]->categorylink = $params->get('linkcategory', 1) ? self::_getCatLinks($row) : '';
			$lists[$i]->date = self::_format_date($row, $params);
			$lists[$i]->day = self::_format_day($row, $params);
			$lists[$i]->dayname = self::_format_dayname($row);
			$lists[$i]->daynum = self::_format_daynum($row);
			$lists[$i]->month = self::_format_month($row);
			$lists[$i]->year = self::_format_year($row);

			$lists[$i]->time = $row->times ? self::_format_time($row->dates, $row->times, $params) : '';
			$lists[$i]->eventimage = $dimage;
			$lists[$i]->venueimage = $limage;
			$lists[$i]->slug = $row->slug;
			$lists[$i]->xslug = $row->xslug;

			$length = $params->get('descriptionlength');
			$etc = '...';

			$description = strip_tags($row->summary, "<br>");

			if ($params->get('br') == 0)
			{
				$description = str_replace('<br />', ' ', $description);
			}

			if (strlen($description) > $length)
			{
				$length -= strlen($etc);
				$description = preg_replace('/\s+?(\S+)?$/', '', substr($description, 0, $length + 1));
				$lists[$i]->eventdescription = substr($description, 0, $length) . $etc;
			}
			else
			{
				$lists[$i]->eventdescription = $description;
			}

			$i++;
		}

		return $lists;
	}

	/**
	 * adds categories property to event rows
	 *
	 * @param   array  $rows  rows of events
	 *
	 * @return array
	 */
	private static function _categories($rows)
	{
		$gids = JFactory::getUser()->getAuthorisedViewLevels();
		$gids = implode(',', $gids);

		$events = array();

		foreach ($rows as $k => $r)
		{
			$events[] = $r->eventid;
		}

		$events = array_unique($events);

		if (!count($events))
		{
			return $rows;
		}

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('DISTINCT c.id, c.name, c.image, x.event_id')
			->select('CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as slug')
			->from('#__redevent_categories as c')
			->join('INNER', '#__redevent_event_category_xref as x ON x.category_id = c.id')
			->where('c.published = 1')
			->where('x.event_id IN (' . implode(", ", $events) . ')')
			->where('(c.access IN (' . $gids . '))')
			->order('c.ordering');

		$db->setQuery($query);
		$res = $db->loadObjectList();

		// Get categories per events
		$evcats = array();

		foreach ($res as $r)
		{
			if (!isset($evcats[$r->event_id]))
			{
				$evcats[$r->event_id] = array();
			}

			$evcats[$r->event_id][] = $r;
		}

		foreach ($rows as $k => $r)
		{
			if (isset($evcats[$r->id]))
			{
				$rows[$k]->categories = $evcats[$r->id];
			}
			else
			{
				$rows[$k]->categories = array();
			}
		}

		return $rows;
	}

	/**
	 * Returns categories links
	 *
	 * @param   object  $item  item
	 *
	 * @return array
	 */
	private static function _getCatLinks($item)
	{
		$links = array();

		foreach ((array) $item->categories as $c)
		{
			$link = JRoute::_(RedeventHelperRoute::getCategoryEventsRoute($c->slug));
			$links[] = JHTML::link($link, $c->name);
		}

		return $links;
	}

	/**
	 * format date
	 *
	 * @param   object  $row     event data
	 * @param   array   $params  module params
	 *
	 * @return string
	 */
	private static function _format_day($row, &$params)
	{
		// Get needed timestamps and format
		$yesterday_stamp = mktime(0, 0, 0, date("m"), date("d") - 1, date("Y"));
		$yesterday = strftime("%Y-%m-%d", $yesterday_stamp);
		$today_stamp = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
		$today = date('Y-m-d');
		$tomorrow_stamp = mktime(0, 0, 0, date("m"), date("d") + 1, date("Y"));
		$tomorrow = strftime("%Y-%m-%d", $tomorrow_stamp);

		$dates_stamp = strtotime($row->dates);
		$enddates_stamp = $row->enddates ? strtotime($row->enddates) : null;

		// Check if today or tomorrow or yesterday and no current running multiday event
		if ($row->dates == $today && empty($enddates_stamp))
		{
			$result = JText::_('MOD_REDEVENT_TEASER_TODAY');
		}
		elseif ($row->dates == $tomorrow)
		{
			$result = JText::_('MOD_REDEVENT_TEASER_TOMORROW');
		}
		elseif ($row->dates == $yesterday)
		{
			$result = JText::_('MOD_REDEVENT_TEASER_YESTERDAY');
		}
		else
		{
			// If daymethod show day
			if ($params->get('daymethod', 1) == 1)
			{
				// Single day event
				$date = strftime('%A', strtotime($row->dates));
				$result = JText::sprintf('MOD_REDEVENT_TEASER_ON_DATE', $date);

				// Upcoming multidayevent (From 16.10.2010 Until 18.10.2010)
				if ($dates_stamp > $tomorrow_stamp && $enddates_stamp)
				{
					$startdate = strftime('%A', strtotime($row->dates));
					$result = JText::sprintf('MOD_REDEVENT_TEASER_FROM', $startdate);
				}

				// Current multidayevent (Until 18.08.2008)
				if ($row->enddates && $enddates_stamp > $today_stamp && $dates_stamp <= $today_stamp)
				{
					// Format date
					$result = strftime('%A', strtotime($row->enddates));
					$result = JText::sprintf('MOD_REDEVENT_TEASER_UNTIL', $result);
				}
			}
			else
			{
				// Show day difference

				// The event has an enddate and it's earlier than yesterday
				if ($row->enddates && $enddates_stamp < $yesterday_stamp)
				{
					$days = round(($today_stamp - $enddates_stamp) / 86400);
					$result = JText::sprintf('MOD_REDEVENT_TEASER_ENDED_DAYS_AGO', $days);

					// The event has an enddate and it's later than today but the startdate is today or earlier than today
					// Means a currently running event with startdate = today
				}
				elseif ($row->enddates && $enddates_stamp > $today_stamp && $dates_stamp <= $today_stamp)
				{
					$days = round(($enddates_stamp - $today_stamp) / 86400);
					$result = JText::sprintf('MOD_REDEVENT_TEASER_DAYS_LEFT', $days);

					// The events date is earlier than yesterday
				}
				elseif ($dates_stamp < $yesterday_stamp)
				{
					$days = round(($today_stamp - $dates_stamp) / 86400);
					$result = JText::sprintf('MOD_REDEVENT_TEASER_DAYS_AGO', $days);

					// The events date is later than tomorrow
				}
				elseif ($dates_stamp > $tomorrow_stamp)
				{
					$days = round(($dates_stamp - $today_stamp) / 86400);
					$result = JText::sprintf('MOD_REDEVENT_TEASER_DAYS_AHEAD', $days);
				}
			}
		}

		return $result;
	}

	/**
	 * Method to format date information
	 *
	 * @param   object  $row     event data
	 * @param   array   $params  module params
	 *
	 * @return string
	 */
	private static function _format_date($row, &$params)
	{
		$enddates_stamp = $row->enddates ? strtotime($row->enddates) : null;

		// Single day event
		if (empty($enddates_stamp))
		{
			$date = strftime($params->get('formatdate', '%d.%m.%Y'), strtotime($row->dates . ' ' . $row->times));
			$result = JText::sprintf('MOD_REDEVENT_TEASER_ON_DATE', $date);
		}
		else
		{
			// Multidayevent (From 16.10.2008 Until 18.08.2008)
			$startdate = strftime($params->get('formatdate', '%d.%m.%Y'), strtotime($row->dates));
			$enddate = strftime($params->get('formatdate', '%d.%m.%Y'), strtotime($row->enddates));
			$result = JText::sprintf('MOD_REDEVENT_TEASER_FROM_UNTIL', $startdate, $enddate);
		}

		return $result;
	}

	/**
	 * Method to format time information
	 *
	 * @param   string  $date    date
	 * @param   string  $time    time
	 * @param   array   $params  module params
	 *
	 * @return string
	 */
	private static function _format_time($date, $time, &$params)
	{
		$time = strftime($params->get('formattime', '%H:%M'), strtotime($date . ' ' . $time));
		$result = JText::sprintf('MOD_REDEVENT_TEASER_TIME_STRING', $time);

		return $result;
	}

	/**
	 * Method to format day name
	 *
	 * @param   object  $row  data
	 *
	 * @return string
	 */
	private static function _format_dayname($row)
	{
		$date = strtotime($row->dates);
		$result = strftime("%A", $date);

		return $result;
	}

	/**
	 * Method to format day number
	 *
	 * @param   object  $row  data
	 *
	 * @return string
	 */
	private static function _format_daynum($row)
	{
		$date = strtotime($row->dates);
		$result = strftime("%d", $date);

		return $result;
	}

	/**
	 * Method to format year
	 *
	 * @param   object  $row  data
	 *
	 * @return string
	 */
	private static function _format_year($row)
	{
		$date = strtotime($row->dates);
		$result = strftime("%Y", $date);

		return $result;
	}

	/**
	 * Method to format month
	 *
	 * @param   object  $row  data
	 *
	 * @return string
	 */
	private static function _format_month($row)
	{
		$date = strtotime($row->dates);
		$result = strftime("%B", $date);

		return htmlentities($result);
	}
}
