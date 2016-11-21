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
	 * Add to cart
	 *
	 * @return void
	 */
	public function addtocart()
	{
		// Load redeventcart Library
		JLoader::import('redeventcart.library');

		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$bundleId = $this->input->getInt('id');
		$participants = $this->input->get('participants', null, array());
		$sessionpricegroupIds = $this->input->get('sessionpricegroup', null, array());

		$app = JFactory::getApplication();

		$cartId = $app->getUserState('redeventcart.cart', 0);
		$currentCart = RedeventcartEntityCart::load($cartId);
		$app->setUserState('redeventcart.cart', $currentCart->get('id'));

		$added = array();

		try
		{
			foreach ($sessionpricegroupIds as $k => $sessionpricegroupId)
			{
				$sessionPriceGroup = RedeventEntitySessionpricegroup::load($sessionpricegroupId);

				for ($i = 0; $i < $participants[$k]; $i++)
				{
					$added[] = $currentCart->addParticipant($sessionPriceGroup->xref, $sessionpricegroupId);
				}
			}

			$this->setRedirect(RedeventcartHelperRoute::getCartRoute());
		}
		catch (Exception $e)
		{
			if ($added)
			{
				foreach ($added as $participantId)
				{
					$currentCart->deleteParticipant($participantId);
				}
			}

			$this->setRedirect(RedeventHelperRoute::getBundleRoute($bundleId), $e->getMessage(), 'error');
		}
	}
}
