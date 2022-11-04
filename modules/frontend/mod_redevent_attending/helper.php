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
 * Redevent Attending Module helper
 *
 * @package     Redevent.Frontend
 * @subpackage  Modules
 * @since       0.9
 */
class ModRedeventAttendingHelper
{
	/**
	 * Method to get the events
	 *
	 * @param   object  $params  parameters
	 *
	 * @return array
	 */
	public static function getList($params)
	{
		$db = JFactory::getDBO();
		$user = JFactory::getUser();

		if (!$user->get('id'))
		{
			return false;
		}

		$user_gid = (int) max($user->getAuthorisedViewLevels());
		$reparams = JComponentHelper::getParams('com_redevent');
		$weekstart = ($reparams->get('week_start', "MO") == "SU" ? 0 : 1);

		$where = array();
		$where[] = 'reg.uid = ' . $user->get('id');
		$where[] = 'x.published = 1';
		$where[] = 'reg.cancelled = 0';
		$where[] = 'reg.confirmed = 1';

		$offset = JRequest::getInt('reattoffset', (int) $params->get('offset', '0'));
		$signedoffet = sprintf('%+d', $offset);

		$date_cond = array();

		if ($params->get('includeopen', 0))
		{
			$date_cond[] = ' x.dates IS NULL ';
		}

		$type = JRequest::getInt('reattspan', $params->get('type', '0'));

		if ($type == 0) // All upcoming dates
		{
			$startdate = strftime('%Y-%m-%d', strtotime('today' . ($offset ? ' ' . $signedoffet . ' days' : '')));
			$date_cond[] = ' (x.dates >= ' . $db->Quote($startdate) . ')';
		}
		elseif ($type == 1) // Day
		{
			$startdate = strftime('%Y-%m-%d', strtotime('today' . ($offset ? ' ' . $signedoffet . ' days' : '')));
			$date_cond[] = ' (x.dates = ' . $db->Quote($startdate) . ')';
		}
		elseif ($type == 2) // Week
		{
			$startdate = strftime(
				'%Y-%m-%d', strtotime('last ' . ($weekstart ? ' monday ' : ' sunday ') . ($offset ? ' ' . $signedoffet . ' weeks' : ''))
			);
			$enddate = strftime(
				'%Y-%m-%d', strtotime('next ' . ($weekstart ? ' monday ' : ' sunday ') . ($offset ? ' ' . $signedoffet . ' weeks' : ''))
			);
			$date_cond[] = ' (x.dates >= ' . $db->Quote($startdate) . ' AND x.dates < ' . $db->Quote($enddate) . ')';
		}
		elseif ($type == 3) // Month
		{
			$startdate = strftime('%Y-%m-%d', strtotime('first day of this month' . ($offset ? ' ' . $signedoffet . ' months' : '')));
			$enddate = strftime('%Y-%m-%d', strtotime('last day of this month' . ($offset ? ' ' . $signedoffet . ' months' : '')));
			$date_cond[] = ' (x.dates >= ' . $db->Quote($startdate) . ' AND x.dates <= ' . $db->Quote($enddate) . ')';
		}

		$where[] = '(' . implode(" OR ", $date_cond) . ')';

		$order = ' ORDER BY x.dates, x.times';
		$groupby = ' GROUP BY x.id ';

		$query = 'SELECT a.*, x.id AS xref, x.dates, x.enddates, x.allday, x.times, x.endtimes, l.venue, l.city, l.url ,'
			. ' CONCAT_WS(",", c.image) AS categories_images,'
			. ' reg.id AS rid, reg.sid, '
			. ' CASE WHEN CHAR_LENGTH(x.title) THEN x.title ELSE a.title END as session_title, '
			. ' CASE WHEN CHAR_LENGTH(x.title) THEN CONCAT_WS(\' - \', a.title, x.title) ELSE a.title END as full_title, '
			. ' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug, '
			. ' CASE WHEN CHAR_LENGTH(l.alias) THEN CONCAT_WS(\':\', l.id, l.alias) ELSE l.id END as venueslug '
			. ' FROM #__redevent_event_venue_xref AS x'
			. ' INNER JOIN #__redevent_register AS reg ON reg.xref = x.id '
			. ' LEFT JOIN #__redevent_events AS a ON a.id = x.eventid'
			. ' LEFT JOIN #__redevent_venues AS l ON l.id = x.venueid'
			. ' LEFT JOIN #__redevent_event_category_xref AS xcat ON xcat.event_id = a.id'
			. ' LEFT JOIN #__redevent_categories AS c ON c.id = xcat.category_id'
			. ' WHERE ' . implode(' AND ', $where)
			. $groupby
			. $order;

		$db->setQuery($query);
		$rows = $db->loadObjectList();
		self::_addPrices($rows);

		$i = 0;
		$lists = array();
		$title_length = $params->get('cuttitle', '18');

		switch ($params->get('title_type', 0))
		{
			case 1:
				$title_type = 'session_title';
				break;
			case 2:
				$title_type = 'full_title';
				break;
			case 0:
			default:
				$title_type = 'title';
				break;
		}

		foreach ($rows as $k => $row)
		{
			$rowtitle = $row->$title_type;

			$length = mb_strlen($rowtitle, 'UTF-8');

			if ($title_length && $length > $title_length)
			{
				$rows[$k]->title_short = mb_substr($rowtitle, 0, $title_length, 'UTF-8') . '...';
			}
			else
			{
				$rows[$k]->title_short = $rowtitle;
			}

			// Cut venue name
			$length = mb_strlen($row->venue, 'UTF-8');

			if ($title_length && $length > $title_length)
			{
				$rows[$k]->venue_short = mb_substr($row->venue, 0, $title_length, 'UTF-8') . '...';
			}
			else
			{
				$rows[$k]->venue_short = $row->venue;
			}

			$rows[$k]->link = JRoute::_(RedeventHelperRoute::getDetailsRoute($row->slug, $row->xref));
			$rows[$k]->dateinfo = self::_builddateinfo($row, $params);
			$rows[$k]->city = htmlspecialchars($row->city, ENT_COMPAT, 'UTF-8');
			$rows[$k]->venueurl = !empty($row->url)
				? self::_format_url($row->url) : JRoute::_(RedeventHelperRoute::getVenueEventsRoute($row->venueslug), false);
		}

		return $rows;
	}

