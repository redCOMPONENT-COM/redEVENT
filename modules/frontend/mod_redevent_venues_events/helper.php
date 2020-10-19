<?php
/**
 * @package     Redevent.Frontend
 * @subpackage  Modules
 *
 * @copyright   Copyright (C) 2008 - 2020 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Redevent Module helper
 *
 * @package     Redevent.Frontend
 * @subpackage  Modules
 * @since       2.5
 */
class ModRedEventVenuesEventsHelper
{
	/**
	 * Get items
	 *
	 * @param   JRegistry  $params  plugin params
	 *
	 * @return array
	 */
	public static function getList($params)
	{
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$user_gid = $user->getAuthorisedViewLevels();

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('a.*, x.id AS xref, x.dates, x.enddates, x.allday, x.times, x.endtimes, x.venueid, l.venue, l.city, l.url')
			->select('CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug')
			->select('CASE WHEN CHAR_LENGTH(l.alias) THEN CONCAT_WS(\':\', l.id, l.alias) ELSE l.id END as venueslug')
			->from('#__redevent_event_venue_xref AS x')
			->join('LEFT', '#__redevent_events AS a ON a.id = x.eventid')
			->join('LEFT', '#__redevent_venues AS l ON l.id = x.venueid')
			->join('LEFT', '#__redevent_event_category_xref AS xcat ON xcat.event_id = a.id')
			->join('LEFT', '#__redevent_categories AS c ON c.id = xcat.category_id')
			->where('a.published = 1 AND x.published = 1')
			->where('c.access IN (' . implode(',', $user_gid) . ')')
			->group('a.id, l.id')
			->order('a.title ASC');

		$catid = $params->get('catid');

		if (is_array($catid) && count($catid))
		{
			JArrayHelper::toInteger($catid);
			$query->where('c.id IN (' . implode(',', $catid) . ')');
		}

		$venid = $params->get('venid');

		if (is_array($venid) && count($venid))
		{
			JArrayHelper::toInteger($venid);
			$query->where('l.id IN (' . implode(',', $venid) . ')');
		}

		if ($app->getLanguageFilter())
		{
			$query->where('(a.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ') OR a.language IS NULL)');
		}

		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$title_length = $params->get('cuttitle', '18');

		foreach ($rows as $k => $row)
		{
			// Cut title
			$length = strlen(htmlspecialchars($row->title));

			if ($title_length && $length > $title_length)
			{
				$rows[$k]->title_short = '<span class="hasTooltip" title="' . $row->title . '">'
					. htmlspecialchars(substr($row->title, 0, $title_length) . '...', ENT_COMPAT, 'UTF-8')
					. '</span>';
			}
			else
			{
				$rows[$k]->title_short = htmlspecialchars($row->title, ENT_COMPAT, 'UTF-8');
			}

			$rows[$k]->link = JRoute::_(RedeventHelperRoute::getDetailsRoute($row->slug));
			$rows[$k]->text = $rows[$k]->title_short;
		}

		// Now sort all this by venues
		$venuesevents = array();

		foreach ($rows as $k => $row)
		{
			if (!isset($venuesevents[$row->venueid]))
			{
				$venuesevents[$row->venueid] = array();
			}

			$venuesevents[$row->venueid][] = $row;
		}

		return $venuesevents;
	}

	/**
	 * Method to get a valid url
	 *
	 * @param   string  $url  url
	 *
	 * @return string
	 */
	private static function _format_url($url)
	{
		if (!empty($url) && strtolower(substr($url, 0, 7)) != "http://")
		{
			$url = 'http://' . $url;
		}

		return $url;
	}

	/**
	 * returns events venues as options
	 *
	 * @param   array  $sessions  sessions data
	 *
	 * @return array $options for select
	 */
	public static function getVenuesEventsOptions($sessions)
	{
		$options = array(JHTML::_('select.option', '', JText::_('MOD_REDEVENT_VENUES_EVENTS_SELECT_EVENT')));

		foreach ($sessions as $s)
		{
			$link = RedeventHelperRoute::getDetailsRoute($s->id, $s->xref);
			$options[] = JHTML::_('select.option', JRoute::_($link), $s->title);
		}

		return $options;
	}
}
