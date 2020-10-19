<?php
/**
 * @package    Redevent.Library
 * @copyright  Copyright (C) 2008 - 2020 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Helper for handling recurrence
 *
 * @package  Redevent.Library
 * @since    2.5
 */
class RedeventRecurrenceParser
{
	private $rule;

	private $params;

	private $data;

	/**
	 * Constructor
	 *
	 * @param   array  $config  optional config
	 */
	public function __construct($config = null)
	{
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
	 * parses the data from editxref form, and returns the corresponding rrule
	 *
	 * @param   array  $data  posted data
	 *
	 * @return string rrule
	 */
	public function parsePost($data)
	{
		$this->data = $data;

		if (empty($data['type']))
		{
			return '';
		}

		switch ($data['type'])
		{
			case 'DAILY':
				$rrule = $this->parseDaily();
				break;

			case 'WEEKLY':
				$rrule = $this->parseWeekly();
				break;

			case 'MONTHLY':
				$rrule = $this->parseMonthly();
				break;

			case 'YEARLY':
				$rrule = $this->parseYearly();
				break;

			case 'NONE':
			default:
				$rrule = '';
				break;
		}

		return $rrule;
	}

	/**
	 * returns daily parsed rule
	 *
	 * @return string rrule
	 */
	private function parseDaily()
	{
		$data = $this->data;

		$rrule = "RRULE:FREQ=DAILY;INTERVAL=" . $data['interval'] . ';';

		if ($data['repeat_type'] == 'count')
		{
			$rrule .= "COUNT=" . $data['repeat_until_count'];
		}
		else
		{
			$rrule .= "UNTIL=" . $this->convertDate($data['repeat_until_date']);
		}

		return $rrule;
	}

	/**
	 * returns weekly parsed rule
	 *
	 * @return string rrule
	 */
	private function parseWeekly()
	{
		$data = $this->data;

		$rrule = "RRULE:FREQ=WEEKLY;INTERVAL=" . $data['interval'] . ';';

		// Limit
		if ($data['repeat_type'] == 'count')
		{
			$rrule .= "COUNT=" . $data['repeat_until_count'] . ';';
		}
		else
		{
			$rrule .= "UNTIL=" . $this->convertDate($data['repeat_until_date']) . ';';
		}

		// Week start
		$rrule .= "WKST=" . $this->params->get('week_start', 'MO') . ';';

		// Selected days
		if (isset($data['wweekdays']))
		{
			$rrule .= "BYDAY=" . implode(',', $data['wweekdays']) . ';';
		}

		return $rrule;
	}

	/**
	 * returns monthly parsed rule
	 *
	 * @return string rrule
	 */
	private function parseMonthly()
	{
		$data = $this->data;
		$rrule = "RRULE:FREQ=MONTHLY;INTERVAL=" . $data['interval'] . ';';

		// Limit
		if ($data['repeat_type'] == 'count')
		{
			$rrule .= "COUNT=" . $data['repeat_until_count'] . ';';
		}
		else
		{
			$rrule .= "UNTIL=" . $this->convertDate($data['repeat_until_date']) . ';';
		}

		if ($data['monthtype'] == 'byday')
		{
			// Week start
			$rrule .= "WKST=" . $this->params->get('week_start', 'MO') . ';';

			// Selected weeks, normal order
			$days = array();

			if (isset($data['mweeks']))
			{
				foreach ($data['mweeks'] as $week)
				{
					foreach ($data['mweekdays'] as $day)
					{
						$days[] = $week . $day;
					}
				}
			}

			if (isset($data['mrweeks']))
			{
				foreach ($data['mrweeks'] as $week)
				{
					foreach ($data['mrweekdays'] as $day)
					{
						$days[] = '-' . $week . $day;
					}
				}
			}

			if (count($days))
			{
				$rrule .= "BYDAY=" . implode(',', $days) . ';';
			}
		}

		if ($data['monthtype'] == 'bymonthday')
		{
			$days = array();
			$reverse = (isset($data['reverse_bymonthday'])) ? true : false;

			foreach (explode(',', $data['bymonthdays']) as $day)
			{
				$days[] = ($reverse ? '-' : '') . ((int) $day);
			}

			$rrule .= "BYDAY=" . implode(',', $days) . ';';
		}

		return $rrule;
	}

	/**
	 * returns monthly parsed rule
	 *
	 * @return string rrule
	 */
	private function parseYearly()
	{
		$data = $this->data;
		$rrule = "RRULE:FREQ=YEARLY;INTERVAL=" . $data['interval'] . ';';

		// Limit
		if ($data['repeat_type'] == 'count')
		{
			$rrule .= "COUNT=" . $data['repeat_until_count'] . ';';
		}
		else
		{
			$rrule .= "UNTIL=" . $this->convertDate($data['repeat_until_date']) . ';';
		}

		$days = array();
		$reverse = (isset($data['reverse_byyearday'])) ? true : false;

		foreach (explode(',', $data['byyeardays']) as $day)
		{
			$days[] = ($reverse ? '-' : '') . ((int) $day);
		}

		$rrule .= "BYDAY=" . implode(',', $days) . ';';

		return $rrule;
	}

	/**
	 * Convert date to ical recurrence format
	 *
	 * @param   string  $date  date
	 *
	 * @return string
	 */
	private function convertDate($date)
	{
		$convert = strftime('%Y%m%dT%H%M%S', strtotime($date));

		return $convert;
	}
}
