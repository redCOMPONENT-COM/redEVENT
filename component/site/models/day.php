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

jimport('joomla.application.component.model');

/**
 * Redevent Model Day
 *
 * @package     Joomla
 * @subpackage  redEVENT
 * @since       0.9
 */
class RedeventModelDay extends RedeventModelBaseeventlist
{
	/**
	 * Constructor
	 *
	 * @since 0.9
	 */
	public function __construct()
	{
		parent::__construct();

		$rawday = JRequest::getInt('id', 0, 'request');
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
	 * @see RedeventModelBaseeventlist::_buildWhere()
	 */
	protected function _buildWhere($query)
	{
		$query = parent::_buildWhere($query);
		$nulldate  = '0000-00-00';

		// Only select events of the specified day
		$query->where('(\'' . $this->_date . '\' BETWEEN (x.dates) AND (IF (x.enddates >= now(), x.enddates, \'' . $nulldate . '\')) '
		. 'OR \'' . $this->_date . '\' = x.dates)');

		return $query;
	}

	/**
	 * Return date
	 *
	 * @access public
	 * @return string
	 */
	public function getDay()
	{
		return $this->_date;
	}
}
