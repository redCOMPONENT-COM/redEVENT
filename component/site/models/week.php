<?php
/**
 * @version 1.0 $Id: venues.php 321 2009-06-25 09:26:36Z julien $
 * @package Joomla
 * @subpackage redEVENT
 * @copyright redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license GNU/GPL, see LICENSE.php
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

// no direct access
defined('_JEXEC') or die('Restricted access');

require_once('baseeventslist.php');

/**
 * EventList Component Week Model
 *
 * @package Joomla
 * @subpackage redEVENT
 * @since		2.0
*/
class RedEventModelWeek extends RedeventModelBaseEventList
{
	protected $_week;

	protected $_data;

	public function __construct()
	{
		parent::__construct();
		$week = JRequest::getVar('week');
		$this->setWeek($week);
		if (!$week) // is there an offset in the view parameters ?
		{
			$offset = JRequest::getInt('weekoffset');
			if (intval($offset)) {
				$this->addOffset(intval($offset));
			}
		}
	}

	/**
	 * sets the week reference, must be in year-number format
	 * @param string $week null for current week
	 */
	public function setWeek($week = null)
	{
		if (!$week) {
			$this->_week = date("Y-W");
		}
		else if (preg_match('/^([0-9]{4})([0-9]{2})$/', $week, $matches)) {
			$this->_week = $matches[1].'-'.$matches[2];
		}
		else {
			JError::raiseWarning(0, 'wrong week format '.$week);
		}
	}

	/**
	 * adds an offset to current week
	 * @param int $offset
	 */
	public function addOffset($offset)
	{
		$aday = $this->getWeekMonday();
		$week = date("YW", strtotime(sprintf("%s %+d weeks",$aday, $offset)));
		$this->setWeek($week);
	}

	/**
	 * Method to get the Events
	 *
	 * @access public
	 * @return array
	 */
	function &getData( )
	{
		$pop	= JRequest::getBool('pop');

		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$query = $this->_buildQuery();

			if ($pop) {
				// put a limit for print pagination
				//$this->setLimit(5);
			}
			$this->_data = $this->_getList($query);
			$this->_data = $this->_categories($this->_data);
			$this->_data = $this->_getPlacesLeft($this->_data);
			$this->_data = $this->_getPrices($this->_data);
		}

		return $this->_data;
	}

	/**
	 * Build the where clause
	 *
	 * @access private
	 * @return string
	 */
	function _buildWhere()
	{
		$mainframe = JFactory::getApplication();

		$user		= JFactory::getUser();
		$gid		= max($user->getAuthorisedViewLevels());

		// Get the paramaters of the active menu item
		$params 	= & $mainframe->getParams();

		$where = array();

		if (!$this->_week)
		{
			// current week
			$mode = $params->get('week_start') == 'MO' ? 3 : 6;
			$where[] = ' YEARWEEK(x.dates, '.$mode.') = YEARWEEK(NOW(), '.$mode.') ';
		}
		else
		{
			$firstday = $this->getWeekMonday();
			$mode = $params->get('week_start') == 'MO' ? 3 : 6;
			$where[] = ' YEARWEEK(x.dates, '.$mode.') = YEARWEEK('.$this->_db->Quote($firstday).', '.$mode.') ';
		}

		// Second is to only select events assigned to category the user has access to
		$where[] = ' c.access <= '.$gid;

		/*
		 * If we have a filter, and this is enabled... lets tack the AND clause
		* for the filter onto the WHERE clause of the item query.
		*/
		if ($params->get('filter_text'))
		{
			$filter 		  = $this->getState('filter');
			$filter_type 	= $this->getState('filter_type');

			if ($filter)
			{
				// clean filter variables
				$filter 		= JString::strtolower($filter);
				$filter			= $this->_db->Quote( '%'.$this->_db->getEscaped( $filter, true ).'%', false );
				$filter_type 	= JString::strtolower($filter_type);

				switch ($filter_type)
				{
					case 'title' :
						$where[] = ' LOWER( a.title ) LIKE '.$filter;
						break;

					case 'venue' :
						$where[] = ' LOWER( l.venue ) LIKE '.$filter;
						break;

					case 'city' :
						$where[] = ' LOWER( l.city ) LIKE '.$filter;
						break;

					case 'type' :
						$where[] = ' LOWER( c.catname ) LIKE '.$filter;
						break;
				}
			}
		}

		if ($ev = $this->getState('filter_event'))
		{
			$where[] = 'a.id = '.$this->_db->Quote($ev);
		}

		if ($filter_venue = $this->getState('filter_venue'))
		{
			$where[] = ' l.id = ' . $this->_db->Quote($filter_venue);
		}

		if ($cat = $this->getState('filter_category'))
		{
			$category = $this->getCategory((int) $cat);
			if ($category) {
				$where[] = '(c.id = '.$this->_db->Quote($category->id) . ' OR (c.lft > ' . $this->_db->Quote($category->lft) . ' AND c.rgt < ' . $this->_db->Quote($category->rgt) . '))';
			}
		}

		// more filters
		if ($state = JRequest::getVar('state', '', 'request', 'string')) {
			$where[] = ' STRCMP(l.state, '.$this->_db->Quote($state).') = 0 ';
		}
		if ($country = JRequest::getVar('country', '', 'request', 'string')) {
			$where[] = ' STRCMP(l.country, '.$this->_db->Quote($country).') = 0 ';
		}

		$customs = $this->getState('filter_customs');

		foreach ((array) $customs as $key => $custom)
		{
			if ($custom != '')
			{
				if (is_array($custom)) {
					$custom = implode("/n", $custom);
				}
				$where[] = ' custom'.$key.' LIKE ' . $this->_db->Quote('%'.$custom.'%');
			}
		}

		return ' WHERE '.implode(' AND ', $where);
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
		if (JFactory::getApplication()->getParams()->get('week_start') == 'SU')
		{
			$offset = -1;
		}
		else
		{
			$offset = 0;
		}

		for ($day = 1; $day <= 7; $day++)
		{
			$days[] = date('Y-m-d', strtotime($year."W".$week_number.($day + $offset)));
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
		$aday = $this->getWeekMonday();
		$prev = strtotime("$aday +7 days");
		return date('YW', $prev);
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