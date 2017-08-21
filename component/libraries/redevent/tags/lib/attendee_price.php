<?php
/**
 * @package    Redevent.Library
 *
 * @copyright  Copyright (C) 2009 - 2017 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

/**
 * Tag to return the total cost for one specific attendee
 *
 * @package  Redevent.Library
 * @since    __deploy_version__
 */
class RedeventTagsLibAttendee_Price extends \Redevent\Tag\Replacer
{
	/**
	 * Get supported tag description
	 *
	 * @return  RedeventTagsTag
	 *
	 * @since __deploy_version__
	 */
	public function getDescription()
	{
		return new RedeventTagsTag('attendee_price', JText::_('COM_REDEVENT_TAG_DESC_ATTENDEE_PRICE'), 'registration');
	}

	/**
	 * Get replacement
	 *
	 * @return string
	 *
	 * @since __deploy_version__
	 */
	public function getReplace()
	{
		$attendees = $this->parent->getAttendees();

		if (count($attendees) !== 1)
		{
			return '[invalid tag usage: attendee_price]';
		}

		$attendee = reset($attendees);

		if (!$attendee->isValid())
		{
			return '[invalid tag usage: attendee_price]';
		}

		$submitter = $attendee->getSubmitter();
		$price = $submitter->price + $submitter->vat;

		if ($price == 0)
		{
			return JText::_('COM_REDEVENT_EVENT_PRICE_FREE');
		}

		return RedeventHelperOutput::formatprice($price, $submitter->currency);
	}
}
