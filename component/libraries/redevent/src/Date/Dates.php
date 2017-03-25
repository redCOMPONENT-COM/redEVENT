<?php
/**
 * @package    Redevent.Library
 *
 * @copyright  Copyright (C) 2009 - 2014 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

namespace Redevent\Date;

defined('_JEXEC') or die;

/**
 * Minimal fields to define session dates
 *
 * @todo: use Traits when we don't have to use php 5.3 any more...
 *
 * @since  3.2.0
 */
class Dates
{
	public $dates;

	public $times;

	public $enddates;

	public $endtimes;

	public $allday;

	/**
	 * Init object, requires dates, times, enddates, enddtimes, allday properties
	 *
	 * @param   mixed  $data  object or array
	 */
	public function __construct($data)
	{
		if (is_object($data))
		{
			$data = get_object_vars($data);
		}
		elseif (!is_array($data))
		{
			throw new \InvalidArgumentException('Dates requires an array or object');
		}

		if (!isset($data['dates']) || !isset($data['enddates']) || !isset($data['times']) || !isset($data['endtimes']) || !isset($data['allday']))
		{
			throw new \InvalidArgumentException('Dates requires properties dates, times, enddates, enddtimes, allday from input object');
		}

		$this->dates = $data['dates'];
		$this->enddates = $data['enddates'];
		$this->times = $data['times'];
		$this->endtimes = $data['endtimes'];
		$this->allday = $data['allday'];
	}

	/**
	 * return formatted date and time (start and end)
	 *
	 * @param   boolean  $showend   show end
	 * @param   boolean  $showtime  show end
	 *
	 * @return string
	 */
	public function formatEventDateTime($showend = true, $showtime = true)
	{
		if (!\RedeventHelperDate::isValidDate($this->dates))
		{
			return \RedeventLayoutHelper::render('redevent.date.open');
		}

		$date_start = \RedeventHelperDate::formatdate($this->dates, $this->times);
		$time_start = $showtime && !$this->isAllDay() ? \RedeventHelperDate::formattime($this->dates, $this->times) : '';
		$date_end   = $showend          ? \RedeventHelperDate::formatdate($this->enddates, $this->endtimes) : '';
		$time_end   = $showtime && !$this->isAllDay() ? \RedeventHelperDate::formattime($this->enddates, $this->endtimes) :  '';

		if ($this->isOneDay())
		{
			return \RedeventLayoutHelper::render('redevent.date.oneday', compact('date_start', 'time_start', 'time_end'));
		}

		return \RedeventLayoutHelper::render('redevent.date.multipledays', compact('date_start', 'time_start', 'date_end', 'time_end'));
	}

	/**
	 * Get start date/time
	 *
	 * @param   bool  $dateOnly  only take day into account
	 *
	 * @return JDate
	 */
	public function getDateStart($dateOnly = false)
	{
		if ($this->isOpenDate())
		{
			return false;
		}

		return JFactory::getDate($this->dates . ($this->isAllDay() || $dateOnly ? '' : ' ' . $this->times));
	}

	/**
	 * Get end date/time
	 *
	 * @param   bool  $dateOnly  only take day into account
	 *
	 * @return JDate
	 */
	public function getDateEnd($dateOnly = false)
	{
		if ($this->isOpenDate())
		{
			return false;
		}

		if (RedeventHelperDate::isValidDate($this->enddates))
		{
			$endDate = $this->enddates;
		}
		else
		{
			$endDate = $this->dates;
		}

		return JFactory::getDate($endDate . ($this->isAllDay() || $dateOnly ? '' : ' ' . $this->endtimes));
	}

	/**
	 * Get session duration in days (On how many days it spans)
	 *
	 * @return integer
	 */
	public function getDurationDays()
	{
		if ($this->isOpenDate())
		{
			return false;
		}

		if ($this->getDateStart(true) == $this->getDateEnd(true))
		{
			return 1;
		}

		return $this->getDateEnd(true)->diff($this->getDateStart(true))->format('%a') + 1;
	}

	/**
	 * Get unix start date/time from db
	 *
	 * @return string
	 */
	public function getUnixStart()
	{
		if (!RedeventHelperDate::isValidDate($this->dates))
		{
			return null;
		}

		return strtotime($this->dates . ($this->isAllDay() ? '' : ' ' . $this->times));
	}

	/**
	 * Get unix start date/time from db
	 *
	 * @return string
	 */
	public function getUnixEnd()
	{
		if (!RedeventHelperDate::isValidDate($this->enddates))
		{
			return null;
		}

		return strtotime($this->enddates . ($this->isAllDay() ? '' : ' ' . $item->endtimes));
	}

	/**
	 * Return true if it's a full day session
	 *
	 * @return boolean
	 */
	public function isAllDay()
	{
		return $this->allday > 0;
	}

	/**
	 * Return true if it's a one day session
	 *
	 * @return boolean
	 */
	public function isOneDay()
	{
		if (!\RedeventHelperDate::isValidDate($this->enddates))
		{
			return true;
		}

		if ($this->isAllDay() && strtotime($this->enddates . ' -1 day') == strtotime($this->dates))
		{
			// All day events end at midnight the day before defined end date
			return true;
		}

		return strtotime($this->enddates) == strtotime($this->dates);
	}

	/**
	 * returns true if the session is over.
	 * object in parameters must include properties
	 *
	 * @param   bool  $day_check  daycheck: if true, events are over only the next day, otherwise, use time too.
	 *
	 * @return boolean
	 */
	public function isOver($day_check = true)
	{
		if (!\RedeventHelperDate::isValidDate($this->dates))
		{
			// Open dates
			return false;
		}

		$cmp = $day_check ? strtotime('today') : time();

		if (\RedeventHelperDate::isValidDate($this->enddates))
		{
			return strtotime($this->enddates . ($this->allday ? ' 23:59:59' : ' ' . $this->endtimes)) < $cmp;
		}
		else
		{
			return strtotime($this->dates . ' ' . ($this->allday ? '' : ' ' . $this->times)) < $cmp;
		}
	}
}
