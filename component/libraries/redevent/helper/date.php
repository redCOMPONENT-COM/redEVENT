<?php
/**
 * @package    Redevent.Library
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Date functions
 *
 * @package  Redevent.Library
 * @since    3.0
 */
class RedeventHelperDate
{
	/**
	 * return true is a date is valid (not null, or 0000-00...)
	 *
	 * @param   string  $date  date string from db
	 *
	 * @return boolean
	 */
	public static function isValidDate($date)
	{
		$format = strlen($date) > 10 ? 'Y-m-d H:i:s' : 'Y-m-d';
		$d = DateTime::createFromFormat($format, $date);

		return $d && $d->format($format) == $date;
	}

	/**
	 * return true is a date is valid (not null, or 0000-00-00...)
	 *
	 * @param   string  $time  time string from db
	 *
	 * @return boolean
	 */
	public static function isValidTime($time)
	{
		$format = strlen($time) > 5 ? 'H:i:s' : 'H:i';
		$d = DateTime::createFromFormat($format, $time);

		return $d && $d->format($format) == $time;
	}

	/**
	 * returns true if the session is over.
	 * object in parameters must include properties
	 *
	 * @param   object  $session    event data
	 * @param   bool    $day_check  daycheck: if true, events are over only the next day, otherwise, use time too.
	 *
	 * @return bool
	 *
	 * @throws Exception
	 */
	public static function isOver($session, $day_check = true)
	{
		if (!(property_exists($session, 'dates') && property_exists($session, 'times')
			&& property_exists($session, 'enddates') && property_exists($session, 'endtimes') && property_exists($session, 'allday')))
		{
			throw new Exception('Missing object properties');
		}

		if (!static::isValidDate($session->dates))
		{
			// Open dates
			return false;
		}

		$cmp = $day_check ? strtotime('today') : time();

		if (static::isValidDate($session->enddates))
		{
			return strtotime($session->enddates . ($session->allday ? ' 23:59:59' : ' ' . $session->endtimes)) < $cmp;
		}
		else
		{
			return strtotime($session->dates . ' ' . ($session->allday ? '' : ' ' . $session->times)) < $cmp;
		}
	}

	/**
	 * returns formatted event duration.
	 *
	 * @param   object  $event  object having properties dates, enddates, times, endtimes
	 *
	 * @return string
	 */
	public static function getEventDuration($event)
	{
		if (!static::isValidDate($event->dates))
		{
			return '-';
		}

		// All day events if start or end time is null or 00:00:00
		if ($event->allday)
		{
			if (!static::isValidDate($event->enddates) || $event->enddates == $event->dates)
			{
				// Same day
				return '1' . ' ' . JText::_('COM_REDEVENT_Day');
			}
			else
			{
				$days = floor((strtotime($event->enddates) - strtotime($event->dates)) / (3600 * 24)) + 1;

				return $days . ' ' . JText::_('COM_REDEVENT_Days');
			}
		}
		else
		{
			// There is start and end times
			$start = strtotime($event->dates . ' ' . $event->times);

			if (!static::isValidDate($event->enddates) || $event->enddates == $event->dates)
			{
				// Same day, return hours and minutes
				$end = strtotime($event->dates . ' ' . $event->endtimes);
				$duration = $end - $start;

				return floor($duration / 3600) . JText::_('COM_REDEVENT_LOC_H') . sprintf('%02d', floor(($duration % 3600) / 60));
			}
			else
			{
				// Not same day, display in days
				$days = floor((strtotime($event->enddates) - strtotime($event->dates)) / (3600 * 24)) + 1;

				return $days . ' ' . JText::_('COM_REDEVENT_Days');
			}
		}
	}

	/**
	 * Formats date
	 *
	 * @param   string  $date    date to format in a format accepted by strtotime
	 * @param   string  $time    time to format in a format accepted by strtotime
	 * @param   string  $format  format, optional
	 *
	 * @return string
	 */
	public static function formatdate($date, $time = null, $format = null)
	{
		$settings = RedeventHelper::config();

		if (!static::isValidDate($date))
		{
			return JText::_('LIB_REDEVENT_OPEN_DATE');
		}

		if (static::isValidTime($time))
		{
			$date .= ' ' . $time;
		}

		// Format date
		$date = JFactory::getDate($date);
		$formatdate = $date->format($format ?: $settings->get('formatdate', 'd.m.Y'));

		return $formatdate;
	}

	/**
	 * Formats date
	 *
	 * @param   string  $datetime  date to format in a format accepted by strtotime
	 * @param   string  $format    format, optional
	 *
	 * @return string
	 */
	public static function formatdatetime($datetime, $format = null)
	{
		$settings = RedeventHelper::config();

		if (!static::isValidDate($datetime))
		{
			return JText::_('COM_REDEVENT_OPEN_DATE');
		}

		// Format date
		$date = JFactory::getDate($datetime);
		$formatdate = $date->format($format ?: $settings->get('formatdate', 'd.m.Y'));

		return $formatdate;
	}

