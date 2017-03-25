<?php
/**
 * @package     Redevent.Library
 * @subpackage  Entity.twig
 *
 * @copyright   Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

JLoader::import('reditem.library');

use Aesir\Entity\Twig\AbstractTwigEntity;
use Aesir\Entity\Twig\Traits;

defined('_JEXEC') or die;

/**
 * redEVENT event Twig Entity.
 *
 * @since  3.2.0
 */
final class RedeventEntityTwigEvent extends AbstractTwigEntity
{
	/**
	 * Instances cache
	 *
	 * @var RedeventEntityTwigEvent[]
	 *
	 * @since 3.2.3
	 */
	private static $instances = [];

	/**
	 * @var RedeventEntitySession[][]
	 */
	private $sessions;

	/**
	 * @var RedeventEntityVenue[]
	 */
	private $venues;

	/**
	 * Constructor.
	 *
	 * @param   \RedeventEntityEvent  $entity  The entity
	 */
	public function __construct(\RedeventEntityEvent $entity)
	{
		$this->entity = $entity;
	}

	/**
	 * Get instance
	 *
	 * @param   \RedeventEntityEvent  $entity  The entity
	 *
	 * @return RedeventEntityTwigEvent
	 *
	 * @since 3.2.3
	 */
	public static function getInstance($entity)
	{
		if (empty(self::$instances[$entity->id]))
		{
			self::$instances[$entity->id] = new static($entity);
		}

		return self::$instances[$entity->id];
	}

	/**
	 * is utilized for reading data from inaccessible members.
	 *
	 * @param   string  $name  string
	 *
	 * @return mixed
	 */
	public function __get($name)
	{
		if (isset($this->entity->$name))
		{
			return $this->entity->$name;
		}

		throw new \RuntimeException('unsupported property in __get: ' . $name);
	}

	/**
	 * is triggered by calling isset() or empty() on inaccessible members.
	 *
	 * @param   string  $name  string
	 *
	 * @return boolean
	 */
	public function __isset($name)
	{
		return isset($this->entity->$name);
	}

	/**
	 * Get all bundles this event belongs to
	 *
	 * @return \RedeventEntityTwigBundle[]
	 */
	public function getBundles()
	{
		if (!$bundles = $this->entity->getBundles())
		{
			return false;
		}

		// Filter published
		$published = array_filter(
			$bundles,
			function($bundle)
			{
				return $bundle->published;
			}
		);

		// Return twig entities
		return $published ? array_map(
			function($bundle)
			{
				return \RedeventEntityTwigBundle::getInstance($bundle);
			}, $published
		) : false;
	}

	/**
	 * Get all bundles this event belongs to
	 *
	 * @return \RedeventEntityTwigBundle[]
	 */
	public function getBundlesLink()
	{
		return \JRoute::_(\RedeventHelperRoute::getBundlesRoute() . '&filter[event]' . $this->entity->id);
	}

	/**
	 * Get duration max in days
	 *
	 * @return integer
	 */
	public function getDurationMax()
	{
		if (!$sessions = $this->getEventSessions())
		{
			return false;
		}

		return array_reduce(
			$sessions,
			function($value, $session)
			{
				return max($value, $session->getDurationDays());
			}
		);
	}

	/**
	 * Get duration min in days
	 *
	 * @return integer
	 */
	public function getDurationMin()
	{
		if (!$sessions = $this->getEventSessions())
		{
			return false;
		}

		return array_reduce(
			$sessions,
			function($value, $session)
			{
				$duration = $session->getDurationDays();

				if (!$duration)
				{
					return $value;
				}

				return $value ? min($value, $duration) : $duration;
			}
		);
	}

	/**
	 * Get next session
	 *
	 * @return \RedeventEntityTwigSession
	 */
	public function getNext()
	{
		if (!$sessions = $this->getEventSessions(1, "dates.asc"))
		{
			return false;
		}

		$upcomings = array_filter(
			$sessions,
			function($session)
			{
				return $session->isUpcoming() && !$session->isOpenDate();
			}
		);

		if (!$upcomings)
		{
			// Try allowing open dates
			$upcomings = array_filter(
				$sessions,
				function($session)
				{
					return $session->isUpcoming();
				}
			);
		}

		if (!$upcomings)
		{
			return false;
		}

		return \RedeventEntityTwigSession::getInstance(reset($upcomings));
	}

	/**
	 * Return signup form
	 *
	 * @return string
	 */
	public function getSignupform()
	{
		$helper = new \RedeventTagsRegistrationEvent($this->entity->id);

		return $helper->getHtml();
	}

	/**
	 * Return signup url
	 *
	 * @return string
	 */
	public function getSignuplink()
	{
		return \JRoute::_(\RedeventHelperRoute::getSignupRoute('webform', $this->entity->id));
	}

	/**
	 * Return sessions
	 *
	 * @param   int     $published  publish state
	 * @param   string  $ordering   ordering
	 * @param   bool    $featured   filtered featured
	 *
	 * @return array|bool
	 */
	public function getSessions($published = 1, $ordering = 'dates.asc', $featured = false)
	{
		$sessions = $this->getEventSessions($published, $ordering, $featured);

		return $sessions ? array_map(
			function($session)
			{
				return \RedeventEntityTwigSession::getInstance($session);
			},
			$sessions
		) : false;
	}

	/**
	 * Get event venues
	 *
	 * @return \RedeventEntityTwigVenue[]
	 */
	public function getVenues()
	{
		if (is_null($this->venues))
		{
			$this->venues = $this->entity->getActiveVenues();
		}

		return $this->venues;
	}

	/**
	 * Get all start dates of active sessions
	 *
	 * @return string[]
	 *
	 * @since 3.2.3
	 */
	public function getStartDates()
	{
		if (!$sessions = $this->getEventSessions())
		{
			return false;
		}

		return array_reduce(
			$sessions,
			function($values, $session)
			{
				if (!in_array($session->dates, $values))
				{
					$values[] = $session->dates;
				}

				return $values;
			},
			array()
		);
	}

	/**
	 * Return cached event sessions
	 *
	 * @param   int     $published  publish state
	 * @param   string  $ordering   ordering
	 * @param   bool    $featured   filtered featured
	 *
	 * @return \RedeventEntitySession[]
	 */
	private function getEventSessions($published = 1, $ordering = 'dates.asc', $featured = false)
	{
		$hash = "published=$published&ordering=$ordering&featured=$featured";

		if (!isset($this->sessions[$hash]))
		{
			switch ($ordering)
			{
				case 'dates.desc':
					$order = 'dates';
					$orderDir = 'desc';
					break;

				case 'dates.asc':
				default:
					$order = 'dates';
					$orderDir = 'asc';
			}

			$filters = array();

			if (is_numeric($published))
			{
				$filters['published'] = $published;
			}

			if ($featured)
			{
				$filters['featured'] = 1;
			}

			$this->sessions[$hash] = $this->entity->getSessions($order, $orderDir, $filters);
		}

		return $this->sessions[$hash];
	}
}
