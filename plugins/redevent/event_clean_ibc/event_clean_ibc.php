<?php
/**
 * @package    Redevent.Plugin
 *
 * @copyright  Copyright (C) 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

// Import library dependencies
jimport('joomla.plugin.plugin');
jimport('redcore.library');

/**
 * Specific plugin for redEVENT.
 *
 * @package  Redevent.Plugin
 * @since    3.0
 */
class PlgRedeventEvent_Clean_Ibc extends JPlugin
{
	/**
	 * Ensures that events are assigned an open date session when the last session is removed
	 *
	 * @param   array  $xrefs  Affected database objects
	 *
	 * @return bool true on success
	 */
	public function onEventCleanArchived($xrefs)
	{
		$events = array();

		foreach ($xrefs as $xref)
		{
			$events[$xref->eventid][] = $xref->id;
		}

		$db = JFactory::getDBO();
		$query = $db->getQuery(true)
			->select('e.id')
			->from('#__redevent_events AS e')
			->join('LEFT', '#__redevent_event_venue_xref AS x ON x.eventid = e.id AND x.published <> -1')
			->where('x.id IS NULL')
			->where('e.id IN (' . implode(', ', array_keys($events)) . ')');
		$db->setQuery($query);
		$db->execute();
		$empty_events = $db->loadObjectList();

		$table = RTable::getAdminInstance('Session', array('ignore_request' => true), 'com_redevent');

		foreach ($empty_events as $empty_event)
		{
			$newest_session = max($events[$empty_event->id]);
			$sessionEntity = RedeventEntitySession::load($newest_session);
			$priceGroups = $sessionEntity->getPricegroups();
			$table->load($newest_session);
			$table->set('id', null);
			$table->set('dates', null);
			$table->set('enddates', null);
			$table->set('times', null);
			$table->set('endtimes', null);
			$table->set('registrationend', null);
			$table->set('published', 1);
			$table->store();
			$table->setPrices($priceGroups);
		}
	}
}
