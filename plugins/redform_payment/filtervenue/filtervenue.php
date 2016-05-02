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
	 * @param   array           &$gateways  current allowed gateways
	 * @param   RdfPaymentInfo  $details    submission details
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
		if ($details->integration !== 'redevent')
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

		$allowed = $registry->get('allowed_gateways');

		if (!$allowed || !count($allowed))
		{
			return true;
		}

		// Intersect !
		$filtered = array();

		foreach ($gateways as $g)
		{
			if (in_array($g->value, $allowed))
			{
				$filtered[] = $g;
			}
		}

		$gateways = $filtered;

		return true;
	}

	/**
	 * Filter from session reference
	 *
	 * @param   int    $xref       session id
	 * @param   array  &$gateways  gateways array
	 *
	 * @return bool
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

		$allowed = $registry->get('allowed_gateways');

		if (!$allowed || !count($allowed))
		{
			return true;
		}

		// Intersect !
		$filtered = array();

		foreach ($gateways as $g)
		{
			if (in_array($g->value, $allowed))
			{
				$filtered[] = $g;
			}
		}

		$gateways = $filtered;

		return true;
	}
}
