<?php
/**
 * @package     Redevent.Library
 * @subpackage  Entity
 *
 * @copyright   Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Event entity.
 *
 * @since  1.0
 */
class RedeventEntityEvent extends RedeventEntityBase
{
	/**
	 * @var JUser
	 */
	private $creator;

	/**
	 * @var RedeventEntityCategory[]
	 */
	private $categories;

	/**
	 * @var RedeventEntitySession[]
	 */
	private $sessions;

	/**
	 * @var RedeventEntityEventtemplate
	 */
	private $template;

	/**
	 * @var RedeventEntityBundle[]
	 */
	private $bundles;

	/**
	 * @var RedeventEntityVenue[]
	 */
	private $activeVenues;

	/**
	 * Get venues used by published event sessions
	 *
	 * @return RedeventEntityVenue[]
	 */
	public function getActiveVenues()
	{
		if (is_null($this->activeVenues))
		{
			$sessions = $this->getPublishedSessions();

			$this->activeVenues = array_reduce(
				$sessions,
				function ($list, $session)
				{
					$venue = $session->getVenue();

					// PHPCS Indentation error false-positive
					// @codingStandardsIgnoreStart
					if (empty($list[$venue->id]))
					{
						$list[$venue->id] = new RedeventEntityTwigVenue($venue);
					}
					// @codingStandardsIgnoreEnd

					return $list;
				},
				array()
			);
		}

		return $this->activeVenues;
	}

	/**
	 * Get event categories
	 *
	 * @return RedeventEntityCategory[]
	 */
	public function getCategories()
	{
		if ($this->categories)
		{
			return $this->categories;
		}

		if (!$this->isValid())
		{
			throw new RuntimeException('Invalid event entity');
		}

		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('c.*')
			->from('#__redevent_categories AS c')
			->join('INNER', '#__redevent_event_category_xref AS x ON x.category_id = c.id')
			->where('x.event_id = ' . $this->id);

		$db->setQuery($query);

		if (!$res = $db->loadObjectList())
		{
			return false;
		}

		$this->categories = array_map(
			function ($row)
			{
				return RedeventEntityCategory::getInstance($row->id)->bind($row);
			},
			$res
		);

		return $this->categories;
	}

	/**
	 * Return creator
	 *
	 * @return JUser
	 */
	public function getCreator()
	{
		if (!$this->creator)
		{
			$item = $this->getItem();

			if (!empty($item))
			{
				$this->creator = JFactory::getUser($item->created_by);
			}
		}

		return $this->creator;
	}

	/**
	 * Get all bundles this event belongs to
	 *
	 * @return RedeventEntityBundle
	 */
	public function getBundles()
	{
		if (is_null($this->bundles))
		{
			if (!$this->hasId())
			{
				return false;
			}

			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('b.*')
				->from('#__redevent_bundle AS b')
				->join('INNER', '#__redevent_bundle_event AS be ON be.bundle_id = b.id')
				->where('be.event_id = ' . $this->id);

			$db->setQuery($query);

			if (!$res = $db->loadObjectList())
			{
				return false;
			}

			$this->bundles = RedeventEntityBundle::loadArray($res);
		}

		return $this->bundles;
	}

	/**
	 * Return event template
	 *
	 * @return RedeventEntityEventtemplate
	 */
	public function getEventtemplate()
	{
		if (!$this->template)
		{
			$item = $this->getItem();

			if (!empty($item))
			{
				$this->template = RedeventEntityEventtemplate::load($item->template_id);
			}
		}

		return $this->template;
	}

	/**
	 * Return associated redform form
	 *
	 * @return RdfEntityForm
	 */
	public function getForm()
	{
		if (!$template = $this->getEventtemplate())
		{
			return false;
		}

		return $template->getForm();
	}

	/**
	 * Get event sessions
	 *
	 * @param   string  $order     order string (startdate, venue)
	 * @param   string  $orderDir  order direction
	 * @param   array   $filters   array of filters (only published for now)
	 *
	 * @return RedeventEntitySession[]
	 */
	public function getSessions($order = null, $orderDir = null, $filters = array())
	{
		if (!$this->isValid())
		{
			return false;
		}

		$order = $order ?: 'dates';
		$orderDir = $orderDir ?: 'asc';

		$hash = "order=$order&orderDir=$orderDir";

		if (!empty($filters))
		{
			foreach ($filters as $k => $val)
			{
				$hash .= "$k=$val";
			}
		}

		if (is_null($this->sessions[$hash]))
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('*')
				->from('#__redevent_event_venue_xref')
				->where('eventid = ' . $this->id);

			$query = $this->filterSessions($query, $filters);
			$query = $this->orderSessions($query, $order, $orderDir);

			$db->setQuery($query);

			if (!$res = $db->loadObjectList())
			{
				$this->sessions[$hash] = false;

				return false;
			}

			$this->sessions[$hash] = array_map(
				function ($row)
				{
					return RedeventEntitySession::getInstance($row->id)->bind($row);
				},
				$res
			);
		}

		return $this->sessions[$hash];
	}

	/**
	 * Get event sessions
	 *
	 * @param   string  $order     order string (startdate, venue)
	 * @param   string  $orderDir  order direction
	 * @param   array   $filters   array of filters (only published for now)
	 *
	 * @return RedeventEntitySession[]
	 */
	public function getPublishedSessions($order = null, $orderDir = null, $filters = array())
	{
		$filters = array_merge(array('published' => 1), $filters);

		return $this->getSessions($order, $orderDir, $filters);
	}

	/**
	 * Check if event has a valid review text
	 *
	 * @return boolean
	 */
	public function hasReview()
	{
		if (!$this->isValid())
		{
			throw new RuntimeException('invalid user entity');
		}

		return strlen(trim(strip_tags($this->getEventtemplate()->review_message))) > 0;
	}

	/**
	 * filter sessions
	 *
	 * @param   JDatabaseQuery  $query    query
	 * @param   array           $filters  array of filters
	 *
	 * @return JDatabaseQuery
	 */
	private function filterSessions($query, $filters = array())
	{
		if (!$filters)
		{
			return $query;
		}

		foreach ($filters as $filter => $value)
		{
			switch ($filter)
			{
				case 'published':
					$query->where('published = ' . (int) $value);
					break;

				case 'upcoming':
					$where = array();

					if (RedeventHelper::config()->get('open_as_upcoming'))
					{
						$where[] = "dates = 0";
					}

					$where[] = '(CASE WHEN x.times THEN CONCAT(x.dates, " ", x.times) ELSE x.dates END > NOW())';

					$query->where('(' . implode(" OR ", $where) . ')');
					break;

				case 'featured':
					$query->where('featured = 1');
					break;
			}
		}

		return $query;
	}

	/**
	 * filter sessions
	 *
	 * @param   JDatabaseQuery  $query     query
	 * @param   string          $order     property to use for ordering
	 * @param   string          $orderDir  direction
	 *
	 * @return JDatabaseQuery
	 */
	private function orderSessions($query, $order = null, $orderDir = null)
	{
		$open_order = JComponentHelper::getParams('com_redevent')->get('open_dates_ordering', 0);
		$ordering_def = ($open_order ? 'dates = 0 ' : 'dates > 0 ') . $orderDir
			. ', dates ' . $orderDir . ', times ' . $orderDir . ', featured DESC';

		switch ($order)
		{
			case 'dates':
				$ordering = $ordering_def;
				break;

			default:
				$ordering = $order . ' ' . $orderDir . ', ' . $ordering_def;
		}

		$query->order($ordering);

		return $query;
	}
}
