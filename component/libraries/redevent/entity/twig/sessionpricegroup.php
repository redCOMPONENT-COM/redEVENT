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
 * redEVENT Session price group Twig Entity.
 *
 * @since  3.2.0
 */
final class RedeventEntityTwigSessionpricegroup extends AbstractTwigEntity
{
	/**
	 * Instances cache
	 *
	 * @var RedeventEntityTwigSessionpricegroup[]
	 *
	 * @since 3.2.3
	 */
	private static $instances = [];

	/**
	 * Constructor.
	 *
	 * @param   \RedeventEntitySessionpricegroup  $entity  The entity
	 */
	public function __construct(\RedeventEntitySessionpricegroup $entity)
	{
		$this->entity = $entity;
	}

	/**
	 * Get instance
	 *
	 * @param   \RedeventEntitySessionpricegroup  $entity  The entity
	 *
	 * @return RedeventEntityTwigSessionpricegroup
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
}
