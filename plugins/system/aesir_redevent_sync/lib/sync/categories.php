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
 * Class PlgSystemAesir_Redevent_SyncSyncCategories
 *
 * @since  3.2.3
 */
class PlgSystemAesir_Redevent_SyncSyncCategories
{
	private static $aesirCategories;

	/**
	 * Sync categories
	 *
	 * @return void
	 */
	public function categoriesSync()
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
				$entity = RedeventEntityCategory::load($id);

				if ($this->syncCategory($entity))
				{
					$synced++;
				}
			}

			$msg = JText::sprintf('PLG_AESIR_REDEVENT_SYNC_D_CATEGORIES_SYNCED', $synced);
		}

		$app->redirect('index.php?option=com_redevent&view=categories', $msg);
	}

	/**
	 * Sync a category
	 *
	 * @param   RedeventEntityCategory  $category  category to sync
	 *
	 * @return true on success
	 */
	public function syncCategory(RedeventEntityCategory $category)
	{
		$item = $this->getAesirCategory($category->id);

		if (!$item->isValid())
		{
			$data = array(
				'type_id' => RedeventHelperConfig::get('aesir_category_type_id'),
				'template_id' => RedeventHelperConfig::get('aesir_category_template_id'),
				'title'   => $category->name,
				'access'  => RedeventHelperConfig::get('aesir_category_access'),
				'parent_id' => RedeventHelperConfig::get('aesir_category_parent_id'),
				'custom_fields' => array(
					$this->getCategorySelectField()->fieldcode => $category->id
				)
			);

			// TODO: remove this workaround when aesir code gets fixed
			$jform = JFactory::getApplication()->input->get('jform', null, 'array');
			$jform['access'] = RedeventHelperConfig::get('aesir_category_access');
			JFactory::getApplication()->input->set('jform', $jform);

			$model = RModel::getAdminInstance('Category', array('ignore_request' => true), 'com_reditem');

			if (!$model->save($data))
			{
				throw new LogicException($model->getError());
			}
		}

		return true;
	}

	/**
	 * Get aesir category
	 *
	 * @param   int  $redeventCategoryId  redEVENT category id
	 *
	 * @return ReditemEntityCategory
	 */
	public function getAesirCategory($redeventCategoryId)
	{
		if (!isset(self::$aesirCategories[$redeventCategoryId]))
		{
			$types = $this->getCategoryTypes();

			foreach ($types as $type)
			{
				if ($category = $this->findCategoryInType($type, $redeventCategoryId))
				{
					$this->aesirCategories[$redeventCategoryId] = $category;

					return $category;
				}
			}

			self::$aesirCategories[$redeventCategoryId] = ReditemEntityCategory::getInstance();
		}

		return self::$aesirCategories[$redeventCategoryId];
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
			->from('#__redevent_categories AS c')
			->where('c.published = 1')
			->order('c.id DESC');

		$db->setQuery($query, $limitstart, 5);

		if (!$unsynced = $db->loadObjectList())
		{
			$app->enqueueMessage(sprintf('Done, %d categories synced', $synced));
			$app->redirect('index.php?option=com_redevent&view=categories');
		}

		$instances = RedeventEntityCategory::loadArray($unsynced);

		foreach ($instances as $instance)
		{
			if ($this->syncCategory($instance))
			{
				$synced++;
			}
		}

		echo JText::sprintf('PLG_AESIR_REDEVENT_SYNC_D_CATEGORIES_SYNCED', $synced);

		$next = 'index.php?option=com_redevent&task=categories.aesirsync'
			. '&synced=' . $synced . '&limitstart=' . ($limitstart + 5)
			. '&' . JSession::getFormToken() . '=1' . '&rand=' . uniqid();

		JFactory::getDocument()->addScriptDeclaration('window.location = "' . $next . '";');
	}

	/**
	 * Try to find category from category types tables
	 *
	 * @param   ReditemEntityType  $type                category type
	 * @param   int                $redeventCategoryId  redevent category id
	 *
	 * @return ReditemEntityCategory
	 *
	 * @since 3.2.3
	 */
	private function findCategoryInType(ReditemEntityType $type, $redeventCategoryId)
	{
		$db    = JFactory::getDbo();

		$categoryTable = $db->qn('#__reditem_types_' . $type->table_name);
		$categorySelectFieldId = $db->qn($this->getCategorySelectField()->fieldcode);

		$query = $db->getQuery(true)
			->select('id')
			->from($categoryTable)
			->where($categorySelectFieldId . ' = ' . $redeventCategoryId);

		$db->setQuery($query);

		if (!$id = $db->loadResult())
		{
			return false;
		}

		return ReditemEntityCategory::load($id);
	}

	/**
	 * Get category select field entity
	 *
	 * @return ReditemEntityfield
	 *
	 * @since 3.2.3
	 */
	private function getCategorySelectField()
	{
		if (is_null($this->categorySelectField))
		{
			$id = RedeventHelperConfig::get('aesir_category_select_field');
			$field = ReditemEntityField::load($id);

			if (!$field->isValid())
			{
				throw new LogicException('Category select field is not set');
			}

			$this->categorySelectField = $field;
		}

		return $this->categorySelectField;
	}

	/**
	 * Get category types
	 *
	 * @return RedeventEnityType[]
	 *
	 * @since 3.2.3
	 */
	private function getCategoryTypes()
	{
		if (is_null($this->categoryTypes))
		{
			if (!$categorySelectFieldId = RedeventHelperConfig::get('aesir_category_select_field'))
			{
				$this->categoryTypes = false;

				return $this->categoryTypes;
			}

			$db    = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('t.*')
				->from('#__reditem_type_field_xref AS x')
				->join('INNER', '#__reditem_types AS t ON t.id = x.type_id')
				->where('x.field_id = ' . $categorySelectFieldId);

			$db->setQuery($query);

			if (!$res = $db->loadObjectList())
			{
				$this->categoryTypes = false;

				return $this->categoryTypes;
			}

			$this->categoryTypes = array_map(
				function($row)
				{
					return ReditemEntityType::getInstance($row->id)->bind($row);
				},
				$res
			);
		}

		return $this->categoryTypes;
	}
}
