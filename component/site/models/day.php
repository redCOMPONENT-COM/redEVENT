<?php
/**
 * @package    Redevent.Site
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevent Model Day
 *
 * @package  Redevent.Site
 * @since    0.9
 */
class RedeventModelDay extends RedeventModelBasesessionlist
{
	/**
	 * Constructor
	 *
	 * @since 0.9
	 */
	public function __construct()
	{
		parent::__construct();

		$rawday = JFactory::getApplication()->input->getInt('id', 0, 'request');
		$this->setDate($rawday);
	}

	/**
	 * Method to set the date
	 *
	 * @param   string  $date  the date to display. should be YYYYMMDD format
	 *
	 * @return void
	 */
	public function setDate($date)
	{
		$mainframe = JFactory::getApplication();

		// Get the paramaters of the active menu item
		$params    = $mainframe->getParams('com_redevent');

		// 0 means we have a direct request from a menuitem and without any parameters (eg: calendar module)
		if ($date == 0)
		{
			$dayoffset	= $params->get('days');
			$timestamp	= mktime(0, 0, 0, date("m"), date("d") + $dayoffset, date("Y"));
			$date		= strftime('%Y-%m-%d', $timestamp);
		}
		// A valid date  has 8 characters
		elseif (strlen($date) == 8)
		{
			$year 	= substr($date, 0, -4);
			$month	= substr($date, 4, -2);
			$tag	= substr($date, 6);

			// Check if date is valid
			if (checkdate($month, $tag, $year))
			{
				$date = $year . '-' . $month . '-' . $tag;
			}
			else
			{
				// Date isn't valid raise notice and use current date
				$date = date('Ymd');
				JError::raiseNotice('REDEVENT_GENERIC_ERROR', JText::_('COM_REDEVENT_INVALID_DATE_REQUESTED_USING_CURRENT'));
			}
		}
		else
		{
			// Date isn't valid raise notice and use current date
			$date = date('Ymd');
			JError::raiseNotice('REDEVENT_GENERIC_ERROR', JText::_('COM_REDEVENT_INVALID_DATE_REQUESTED_USING_CURRENT'));
		}

		$this->_date = $date;
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
		$query = parent::_buildWhere($query);

		// Only select events of the specified day
		$query->where('x.dates > 0');
		$query->where('(' . $this->_db->quote($this->_date) . ' BETWEEN x.dates AND x.enddates '
			. ' OR ' . $this->_db->quote($this->_date) . ' = x.dates)'
		);

		return $query;
	}

	/**
	 * Return date
	 *
	 * @return string
	 */
	public function getDay()
	{
		return $this->_date;
	}
}
