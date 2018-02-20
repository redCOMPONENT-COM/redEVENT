<?php
/**
 * @package     Redevent.Plugins
 * @subpackage  RedformPayment
 *
 * @copyright   Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Import library dependencies
jimport('joomla.event.plugin');
JLoader::import('redevent.bootstrap');
JLoader::import('redeventcart.library');

/**
 * Class plgRedform_PaymentFiltervenue
 *
 * @since  2.5
 */
class PlgRedform_PaymentFiltervenue extends JPlugin
{
	/**
	 * filters available gateways based on venue
	 *
	 * @param   array           $gateways  current allowed gateways
	 * @param   RdfPaymentInfo  $details   submission details
	 *
	 * @return boolean
	 */
	public function onFilterGateways(&$gateways, $details)
	{
		if ($xref = JFactory::getApplication()->input->getInt('xref'))
		{
			return $this->filterSession($xref, $gateways);
		}

		// First check that if this is redEVENT submission
		if ($details->integration == 'redeventcart')
		{
			$this->filterRedeventcart($gateways, $details);
		}
		elseif ($details->integration !== 'redevent')
		{
			return true;
		}

		$db      = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('v.params');
		$query->from('#__redevent_register AS r');
		$query->join('INNER', '#__redevent_event_venue_xref AS x ON x.id = r.xref');
		$query->join('INNER', '#__redevent_venues AS v ON v.id = x.venueid');
		$query->where('r.submit_key = ' . $db->quote($details->submit_key));

		$db->setQuery($query);
		$res = $db->loadResult();

		$registry = new JRegistry;
		$registry->loadString($res);

		$gateways = $this->filter($gateways, $registry->get('allowed_gateways'));

		return true;
	}

	/**
	 * Filter for redEVENTCART integration
	 *
	 * @param   array           &$gateways  current allowed gateways
	 * @param   RdfPaymentInfo  $details    submission details
	 *
	 * @return void
	 */
	private function filterRedeventcart(&$gateways, $details)
	{
		$cart = $details->getCart();
		$sid = current($cart->getSubmitters())->id;

		$participant = RedeventcartEntityParticipant::getInstance();
		$participant->loadBySubmitterId($sid);

		$venue = $participant->getSession()->getVenue();
		$params = new \Joomla\Registry\Registry($venue->params);

		$gateways = $this->filter($gateways, $params->get('allowed_gateways'));
	}

	/**
	 * Filter from session reference
	 *
	 * @param   int    $xref      session id
	 * @param   array  $gateways  gateways array
	 *
	 * @return boolean
	 */
	private function filterSession($xref, &$gateways)
	{
		$db      = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('v.params');
		$query->from('#__redevent_event_venue_xref AS x');
		$query->join('INNER', '#__redevent_venues AS v ON v.id = x.venueid');
		$query->where('x.id = ' . $db->quote($xref));

		$db->setQuery($query);
		$res = $db->loadResult();

		$registry = new JRegistry;
		$registry->loadString($res);

		$gateways = $this->filter($gateways, $registry->get('allowed_gateways'));

		return true;
	}

	/**
	 * Do the filtering
	 *
	 * @param   array  $gateways  input list
	 * @param   array  $allowed   allowed ids
	 *
	 * @return array filtered list
	 */
	private function filter($gateways, $allowed)
	{
		if (empty($allowed))
		{
			return $gateways;
		}

		return array_filter(
			$gateways,
			function($element) use ($allowed) {
				return in_array($element->value, $allowed);
			}
		);
	}
}
