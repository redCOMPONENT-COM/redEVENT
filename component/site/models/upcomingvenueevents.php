<?php
/**
 * @package    Redevent.Site
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevent Model Upcoming Venue events
 *
 * @package     Joomla
 * @subpackage  redEVENT
 * @since       0.9
 */
class RedeventModelUpcomingvenueevents extends RedeventModelBasesessionlist
{
	/**
	 * return events
	 *
	 * @return array
	 */
	public function getUpcomingVenueEvents()
	{
		$params = RedeventHelper::config();
		$db = $this->_db;

		$query = parent::_buildQuery();
		$query->select('a.submission_types');

		$query->where('x.venueid = ' . JFactory::getApplication()->input->getInt('id'));

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
