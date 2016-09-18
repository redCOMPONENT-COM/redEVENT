<?php
/**
 * @package    Redevent.Site
 * @copyright  Copyright (C) 2008 - 2016 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * redEVENT Bundle Controller
 *
 * @package  Redevent.Site
 * @since    3.2.0
 */
class RedeventControllerBundle extends JControllerLegacy
{
	/**
	 * Get default session for bundle event
	 *
	 * @return mixed|null|string
	 */
	public function defaultsession()
	{
		$bundeleventid = $this->input->getInt('bundleeventid');

		if (!$entity = RedeventEntityBundleevent::load($bundeleventid))
		{
			return null;
		}

		if (!$next = $entity->getNext())
		{
			return null;
		}

		$id = $next->id;

		$label = JText::sprintf(
			'COM_REDEVENT_VIEW_BUNDLE_SESSION_SELECTED_LABEL_S_S_S_D',
			$next->getFormattedStartDate(),
			$next->getVenue()->city,
			$next->getVenue()->country,
			$next->getDurationDays()
		);

		$prices = $next->getPricegroups(true);
		$singleprice = count($prices) <= 2;

		echo json_encode(compact('label', 'prices', 'singleprice'));
	}

}
