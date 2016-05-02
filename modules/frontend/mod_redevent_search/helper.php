<?php
/**
 * @package     Redeventsync.modules
 * @subpackage  Mod_redevent_search
 *
 * @copyright   Copyright (C) 2013 - 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die();

// Register library prefix
JLoader::registerPrefix('R', JPATH_LIBRARIES . '/redcore');
RLoader::registerPrefix('Redevent', JPATH_LIBRARIES . '/redevent');
RLoader::registerPrefix('Rdf', JPATH_LIBRARIES . '/redform');

/**
 * RedEvent Module Search helper
 *
 * @package     Joomla
 * @subpackage  RedEvent search Module
 * @since       0.9
 */
class ModRedEventSearchHelper
{
	private $db = null;

	/**
	 * modRedEventSearchHelper constructor.
	 */
	public function __construct()
	{
		$this->db = JFactory::getDBO();
	}

	/**
	 * get list of categories as options, according to acl
	 *
	 * @return array
	 */
	public function getCategoriesOptions()
	{
		return RedeventHelper::getEventsCatOptions(true, false);
	}

	/**
	 * get venues options
	 *
	 * @return array
	 */
	public function getVenuesOptions()
	{
		$app = JFactory::getApplication();

		$gids = JFactory::getUser()->getAuthorisedViewLevels();
		$gids = implode(',', $gids);

		$query = ' SELECT DISTINCT v.id AS value, '
		. ' CASE WHEN CHAR_LENGTH(v.city) AND v.city <> v.venue THEN CONCAT_WS(\' - \', v.venue, v.city) ELSE v.venue END as text '
		. ' FROM #__redevent_venues AS v '
		. ' LEFT JOIN #__redevent_venue_category_xref AS xcat ON xcat.venue_id = v.id '
		. ' LEFT JOIN #__redevent_venues_categories AS vcat ON vcat.id = xcat.category_id ';
		$where = array('v.published = 1');

		// Acl
		$where[] = ' (v.access IN (' . $gids . ')) ';
		$where[] = ' (vcat.id IS NULL OR vcat.access IN (' . $gids . ')) ';

		if ($app->getLanguageFilter())
		{
			$where[] = '(v.language in (' . $this->db->quote(JFactory::getLanguage()->getTag())
				. ',' . $this->db->quote('*') . ') OR v.language IS NULL)';
		}

		if (count($where))
		{
			$query .= ' WHERE ' . implode(' AND ', $where);
		}

		$query .= ' ORDER BY v.venue ';
		$this->db->setQuery($query);

		$res = $this->db->loadObjectList();

		return $res;
	}

	/**
	 * Get custom filters
	 *
	 * @return array
	 */
	public function getCustomFilters()
	{
		$app = JFactory::getApplication();

		$db      = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('f.*');
		$query->from('#__redevent_fields AS f');
		$query->where('f.published = 1');
		$query->where('f.searchable = 1');
		$query->order('f.ordering');

		if ($app->getLanguageFilter())
		{
			$query->where('(f.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ') OR f.language IS NULL)');
		}

		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$filters = array();

		foreach ($rows as $r)
		{
			$field = RedeventFactoryCustomfield::getField($r->type);
			$field->bind($r);
			$filters[] = $field;
		}

		return $filters;
	}

	/**
	 * ajax search of events
	 *
	 * @param   string  $string  string that should be search
	 *
	 * @return string
	 */
	public function getAjaxSearch($string)
	{
		$app = JFactory::getApplication();

		$db      = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('e.id, e.title ');
		$query->from('#__redevent_events AS e');
		$query->where('e.published = 1');
		$query->where('e.title  LIKE "%' . $string . '%"');
		$query->order('e.title');

		if ($app->getLanguageFilter())
		{
			$query->where('(e.language in (' . $this->db->quote(JFactory::getLanguage()->getTag()) . ',' . $this->db->quote('*') . ') OR e.language IS NULL)');
		}

		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$i = 1;

		if ($string == '')
		{
			$tags = '{';

			foreach ($rows AS $row)
			{
				if ($i < count($rows))
				{
					$tags .= ' "' . $row->id . '": "' . $row->title . '",';
				}
				else
				{
					$tags .= ' "' . $row->id . '": "' . $row->title . '"';
				}

				$i ++;
			}

			$tags .= " } ";
		}
		else
		{
			$tags = '';

			foreach ($rows AS $row)
			{
				if ($i < count($rows))
				{
					$tags .= '"' . $row->title . '",';
				}
				else
				{
					$tags .= '"' . $row->title . '"';
				}

				$i ++;
			}
		}

		return $tags;
	}
}
