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

	private $gateways;

	private $details;

	/**
	 * filters available gateways based on venue
	 *
	 * @param   array   &$gateways  current allowed gateways
	 * @param   object  $details    submission details
	 *
	 * @return boolean
	 */
	public function onFilterGateways(&$gateways, $details)
	{
		$this->gateways = $gateways;
		$this->details = $details;

		if ($this->isRedformSelectPage())
		{
			$this->filterRedformSelectPage();
		}
		elseif ($this->isRegistrationForm())
		{
			$this->filterRegistrationPage();
		}

		$gateways = $this->gateways;

		return true;
	}

	/**
	 * Return true is current page is redFORM payment select
	 *
	 * @return bool
	 */
	protected function isRedformSelectPage()
	{
		$input = JFactory::getApplication()->input;

		return $input->get('option') == 'com_redform'
			&& $input->get('controller') == 'payment'
			&& $input->get('task') == 'select';
	}

	/**
	 * Filter for redFORM select payment page
	 *
	 * @return void
	 */
	protected function filterRedformSelectPage()
	{
		// First check that if this is redEVENT submission
		if (!$this->details->integration == 'redevent')
		{
			return true;
		}

		$allowed = $this->getAttendeesVenueAllowedGateways();
		$this->keepAllowed($allowed);
	}

	/**
	 * Return allowed gateways for venue associated to registration
	 *
	 * @return mixed
	 */
	protected function getAttendeesVenueAllowedGateways()
	{
		$db      = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('v.params');
		$query->from('#__redevent_register AS r');
		$query->join('INNER', '#__redevent_event_venue_xref AS x ON x.id = r.xref');
		$query->join('INNER', '#__redevent_venues AS v ON v.id = x.venueid');
		$query->where('r.submit_key = ' . $db->quote($this->details->key));

		$db->setQuery($query);
		$res = $db->loadResult();

		$registry = new JRegistry;
		$registry->loadString($res);

		return $registry->get('allowed_gateways');
	}

	/**
	 * Return true is current page is redFORM payment select
	 *
	 * @return bool
	 */
	protected function isRegistrationForm()
	{
		$input = JFactory::getApplication()->input;

		if (isset($this->details->xref) && $this->details->xref)
		{
			return true;
		}

		return $input->get('option') == 'com_redevent'
			&& ($input->get('view') == 'details' || $input->get('view') == 'signup')
			&& $input->getInt('xref');
	}

	/**
	 * Filter for session registration page
	 *
	 * @return void
	 */
	protected function filterRegistrationPage()
	{
		$allowed = $this->getSessionVenueAllowedGateways();
		$this->keepAllowed($allowed);
	}

	/**
	 * Return allowed gateways for venue associated to session
	 *
	 * @return mixed
	 */
	protected function getSessionVenueAllowedGateways()
	{
		if (isset($this->details->xref) && $this->details->xref)
		{
			$xref = $this->details->xref;
		}
		else
		{
			$xref = JFactory::getApplication()->input->getInt('xref');
		}

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

		return $registry->get('allowed_gateways');
	}

	/**
	 * Intersect allowed gateways with current gateways
	 *
	 * @param   array  $allowed  allowed gateways
	 *
	 * @return void
	 */
	protected function keepAllowed($allowed)
	{
		if (!$allowed || !count($allowed)) // Not set or empty, so all is allowed
		{
			return true;
		}

		// Else intersect !
		$filtered = array();

		foreach ($this->gateways as $g)
		{
			if (in_array($g->value, $allowed))
			{
				$filtered[] = $g;
			}
		}

		$this->gateways = $filtered;
	}
}
