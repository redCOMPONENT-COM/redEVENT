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

// Register library prefix
RLoader::registerPrefix('PlgSystemAesir_Redevent_Sync', __DIR__ . '/lib');

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

		switch ($task)
		{
			case 'sessions.aesirsync':
				$helper = new PlgSystemAesir_Redevent_SyncSyncSessions;

				return $helper->sessionsSync();

			case 'events.missingeventsitems':
				$helper = new PlgSystemAesir_Redevent_SyncSyncEvents;

				return $helper->listMissingEventsItems();

			case 'events.aesirsync':
				$helper = new PlgSystemAesir_Redevent_SyncSyncEvents;

				return $helper->eventsSync();

			case 'categories.aesirsync':
				$helper = new PlgSystemAesir_Redevent_SyncSyncCategories;

				return $helper->categoriesSync();

			case 'venues.aesirsync':
				$helper = new PlgSystemAesir_Redevent_SyncSyncVenues;

				return $helper->venuesSync();
		}
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
		try
		{
			if ($table instanceof RedeventTableSession && $this->params->get('sync_sessions'))
			{
				$session = RedeventEntitySession::getInstance($table->id)->bind($table);
				$helper = new PlgSystemAesir_Redevent_SyncSyncSessions;
				$helper->syncSession($session);
			}
			elseif ($table instanceof RedeventTableEvent && $this->params->get('sync_events'))
			{
				$event = RedeventEntityEvent::getInstance($table->id)->bind($table);
				$helper = new PlgSystemAesir_Redevent_SyncSyncEvents;
				$helper->syncEvent($event);
			}
			elseif ($table instanceof RedeventTableCategory && $this->params->get('sync_categories'))
			{
				$category = RedeventEntityCategory::getInstance($table->id)->bind($table);
				$helper = new PlgSystemAesir_Redevent_SyncSyncCategories;
				$helper->syncCategory($category);
			}
			elseif ($table instanceof RedeventTableVenue && $this->params->get('sync_venues'))
			{
				$venue = RedeventEntityVenue::getInstance($table->id)->bind($table);
				$helper = new PlgSystemAesir_Redevent_SyncSyncVenues;
				$helper->syncVenue($venue);
			}
		}
		catch (Exception $e)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('PLG_AESIR_REDEVENT_SYNC_ERROR_SYNC') . $e->getMessage(), 'warning');
		}
	}

	/**
	 * Override toolbar
	 *
	 * @param   RedeventViewAdmin  $view     the view object
	 * @param   RToolbar           $toolbar  the toolbar
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
				$missing = RToolbarBuilder::createStandardButton(
					'events.missingeventsitems',
					JText::_('PLG_AESIR_REDEVENT_SYNC_MISSINGEVENTSITEMS_BUTTON_LABEL'), '', 'icon-refresh', false
				);
				$group->addButton($missing);

				$sync = RToolbarBuilder::createStandardButton(
					'events.aesirsync',
					JText::_('PLG_AESIR_REDEVENT_SYNC_SYNC_BUTTON_LABEL'), '', 'icon-refresh', false
				);
				$group->addButton($sync);

				$toolbar->addGroup($group);
			}
		}
		elseif ($view instanceof RedeventViewCategories)
		{
			if (JFactory::getUser()->authorise('core.manage', 'com_redevent'))
			{
				$group = new RToolbarButtonGroup;
				$sync = RToolbarBuilder::createStandardButton(
					'categories.aesirsync',
					JText::_('PLG_AESIR_REDEVENT_SYNC_SYNC_BUTTON_LABEL'), '', 'icon-refresh', false
				);
				$group->addButton($sync);

				$toolbar->addGroup($group);
			}
		}
		elseif ($view instanceof RedeventViewVenues)
		{
			if (JFactory::getUser()->authorise('core.manage', 'com_redevent'))
			{
				$group = new RToolbarButtonGroup;
				$sync = RToolbarBuilder::createStandardButton(
					'venues.aesirsync',
					JText::_('PLG_AESIR_REDEVENT_SYNC_SYNC_BUTTON_LABEL'), '', 'icon-refresh', false
				);
				$group->addButton($sync);

				$toolbar->addGroup($group);
			}
		}
	}
}
