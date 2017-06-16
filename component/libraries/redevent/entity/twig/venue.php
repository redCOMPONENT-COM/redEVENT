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
 * redEVENT Venue Twig Entity.
 *
 * @since  3.2.0
 */
final class RedeventEntityTwigVenue extends AbstractTwigEntity
{
	/**
	 * Instances cache
	 *
	 * @var RedeventEntityTwigVenue[]
	 *
	 * @since 3.2.3
	 */
	private static $instances = [];

	/**
	 * Constructor.
	 *
	 * @param   \RedeventEntityVenue  $entity  The entity
	 */
	public function __construct(\RedeventEntityVenue $entity)
	{
		$this->entity = $entity;
	}

	/**
	 * Get instance
	 *
	 * @param   \RedeventEntityVenue  $entity  The entity
	 *
	 * @return RedeventEntityTwigVenue
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

		if ('categories' == $name)
		{
			return $this->getCategories();
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
	 * Get associated bundles
	 *
	 * @return RedeventEntityTwigBundle[]
	 */
	public function getBundles()
	{
		return array_map(
			function ($bundle)
			{
				return \RedeventEntityTwigBundle::getInstance($bundle);
			},
			$this->entity->getBundles()
		);
	}

	/**
	 * GEt route to bundles list
	 *
	 * @return string
	 */
	public function getBundlesLink()
	{
		return \JRoute::_(\RedeventHelperRoute::getBundlesRoute() . '&filter[venue]' . $this->entity->id);
	}

	/**
	 * Get associated venue categories
	 *
	 * @return RedeventEntityTwigVenuescategory[]
	 */
	public function getCategories()
	{
		return array_map(
			function ($entity)
			{
				return \RedeventEntityTwigVenuescategory::getInstance($entity);
			},
			$this->entity->getCategories()
		);
	}

	/**
	 * Get associated events
	 *
	 * @return RedeventEntityTwigEvent[]
	 */
	public function getEvents()
	{
		return array_map(
			function ($event)
			{
				return RedeventEntityTwigEvent::getInstance($event);
			},
			$this->entity->getEvents()
		);
	}

	/**
	 * Get upcoming sessions
	 *
	 * @return array
	 */
	public function getUpcomingsessions()
	{
		return array_map(
			function ($session)
			{
				return RedeventEntityTwigSession::getInstance($session);
			},
			$this->entity->getUpcomings()
		);
	}

	/**
	 * Get upcoming sessions
	 *
	 * @return array
	 */
	public function getUpcomingsessionsCount()
	{
		return count($this->entity->getUpcomings());
	}

	/**
	 * Get upcoming sessions
	 *
	 * @return array
	 */
	public function getUsedLanguages()
	{
		$res = array_reduce(
			$this->entity->getUpcomings(),
			function ($values, $session)
			{
				// PHPCS Indentation error false-positive
				// @codingStandardsIgnoreStart
				if (!in_array($session->session_language, $values))
				{
					$values[] = $session->session_language;
				}
				// @codingStandardsIgnoreEnd

				return $values;
			},
			array()
		);

		return $res;
	}
}
