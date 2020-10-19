<?php
/**
 * @package    Redevent.Library
 * @copyright  Copyright (C) 2008 - 2020 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Computes the next session of an event session according to recurrence rule
 *
 * @package  Redevent.Library
 * @since    2.5
 */
class RedeventRecurrenceNext
{
	/**
	 * @var RedeventRecurrenceRule
	 */
	private $rule;

	private $params;

	/**
	 * Constructor
	 *
	 * @param   RedeventRecurrenceRule  $rule    recurrence rule
	 * @param   array                   $config  optional config
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct($rule, $config = null)
	{
		if ($rule instanceof RedeventRecurrenceRule)
		{
			$this->rule = $rule;
		}
		else
		{
			throw new InvalidArgumentException('Wrong type for rule');
		}

		if (is_array($config) && isset($config['params']))
		{
			$this->params = $config['params'];
		}
		else
		{
			$this->params = JComponentHelper::getParams('com_redevent');
		}
	}

	/**
	 * Get next date
	 *
	 * @param   object  $last_xref  Data of last (furthest date) session for this recurrence
	 *
	 * @return object next session data
	 */
	public function getNext($last_xref)
	{
		$rule = $this->rule;
		$params = $this->params;

		$week_start = $params->get('week_start', 'SU');

		// Check the count
		if ($rule->repeat_type == 'count' && $last_xref->count >= $rule->repeat_until_count)
		{
			return false;
		}

		$days_name = array(
			'SU' => 'sunday', 'MO' => 'monday', 'TU' => 'tuesday',
			'WE' => 'wednesday', 'TH' => 'thursday', 'FR' => 'friday', 'SA' => 'saturday'
		);
		$xref_start = strtotime($last_xref->dates);

		// Get the next start timestamp
		switch ($rule->type)
		{
			case 'DAILY':
				$next_start = strtotime($last_xref->dates . " +" . $rule->interval . " day");
				break;

			case 'WEEKLY':
				// Calculate next dates for all set weekdays
				$next = array();

				if ($week_start == 'SU')
				{
					$current = strftime('%w', $xref_start);
					$days_number = array('SU' => 0, 'MO' => 1, 'TU' => 2, 'WE' => 3, 'TH' => 4, 'FR' => 5, 'SA' => 6);
				}
				else
				{
					$current = strftime('%u', $xref_start);
					$days_number = array('MO' => 1, 'TU' => 2, 'WE' => 3, 'TH' => 4, 'FR' => 5, 'SA' => 6, 'SU' => 7);
				}

				if (!$rule->weekdays || !count($rule->weekdays))
				{
					// Force to the day of previous session
					$rule->weekdays = array(array_search(date('N', strtotime($last_xref->dates)), $days_number));
				}

				foreach ($rule->weekdays as $d)
				{
					if ($days_number[$d] > $current)
					{
						$next[] = strtotime('+1 ' . $days_name[$d], strtotime($last_xref->dates));
					}
					elseif ($days_number[$d] == $current)
					{
						// Same day, look in next intervall, after this day
						$next[] = strtotime('+' . $rule->interval . ' ' . $days_name[$d], strtotime($last_xref->dates) + 3600 * 24);
					}
					else
					{
						// In next intervall
						$next[] = strtotime('+' . $rule->interval . ' ' . $days_name[$d], strtotime($last_xref->dates));
					}
				}

				// The next one is the lowest value
				$next_start = min($next);
				break;

			case 'MONTHLY':
				if ($rule->monthtype == 'byday')
				{
					// First day of this month
					$first_this = mktime(0, 0, 0, strftime('%m', $xref_start), 1, strftime('%Y', $xref_start));

					// Last day of this month
					$last_this = mktime(0, 0, 0, strftime('%m', $xref_start) + 1, 0, strftime('%Y', $xref_start));

					// First day of +interval month
					$first_next_interval = mktime(0, 0, 0, strftime('%m', $xref_start) + $rule->interval, 1, strftime('%Y', $xref_start));

					// Last day of this month
					$last_next_interval = mktime(0, 0, 0, strftime('%m', $xref_start) + 1 + $rule->interval, 0, strftime('%Y', $xref_start));

					$days = array();

					foreach ($rule->weeks as $week)
					{
						foreach ($rule->weekdays as $day)
						{
							$int_day = strtotime($week . ' ' . $days_name[$day], $first_this);

							if ($int_day > $xref_start && $int_day <= $last_this)
							{
								$days[] = $int_day;
							}

							$int_day = strtotime($week . ' ' . $days_name[$day], $first_next_interval);

							if ($int_day > $xref_start && $int_day <= $last_next_interval)
							{
								$days[] = $int_day;
							}
						}
					}

					foreach ($rule->rweeks as $week)
					{
						foreach ($rule->rweekdays as $day)
						{
							$int_day = strtotime('-' . $week . ' ' . $days_name[$day], $last_this + 24 * 3600);

							if ($int_day > $xref_start && $int_day >= $first_this)
							{
								$days[] = $int_day;
							}

							$int_day = strtotime('-' . $week . ' ' . $days_name[$day], $last_next_interval + 24 * 3600);

							if ($int_day > $xref_start && $int_day >= $first_next_interval)
							{
								$days[] = $int_day;
							}
						}
					}

					$next_start = min($days);
				}
				else
				{
					$current = strftime('%d', strtotime($last_xref->dates));

					if (!$rule->bydays || !count($rule->bydays))
					{
						// Force to the day of previous session
						$rule->bydays = array(date('d', strtotime($last_xref->dates)));
					}

					if (!$rule->reverse_bydays)
					{
						sort($rule->bydays);
						$next_day = null;

						foreach ($rule->bydays as $day)
						{
							if ($day > $current)
							{
								$next_day = $day;
								break;
							}
						}

						if ($next_day == null)
						{
							// Not this month => this month + interval month!
							$year_month = strftime(
								'%Y-%m', strtotime(date("Y-m-1", strtotime($last_xref->dates)) . ' + ' . $rule->interval . " months")
							);
							$next_start = strtotime($year_month . '-' . $rule->bydays[0]);
						}
						else
						{
							$year_month = strftime('%Y-%m', strtotime($last_xref->dates));
							$next_start = strtotime($year_month . '-' . $next_day);
						}
					}
					else
					{
						$current_sec = strtotime($last_xref->dates);
						$next = array();

						foreach ($rule->bydays as $day)
						{
							// We need to check the dates for this month, and the +interval month
							$dd = strtotime(date("Y-m-1", strtotime($last_xref->dates)) . ' + 1 months -' . $day . ' day');

							if ($dd > $current_sec)
							{
								$next[] = $dd;
							}

							$dd = strtotime(
								date("Y-m-1", strtotime($last_xref->dates)) . ' +'
								. (1 + $rule->interval) . ' months -' . $day . ' days', strtotime($last_xref->dates)
							);

							if ($dd > $current_sec)
							{
								$next[] = $dd;
							}
						}

						// The next is the closest, lower value
						$next_start = min($next);
					}
				}

				break;

			case 'YEARLY':
				$current = strtotime($last_xref->dates);

				if (empty($rule->bydays))
				{
					// In that case, use current date, plus a year
					$next_start = mktime(0, 0, 0, strftime('%m', $current), strftime('%d', $current), strftime('%Y', $current) + $rule->interval);
				}
				else
				{
					if (!$rule->reverse_bydays)
					{
						sort($rule->bydays);
						$next_day = $rule->bydays[0];

						foreach ($rule->bydays as $day)
						{
							if ($day > $current)
							{
								$next_day = $day;
								break;
							}
						}

						if ($next_day == $rule->bydays[0])
						{
							// Not this year => this year + interval year!
							$next_start = mktime(0, 0, 0, 1, $next_day, strftime('%Y', strtotime($last_xref->dates)) + 1);
						}
						else
						{
							$next_start = mktime(0, 0, 0, 1, $next_day, strftime('%Y', strtotime($last_xref->dates)));
						}
					}
					else
					{
						// Total days in this year
						$total = strftime('%j', mktime(0, 0, 0, 1, 0, strftime('%Y', strtotime($last_xref->dates)) + 1));
						$rev_days = array();

						// Get number in proper order
						rsort($rule->bydays);

						foreach ($rule->bydays as $day)
						{
							$rev_days[] = $total - $day + 1;
						}

						$next_day = null;

						foreach ($rev_days as $day)
						{
							if ($day > $current)
							{
								$next_day = $day;
								break;
							}
						}

						if ($next_day == null)
						{
							// Not this year => this year + interval year!
							$next_start = mktime(0, 0, 0, 1, -$rule->bydays[0], strftime('%Y', strtotime($last_xref->dates)) + 1 + $rule->interval);
						}
						else
						{
							$next_start = mktime(0, 0, 0, 1, $next_day, strftime('%Y', strtotime($last_xref->dates)));
						}
					}
				}
				break;

			case 'NONE':
			default:
				break;
		}

		if (!isset($next_start) || !$next_start)
		{
			return false;
		}

		// Check the until rule
		if ($rule->repeat_type == 'until'
			&& strtotime(strftime('%Y-%m-%d', $next_start) . ' ' . $last_xref->times) > strtotime($rule->repeat_until_date))
		{
			return false;
		}

		$delta = $next_start - strtotime($last_xref->dates);

		if (!$delta)
		{
			// No delta, so same session...
			return false;
		}

		// Return the new occurence
		$new = clone $last_xref;

		unset($new->id);

		$new->dates = strftime('%Y-%m-%d', $next_start);

		if (strtotime($last_xref->enddates))
		{
			$new->enddates = strftime('%Y-%m-%d', strtotime($last_xref->enddates) + $delta);
		}

		if (strtotime($last_xref->registrationend))
		{
			$new->registrationend = strftime('%Y-%m-%d', strtotime($last_xref->registrationend) + $delta);
		}

		$new->count++;

		return $new;
	}
}
