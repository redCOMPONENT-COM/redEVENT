<?php
/**
 * @package    Redevent.Site
 *
 * @copyright  Copyright (C) 2008 - 2014 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

class RedeventControllerEditvenue extends RControllerForm
{
	/**
	 * Get the JRoute object for a redirect to list.
	 *
	 * @param   string  $append  An optionnal string to append to the route
	 *
	 * @return  JRoute  The JRoute object
	 */
	protected function getRedirectToListRoute($append = null)
	{
		$returnUrl = $this->input->get('return');

		if ($returnUrl)
		{
			$returnUrl = base64_decode($returnUrl);

			return JRoute::_($returnUrl . $append, false);
		}
		else
		{
			return JRoute::_(RedeventHelperRoute::getVenuesRoute() . $append, false);
		}
	}
}