	/**
	 * Method to a formated and structured string of date infos
	 *
	 * @param   Object  $row     data
	 * @param   Object  $params  params
	 *
	 * @return string
	 */
	protected static function _builddateinfo($row, $params)
	{
		if (!RedeventHelperDate::isValidDate($row->dates))
		{
			return JText::_('MOD_REDEVENT_ATTENDING_OPEN_DATE');
		}

		if ($params->get('show_enddate', 1) && strtotime($row->enddates) && $row->dates != $row->enddates)
		{
			$txt = self::datesSpan($row->dates, $row->enddates, $params->get('formatdate', '%d.%m.%Y'));
		}
		else
		{
			$txt = self::datesSpan($row->dates, null, $params->get('formatdate', '%d.%m.%Y'));
		}

		$time = ($row->times && $row->times != '00:00:00') ? self::_format_date($row->dates, $row->times, $params->get('formattime', '%H:%M')) : null;

		if (isset($time) && $params->get('show_time', 1))
		{
			$txt .= ' <span class="event-time">' . $time . '</span>';
		}

		return $txt;
	}

	/**
	 * Method to get a valid url
	 *
	 * @param   string  $url  url
	 *
	 * @return string
	 */
	protected static function _format_url($url)
	{
		if (!empty($url) && strtolower(substr($url, 0, 7)) != "http://")
		{
			$url = 'http://' . $url;
		}

		return $url;
	}

	/**
	 * Method to format date information
	 *
	 * @param   string  $date    date
	 * @param   string  $time    time
	 * @param   string  $format  format
	 *
	 * @return string
	 */
	protected static function _format_date($date, $time, $format)
	{
		$date = strftime($format, strtotime($date . ' ' . $time));

		return $date;
	}

