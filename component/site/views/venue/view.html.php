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
 * @subpackage redEVENT
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
    if ($this->getLayout() == 'gmap')
    {
    	return $this->_displayGmap($tpl);
    }

    $elsettings = & redeventHelper::config();


		//Get Data from the model
		$row 		= $this->Get('Data');
		JFilterOutput::objectHTMLSafe( $row, ENT_QUOTES, array('locdescription','locmage', 'countryimg', 'targetlink') );
    $row->target = JRoute::_('index.php?option=com_redevent&view=venueevents&id=' . $row->slug);
		//echo json_encode($row);

		$this->assignRef('row', $row);
    $this->assignRef('elsettings', $elsettings);

		parent::display($tpl);
	}

	function _displayGmap( $tpl=null )
	{
		$params 	= & JFactory::getApplication()->getParams();

		JHTML::_('behavior.mootools');
		$document 	= & JFactory::getDocument();
		$document->addScript('https://maps.google.com/maps/api/js?sensor=false');
		$document->addScript(JURI::root().'/components/com_redevent/assets/js/venuemap.js');
		JText::script("COM_REDEVENT_GET_DIRECTIONS");

		//add css file
    if (!$params->get('custom_css')) {
      $document->addStyleSheet('media/com_redevent/css/redevent.css');
    }
    else {
      $document->addStyleSheet($params->get('custom_css'));
    }

		//Get Data from the model
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
			$address[] = RedeventHelperCountries::getCountryName($row->country);
		}
		$address = implode(',', $address);
		JFilterOutput::objectHTMLSafe( $row, ENT_QUOTES, array('locdescription','locmage', 'countryimg', 'targetlink') );
    $row->target = JRoute::_('index.php?option=com_redevent&view=venueevents&id=' . $row->slug);

		$this->assignRef('row', $row);
		$this->assignRef('address', $address);

		parent::display($tpl);
	}
}
