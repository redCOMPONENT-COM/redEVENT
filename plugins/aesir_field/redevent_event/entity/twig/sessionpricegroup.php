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
 * redEVENT event Twig Entity.
 *
 * @since  3.3.10
 */
final class PlgAesir_FieldRedevent_eventEntityTwigSessionpricegroup extends AbstractTwigEntity
{
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
	 * @return bool
	 */
	public function __isset($name)
	{
		return isset($this->entity->$name);
	}
}
