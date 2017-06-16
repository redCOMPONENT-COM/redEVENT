<?php
/**
 * @package     Redevent
 * @subpackage  Plugins
 *
 * @copyright   Copyright (C) 2008 - 2017 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Class PlgSystemAesir_Redevent_SyncSyncEvents
 *
 * @since  3.2.3
 */
class PlgSystemAesir_Redevent_SyncSyncEvents
{
	static private $aesirEvents;

	/**
	 * Sync sessions
	 *
	 * @return void
	 */
	public function eventsSync()
	{
		$app = JFactory::getApplication();
		$msg = null;

		// Get items to publish from the request.
		$cid = JFactory::getApplication()->input->get('cid', array(), 'array');
		$synced = 0;

		if (empty($cid))
		{
			return $this->globalSync();
		}
		else
		{
			// Check for request forgeries
			JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

			foreach ($cid as $eventId)
			{
				$event = RedeventEntityEvent::load($eventId);

				if ($this->syncEvent($event))
				{
					$synced++;
				}
			}

			$msg = JText::sprintf('PLG_AESIR_REDEVENT_SYNC_D_EVENTS_SYNCED', $synced);
		}

		$app->redirect('index.php?option=com_redevent&view=events', $msg);
	}

	/**
	 * Sync an event
	 *
	 * @param   RedeventEntityEvent  $event  event to sync
	 *
	 * @return true on success
	 */
	public function syncEvent(RedeventEntityEvent $event)
	{
		$item = $this->getAesirEventItem($event->id);

		if (!$item->isValid())
		{
			if (!$access = RedeventHelperConfig::get('aesir_event_access'))
			{
				throw new LogicException('Event default access is not set in config plugin');
			}

			$title = RdfLayoutHelper::render(
				'aesir_redevent_sync.event.title',
				compact('event'),
				null,
				array('component' => 'com_redform', 'defaultLayoutsPath' => PLGSYSTEMAESIR_REDEVENT_SYNC_LAYOUTS)
			);

			$data = array(
				'type_id' => RedeventHelperConfig::get('aesir_event_type_id'),
				'template_id' => RedeventHelperConfig::get('aesir_event_template_id'),
				'title'   => $title,
				'access'  => RedeventHelperConfig::get('aesir_event_access'),
				'custom_fields' => array(
					$this->getEventSelectField()->fieldcode => $event->id
				)
			);

			$categories = array();
			$eventCategories = $event->getCategories();
			$categoryHelper = new PlgSystemAesir_Redevent_SyncSyncCategories;

			foreach ($eventCategories as $eventCategory)
			{
				if ($category = $categoryHelper->getAesirCategory($eventCategory->id))
				{
					$categories[] = $category->id;
				}
			}

			// TODO: remove this workaround when aesir code gets fixed
			$jform = JFactory::getApplication()->input->get('jform', null, 'array');
			$jform['access'] = RedeventHelperConfig::get('aesir_session_access');
			$jform['categories'] = $categories;
			JFactory::getApplication()->input->set('jform', $jform);

			$item->save($data);
		}

		return true;
	}

	/**
	 * Enqueue list of redEVENT events not having a corresponding aesir item
	 *
	 * @return void
	 *
	 * @since 3.2.3
	 */
	public function listMissingEventsItems()
	{
		$app = JFactory::getApplication();

		if (!$events = $this->getMissingEventItems())
		{
			$app->enqueueMessage(JText::_('PLG_AESIR_REDEVENT_SYNC_NO_MISSING_EVENT_ITEMS'), 'success');
		}
		else
		{
			$app->enqueueMessage(JText::sprintf('PLG_AESIR_REDEVENT_SYNC_D_MISSING_EVENT_ITEMS', count($events)), 'warning');

			foreach ($events as $event)
			{
				$app->enqueueMessage($event->title, 'warning');
			}
		}

		$app->redirect('index.php?option=com_redevent&view=events');
	}

