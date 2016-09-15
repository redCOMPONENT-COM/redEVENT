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
	 * Constructor.
	 *
	 * @param   \RedeventEntitySession  $entity  The entity
	 */
	public function __construct(\RedeventEntitySession $entity)
	{
		$this->entity = $entity;
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
	 * @return bool
	 */
	public function __isset($name)
	{
		return isset($this->entity->$name);
	}

	/**
	 * Return number of booked places
	 *
	 * @return int
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

		return $start->diff(JFactory::getDate('today'))->format('%d');
	}

	/**
	 * Get venue twig entity
	 *
	 * @return \RedeventEntityTwigVenue
	 */
	public function getEvent()
	{
		$event = $this->entity->getEvent();

		return $event->isValid() ? new \RedeventEntityTwigEvent($event) : false;
	}

	/**
	 * Return number of places left
	 *
	 * @return int
	 */
	public function getLeft()
	{
		return $this->entity->getNumberLeft();
	}

	/**
	 * Get session price groups
	 *
	 * @return   array|bool
	 */
	public function getPrices()
	{
		$prices = $this->entity->getPricegroups();

		return $prices
			? array_map(
				function($entity)
				{
					return new \RedeventEntityTwigSessionpricegroup($entity);
				},
				$prices
			)
			: false;
	}

	/**
	 * Get venue twig entity
	 *
	 * @return \RedeventEntityTwigVenue
	 */
	public function getVenue()
	{
		$venue = $this->entity->getVenue();

		return $venue->isValid() ? new \RedeventEntityTwigVenue($venue) : false;
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
