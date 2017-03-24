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
 * Class PlgSystemAesir_Redevent_SyncSyncVenues
 *
 * @since  3.2.3
 */
class PlgSystemAesir_Redevent_SyncSyncVenues
{
	/**
	 * Sync categories
	 *
	 * @return void
	 */
	public function venuesSync()
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

			foreach ($cid as $id)
			{
				$entity = RedeventEntityVenue::load($id);

				if ($this->syncVenue($entity))
				{
					$synced++;
				}
			}

			$msg = JText::sprintf('PLG_AESIR_REDEVENT_SYNC_D_VENUES_SYNCED', $synced);
		}

		$app->redirect('index.php?option=com_redevent&view=venues', $msg);
	}

	/**
	 * Sync a venue
	 *
	 * @param   RedeventEntityVenue  $venue  venue to sync
	 *
	 * @return true on success
	 */
	public function syncVenue(RedeventEntityVenue $venue)
	{
		$item = $this->getAesirVenueItem($venue->id);

		if (!$item->isValid())
		{
			if (!$access = RedeventHelperConfig::get('aesir_venue_access'))
			{
				throw new LogicException('Venue default access is not set in config plugin');
			}

			$data = array(
				'type_id' => RedeventHelperConfig::get('aesir_venue_type_id'),
				'template_id' => RedeventHelperConfig::get('aesir_venue_template_id'),
				'title'   => $venue->name,
				'access'  => RedeventHelperConfig::get('aesir_venue_access'),
				'custom_fields' => array(
					$this->getVenueSelectField()->fieldcode => $venue->id
				)
			);

			// TODO: remove this workaround when aesir code gets fixed
			$jform = JFactory::getApplication()->input->get('jform', null, 'array');
			$jform['access'] = RedeventHelperConfig::get('aesir_venue_access');
			$jform['categories'] = false;
			JFactory::getApplication()->input->set('jform', $jform);

			$item->save($data);
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
			->select('c.*')
			->from('#__redevent_venues AS c')
			->where('c.published = 1')
			->order('c.id DESC');

		$db->setQuery($query, $limitstart, 5);

		if (!$unsynced = $db->loadObjectList())
		{
			$app->enqueueMessage(sprintf('Done, %d venues synced', $synced));
			$app->redirect('index.php?option=com_redevent&view=venues');
		}

		$instances = RedeventEntityVenue::loadArray($unsynced);

		foreach ($instances as $instance)
		{
			if ($this->syncVenue($instance))
			{
				$synced++;
			}
		}

		echo JText::sprintf('PLG_AESIR_REDEVENT_SYNC_D_venues_SYNCED', $synced);

		$next = 'index.php?option=com_redevent&task=venues.aesirsync'
			. '&synced=' . $synced . '&limitstart=' . ($limitstart + 5)
			. '&' . JSession::getFormToken() . '=1' . '&rand=' . uniqid();

		JFactory::getDocument()->addScriptDeclaration('window.location = "' . $next . '";');
	}

	/**
	 * Get aesir item venue
	 *
	 * @param   int  $venueId  redEVENT venue id
	 *
	 * @return ReditemEntityItem
	 */
	private function getAesirVenueItem($venueId)
	{
		if (!isset($this->aesirVenues[$venueId]))
		{
			$db = JFactory::getDbo();

			$venueType = $this->getVenueType();
			$venueItemTable = $db->qn('#__reditem_types_' . $venueType->table_name, 'c');
			$venueSelectField = $db->qn('c.' . $this->getVenueSelectField()->fieldcode);

			$query = $db->getQuery(true)
				->select('c.id')
				->from($venueItemTable)
				->join('INNER', '#__reditem_items AS i ON i.id = c.id')
				->where($venueSelectField . ' = ' . $venueId);

			$db->setQuery($query);
			$res = $db->loadResult();

			$this->aesirVenues[$venueId] = $res ?: false;
		}

		return ReditemEntityItem::getInstance($this->aesirVenues[$venueId] ?: null);
	}

	/**
	 * Get venue type entity
	 *
	 * @return ReditemEntityType
	 *
	 * @since 3.2.3
	 */
	private function getVenueType()
	{
		if (is_null($this->venueType))
		{
			$typeId = RedeventHelperConfig::get('aesir_venue_type_id');
			$type = ReditemEntityType::load($typeId);

			if (!$type->isValid())
			{
				throw new LogicException('venue type is not selected');
			}

			$this->venueType = $type;
		}

		return $this->venueType;
	}

	/**
	 * Get venue select field
	 *
	 * @return ReditemEntityField
	 *
	 * @since 3.2.3
	 */
	private function getVenueSelectField()
	{
		if (is_null($this->venueSelectField))
		{
			$id = RedeventHelperConfig::get('aesir_venue_select_field');
			$field = ReditemEntityField::load($id);

			if (!$field->isValid())
			{
				throw new LogicException('venue select field is not selected');
			}

			$this->venueSelectField = $field;
		}

		return $this->venueSelectField;
	}
}
