<?php
/**
 * @package    Redevent.Library
 * @copyright  Copyright (C) 2008 - 2014 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Helper for handling recurrence
 *
 * @package  Redevent.Library
 * @since    2.5
 */
class RedeventRecurrenceHelper
{
	/**
	 * Parse an ical recurrence rule, and return a RedeventRecurrenceRule object
	 *
	 * @param   string  $icalRule  rule string
	 *
	 * @return RedeventRecurrenceRule
	 */
	public function getRule($icalRule = null)
	{
		$rule = new RedeventRecurrenceRule;

		if (!$icalRule)
		{
			return $rule;
		}

		$parts = explode(';', $icalRule);

		foreach ($parts as $p)
		{
			if (!strpos($p, '='))
			{
				continue;
			}

			list($element, $value) = explode('=', $p);

			switch ($element)
			{
				case 'RRULE:FREQ':
					$rule->type = $value;
					break;

				case 'INTERVAL':
					$rule->interval = $value;
					break;

				case 'COUNT':
					$rule->repeat_type = 'count';
					$rule->repeat_until_count = $value;
					break;

				case 'UNTIL':
					$rule->repeat_type = 'until';
					$rule->repeat_until_date = $this->icalDatetotime($value);
					break;

				case 'BYDAY':
					$days = explode(',', $value);

					foreach ($days as $d)
					{
						preg_match('/([-]*)([0-9]*)([A-Z]*)/', $d, $res);
						$revert = ($res[1] == '-');
						if ($res[2] && $res[3])
						{ // has number and day
							if ($rule->type == 'MONTHLY')
							{
								$rule->monthtype = 'byday';
							}
							if ($revert)
							{
								if (!in_array($res[2], $rule->rweeks))
								{
									$rule->rweeks[] = $res[2];
								}
								if (!in_array($res[3], $rule->rweekdays))
								{
									$rule->rweekdays[] = $res[3];
								}
							}
							else
							{
								if (!in_array($res[2], $rule->weeks))
								{
									$rule->weeks[] = $res[2];
								}
								if (!in_array($res[3], $rule->weekdays))
								{
									$rule->weekdays[] = $res[3];
								}
							}
						}
						else if ($res[2])
						{ // only number
							$rule->bydays[] = $res[2];
							if ($rule->type == 'MONTHLY')
							{
								$rule->monthtype = 'bymonthdays';
							}
							if ($revert)
							{
								$rule->reverse_bydays = 1;
							}
						}
						else if ($res[3])
						{ // only day
							if ($rule->type == 'MONTHLY')
							{
								$rule->monthtype = 'byday';
							}
							if ($revert)
							{
								if (!in_array($res[3], $rule->rweekdays))
								{
									$rule->rweekdays[] = $res[3];
								}
							}
							else
							{
								if (!in_array($res[3], $rule->weekdays))
								{
									$rule->weekdays[] = $res[3];
								}
							}
						}
					}
					break;
				default:
					break;
			}
		}

		return $rule;
	}

	private function icalDatetotime($date)
	{
		if (preg_match('/([0-9]{4})([0-9]{2})([0-9]{2})T([0-9]{2})([0-9]{2})([0-9]{2})(Z?)/', $date, $res))
		{
			$res = mktime($res[4], $res[5], $res[6], $res[2], $res[3], $res[1]);
			return strftime('%Y-%m-%d %H:%M:%S', $res);
		}
		else
		{
			return false;
		}
	}
}
