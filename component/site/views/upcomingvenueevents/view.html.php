<?php
/**
 * @package    Redevent.Site
 *
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * HTML View class for the Upcoming events View
 *
 * @package  Redevent.Site
 * @since    2.5
 */
class RedeventViewUpcomingvenueevents extends RViewSite
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 */
	public function display($tpl = null)
	{
		$mainframe = JFactory::getApplication();
		$option = JRequest::getCmd('option');

		// Initialize variables
		$document = JFactory::getDocument();
		$menu = $mainframe->getMenu();
		$elsettings = RedeventHelper::config();
		$item = $menu->getActive();
		$params = $mainframe->getParams('com_redevent');
		$uri = JFactory::getURI();
		$pop = JRequest::getBool('pop');
		$upcomingvenueevents = $this->get('UpcomingVenueEvents');

		$model_venueevents = RModel::getFrontInstance('Venueevents');
		$venue = $model_venueevents->getVenue();

		if (!$venue)
		{
			echo JText::_('COM_REDEVENT_ACCESS_NOT_ALLOWED');

			return false;
		}

		if (!$params->get('custom_css'))
		{
			RHelperAsset::load('redevent.css');
		}
		else
		{
			$document->addStyleSheet($params->get('custom_css'));
		}

		$document->addCustomTag('<!--[if IE]><style type="text/css">.floattext{zoom:1;}, * html #eventlist dd { height: 1%; }</style><![endif]-->');

		// Add rss link
		$link = '&format=feed';
		$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
		$document->addHeadLink(JRoute::_($link . '&type=rss'), 'alternate', 'rel', $attribs);

		// Add needed scripts if the lightbox effect
		JHTML::_('behavior.modal');

		// Add alternate feed link
		$link = 'index.php?option=com_redevent&view=venueevents&format=feed&id=' . $venue->id;
		$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
		$document->addHeadLink(JRoute::_($link . '&type=rss'), 'alternate', 'rel', $attribs);
		$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
		$document->addHeadLink(JRoute::_($link . '&type=atom'), 'alternate', 'rel', $attribs);

		$pathway = $mainframe->getPathWay();

		$task = JRequest::getVar('task');

		if ($task == 'archive')
		{
			$pathway->addItem(
				JText::_('COM_REDEVENT_ARCHIVE') . ' - ' . $venue->venue,
				JRoute::_('index.php?option=' . $option . '&view=upcomingvenueevents&task=archive&id=' . $venue->slug)
			);
			$link = JRoute::_('index.php?option=com_redevent&view=upcomingvenueevents&id=' . $venue->slug . '&task=archive');
			$print_link = JRoute::_(
				'index.php?option=com_redevent&view=upcomingvenueevents&id=' . $venue->slug . '&task=archive&pop=1&tmpl=component'
			);
			$pagetitle = $venue->venue . ' - ' . JText::_('COM_REDEVENT_ARCHIVE');
		}
		else
		{
			$pathway->addItem($venue->venue, JRoute::_('index.php?option=' . $option . '&view=upcomingvenueevents&id=' . $venue->slug));
			$link = JRoute::_('index.php?option=com_redevent&view=upcomingvenueevents&id=' . $venue->slug);
			$print_link = JRoute::_('index.php?option=com_redevent&view=upcomingvenueevents&id=' . $venue->slug . '&pop=1&tmpl=component');
			$pagetitle = $venue->venue . ' - ' . JText::_('COM_REDEVENT_UPCOMING_EVENTS_TITLE');
		}

		// Set Page title
		$document->setTitle($pagetitle);
		$document->setMetadata('keywords', $venue->meta_keywords);
		$document->setDescription(strip_tags($venue->meta_description));

		// Check if the user has access to the form
		$dellink = JFactory::getUser()->authorise('re.createevent');

		// Printfunction
		$params->def('print', !$mainframe->getCfg('hidePrint'));
		$params->def('icons', $mainframe->getCfg('icons'));

		if ($pop)
		{
			$params->set('popup', 1);
		}

		// Generate Venuedescription
		if (!empty($venue->locdescription))
		{
			// Execute plugins
			$venuedescription = JHTML::_('content.prepare', $venue->locdescription);
		}

		// Build the url
		if (!empty($venue->url) && strtolower(substr($venue->url, 0, 7)) != "http://")
		{
			$venue->url = 'http://' . $venue->url;
		}

		// Prepare the url for output
		if (strlen(htmlspecialchars($venue->url, ENT_QUOTES)) > 35)
		{
			$venue->urlclean = substr(htmlspecialchars($venue->url, ENT_QUOTES), 0, 35) . '...';
		}
		else
		{
			$venue->urlclean = htmlspecialchars($venue->url, ENT_QUOTES);
		}

		// Create flag
		if ($venue->country)
		{
			$venue->countryimg = RedeventHelperCountries::getCountryFlag($venue->country);
		}

		$this->assignRef('upcomingvenueevents', $upcomingvenueevents);
		$this->assignRef('params', $params);
		$this->assignRef('venue', $venue);
		$this->assignRef('venuedescription', $venuedescription);
		$this->assignRef('elsettings', $elsettings);
		$this->assignRef('item', $item);
		$this->assignRef('pagetitle', $pagetitle);
		$this->assignRef('task', $task);
		$this->assignRef('print_link', $print_link);
		$this->assignRef('dellink', $dellink);
		$this->assign('action', JRoute::_(RedeventHelperRoute::getUpcomingVenueEventsRoute($venue->slug)));

		return parent::display($tpl);
	}
}
