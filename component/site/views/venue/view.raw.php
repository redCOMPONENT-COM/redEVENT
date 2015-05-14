<?php
/**
 * @package    Redevent.Site
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Venue View class
 *
 * @package  Redevent.Site
 * @since    0.9
 */
class RedeventViewVenue extends RViewSite
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 */
	public function display($tpl = null)
	{
		$row = $this->Get('Data');

		$address = array();

		if ($row->street)
		{
			$address[] = $row->street;
		}

		if ($row->city)
		{
			$address[] = $row->city;
		}

		if ($row->country)
		{
			$address[] = RedeventHelperCountries::getCountryName($row->country);
		}

		$address = implode(',', $address);

		$resp = new stdclass;
		$resp->name = $row->venue;
		$resp->address = $address;
		$resp->latitude = ($row->latitude || $row->longitude ? $row->latitude : 'null');
		$resp->longitude = ($row->latitude || $row->longitude ? $row->longitude : 'null');

		echo json_encode($resp);

		JFactory::getApplication()->close();
	}
}
