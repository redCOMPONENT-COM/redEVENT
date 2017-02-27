<?php
/**
 * @package     Aesir.Plugin
 * @subpackage  Aesir_Field.Redevent_bundle
 *
 * @copyright   Copyright (C) 2008 - 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

JLoader::import('reditem.library');

/**
 * PlgAesir_Maersk_categorydetail
 *
 * @since  3.2.3
 */
final class PlgAesirMaersk_categorydetail extends JPlugin
{
	/**
	 * Add location filter for category details with filters
	 *
	 * @param   object           $model  model
	 * @param   \JDatabaseQuery  $query  query
	 *
	 * @return bool
	 *
	 * @since 3.2.3
	 */
	public function onGetListQuery($model, \JDatabaseQuery $query)
	{
		if (!$model instanceof ReditemModelItems)
		{
			return true;
		}

		if (!$catId = $model->getState('filter.catid'))
		{
			return true;
		}

		$category = ReditemEntityCategory::getInstance((int) $catId);

		if (!$category->template_id == 29)
		{
			return true;
		}

		$input = JFactory::getApplication()->input;

		if (!$location = $input->getString('filter_location'))
		{
			return true;
		}

		$db = JFactory::getDbo();
		$query->innerJoin('#__reditem_types_course_1 AS courses ON courses.id = i.id');
		$query->innerJoin('#__redevent_event_venue_xref AS session ON session.eventid = courses.select_redevent_event');
		$query->innerJoin('#__redevent_venues AS venue ON venue.id = session.venueid');
		$query->where($db->quote($location) . ' LIKE CONCAT("%", venue.venue, "%")');
		$query->where('session.published = 1');

		return true;
	}
}
