<?php
/**
 * @version 1.0 $Id$
 * @package Joomla
 * @subpackage redFORM
 * @copyright redFORM (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license GNU/GPL, see LICENSE.php
 * redEVENT is based on EventList made by Christoph Lukes from schlu.net
 * redEVENT can be downloaded from www.redcomponent.com
 * redEVENT is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * redEVENT is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with redEVENT; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// Import library dependencies
jimport('joomla.event.plugin');

class plgRedform_PaymentFiltervenue extends JPlugin {

	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);
	}

	/**
	 * filters available gateways based on venue
	 *
	 * @param   array   $gateways  current allowed gateways
	 * @param   object  $details   submission details
	 *
	 * @return boolean
	 */
	public function onFilterGateways(&$gateways, $details)
	{
		// First check that if this is redEVENT submission
		if (!isset($details->integration) || $details->integration !== 'redevent')
		{
			return true;
		}

		$db      = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('v.params');
		$query->from('#__redevent_register AS r');
		$query->join('INNER', '#__redevent_event_venue_xref AS x ON x.id = r.xref');
		$query->join('INNER', '#__redevent_venues AS v ON v.id = x.venueid');
		$query->where('r.submit_key = ' . $db->quote($details->key));

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
