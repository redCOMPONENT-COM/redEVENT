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
	 * Constructor.
	 *
	 * @param   \RedeventEntityVenue  $entity  The entity
	 */
	public function __construct(\RedeventEntityVenue $entity)
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
	 * Get associated bundles
	 *
	 * @return RedeventEntityTwigBundle[]
	 */
	public function getBundles()
	{
		return array_map(
			function($bundle)
			{
				return new RedeventEntityTwigBundle($bundle);
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
	 * Get associated events
	 *
	 * @return RedeventEntityTwigEvent[]
	 */
	public function getEvents()
	{
		return array_map(
			function($event)
			{
				return new RedeventEntityTwigEvent($event);
			},
			$this->entity->getEvents()
		);
	}
}
