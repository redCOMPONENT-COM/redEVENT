<?php
/**
 * @package     Joomla
 * @subpackage  redEVENT
 * @copyright   redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license     GNU/GPL, see LICENSE.php
 * redEVENT is based on EventList made by Christoph Lukes from schlu.net
 * redEVENT can be downloaded from www.redcomponent.com
 * redEVENT is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * redEVENT is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with redEVENT; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

require_once 'baseeventslist.php';

/**
 * RedEvent Component Week Model
 *
 * @package     Joomla
 * @subpackage  redEVENT
 * @since       2.0
 */
class RedEventModelWeek extends RedeventModelBaseEventList
{
	protected $_week;

	protected $_data;

	/**
	 * contructor
	 *
	 */
	public function __construct()
	{
		parent::__construct();
		$week = JRequest::getVar('week');
		$this->setWeek($week);

		if (!$week) // Is there an offset in the view parameters ?
		{
			$offset = JRequest::getInt('weekoffset');

			if (intval($offset))
			{
				$this->addOffset(intval($offset));
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
			$this->_week = date("Y-W");
		}
		elseif (preg_match('/^([0-9]{4})([0-9]{2})$/', $week, $matches))
		{
			$this->_week = $matches[1] . '-' . $matches[2];
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
		$aday = reset($this->getWeekDays());
		$week = date("YW", strtotime(sprintf("%s %+d weeks", $aday, $offset)));
		$this->setWeek($week);
	}

	/**
	 * (non-PHPdoc)
	 * @see RedeventModelBaseEventList::getData()
	 */
	public function &getData()
	{
		$pop	= JRequest::getBool('pop');

		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$query = $this->_buildQuery();

			$this->_data = $this->_getList($query);
			$this->_data = $this->_categories($this->_data);
			$this->_data = $this->_getPlacesLeft($this->_data);
			$this->_data = $this->_getPrices($this->_data);
		}

		return $this->_data;
	}

	/**
	 * (non-PHPdoc)
	 * @see RedeventModelBaseEventList::_buildWhere()
	 */
	protected function _buildWhere($query)
	{
		// Get the paramaters of the active menu item
		$mainframe = JFactory::getApplication();
		$params 	= $mainframe->getParams();

		$query = parent::_buildWhere($query);

		if (!$this->_week)
		{
			// Current week
			$mode = $params->get('week_start') == 'MO' ? 1 : 0;
			$query->where('YEARWEEK(x.dates, ' . $mode . ') = YEARWEEK(NOW(), ' . $mode . ') ');
		}
		else
		{
			$firstday = reset($this->getWeekDays());
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
		if (!$this->_week)
		{
			$this->setWeek();
		}

		return $this->_week;
	}

	/**
	 * return week number
	 *
	 * @return int
	 */
	public function getWeekNumber()
	{
		$week = $this->getWeek();

		return substr($week, 5);
	}

	/**
	 * return year
	 *
	 * @return int
	 */
	public function getYear()
	{
		$week = $this->getWeek();

		return substr($week, 0, 4);
	}

	/**
	 * return days of the week
	 *
	 * @return array
	 */
	public function getWeekDays()
	{
		$week = $this->getWeek();
		$week_number = $this->getWeekNumber();
		$year = $this->getYear();

		$days = array();

		for ($day = 1; $day <= 7; $day++)
		{
			$days[] = date('Y-m-d', strtotime($year . "W" . $week_number . $day));
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
		$aday = reset($this->getWeekDays());
		$prev = strtotime("$aday -7 days");

		return date('YW', $prev);
	}

	/**
	 * return reference for next week (year-weeknumber)
	 *
	 * @return string
	 */
	public function getNextWeek()
	{
		$aday = reset($this->getWeekDays());
		$prev = strtotime("$aday +7 days");

		return date('YW', $prev);
	}
}
