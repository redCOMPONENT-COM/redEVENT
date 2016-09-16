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
			function($row)
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

		return RedeventEntityBundle::loadArray($res);
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
		$sessions = $this->getAllSessions();
		$sessions = $this->filterSessions($sessions, $filters);
		$sessions = $this->orderSessions($sessions, $order, $orderDir);

		return $sessions;
	}

	/**
	 * Check if event has a valid review text
	 *
	 * @return bool
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
	 * @param   RedeventEntitySession[]  $sessions  sessions to order
	 * @param   array                    $filters   array of filters
	 *
	 * @return array
	 */
	private function filterSessions($sessions, $filters = array())
	{
		if (!$filters)
		{
			return $sessions;
		}

		foreach ($filters as $filter => $value)
		{
			switch ($filter)
			{
				case 'published':
					$sessions = array_filter(
						$sessions,
						function($session) use ($value)
						{
							return $session->published == $value;
						}
					);

				case 'upcoming':
					$sessions = array_filter(
						$sessions,
						function($session) use ($value)
						{
							$upcoming = $session->isUpcoming();

							return $value ? $upcoming : !$upcoming;
						}
					);
			}
		}

		return $sessions;
	}

	/**
	 * Get all event sessions
	 *
	 * @return RedeventEntitySession[]
	 */
	private function getAllSessions()
	{
		if (!$this->isValid())
		{
			return false;
		}

		if (is_null($this->sessions))
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('*')
				->from('#__redevent_event_venue_xref')
				->where('eventid = ' . $this->id);

			$db->setQuery($query);

			if (!$res = $db->loadObjectList())
			{
				$this->sessions = false;

				return false;
			}

			$this->sessions = array_map(
				function($row)
				{
					return RedeventEntitySession::getInstance($row->id)->bind($row);
				},
				$res
			);
		}

		return $this->sessions;
	}

	/**
	 * order sessions
	 *
	 * @param   RedeventEntitySession[]  $sessions  sessions to order
	 * @param   string                   $order     property to use for ordering
	 * @param   string                   $orderDir  direction
	 *
	 * @return array
	 */
	private function orderSessions($sessions, $order = null, $orderDir = null)
	{
		$orderDir = strtolower($orderDir) == 'asc' ? 'ASC' : 'DESC';

		switch ($order)
		{
			case 'dates':
				usort(
					$sessions,
					function($a, $b)
					{
						$dateA = JFactory::getDate($a->dates . ' ' . $a->times);
						$dateB = JFactory::getDate($b->dates . ' ' . $b->times);

						if ($dateA == $dateB)
						{
							return 0;
						}

						return $dateA > $dateB ? 1 : - 1;
					}
				);

				if ($orderDir == 'DESC')
				{
					$sessions = array_reverse($sessions);
				}

				break;
		}

		return $sessions;
	}
}