	/**
	 * Get select
	 *
	 * @param   array  $params  params
	 *
	 * @return boolean|mixed
	 */
	public static function getSelect($params)
	{
		$type = JRequest::getInt('reattspan', $params->get('type', '0'));

		if ($type == 0)
		{
			return false;
		}

		if ($type == '1')
		{
			return self::_getDaySelect($params);
		}

		if ($type == '2')
		{
			return self::_getWeekSelect($params);
		}

		if ($type == '3')
		{
			return self::_getMonthSelect($params);
		}
	}

	/**
	 * get day selector
	 *
	 * @param   Object  $params  params
	 *
	 * @return mixed
	 */
	protected static function _getDaySelect($params)
	{
		$currentoffset = JRequest::getInt('reattoffset', (int) $params->get('offset', '0'));

		$options = array();

		// Let's have 5 days on each side
		for ($i = 0; $i < 11; $i++)
		{
			$signedoffset = sprintf('%+d', $currentoffset + $i - 5);
			$time = strtotime('today ' . $signedoffset . ' days');

			$text = strftime(JText::_('COM_REDEVENT_ATTENDING_SELECT_DAY_FORMAT'), $time);
			$text = ($currentoffset + $i - 5 == 0) ? '-' . $text . '-' : $text;
			$options[] = JHTML::_('select.option', $currentoffset + $i - 5, $text);
		}

		return JHTML::_('select.genericlist', $options, 'reattoffset', 'class="reattoffset"', 'value', 'text', $currentoffset);
	}

	/**
	 * get week selector
	 *
	 * @param   Object  $params  params
	 *
	 * @return mixed
	 */
	protected static function _getWeekSelect($params)
	{
		$reparams = JComponentHelper::getParams('com_redevent');
		$weekstart = ($reparams->get('week_start', "MO") == "SU" ? 0 : 1);

		$offset = JRequest::getInt('reattoffset', (int) $params->get('offset', '0'));
		$current = strftime($weekstart ? '%W' : '%U');

		$options = array();

		// Let's have 5 weeks on each side
		for ($i = 0; $i < 11; $i++)
		{
			$signedoffet = sprintf('%+d', $offset + $i - 5);
			$weekoption_sec = strtotime('today ' . $signedoffet . ' weeks');
			$start = strftime('%F', strtotime('last ' . ($weekstart ? 'monday' : 'sunday'), $weekoption_sec));
			$end = strftime('%F', strtotime('next ' . ($weekstart ? 'sunday' : 'saturday'), $weekoption_sec));
			$text = self::datesSpan($start, $end);
			$text = ($offset + $i - 5 == 0) ? '> ' . $text : $text;
			$options[] = JHTML::_('select.option', $offset + $i - 5, $text);
		}

		return JHTML::_('select.genericlist', $options, 'reattoffset', 'class="reattoffset"', 'value', 'text', $offset);
	}

	/**
	 * get month selector
	 *
	 * @param   Object  $params  params
	 *
	 * @return mixed
	 */
	protected static function _getMonthSelect($params)
	{
		$offset = JRequest::getInt('reattoffset', (int) $params->get('offset', '0'));

		// Let's have 5 months on each side
		for ($i = 0; $i < 11; $i++)
		{
			$signedoffet = sprintf('%+d', $offset + $i - 5);
			$text = strftime(JText::_('COM_REDEVENT_ATTENDING_SELECT_MONTH_FORMAT'), strtotime('today ' . $signedoffet . ' months'));
			$text = ($offset + $i - 5 == 0) ? '> ' . $text : $text;
			$options[] = JHTML::_('select.option', $offset + $i - 5, $text);
		}

		return JHTML::_('select.genericlist', $options, 'reattoffset', 'class="reattoffset"', 'value', 'text', $offset);
	}

