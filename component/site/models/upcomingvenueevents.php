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
 * Redevent Model Upcoming Venue events
 *
 * @package     Joomla
 * @subpackage  redEVENT
 * @since       0.9
 */
class RedeventModelUpcomingvenueevents extends RedeventModelBaseeventlist
{
	/**
	 * return events
	 *
	 * @return array
	 */
	public function getUpcomingVenueEvents()
	{
		$params = RedeventHelper::config();
		$db = JFactory::getDBO();

		$query = parent::_buildQuery();
		$query->select('a.submission_types');

		$query->where('x.venueid = ' . JRequest::getInt('id'));

		$upcoming_cond = '( ( CASE WHEN x.times THEN CONCAT(x.dates, " ", x.times) ELSE x.dates END > NOW() '
		. ' AND x.dates < DATE_ADD(NOW(), INTERVAL ' . $params->get('upcoming_days_ahead', 30) . ' DAY) ) ';

		if ($params->get('show_days_no_date', 0) == 1)
		{
			$upcoming_cond .= "       OR x.dates = '0000-00-00' ";
		}

		$upcoming_cond .= " ) ";

		$query->where($upcoming_cond);

		$db->setQuery($query, 0, $params->get('show_number_courses', 10));
		$rows = $db->loadObjectList();

		$rows = $this->_getPrices($rows);

		return $rows;
	}
}
