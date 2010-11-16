<?php
/**
 * @version 1.0 $Id: output.class.php 392 2009-07-06 17:02:09Z julien $
 * @package Joomla
 * @subpackage redEVENT
 * @copyright redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
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

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the Editevents View
 *
 * @package Joomla
 * @subpackage EventList
 * @since 0.9
 */
class RedeventViewVenue extends JView
{
	/**
	 * Creates the output for venue submissions
	 *
	 * @since 0.5
	 * @param int $tpl
	 */
	function display( $tpl=null )
	{		
    return $this->_displayGmap($tpl);
	}
	
	function _displayGmap( $tpl=null )
	{
		$row 		= $this->Get('Data');
//		echo '<pre>';print_r($row); echo '</pre>';exit;

		$address = array();
		if ($row->street) {
			$address[] = $row->street;
		}
		if ($row->city) {
			$address[] = $row->city;
		}
		if ($row->country) {
			$address[] = redEVENTHelperCountries::getCountryName($row->country);
		}
		$address = implode(',', $address);
		
		$resp = new stdclass();
		$resp->name = $row->venue;
		$resp->address = $address;
		$resp->latitude = ($row->latitude || $row->longitude ? $row->latitude : 'null');
		$resp->longitude = ($row->latitude || $row->longitude ? $row->longitude : 'null');
		if (function_exists('json_encode')) {
			echo json_encode($resp);
		}
		else {
			echo JText::_('ERROR: JSON IS NOT ENABLED');
		}
		exit;
	}
}
?>