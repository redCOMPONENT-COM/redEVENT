<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * RedEvent Model Bundle
 *
 * @package  Redevent.admin
 * @since    3.2.0
 */
class RedeventModelBundle extends RModelAdmin
{
	/**
	 * Save bundle events (and optional sessions)
	 *
	 * @param   int       $bundle_id  bundle id
	 * @param   Object[]  $events     eventd to add
	 *
	 * @return void
	 */
	public function saveEvents($bundle_id, $events)
	{
		// First purge current records
		$query = $this->_db->getQuery(true)
			->delete('#__redevent_bundle_event')
			->where('bundle_id = ' . $bundle_id);

		$this->_db->setQuery($query);
		$this->_db->execute();

		// Then add new
		foreach ($events as $event)
		{
			$all_dates = empty($event->sessions) ? 1 : 0;

			$query = $this->_db->getQuery(true)
				->insert('#__redevent_bundle_event')
				->columns('bundle_id, event_id, all_dates')
				->values($bundle_id . ', ' . $event->id . ', ' . $all_dates);

			$this->_db->setQuery($query);
			$this->_db->execute();

			$bundleEventId = $this->_db->insertid();

			if ($all_dates)
			{
				continue;
			}

			$query = $this->_db->getQuery(true)
				->insert('#__redevent_bundle_event_session')
				->columns('bundle_event_id, session_id');

			foreach ($event->sessions as $session_id)
			{
				$query->values($bundleEventId . ', ' . $session_id);
			}

			$this->_db->setQuery($query);
			$this->_db->execute();
		}
	}
}
