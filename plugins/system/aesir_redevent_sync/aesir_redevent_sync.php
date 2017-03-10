<?php
/**
 * @package     Redevent
 * @subpackage  Plugins
 *
 * @copyright   Copyright (C) 2008 - 2017 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

// Load redEVENT library
$redeventLoader = JPATH_LIBRARIES . '/redevent/bootstrap.php';

if (!file_exists($redeventLoader))
{
	throw new Exception(JText::_('COM_REDEVENT_INIT_FAILED'), 404);
}

include_once $redeventLoader;

RedeventBootstrap::bootstrap();

// Import library dependencies
jimport('joomla.plugin.plugin');

// Import Aesir library
JLoader::import('reditem.library');

/**
 * Class PlgSystemAesir_redevent_sync
 *
 * @since  3.2.3
 */
class PlgSystemAesir_Redevent_Sync extends JPlugin
{
	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 */
	protected $autoloadLanguage = true;

	/**
	 * @var ReditemEntityItem[]
	 */
	private $aesirEvents;

	/**
	 * @var ReditemEntityType
	 * @since 3.2.3
	 */
	private $eventType;

	/**
	 * @var ReditemEntityField
	 * @since 3.2.3
	 */
	private $eventSelectField;

	/**
	 * @var ReditemEntityType
	 * @since 3.2.3
	 */
	private $sessionType;

	/**
	 * @var ReditemEntityField
	 * @since 3.2.3
	 */
	private $sessionSelectField;

	/**
	 * Constructor
	 *
	 * @param   object  $subject  The object to observe
	 * @param   array   $config   An optional associative array of configuration settings.
	 *                            Recognized key values include 'name', 'group', 'params', 'language'
	 *                            (this list is not meant to be comprehensive).
	 *
	 * @since   1.5
	 */
	public function __construct($subject, array $config = array())
	{
		parent::__construct($subject, $config);

		$this->aesirEvents = array();
	}

	/**
	 * Intercepts task sessions.aesirsync
	 *
	 * @return void
	 */
	public function onAfterRoute()
	{
		$app = JFactory::getApplication();
		$input = $app->input;

		$task = $input->get('task');

		if ($input->get('option') !== 'com_redevent')
		{
			return;
		}

		if ($task == 'sessions.aesirsync')
		{
			return $this->sessionsSync();
		}
		elseif ($task == 'events.missingeventsitems')
		{
			return $this->listMissingEventsItems();
		}

		return;
	}

