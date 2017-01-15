<?php
/**
 * @package     Redevent.Frontend
 * @subpackage  Plugins
 *
 * @copyright   Copyright (C) 2008 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

// No direct access
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
 * Class PlgSystemAesirsessionssync
 *
 * @since  3.2.0
 */
class PlgSystemAesirsessionssync extends JPlugin
{
	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 */
	protected $autoloadLanguage = true;

	/**
	 * Intercepts task sessions.aesirsync
	 *
	 * @return void
	 */
	public function onAfterRoute()
	{
		$app = JFactory::getApplication();
		$input = $app->input;

		if (!($input->get('option') == 'com_redevent' && $input->get('task') == 'sessions.aesirsync'))
		{
			return;
		}

		// Check for request forgeries
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

		$msg = null;

		// Get items to publish from the request.
		$cid = JFactory::getApplication()->input->get('cid', array(), 'array');

		if (empty($cid))
		{
			JLog::add(JText::_('COM_REDEVENT_NO_ITEM_SELECTED'), JLog::WARNING, 'jerror');
		}
		else
		{
			foreach ($cid as $sessionId)
			{
				$session = RedeventEntitySession::load($sessionId);
				$this->syncSession($session);
			}

			$msg = JText::sprintf('PLG_AESIRSESSIONSSYNC_D_SESSIONS_SYNCED', count($cid));
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
		if ('com_redevent.session' !== $context)
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
		if (!$view instanceof RedeventViewSessions)
		{
			return;
		}

		if (JFactory::getUser()->authorise('core.create', 'com_redevent'))
		{
			$group = new RToolbarButtonGroup;
			$sync = RToolbarBuilder::createStandardButton(
				'sessions.aesirsync',
				JText::_('PLG_AESIRSESSIONSSYNC_SYNC_BUTTON_LABEL'), '', 'icon-refresh', true
			);
			$group->addButton($sync);

			$toolbar->addGroup($group);
		}
	}

	/**
	 * Sync a session
	 *
	 * @param   RedeventEntitySession  $session  session to sync
	 *
	 * @return void
	 */
	private function syncSession($session)
	{
		$item = $this->getAesirSessionItem($session->id);

		if (!$item->isValid())
		{
			$data = array(
				'type_id' => $this->params->get('aesir_session_type_id'),
				'template_id' => $this->params->get('aesir_session_template_id'),
				'title'   => JText::sprintf(
					'PLG_AESIRSESSIONSSYNC_ITEM_SESSION_TITLE_FORMAT',
					$session->getEvent()->title,
					$session->getVenue()->name,
					$session->getFormattedStartDate()
				),
				'access'  => 1,
				'custom_fields' => array(
					'select_redevent_session' => $session->id
				)
			);

			$eventItem = $this->getAesirEventItem($session->eventid);

			if ($eventItem->isValid())
			{
				$data['params'] = array(
					"related_items" => array($eventItem->getId())
				);
			}

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
				$relatedItems[] = $sessionItemId;
			}

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
			$db->execute();
		}
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
		$query = $db->getQuery(true)
			->select('s.*')
			->from('#__reditem_types_session_2 AS s')
			->join('INNER', '#__reditem_items AS i ON i.id = s.id')
			->where('s.select_redevent_session = ' . $sessionId);

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
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('c.*')
			->from('#__reditem_types_course_1 AS c')
			->join('INNER', '#__reditem_items AS i ON i.id = c.id')
			->where('c.select_redevent_event = ' . $eventId);

		$db->setQuery($query);

		if ($res = $db->loadObject())
		{
			$entity = ReditemEntityItem::getInstance($res->id);
		}
		else
		{
			$entity = ReditemEntityItem::getInstance();
		}

		return $entity;
	}
}
