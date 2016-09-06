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
class RedeventViewVenue extends RedeventViewFront
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
		if ($this->getLayout() == 'gmap')
		{
			return $this->displayGmap($tpl);
		}

		$this->prepareView();

		$settings = RedeventHelper::config();

		// Get Data from the model
		$row = $this->Get('Data');
		JFilterOutput::objectHTMLSafe($row, ENT_QUOTES, array('locdescription', 'locmage', 'countryimg', 'targetlink'));
		$row->target = JRoute::_('index.php?option=com_redevent&view=venueevents&id=' . $row->slug);

		$this->assignRef('row', $row);
		$this->assignRef('elsettings', $settings);

		parent::display($tpl);
	}

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 */
	public function displayGmap($tpl = null)
	{
		$config = RedeventHelper::config();
		$this->prepareView();

		$document = JFactory::getDocument();
		$document->addScript('https://maps.google.com/maps/api/js?key=' . $config->get('googlemapsApiKey'));
		RHelperAsset::load('venuemap.js');
		JText::script("COM_REDEVENT_GET_DIRECTIONS");

		// Get Data from the model
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
		JFilterOutput::objectHTMLSafe($row, ENT_QUOTES, array('locdescription', 'locmage', 'countryimg', 'targetlink'));
		$row->target = JRoute::_('index.php?option=com_redevent&view=venueevents&id=' . $row->slug);

		$this->assignRef('row', $row);
		$this->assignRef('address', $address);

		parent::display($tpl);
	}
}