	/**
	 * Get aesir item session
	 *
	 * @param   int  $eventId  redEVENT event id
	 *
	 * @return ReditemEntityItem
	 */
	public function getAesirEventItem($eventId)
	{
		if (!isset(self::$aesirEvents[$eventId]))
		{
			$db = JFactory::getDbo();

			$eventType = $this->getEventType();
			$eventItemTable = $db->qn('#__reditem_types_' . $eventType->table_name, 'c');
			$eventSelectField = $db->qn('c.' . $this->getEventSelectField()->fieldcode);

			$query = $db->getQuery(true)
				->select('c.id')
				->from($eventItemTable)
				->join('INNER', '#__reditem_items AS i ON i.id = c.id')
				->where($eventSelectField . ' = ' . $eventId);

			$db->setQuery($query);

			$res = $db->loadResult();

			self::$aesirEvents[$eventId] = $res ?: false;
		}

		return ReditemEntityItem::getInstance(self::$aesirEvents[$eventId] ?: null);
	}

	/**
	 * Sync all
	 *
	 * @return void
	 */
	private function globalSync()
	{
		// Check for request forgeries
		JSession::checkToken() or JSession::checkToken('get') or die(JText::_('JINVALID_TOKEN'));

		$app   = JFactory::getApplication();
		$input = $app->input;

		$synced     = $input->getInt('synced', 0);
		$limitstart = $input->getInt('limitstart', 0);

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('e.*')
			->from('#__redevent_events AS e')
			->where('e.published = 1')
			->order('e.id DESC');

		$db->setQuery($query, $limitstart, 5);

		if (!$unsynced = $db->loadObjectList())
		{
			$app->enqueueMessage(sprintf('Done, %d events synced', $synced));
			$app->redirect('index.php?option=com_redevent&view=events');
		}

		$instances = RedeventEntityEvent::loadArray($unsynced);

		foreach ($instances as $instance)
		{
			if ($this->syncEvent($instance))
			{
				$synced++;
			}
		}

		echo JText::sprintf('PLG_AESIR_REDEVENT_SYNC_D_EVENTS_SYNCED', $synced);

		$next = 'index.php?option=com_redevent&task=events.aesirsync'
			. '&synced=' . $synced . '&limitstart=' . ($limitstart + 5)
			. '&' . JSession::getFormToken() . '=1'
			. '&rand=' . uniqid();

		JFactory::getDocument()->addScriptDeclaration('window.location = "' . $next . '";');
	}

	/**
	 * Get event type entity
	 *
	 * @return ReditemEntityType
	 *
	 * @since 3.2.3
	 */
	private function getEventSelectField()
	{
		if (is_null($this->eventSelectField))
		{
			$id = RedeventHelperConfig::get('aesir_event_select_field');
			$field = ReditemEntityField::load($id);

			if (!$field->isValid())
			{
				throw new LogicException('Event select field is not selected');
			}

			$this->eventSelectField = $field;
		}

		return $this->eventSelectField;
	}

	/**
	 * Get event type entity
	 *
	 * @return ReditemEntityType
	 *
	 * @since 3.2.3
	 */
	private function getEventType()
	{
		if (is_null($this->eventType))
		{
			$typeId = RedeventHelperConfig::get('aesir_event_type_id');
			$type = ReditemEntityType::load($typeId);

			if (!$type->isValid())
			{
				throw new LogicException('Event type is not selected');
			}

			$this->eventType = $type;
		}

		return $this->eventType;
	}

	/**
	 * Return redEVENT events not having a corresponding aesir item
	 *
	 * @return RedeventEntityEvent[]
	 *
	 * @since 3.2.3
	 */
	private function getMissingEventItems()
	{
		$eventType = $this->getEventType();
		$eventSelect = $this->getEventSelectField();

		$db = JFactory::getDbo();

		$eventItemTable = $db->qn('#__reditem_types_' . $eventType->table_name, 'c');
		$eventSelectName = $db->qn('c.' . $eventSelect->fieldcode);

		$query = $db->getQuery(true)
			->select('e.*')
			->from('#__redevent_events AS e')
			->innerJoin('#__redevent_event_venue_xref AS x ON e.id = x.eventid')
			->leftJoin($eventItemTable . ' ON ' . $eventSelectName . ' = e.id')
			->where('c.id IS NULL')
			->where('e.published = 1')
			->where('x.published = 1')
			->group('e.id')
			->order('e.title ASC');

		$db->setQuery($query);

		if (!$res = $db->loadObjectList())
		{
			return false;
		}

		return RedeventEntityEvent::loadArray($res);
	}
}
