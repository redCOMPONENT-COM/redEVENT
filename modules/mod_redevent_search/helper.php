<?php
/**
 * @package     Joomla
 * @subpackage  RedEvent search module
 * @copyright  (C) 2011 redCOMPONENT.com
 * @license    GNU/GPL, see LICENCE.php
 * RedEvent is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * RedEvent is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with RedEvent; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

require_once JPATH_SITE . '/components/com_redevent/helpers/route.php';
require_once JPATH_SITE . '/components/com_redevent/helpers/customfields.php';

/**
 * RedEvent Module Search helper
 *
 * @package     Joomla
 * @subpackage  RedEvent search Module
 * @since       0.9
 */
class modRedEventSearchHelper
{
	private $_db = null;

	public function __construct()
	{
		$this->_db = &Jfactory::getDBO();
	}

	/**
	 * get list of categories as options, according to acl
	 *
	 * @return array
	 */
	function getCategoriesOptions()
	{
		$app = JFactory::getApplication();

		$gids = JFactory::getUser()->getAuthorisedViewLevels();
		$gids = implode(',', $gids);

		//Get Events from Database
		$query  = ' SELECT c.id '
		. ' FROM #__redevent_event_venue_xref AS x'
		. ' INNER JOIN #__redevent_events AS a ON a.id = x.eventid'
		. ' INNER JOIN #__redevent_venues AS l ON l.id = x.venueid'
		. ' LEFT JOIN #__redevent_venue_category_xref AS xvcat ON l.id = xvcat.venue_id'
		. ' LEFT JOIN #__redevent_venues_categories AS vc ON xvcat.category_id = vc.id'
		. ' INNER JOIN #__redevent_event_category_xref AS xcat ON xcat.event_id = a.id'
		. ' INNER JOIN #__redevent_categories AS c ON c.id = xcat.category_id'
		;

		$where = array();
		$where[] = ' x.published = 1';

		//acl
		$where[] = ' (l.access IN (' . $gids . ')) ';
		$where[] = ' (c.access IN (' . $gids . ')) ';
		$where[] = ' (vc.id IS NULL OR vc.access IN (' . $gids . ')) ';

		if ($app->getLanguageFilter())
		{
			$where[] = '(c.language in (' . $this->_db->quote(JFactory::getLanguage()->getTag()) . ',' . $this->_db->quote('*') . ') OR c.language IS NULL)';
		}

		if (count($where)) {
			$query .= ' WHERE '. implode(' AND ', $where);
		}
		$query .= ' GROUP BY c.id ';

		$this->_db->setQuery($query);
		$res = $this->_db->loadResultArray();

		return redEVENTHelper::getEventsCatOptions(true, false, $res);
	}

	/**
	 * get venues options
	 *
	 * @return array
	 */
	function getVenuesOptions()
	{
		$app = &JFactory::getApplication();

		$gids = JFactory::getUser()->getAuthorisedViewLevels();
		$gids = implode(',', $gids);

		$query = ' SELECT DISTINCT v.id AS value, '
		. ' CASE WHEN CHAR_LENGTH(v.city) AND v.city <> v.venue THEN CONCAT_WS(\' - \', v.venue, v.city) ELSE v.venue END as text '
		. ' FROM #__redevent_venues AS v '
		. ' LEFT JOIN #__redevent_venue_category_xref AS xcat ON xcat.venue_id = v.id '
		. ' LEFT JOIN #__redevent_venues_categories AS vcat ON vcat.id = xcat.category_id '
		;
		$where = array();

		//acl
		$where[] = ' (v.access IN (' . $gids . ')) ';
		$where[] = ' (vcat.id IS NULL OR vcat.access IN (' . $gids . ')) ';

		if ($app->getLanguageFilter())
		{
			$where[] = '(v.language in (' . $this->_db->quote(JFactory::getLanguage()->getTag()) . ',' . $this->_db->quote('*') . ') OR v.language IS NULL)';
		}

		if (count($where)) {
			$query .= ' WHERE '. implode(' AND ', $where);
		}
		$query .= ' ORDER BY v.venue ';
		$this->_db->setQuery($query);
		$res = $this->_db->loadObjectList();
		return $res;
	}

	function getCustomFilters()
	{
		$query = ' SELECT f.* FROM #__redevent_fields AS f '
		. ' WHERE f.published = 1 '
		. '   AND f.searchable = 1 '
		. ' ORDER BY f.ordering ASC '
		;
		$this->_db->setQuery($query);
		$rows = $this->_db->loadObjectList();

		$filters = array();
		foreach ($rows as $r)
		{
			$field = redEVENTcustomHelper::getCustomField($r->type);
			$field->bind($r);
			$filters[] = $field;
		}
		return $filters;
	}
}