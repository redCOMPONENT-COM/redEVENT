<?php
/**
 * @package    Redevent.Site
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * RedEvent Component Week Model
 *
 * @package  Redevent.Site
 * @since    2.0
 */
class RedEventModelWeek extends RedeventModelBasesessionlist
{
	protected $week;

	protected $data;

	/**
	 * contructor
	 *
	 */
	public function __construct()
	{
		parent::__construct();

		$input = JFactory::getApplication()->input;
		$week = $input->get('week');
		$this->setWeek($week);

		if (!$week) // Is there an offset in the view parameters ?
		{
			$offset = $input->getInt('weekoffset');

			if ($offset)
			{
				$this->addOffset($offset);
			}
		}
	}

	/**
	 * sets the week reference, must be in year-number format
	 *
	 * @param   string  $week  null for current week
	 *
	 * @return void
	 */
	public function setWeek($week = null)
	{
		if (!$week)
		{
			$this->week = date("Y-W");
		}
		elseif (preg_match('/^([0-9]{4})([0-9]{2})$/', $week, $matches))
		{
			$this->week = $matches[1] . '-' . $matches[2];
		}
		else
		{
			JError::raiseWarning(0, 'wrong week format ' . $week);
		}
	}

	/**
	 * adds an offset to current week
	 *
	 * @param   int  $offset  offset
	 *
	 * @return void
	 */
	public function addOffset($offset)
	{
		$aday = $this->getWeekMonday();
		$week = date("YW", strtotime(sprintf("%s %+d weeks", $aday, $offset)));
		$this->setWeek($week);
	}

	/**
	 * Method to get the data
	 *
	 * @return array
	 */
	public function getData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->data))
		{
			$query = $this->_buildQuery();

			$this->data = $this->_getList($query);
			$this->data = $this->_categories($this->data);
			$this->data = $this->_getPlacesLeft($this->data);
			$this->data = $this->_getPrices($this->data);
		}

		return $this->data;
	}

	/**
	 * Build the where clause
	 *
	 * @param   object  $query  query
	 *
	 * @return object
	 */
	protected function _buildWhere($query)
	{
		// Get the paramaters of the active menu item
		$mainframe = JFactory::getApplication();
		$params 	= $mainframe->getParams();

		$query = parent::_buildWhere($query);

		if (!$this->week)
		{
			// Current week
			$mode = $params->get('week_start') == 'MO' ? 1 : 0;
			$query->where('YEARWEEK(x.dates, ' . $mode . ') = YEARWEEK(NOW(), ' . $mode . ') ');
		}
		else
		{
			$firstday = $this->getWeekMonday();
			$mode = $params->get('week_start') == 'MO' ? 1 : 0;
			$query->where('YEARWEEK(x.dates, ' . $mode . ') = YEARWEEK(' . $this->_db->Quote($firstday) . ', ' . $mode . ') ');
		}

		return $query;
	}

	/**
	 * returns current week reference (year-weeknumber)
	 *
	 * @return string
	 */
	public function getWeek()
	{
		if (!$this->week)
		{
			$this->setWeek();
		}

		return $this->week;
	}

	/**
	 * return week number
	 *
	 * @return int
	 */
	public function getWeekNumber()
	{
		$week = $this->getWeek();

		return (int) substr($week, 5);
	}

	/**
	 * return year
	 *
	 * @return int
	 */
	public function getYear()
	{
		$year = substr($this->getWeek(), 0, 4);

		return (int) $year;
	}

	/**
	 * return days of the week
	 *
	 * @return array
	 */
	public function getWeekDays()
	{
		$week_number = $this->getWeekNumber();
		$year = $this->getYear();

		// First day of the week
		$firstTimestamp = strtotime(sprintf("%04dW%02d", $year, $week_number));

		if (JFactory::getApplication()->getParams()->get('week_start') == 'SU')
		{
			$firstTimestamp = strtotime('last sunday', $firstTimestamp);
		}

		$days = array();

		for ($day = 0; $day < 7; $day++)
		{
			$days[] = date('Y-m-d', strtotime(sprintf("+%d day", $day), $firstTimestamp));
		}

		return $days;
	}

	/**
	 * return reference for previous week (year-weeknumber)
	 *
	 * @return string
	 */
	public function getPreviousWeek()
	{
		$aday = $this->getWeekMonday();
		$prevWeekNumber = date('W', strtotime("-7 days", strtotime($aday)));

		if ($prevWeekNumber > $this->getWeekNumber())
		{
			$year = $this->getYear() - 1;
		}
		else
		{
			$year = $this->getYear();
		}

		return sprintf('%04d%02d', $year, $prevWeekNumber);
	}

	/**
	 * return reference for next week (year-weeknumber)
	 *
	 * @return string
	 */
	public function getNextWeek()
	{
		$aday = $this->getWeekMonday();
		$nextWeekNumber = date('W', strtotime("+7 days", strtotime($aday)));

		if ($nextWeekNumber < $this->getWeekNumber())
		{
			$year = $this->getYear() + 1;
		}
		else
		{
			$year = $this->getYear();
		}

		return sprintf('%04d%02d', $year, $nextWeekNumber);
	}

	/**
	 * returns the date for monday in current week, to be safe to use for week calculations
	 * as first day can be msunday or monday
	 *
	 * @return string date
	 */
	public function getWeekMonday()
	{
		$days = $this->getWeekDays();

		return $days[1];
	}
}
