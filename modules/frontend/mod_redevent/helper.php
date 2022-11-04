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
 * RedEvent Module helper
 *
 * @package     Redevent.Frontend
 * @subpackage  Modules
 * @since       0.9
 */
class ModRedEventHelper
{
	/**
	 * Method to get the events
	 *
	 * @param   array  $params  parameters
	 *
	 * @return array
	 */
	public static function getList(&$params)
	{
		$app = JFactory::getApplication();

		$db			= JFactory::getDBO();
		$user		= JFactory::getUser();
		$user_gid	= $user->getAuthorisedViewLevels();

		$query = $db->getQuery(true);

		switch ($params->get('ordering', 0))
		{
			case 5:
				$order = ' a.title DESC, x.title DESC';
				break;
			case 4:
				$order = ' a.title ASC, x.title ASC';
				break;
			case 3:
				$order = ' x.id DESC';
				break;
			case 2:
				$order = ' x.id ASC';
				break;
			case 1:
				$order = ' x.dates DESC, x.times DESC ';
				break;
			default:
			case 0:
				$order = ' x.dates ASC, x.times ASC ';
				break;
		}

		$query->order($order);

		$where = array();

		$where[] = 'c.access IN (' . implode(',', $user_gid) . ')';

		$type = $params->get('type', '0');
		$offset = (int) $params->get('dayoffset', '0');

		if ($type == 0) // Published
		{
			$where[] = 'x.published = 1';
			$where[] = 'a.published = 1';
		}
		elseif ($type == 1) // Upcoming
		{
			$date = $offset ? 'now +' . $offset . ' days' : 'now';
			$ref = strftime('%Y-%m-%d %H:%M', strtotime($date));
			$where[] = 'a.published = 1';
			$where[] = 'x.published = 1 AND (CASE WHEN x.times THEN CONCAT(x.dates," ",x.times) ELSE x.dates END) > ' . $db->Quote($ref);
		}
		elseif ($type == 2) // Archived
		{
			$where[] = 'a.published = -1';
			$where[] = 'x.published = -1';
		}
		elseif ($type == 3) // Open dates
		{
			$where[] = 'x.published = 1';
			$where[] = 'a.published = 1';
			$where[] = 'x.dates IS NULL';
		}
		elseif ($type == 4) // Just passed dates
		{
			$where[] = 'a.published = 1';
			$date = $offset ? 'now -' . $offset . ' days' : 'now';
			$ref = strftime('%Y-%m-%d %H:%M', strtotime($date));
			$where[] = 'x.published = 1 AND (CASE WHEN x.times THEN CONCAT(x.dates," ",x.times) ELSE x.dates END) < ' . $db->Quote($ref);
		}

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

		if ($params->get('featuredonly', 0) == 1)
		{
			$where[] = ' x.featured = 1 ';
		}

		if ($params->get('showrecurring', 1) == 0)
		{
			$where[] = ' r.count = 0 ';
		}

		foreach ($where as $w)
		{
			$query->where($w);
		}

		if ($params->get('showsessions', 1) == 0)
		{
			$query->group('a.id ');
		}
		else
		{
			$query->group('x.id ');
		}

		$query->select('x.*, x.id AS xref, a.*, l.venue, l.city, l.url, l.state');
		$query->select(' CONCAT_WS(",", c.image) AS categories_images');
		$query->select(' CASE WHEN CHAR_LENGTH(x.title) THEN x.title ELSE a.title END as session_title');
		$query->select(' CASE WHEN CHAR_LENGTH(x.title) THEN CONCAT_WS(\' - \', a.title, x.title) ELSE a.title END as full_title');
		$query->select(' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug');
		$query->select(' CASE WHEN CHAR_LENGTH(x.alias) THEN CONCAT_WS(\':\', x.id, x.alias) ELSE x.id END as xslug');
		$query->select(' CASE WHEN CHAR_LENGTH(l.alias) THEN CONCAT_WS(\':\', l.id, l.alias) ELSE l.id END as venueslug');
		$query->from('#__redevent_event_venue_xref AS x');
		$query->join('LEFT', '#__redevent_events AS a ON a.id = x.eventid');
		$query->join('LEFT', '#__redevent_venues AS l ON l.id = x.venueid');
		$query->join('LEFT', '#__redevent_event_category_xref AS xcat ON xcat.event_id = a.id');
		$query->join('LEFT', '#__redevent_categories AS c ON c.id = xcat.category_id');
		$query->join('LEFT', '#__redevent_repeats AS r ON r.xref_id = x.id');

		if ($app->getLanguageFilter())
		{
			$query->where('(a.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ') OR a.language IS NULL)');
		}

		$db->setQuery($query, 0, (int) $params->get('count', '2'));
		$rows = $db->loadObjectList();
		$rows = self::_categories($rows);

		$lists = array();
		$title_length = $params->get('cuttitle', '18');

		switch ($params->get('title_type', 0))
		{
			case 1:
				$title_type = 'session_title';
				break;
			case 2:
				$title_type = 'full_title';
				break;
			case 0:
			default:
				$title_type = 'title';
				break;
		}