	/**
	 * special display handling of dates when in same month/year
	 *
	 * @param   string  $start             date
	 * @param   string  $end               date
	 * @param   string  $full_date_format  full date format
	 *
	 * @return string dates span
	 */
	public static function datesSpan($start, $end = null, $full_date_format = null)
	{
		if (!strtotime($start))
		{
			return false;
		}

		if (!strtotime($end))
		{
			return strftime($full_date_format ? $full_date_format : JText::_('COM_REDEVENT_ATTENDING_FULL_DATE_FORMAT'), strtotime($start));
		}

		$start_month = strftime('%m-%Y', strtotime($start));
		$end_month = strftime('%m-%Y', strtotime($end));

		if ($start_month == $end_month)
		{
			return JText::sprintf('COM_REDEVENT_ATTENDING_DATES_SPAN',
				strftime(JText::_('COM_REDEVENT_ATTENDING_DATES_SPAN_START_DATE_SAME_MONTH_FORMAT'), strtotime($start)),
				strftime(JText::_('COM_REDEVENT_ATTENDING_DATES_SPAN_END_DATE_SAME_MONTH_FORMAT'), strtotime($end))
			);
		}
		elseif (substr($start_month, 3) == substr($end_month, 3)) // Same year
		{
			return JText::sprintf('COM_REDEVENT_ATTENDING_DATES_SPAN',
				strftime(JText::_('COM_REDEVENT_ATTENDING_DATES_SPAN_START_DATE_SAME_YEAR_FORMAT'), strtotime($start)),
				strftime(JText::_('COM_REDEVENT_ATTENDING_DATES_SPAN_END_DATE_SAME_YEAR_FORMAT'), strtotime($end))
			);
		}
		else
		{
			return JText::sprintf('COM_REDEVENT_ATTENDING_DATES_SPAN',
				strftime(JText::_('COM_REDEVENT_ATTENDING_FULL_DATE_FORMAT'), strtotime($start)),
				strftime(JText::_('COM_REDEVENT_ATTENDING_FULL_DATE_FORMAT'), strtotime($end))
			);
		}
	}

	/**
	 * adds submission price to rows     *
	 *
	 * @param   array  $rows  rows
	 *
	 * @return boolean true on success
	 */
	protected static function _addPrices(&$rows)
	{
		if (!count($rows))
		{
			return true;
		}

		$rids = array();

		foreach ($rows as $k => $r)
		{
			if ($r->rid)
			{
				$rids[] = $r->rid;
			}
		}

		$db = JFactory::getDBO();
		$query = ' SELECT SUM(s.price) as price, r.id, s.currency AS currency '
			. ' FROM #__redevent_register AS r '
			. ' INNER JOIN #__rwf_submitters AS s ON s.id = r.sid '
			. ' INNER JOIN #__rwf_forms AS f ON f.id = s.form_id '
			. ' WHERE r.id IN (' . implode(", ", $rids) . ')'
			. ' GROUP BY r.id ';
		$db->setQuery($query);
		$res = $db->loadObjectList('id');

		foreach ($rows as $k => $r)
		{
			if (isset($res[$r->rid]))
			{
				$rows[$k]->price = $res[$r->rid]->price;
				$rows[$k]->currency = $res[$r->rid]->currency;
			}
			else
			{
				$rows[$k]->price = 0;
				$rows[$k]->currency = null;
			}
		}

		return true;
	}

	/**
	 * Get total price
	 *
	 * @param   array  $rows  rows
	 *
	 * @return boolean|integer
	 */
	public static function getTotal($rows)
	{
		if (!count($rows))
		{
			return 0;
		}

		$currency = null;
		$total = 0;

		foreach ($rows as $r)
		{
			if ($currency && $currency != $r->currency)
			{
				// Can't sum if not the same currency
				return false;
			}

			$total += $r->price;
			$currency = $r->currency;
		}

		return $total;
	}

	/**
	 * Doesn't do anything special, but maybe could call a function more advanced in the future
	 *
	 * @param   float   $price     price
	 * @param   string  $currency  currency
	 *
	 * @return string
	 *
	 * @todo: use redCORE lib helper !
	 */
	public static function printPrice($price, $currency)
	{
		if (!$price)
		{
			return '-';
		}

		return $currency . ' ' . sprintf('%.2f', $price);
	}
}
