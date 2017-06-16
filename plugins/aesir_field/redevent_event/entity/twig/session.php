<?php
/**
 * @package     Redevent.Frontend
 * @subpackage  Plugins
 *
 * @copyright   Copyright (C) 2008 - 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

use Aesir\Entity\Twig\AbstractTwigEntity;
use Aesir\Entity\Twig\Traits;

defined('_JEXEC') or die;

/**
 * redEVENT Session Twig Entity.
 *
 * @since  3.3.10
 */
final class PlgAesir_FieldRedevent_EventEntityTwigSession extends AbstractTwigEntity
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

		throw new RuntimeException('unsupported property in __get: ' . $name);
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
	 * Get session price groups
	 *
	 * @return   array|boolean
	 */
	public function getPrices()
	{
		$prices = $this->entity->getPricegroups();

		return $prices
			? array_map(
				function ($entity)
				{
					return new PlgAesir_FieldRedevent_eventEntityTwigSessionpricegroup($entity);
				},
				$prices
			)
			: false;
	}

	/**
	 * Get venue twig entity
	 *
	 * @return PlgAesir_FieldRedevent_eventEntityTwigVenue
	 */
	public function getVenue()
	{
		$venue = $this->entity->getVenue();

		return $venue->isValid() ? new PlgAesir_FieldRedevent_eventEntityTwigVenue($venue) : false;
	}

	/**
	 * Get booked places
	 *
	 * @return integer
	 */
	public function getBooked()
	{
		if (!$attendees = $this->entity->getAttendees())
		{
			return 0;
		}

		return array_reduce(
			$attendees,
			function ($count, $attendee)
			{
				// PHPCS Indentation error false-positive
				// @codingStandardsIgnoreStart
				if ($attendee->confirmed && !$attendee->cancelled && ! $attendee->waitinglist)
				{
					$count++;
				}
				// @codingStandardsIgnoreEnd

				return $count;
			}
		);
	}

	/**
	 * Get booked places
	 *
	 * @return integer
	 */
	public function getLeft()
	{
		if (!$this->entity->maxattendees)
		{
			return false;
		}

		return $this->entity->maxattendees - $this->getBooked();
	}
}
