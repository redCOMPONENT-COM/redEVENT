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
 * Class PlgSystemAesir_Redevent_SyncSyncSessions
 *
 * @since  3.2.3
 */
class PlgSystemAesir_Redevent_SyncSyncSessions
{
	/**
	 * Sync sessions
	 *
	 * @return void
	 */
	public function sessionsSync()
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

			foreach ($cid as $sessionId)
			{
				$session = RedeventEntitySession::load($sessionId);

				if ($this->syncSession($session))
				{
					$synced++;
				}
			}

			$msg = JText::sprintf('PLG_AESIR_REDEVENT_SYNC_D_SESSIONS_SYNCED', $synced);
		}

		$app->redirect('index.php?option=com_redevent&view=sessions', $msg);
	}

	/**
	 * Sync a session
	 *
	 * @param   RedeventEntitySession  $session  session to sync
	 *
	 * @return true on success
	 */
	public function syncSession($session)
	{
		$item = $this->getAesirSessionItem($session->id);
		$sessionSelectField = $this->getSessionSelectField();

		$eventHelper = new PlgSystemAesir_Redevent_SyncSyncEvents;
		$eventItem = $eventHelper->getAesirEventItem($session->eventid);

		if (!$eventItem->isValid())
		{
			$event = RedeventEntityEvent::load($session->eventid);
			JFactory::getApplication()->enqueueMessage('Aesir item not found for event ' . $event->title, 'warning');

			return false;
		}

		if (!$item->isValid())
		{
			if (!$access = RedeventHelperConfig::get('aesir_session_access'))
			{
				throw new LogicException('Session default access is not set in config plugin');
			}

			$title = RdfLayoutHelper::render(
				'aesir_redevent_sync.session.title',
				compact('session'),
				null,
				array('component' => 'com_redform', 'defaultLayoutsPath' => PLGSYSTEMAESIR_REDEVENT_SYNC_LAYOUTS)
			);

			$data = array(
				'type_id' => RedeventHelperConfig::get('aesir_session_type_id'),
				'template_id' => RedeventHelperConfig::get('aesir_session_template_id'),
				'title'   => $title,
				'access'  => 1,
				'organisation_id' => $this->getOrganisationId($session->getEvent()),
				'custom_fields' => array(
					$sessionSelectField->fieldcode => $session->id
				)
			);

			$data['params'] = array(
				"related_items" => array($eventItem->getId())
			);

			// TODO: remove this workaround when aesir code gets fixed
			$jform = JFactory::getApplication()->input->get('jform', null, 'array');
			$jform['access'] = RedeventHelperConfig::get('aesir_session_access');
			JFactory::getApplication()->input->set('jform', $jform);

			$sessionItemId = $item->save($data);
		}
		else
		{
			$sessionItemId = $item->getId();
		}

		// Add to event item related items
		$params = new \Joomla\Registry\Registry($eventItem->params);
		$relatedItems = $params->get('related_items', array());

		if ($relatedItems && !is_array($relatedItems))
		{
			$relatedItems = array($relatedItems);
		}

		if (!in_array($sessionItemId, $relatedItems))
		{
			echo "<p>Adding $session->id $session->dates / $sessionItemId</p>";
			$relatedItems[] = $sessionItemId;

			$params->set('related_items', $relatedItems);

			/*
			 * This doesn't work because current redITEM pulls input data from the table file, so we have to use manual sql update
			 * $eventItem->params = $params->toString();
			 * $eventItem->save();
			 *
			 * @todo: wait for aesir fix !
			 */
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true)
				->update('#__reditem_items')
				->set('params = ' . $db->quote($params->toString()))
				->where('id = ' . $eventItem->getItemId());

			$db->setQuery($query);

			if (!$db->execute())
			{
				echo $db->getErrorMsg();

				exit();
			}

			ReditemEntityItem::clearInstance($eventItem->id);
		}

		return true;
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
			->select('x.*')
			->from('#__redevent_event_venue_xref AS x')
			->innerJoin('#__redevent_events AS e On e.id = x.eventid')
			->where('x.published = 1')
			->where('e.published = 1')
			->order('x.id DESC');

		$db->setQuery($query, $limitstart, 5);

		if (!$unsynced = $db->loadObjectList())
		{
			$app->enqueueMessage(sprintf('Done, %d sessions synced', $synced));
			$app->redirect('index.php?option=com_redevent&view=sessions');
		}

		$sessions = RedeventEntitySession::loadArray($unsynced);

		foreach ($sessions as $session)
		{
			if ($this->syncSession($session))
			{
				$synced++;
			}
		}

		echo JText::sprintf('PLG_AESIR_REDEVENT_SYNC_D_SESSIONS_SYNCED', $synced);

		$next = 'index.php?option=com_redevent&task=sessions.aesirsync'
			. '&synced=' . $synced . '&limitstart=' . ($limitstart + 5)
			. '&' . JSession::getFormToken() . '=1'
			. '&rand=' . uniqid();

		JFactory::getDocument()->addScriptDeclaration('window.location = "' . $next . '";');
	}

	/**
	 * Get aesir item session
	 *
	 * @param   int  $sessionId  redEVENT session id
	 *
	 * @return ReditemEntityItem
	 */
	public function getAesirSessionItem($sessionId)
	{
		$db = JFactory::getDbo();

		$sessionTableName = $db->qn('#__reditem_types_' . $this->getSessionType()->table_name, 's');
		$sessionSelectField = $db->qn('s.' . $this->getSessionSelectField()->fieldcode);

		$query = $db->getQuery(true)
			->select('s.id')
			->from($sessionTableName)
			->join('INNER', '#__reditem_items AS i ON i.id = s.id')
			->where($sessionSelectField . ' = ' . $sessionId);

		$db->setQuery($query);
		$res = $db->loadResult();

		return ReditemEntityItem::getInstance($res ?: null);
	}

	/**
	 * Get session type entity
	 *
	 * @return ReditemEntityType
	 *
	 * @since 3.2.3
	 */
	private function getSessionType()
	{
		if (is_null($this->sessionType))
		{
			$typeId = RedeventHelperConfig::get('aesir_session_type_id');
			$type = ReditemEntityType::load($typeId);

			if (!$type->isValid())
			{
				throw new LogicException('Session type is not selected');
			}

			$this->sessionType = $type;
		}

		return $this->sessionType;
	}

	/**
	 * Get session type entity
	 *
	 * @return ReditemEntityField
	 *
	 * @since 3.2.3
	 */
	private function getSessionSelectField()
	{
		if (is_null($this->sessionSelectField))
		{
			$id = RedeventHelperConfig::get('aesir_session_select_field');
			$field = ReditemEntityField::load($id);

			if (!$field->isValid())
			{
				throw new LogicException('Session select field is not selected');
			}

			$this->sessionSelectField = $field;
		}

		return $this->sessionSelectField;
	}

	/**
	 * Get associated organisation
	 *
	 * @param   RedeventEntityEvent  $event  event
	 *
	 * @return string
	 *
	 * @since  __deploy_version__
	 */
	private function getOrganisationId(RedeventEntityEvent $event)
	{
		if (!$customFieldId = RedeventHelperConfig::get('event_organisation_field'))
		{
			return false;
		}

		$prop = 'custom' . $customFieldId;

		return empty($event->$prop) ? false : $event->$prop;
	}
}
