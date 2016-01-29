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
	 * Associated venue
	 *
	 * @var RedeventEntityVenue
	 */
	private $venue;

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

	/**
	 * Return associated venue
	 *
	 * @return RedeventEntityVenue
	 */
	public function getVenue()
	{
		if (!$this->venue)
		{
			$item = $this->getItem();

			if (!empty($item))
			{
				$this->venue = RedeventEntityVenue::getInstance($item->venueid)->loadItem();
			}
		}

		return $this->venue;
	}
}