		foreach ($rows as $k => $row)
		{
			$rowtitle = $row->$title_type;

			// Cut title
			$length = mb_strlen($rowtitle, 'UTF-8');

			if ($title_length && $length > $title_length)
			{
				$rows[$k]->title_short = mb_substr($rowtitle, 0, $title_length, 'UTF-8') . '...';
			}
			else
			{
				$rows[$k]->title_short = $rowtitle;
			}

			// Cut venue name
			$length = mb_strlen($row->venue, 'UTF-8');

			if ($title_length && $length > $title_length)
			{
				$rows[$k]->venue_short = mb_substr($row->venue, 0, $title_length, 'UTF-8') . '...';
			}
			else
			{
				$rows[$k]->venue_short = $row->venue;
			}

			$rows[$k]->link		= JRoute::_(RedeventHelperRoute::getDetailsRoute($row->slug, $row->xref));
			$rows[$k]->dateinfo 	= self::_builddateinfo($row, $params);
			$rows[$k]->city		= htmlspecialchars($row->city, ENT_COMPAT, 'UTF-8');
			$rows[$k]->venueurl 	= !empty($row->url)
				? self::_format_url($row->url) : JRoute::_(RedeventHelperRoute::getVenueEventsRoute($row->venueslug), false);
		}

		return $rows;
	}

	/**
	 * Method to a formated and structured string of date infos
	 *
	 * @param   object  $row     data
	 * @param   array   $params  parameters
	 *
	 * @return string
	 */
	protected static function _builddateinfo($row, &$params)
	{
		if (!RedeventHelperDate::isValidDate($row->dates))
		{
			return JText::_('MOD_REDEVENT_OPEN_DATE');
		}

		$date = self::_format_date($row->dates, $row->times, $params->get('formatdate', '%d.%m.%Y'));
		$enddate = RedeventHelperDate::isValidDate($row->enddates)
			? self::_format_date($row->enddates, $row->endtimes, $params->get('formatdate', '%d.%m.%Y'))
			: null;
		$time = ($row->times && $row->times != '00:00:00')
			? self::_format_date($row->dates, $row->times, $params->get('formattime', '%H:%M'))
			: null;
		$dateinfo = '<span class="event-start">' . $date . '</span>';

		if (isset($enddate) && $params->get('show_enddate', 1) && $row->dates != $row->enddates)
		{
			$dateinfo .= ' - <span class="event-end">' . $enddate . '</span>';
		}

		if (isset($time) && $params->get('show_time', 1))
		{
			$dateinfo .= ' <span class="event-time">' . $time . '</span>';
		}

		return $dateinfo;
	}

	/**
	 * Method to get a valid url
	 *
	 * @param   string  $url  url
	 *
	 * @return string
	 */
	protected static function _format_url($url)
	{
		if (!empty($url) && strtolower(substr($url, 0, 7)) != "http://")
		{
			$url = 'http://' . $url;
		}

		return $url;
	}

	/**
	 * Method to format date information
	 *
	 * @param   string  $date    date
	 * @param   string  $time    time
	 * @param   string  $format  format
	 *
	 * @return string
	 */
	protected static function _format_date($date, $time, $format)
	{
		// Format date
		$date = strftime($format, strtotime($date . ' ' . $time));

		return $date;
	}

	/**
	 * adds categories property to event rows
	 *
	 * @param   array  $rows  of events
	 *
	 * @return array
	 */
	protected static function _categories($rows)
	{
		$app = JFactory::getApplication();
		$db  = JFactory::getDBO();

		$gids = JFactory::getUser()->getAuthorisedViewLevels();
		$gids = implode(',', $gids);

		for ($i = 0, $n = count($rows); $i < $n; $i++)
		{
			$query = $db->getQuery(true);

			$query->select('c.id, c.name, c.color');
			$query->select('CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as slug');
			$query->from('#__redevent_categories as c');
			$query->join('INNER', '#__redevent_event_category_xref as x ON x.category_id = c.id');
			$query->where('c.published = 1');
			$query->where('x.event_id = ' . $db->Quote($rows[$i]->id));
			$query->where('(c.access IN (' . $gids . '))');
			$query->group('c.id');
			$query->order('c.ordering');

			if ($app->getLanguageFilter())
			{
				$query->where(
					'(c.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ') OR c.language IS NULL)'
				);
			}

			$db->setQuery($query);

			$rows[$i]->categories = $db->loadObjectList();
		}

		return $rows;
	}

	/**
	 * returns code for list of cats separated by comma
	 *
	 * @param   array  $categories  cats
	 *
	 * @return string html
	 */
	public static function displayCats($categories)
	{
		$res = array();

		foreach ($categories as $c)
		{
			$res[] = $c->name;
		}

		return implode(", ", $res);
	}

	/**
	 * return custom fields indexed by id
	 *
	 * @return array
	 */
	public static function getCustomFields()
	{
		$db = Jfactory::getDBO();
		$query = ' SELECT f.id, f.* FROM #__redevent_fields AS f';
		$db->setQuery($query);

		return $db->loadObjectList('id');
	}
}
