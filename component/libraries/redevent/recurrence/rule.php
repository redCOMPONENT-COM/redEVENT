<?php
/**
 * @package    Redevent.Library
 * @copyright  Copyright (C) 2008 - 2020 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Recurrence rule container
 *
 * @package  Redevent.Library
 * @since    2.5
 */
class RedeventRecurrenceRule
{
	/**
	 * type of recurence: NONE, DAILY,WEEKLY, MONTHLY, YEARLY
	 * @var string
	 */
	public $type = 'NONE';

	/**
	 * interval of repetition
	 * @var int
	 */
	public $interval = 1;

	/**
	 * type of repetition limit: count (count), or until date (until)
	 * @var string
	 */
	public $repeat_type = 'count';

	/**
	 * number of repeats
	 * @var int
	 */
	public $repeat_until_count = 10;

	/**
	 * repeat limit date
	 * @var string
	 */
	public $repeat_until_date = null;

	/**
	 * selected days for weekly repeat (list SU, MO, ...)
	 * @var array
	 */
	public $weekdays = array();

	/**
	 * selected days for weekly repeat reverted (list SU, MO, ...)
	 * @var array
	 */
	public $rweekdays = array();

	/**
	 * type of rule for month freq: bymonthday (int list: bymonthday), or by weekdays (byday)
	 * @var string
	 */
	public $monthtype = 'bymonthday';

	/**
	 * array of days number
	 * @var array
	 */
	public $bydays = array();

	/**
	 * count days from end
	 * @var int
	 */
	public $reverse_bydays = 0;

	/**
	 * array of weeks numbers (1, 2, ...)
	 * @var array
	 */
	public $weeks = array();

	/**
	 * array of weeks numbers (1, 2, ...), counted from end of the month
	 * @var array
	 */
	public $rweeks = array();

	/**
	 * Adapt to form data for binding
	 *
	 * the form reuse same property for different fields (for clarity)
	 *
	 * @return object
	 */
	public function getFormData()
	{
		$data = new stdClass;
		$data->type = $this->type;
		$data->interval = $this->interval;
		$data->repeat_type = $this->repeat_type;
		$data->repeat_until_count = $this->repeat_until_count;
		$data->repeat_until_date = $this->repeat_until_date;

		// Weekly recurrence
		$data->wweekdays = $this->weekdays;

		// Monthly recurrence
		$data->month_type = $this->monthtype;
		$data->bymonthdays = implode(',', $this->bydays);
		$data->reverse_bymonthday = $this->reverse_bydays;
		$data->mweeks = $this->weeks;
		$data->mweekdays = $this->weekdays;
		$data->mrweeks = $this->rweeks;
		$data->mrweekdays = $this->rweekdays;

		// Yearly recurrence
		$data->byyeardays = implode(',', $this->bydays);
		$data->reverse_byyearday = $this->reverse_bydays;

		return $data;
	}
}
