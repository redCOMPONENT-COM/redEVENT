<?php
/**
 * @package     Redevent.Library
 * @subpackage  Entity
 *
 * @copyright   Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Event entity.
 *
 * @since  1.0
 */
class RedeventEntitySession extends RedeventEntityBase
{
	/**
	 * Associated event
	 *
	 * @var RedeventEntityEvent
	 */
	private $event;

	/**
	 * Return associated event
	 *
	 * @return RedeventEntityEvent
	 */
	public function getEvent()
	{
		if (!$this->event)
		{
			$item = $this->getItem();

			if (!empty($item))
			{
				$this->event = RedeventEntityEvent::getInstance($item->eventid)->loadItem();
			}
		}

		return $this->event;
	}
}
