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
}