	/**
	 * Sync sessions
	 *
	 * @return void
	 */
	private function sessionsSync()
	{
		$app = JFactory::getApplication();
		$msg = null;

		// Get items to publish from the request.
		$cid = JFactory::getApplication()->input->get('cid', array(), 'array');
		$synced = 0;

		if (empty($cid))
		{
			return $this->globalsync();
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
	 * Sync session to aesir after save
	 *
	 * @param   string  $context  context
	 * @param   object  $table    table data
	 * @param   bool    $isNew    is new
	 *
	 * @return void
	 */
	public function onContentAfterSave($context, $table, $isNew)
	{
		if (!$table instanceof RedeventTableSession)
		{
			return;
		}

		$session = RedeventEntitySession::getInstance($table->id)->bind($table);

		$this->syncSession($session);
	}

	/**
	 * Override toolbar
	 *
	 * @param   RedeventViewAdmin  $view      the view object
	 * @param   RToolbar           &$toolbar  the toolbar
	 *
	 * @return void
	 */
	public function onRedeventViewGetToolbar(RedeventViewAdmin $view, RToolbar &$toolbar)
	{
		if ($view instanceof RedeventViewSessions)
		{
			if (JFactory::getUser()->authorise('core.create', 'com_redevent'))
			{
				$group = new RToolbarButtonGroup;
				$sync = RToolbarBuilder::createStandardButton(
					'sessions.aesirsync',
					JText::_('PLG_AESIR_REDEVENT_SYNC_SYNC_BUTTON_LABEL'), '', 'icon-refresh', false
				);
				$group->addButton($sync);

				$toolbar->addGroup($group);
			}
		}
		elseif ($view instanceof RedeventViewEvents)
		{
			if (JFactory::getUser()->authorise('core.manage', 'com_redevent'))
			{
				$group = new RToolbarButtonGroup;
				$sync = RToolbarBuilder::createStandardButton(
					'events.missingeventsitems',
					JText::_('PLG_AESIR_REDEVENT_SYNC_MISSINGEVENTSITEMS_BUTTON_LABEL'), '', 'icon-refresh', false
				);
				$group->addButton($sync);

				$toolbar->addGroup($group);
			}
		}
	}

	/**
	 * Sync all
	 *
	 * @return void
	 */
	private function globalsync()
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
			. '&' . JSession::getFormToken() . '=1' . '&rand=' . uniqid();

		JFactory::getDocument()->addScriptDeclaration('window.location = "' . $next . '";');
	}

	/**
	 * Sync a session
	 *
	 * @param   RedeventEntitySession  $session  session to sync
	 *
	 * @return true on success
	 */
	private function syncSession($session)
	{
		$item = $this->getAesirSessionItem($session->id);
		$sessionSelectField = $this->getSessionSelectField();

		if (!$item->isValid())
		{
			$data = array(
				'type_id' => $this->params->get('aesir_session_type_id'),
				'template_id' => $this->params->get('aesir_session_template_id'),
				'title'   => JText::sprintf(
					'PLG_AESIR_REDEVENT_SYNC_ITEM_SESSION_TITLE_FORMAT',
					$session->getEvent()->title,
					$session->getVenue()->name,
					$session->getFormattedStartDate()
				),
				'access'  => 1,
				'custom_fields' => array(
					$sessionSelectField->fieldcode => $session->id
				)
			);

			$eventItem = $this->getAesirEventItem($session->eventid);

			if (!$eventItem->isValid())
			{
				return false;
			}

			$data['params'] = array(
				"related_items" => array($eventItem->getId())
			);

			// TODO: remove this workaround when aesir code gets fixed
			$jform = JFactory::getApplication()->input->get('jform', null, 'array');
			$jform['access'] = $this->params->get('session_access');
			JFactory::getApplication()->input->set('jform', $jform);

			$sessionItemId = $item->save($data);
		}
		else
		{
			$sessionItemId = $item->getId();
		}

		$eventItem = $this->getAesirEventItem($session->eventid);

		if ($eventItem->isValid())
		{
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
		}

		return true;
	}

	/**
	 * Get aesir item session
	 *
	 * @param   int  $sessionId  redEVENT session id
	 *
	 * @return ReditemEntityItem
	 */
	private function getAesirSessionItem($sessionId)
	{
		$db = JFactory::getDbo();

		$sessionTableName = $db->qn('#__reditem_types_' . $this->getSessionType()->table_name, 's');
		$sessionSelectField = $db->qn('s.' . $this->getSessionSelectField()->fieldcode);

		$query = $db->getQuery(true)
			->select('s.*')
			->from($sessionTableName)
			->join('INNER', '#__reditem_items AS i ON i.id = s.id')
			->where($sessionSelectField . ' = ' . $sessionId);

		$db->setQuery($query);

		if ($res = $db->loadObject())
		{
			$entity = ReditemEntityItem::getInstance($res->id)->bind($res);
		}
		else
		{
			$entity = ReditemEntityItem::getInstance();
		}

		return $entity;
	}

	/**
	 * Get aesir item session
	 *
	 * @param   int  $eventId  redEVENT event id
	 *
	 * @return ReditemEntityItem
	 */
	private function getAesirEventItem($eventId)
	{
		if (!isset($this->aesirEvents[$eventId]))
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

			if (!$res = $db->loadResult())
			{
				$event = RedeventEntityEvent::load($eventId);
				JFactory::getApplication()->enqueueMessage('Aesir item not found for event ' . $event->title, 'warning');
			}

			$this->aesirEvents[$eventId] = $res ?: false;
		}

		return ReditemEntityItem::getInstance($this->aesirEvents[$eventId] ?: null);
	}

	/**
	 * Enqueue list of redEVENT events not having a corresponding aesir item
	 *
	 * @return void
	 *
	 * @since 3.2.3
	 */
	private function listMissingEventsItems()
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
			$typeId = $this->params->get('aesir_event_type_id');
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
			$id = $this->params->get('aesir_event_select_field');
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
			$typeId = $this->params->get('aesir_session_type_id');
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
	 * @return ReditemEntityType
	 *
	 * @since 3.2.3
	 */
	private function getSessionSelectField()
	{
		if (is_null($this->sessionSelectField))
		{
			$id = $this->params->get('aesir_session_select_field');
			$field = ReditemEntityField::load($id);

			if (!$field->isValid())
			{
				throw new LogicException('Session select field is not selected');
			}

			$this->sessionSelectField = $field;
		}

		return $this->sessionSelectField;
	}
}
