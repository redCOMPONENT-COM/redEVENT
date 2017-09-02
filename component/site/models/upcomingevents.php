<?php
/**
 * @package    Redevent.Site
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevent Model Upcoming events
 *
 * @package  Redevent.Site
 * @since    0.9
 */
class RedeventModelUpcomingevents extends RedeventModelBasesessionlist
{
	/**
	 * returns events
	 *
	 * @return array
	 */
	public function getUpcomingEvents()
	{
		$params = RedeventHelper::config();
		$db = JFactory::getDBO();

		$query = parent::buildQuery();
		$query->select('t.submission_types');

		$upcoming_cond = '( ( CASE WHEN x.times THEN CONCAT(x.dates, " ", x.times) ELSE x.dates END > NOW() '
		. ' AND x.dates < DATE_ADD(NOW(), INTERVAL ' . $params->get('upcoming_days_ahead', 30) . ' DAY) ) ';

		if ($params->get('show_days_no_date', 0) == 1)
		{
			$upcoming_cond .= "       OR x.dates IS NULL ";
		}

		$upcoming_cond .= " ) ";

		$query->where($upcoming_cond);

		$db->setQuery($query, 0, $params->get('show_number_courses', 10));
		$rows = $db->loadObjectList();

		$rows = $this->_getPrices($rows);

		return $rows;
	}
}