	/**
	 * Formats time
	 *
	 * @param   string  $date    date to format in a format accepted by strtotime
	 * @param   string  $time    time to format in a format accepted by strtotime
	 * @param   string  $format  format, optional
	 *
	 * @return string
	 */
	public static function formattime($date = null, $time = null, $format = null)
	{
		$settings = RedeventHelper::config();

		if (!$time)
		{
			return;
		}

		$date = $date ?: 'today';

		// Format time
		$date = JFactory::getDate($date . ' ' . $time);
		$formattime = $date->format($format ?: $settings->get('formattime', 'H:i'));

		return $formattime;
	}

	/**
	 * return formatted event date and time (start and end), or false if open date
	 *
	 * @param   object   $event    event data
	 * @param   boolean  $showend  show end
	 *
	 * @return string
	 */
	public static function formatEventDateTime($event, $showend = null)
	{
		if (!static::isValidDate($event->dates))
		{
			// Open dates
			$date = '<span class="event-date open-date">' . JText::_('COM_REDEVENT_OPEN_DATE') . '</span>';

			return $date;
		}

		$settings = RedeventHelper::config();

		if (is_null($showend))
		{
			$showend = $settings->get('lists_showend', 1);
		}

		$date_start = static::formatdate($event->dates, $event->times);
		$time_start = '';
		$date_end = '';
		$time_end = '';

		// Is this a full day(s) event ?
		if (!$event->allday)
		{
			$time_start = static::formattime($event->dates, $event->times);
		}

		if ($event->allday)
		{
			if ($showend && static::isValidDate($event->enddates))
			{
				if (strtotime($event->enddates . ' -1 day') != strtotime($event->dates)
					&& strtotime($event->enddates) != strtotime($event->dates))
				{
					$date_end = static::formatdate(strftime('Y-m-d', strtotime($event->enddates . ' -1 day')), $event->endtimes);
				}
			}
		}
		elseif ($showend)
		{
			if (static::isValidDate($event->enddates) && strtotime($event->enddates) != strtotime($event->dates))
			{
				$date_end = static::formatdate($event->enddates, $event->endtimes);
				$time_end = static::formattime($event->dates, $event->endtimes);
			}
			else
			{
				// Same day, just display end time after start time
				$time_start .= ' ' . static::formattime($event->dates, $event->endtimes);
			}
		}

		$date = '<span class="event-date">';
		$date .= '<span class="event-start">';
		$date .= '<span class="event-day">' . $date_start . '</span>';

		if ($settings->get('lists_show_time', 0) == 1 && $time_start)
		{
			$date .= ' <span class="event-time">' . $time_start . '</span>';
		}

		$date .= '</span>';

		if ($date_end)
		{
			$date .= ' <span class="event-end"><span class="event-day">' . $date_end . '</span>';

			if ($settings->get('lists_show_time', 0) == 1 && $time_end)
			{
				$date .= ' <span class="event-time">' . $time_end . '</span>';
			}

			$date .= '</span>';
		}

		$date .= '</span>';

		return $date;
	}

	/**
	 * returns iso date
	 *
	 * @param   string  $date  date to format in a format accepted by strtotime
	 * @param   string  $time  time to format in a format accepted by strtotime
	 *
	 * @return string
	 */
	public static function getISODate($date, $time)
	{
		if ($date && strtotime($date))
		{
			$txt = $date;
		}
		else
		{
			return false;
		}

		if ($time)
		{
			$txt .= 'T' . $time;
		}

		return $txt;
	}

	/**
	 * Returns an array for ical formatting
	 *
	 * @param   string  $date  date to format in a format accepted by strtotime
	 * @param   string  $time  time to format in a format accepted by strtotime
	 *
	 * @return array
	 */
	public static function getIcalDateArray($date, $time = null)
	{
		if ($time)
		{
			$sec = strtotime($date . ' ' . $time);
		}
		else
		{
			$sec = strtotime($date);
		}

		if (!$sec)
		{
			return false;
		}

		// Format date
		$parsed = strftime('%Y-%m-%d %H:%M:%S', $sec);

		$date = array('year' => (int) substr($parsed, 0, 4),
			'month' => (int) substr($parsed, 5, 2),
			'day' => (int) substr($parsed, 8, 2));

		// Format time
		if (substr($parsed, 11, 8) != '00:00:00')
		{
			$date['hour'] = substr($parsed, 11, 2);
			$date['min'] = substr($parsed, 14, 2);
			$date['sec'] = substr($parsed, 17, 2);
		}

		return $date;
	}
}
