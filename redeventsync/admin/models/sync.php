<?php
/**
 * @package     Redcomponent.redeventsync
 * @subpackage  com_redeventsync
 * @copyright   Copyright (C) 2013 redCOMPONENT.com
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die();

class RedeventsyncModelSync extends FOFModel
{
	/**
	 * return sessions between two dates
	 *
	 * @param   string  $from  from date
	 * @param   string  $to    to date
	 *
	 * @return array
	 */
	public function getSessions($from, $to)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$fromDate = JDate::getInstance($from);
		$toDate = JDate::getInstance($to);

		$query->select('x.*, v.venue_code');
		$query->from('#__redevent_event_venue_xref AS x');
		$query->join('INNER', '#__redevent_venues AS v ON v.id = x.venueid');
		$query->where('x.dates BETWEEN ' . $db->quote($fromDate->toSql()) . ' AND ' . $db->quote($toDate->toSql()));

		$db->setQuery($query);
		$sessions = $db->loadObjectList();

		return $sessions;
	}
}
