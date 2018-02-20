<?php
/**
 * @package     Aesir.Library
 * @subpackage  Twig.Extension
 *
 * @copyright   Copyright (C) 2012 - 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

namespace Redevent\Twig;

use Aesir\Entity\Twig\AbstractTwigEntity;
use Aesir\Entity\Twig\Traits;

\JLoader::import('reditem.library');

defined('_JEXEC') or die;

/**
 * Bundle Twig extension.
 *
 * @since  3.2.0
 */
abstract class Plugin extends \Twig_Extension
{
	protected static $twigEntities = [];

	/**
	 * Inject our filter.
	 *
	 * @return  array
	 */
	public function getFunctions()
	{
		return array(
			new \Twig_SimpleFunction('redevent_allvenues', array($this, 'getAllVenues')),
			new \Twig_SimpleFunction('redevent_eventvenues', array($this, 'getActiveEventVenues')),
			new \Twig_SimpleFunction('redevent_venuesperevent', array($this, 'getActiveVenuesPerEvent')),
			new \Twig_SimpleFunction('redevent_eventlanguages', array($this, 'getActiveEventLanguages')),
		);
	}

	/**
	 * Get all published venues
	 *
	 * @return \RedeventEntityTwigVenue[]
	 *
	 * @since 3.2.3
	 */
	public function getAllVenues()
	{
		$db    = \JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('*')
			->from('#__redevent_venues')
			->where('published = 1')
			->order('venue ASC');

		$query->where('(language in (' . $db->quote(\JFactory::getLanguage()->getTag())
			. ',' . $db->quote('*') . ') OR language IS NULL)'
		);

		$db->setQuery($query);
		$venues = $db->loadObjectList();

		$entities = \RedeventEntityVenue::loadArray($venues);

		return $venues ? array_map(
			function ($item)
			{
				return new \RedeventEntityTwigVenue($item);
			},
			$entities
		) : false;
	}

	/**
	 * Get venues associated to published sessions of list of events
	 *
	 * @param   int[]  $eventIds  event ids
	 *
	 * @return \RedeventEntityTwigVenue[]
	 *
	 * @since 3.2.3
	 */
	public function getActiveEventVenues($eventIds)
	{
		if (empty($eventIds))
		{
			return null;
		}

		$eventIds = array_map('intval', $eventIds);

		$db    = \JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('DISTINCT v.*')
			->from('#__redevent_venues AS v')
			->innerJoin('#__redevent_event_venue_xref AS x ON x.venueid = v.id')
			->where('x.published = 1')
			->where('x.eventid IN (' . implode(", ", $eventIds) . ')')
			->order('v.venue ASC');

		$db->setQuery($query);
		$venues = $db->loadObjectList();

		$entities = \RedeventEntityVenue::loadArray($venues);

		return $venues ? array_map(
			function ($item)
			{
				return new \RedeventEntityTwigVenue($item);
			},
			$entities
		) : false;
	}

	/**
	 * Get venues associated to published sessions of list of events, assigned per event id
	 *
	 * @param   int[]  $eventIds  event ids
	 *
	 * @return \RedeventEntityTwigVenue[][]
	 *
	 * @since 3.2.3
	 */
	public function getActiveVenuesPerEvent($eventIds)
	{
		if (empty($eventIds))
		{
			return null;
		}

		if (!$venues = $this->getActiveEventVenues($eventIds))
		{
			return null;
		}

		$eventIds = array_map('intval', $eventIds);

		$db    = \JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('DISTINCT x.eventid, x.venueid')
			->from('#__redevent_venues AS v')
			->innerJoin('#__redevent_event_venue_xref AS x ON x.venueid = v.id')
			->where('x.published = 1')
			->where('x.eventid IN (' . implode(", ", $eventIds) . ')')
			->order('v.venue ASC');

		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$result = array();

		foreach ($rows as $row)
		{
			if (!isset($result[$row->eventid]))
			{
				$result[$row->eventid] = array();
			}

			foreach ($venues as $venue)
			{
				if ($venue->id == $row->venueid)
				{
					$result[$row->eventid][] = $venue;
				}
			}
		}

		return $result;
	}

	/**
	 * Get venues associated to published sessions of list of events
	 *
	 * @param   int[]  $eventIds  event ids
	 *
	 * @return string[]
	 *
	 * @since 3.2.3
	 */
	public function getActiveEventLanguages($eventIds)
	{
		if (empty($eventIds))
		{
			return null;
		}

		$eventIds = array_map('intval', $eventIds);

		$db    = \JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('DISTINCT x.session_language')
			->from('#__redevent_event_venue_xref AS x')
			->where('x.published = 1')
			->where('x.eventid IN (' . implode(", ", $eventIds) . ')')
			->order('x.session_language ASC');

		$db->setQuery($query);

		$res = $db->loadColumn();

		return $res ?: null;
	}
}
