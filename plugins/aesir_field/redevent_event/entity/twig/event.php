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
final class PlgAesir_FieldRedevent_eventEntityTwigEvent extends AbstractTwigEntity
{
	use Traits\HasCheckin, Traits\HasFeatured, Traits\HasState, Traits\HasName;

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
	 * Get the item title.
	 *
	 * @return  string
	 */
	public function getTitle()
	{
		return $this->entity->title;
	}
}
