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
 * redEVENT Session Twig Entity.
 *
 * @since  3.2.0
 */
final class RedeventEntityTwigSession extends AbstractTwigEntity
{
	use Traits\HasCheckin, Traits\HasFeatured, Traits\HasState;

	/**
	 * Instances cache
	 *
	 * @var RedeventEntityTwigSession[]
	 *
	 * @since 3.2.3
	 */
	private static $instances = [];

	/**
	 * Constructor.
	 *
	 * @param   \RedeventEntitySession  $entity  The entity
	 */
	public function __construct(\RedeventEntitySession $entity)
	{
		$this->entity = $entity;
	}

	/**
	 * Get instance
	 *
	 * @param   \RedeventEntitySession  $entity  The entity
	 *
	 * @return RedeventEntityTwigSession
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
	 * is triggered when invoking inaccessible methods in an object context.
	 *
	 * @param   method  $name       method name
	 * @param   array   $arguments  arguments
	 *
	 * @return mixed
	 *
	 * @throws LogicException
	 */
	public function __call($name, $arguments)
	{
		if (is_callable(array($this->entity, 'get' . ucfirst($name))))
		{
			return call_user_func_array(array($this->entity, 'get' . ucfirst($name)), $arguments);
		}

		throw new LogicException('wrong function call');
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
	 * Return number of booked places
	 *
	 * @return integer
	 */
	public function getBooked()
	{
		return $this->entity->getNumberAttending();
	}

	/**
	 * Get number of days until start
	 *
	 * @return string
	 */
	public function getDaysUntil()
	{
		if (!RedeventHelperDate::isValidDate($this->entity->dates))
		{
			return false;
		}

		$start = $this->entity->getDateStart();

		return $start->diff(JFactory::getDate('today'))->format('%a');
	}

	/**
	 * Get venue twig entity
	 *
	 * @return \RedeventEntityTwigVenue
	 */
	public function getEvent()
	{
		$event = $this->entity->getEvent();

		return $event->isValid() ? \RedeventEntityTwigEvent::getInstance($event) : false;
	}

	/**
	 * Return number of places left
	 *
	 * @return integer
	 */
	public function getLeft()
	{
		return $this->entity->getNumberLeft();
	}

	/**
	 * Get session price groups
	 *
	 * @return   array|boolean
	 */
	public function getPrices()
	{
		$prices = $this->entity->getUserActivePricegroups();

		return $prices
			? array_map(
				function ($entity)
				{
					return \RedeventEntityTwigSessionpricegroup::getInstance($entity);
				},
				$prices
			)
			: false;
	}

	/**
	 * Return signup url
	 *
	 * @return string
	 */
	public function getSignuplink()
	{
		return \JRoute::_(\RedeventHelperRoute::getSignupRoute('webform', $this->entity->eventid, $this->entity->id));
	}

	/**
	 * Get venue twig entity
	 *
	 * @return \RedeventEntityTwigVenue
	 */
	public function getVenue()
	{
		$venue = $this->entity->getVenue();

		return $venue->isValid() ? \RedeventEntityTwigVenue::getInstance($venue) : false;
	}

	/**
	 * check if session is full
	 *
	 * @return boolean
	 */
	public function getFull()
	{
		return $this->entity->isFull();
	}
}
