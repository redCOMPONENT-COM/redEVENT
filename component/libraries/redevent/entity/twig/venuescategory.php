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
 * redEVENT Venue category Twig Entity.
 *
 * @since  3.2.2
 */
final class RedeventEntityTwigVenuescategory extends AbstractTwigEntity
{
	/**
	 * Instances cache
	 *
	 * @var RedeventEntityTwigVenuescategory[]
	 *
	 * @since 3.2.3
	 */
	private static $instances = [];

	/**
	 * Constructor.
	 *
	 * @param   \RedeventEntityVenuescategory  $entity  The entity
	 */
	public function __construct(\RedeventEntityVenuescategory $entity)
	{
		$this->entity = $entity;
	}

	/**
	 * Get instance
	 *
	 * @param   \RedeventEntityVenuescategory  $entity  The entity
	 *
	 * @return RedeventEntityTwigVenuescategory
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
