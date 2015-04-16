<?php
/**
 * @version 1.0 $Id$
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
 * HTML View class for the Upcoming events View
 *
 * @package Joomla
 * @subpackage redEVENT
 * @since 0.9
*/
class RedeventViewUpcomingvenueevents extends RViewSite
{
	/**
	 * Creates the Venueevents View
	 *
	 * @since 0.9
	 */
	function display( $tpl = null )
	{
		$mainframe = &JFactory::getApplication();
		$option = JRequest::getCmd('option');

		//initialize variables
		$document 	= & JFactory::getDocument();
		$menu		= & JSite::getMenu();
		$elsettings = & RedeventHelper::config();
		$item    	= $menu->getActive();
		$params 	= & $mainframe->getParams('com_redevent');
		$uri 		= & JFactory::getURI();
		$pop			= JRequest::getBool('pop');
		$upcomingvenueevents = $this->get('UpcomingVenueEvents');

		$model_venueevents = RModel::getFrontInstance('Venueevents');
		$venue	 	= $model_venueevents->getVenue();

		//add css file
		if (!$params->get('custom_css')) {
			$document->addStyleSheet('media/com_redevent/css/redevent.css');
		}
		else {
			$document->addStyleSheet($params->get('custom_css'));
		}
		$document->addCustomTag('<!--[if IE]><style type="text/css">.floattext{zoom:1;}, * html #eventlist dd { height: 1%; }</style><![endif]-->');

		/* Add rss link */
		$link	= '&format=feed';
		$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
		$document->addHeadLink(JRoute::_($link.'&type=rss'), 'alternate', 'rel', $attribs);

		// Add needed scripts if the lightbox effect
		JHTML::_('behavior.modal');

		//add alternate feed link
		$link    = 'index.php?option=com_redevent&view=venueevents&format=feed&id='.$venue->id;
		$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
		$document->addHeadLink(JRoute::_($link.'&type=rss'), 'alternate', 'rel', $attribs);
		$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
		$document->addHeadLink(JRoute::_($link.'&type=atom'), 'alternate', 'rel', $attribs);

		//pathway
		$pathway 	= & $mainframe->getPathWay();

		$task = JRequest::getVar('task');

		//create the pathway
		if ($task == 'archive') {
			$pathway->addItem( JText::_('COM_REDEVENT_ARCHIVE' ).' - '.$venue->venue, JRoute::_('index.php?option='.$option.'&view=upcomingvenueevents&task=archive&id='.$venue->slug));
			$link = JRoute::_( 'index.php?option=com_redevent&view=upcomingvenueevents&id='.$venue->slug.'&task=archive' );
			$print_link = JRoute::_('index.php?option=com_redevent&view=upcomingvenueevents&id='. $venue->slug .'&task=archive&pop=1&tmpl=component');
			$pagetitle = $venue->venue.' - '.JText::_('COM_REDEVENT_ARCHIVE' );
		} else {
			$pathway->addItem( $venue->venue, JRoute::_('index.php?option='.$option.'&view=upcomingvenueevents&id='.$venue->slug));
			$link = JRoute::_( 'index.php?option=com_redevent&view=upcomingvenueevents&id='.$venue->slug );
			$print_link = JRoute::_('index.php?option=com_redevent&view=upcomingvenueevents&id='. $venue->slug .'&pop=1&tmpl=component');
			$pagetitle = $venue->venue.' - '.JText::_( 'COM_REDEVENT_UPCOMING_EVENTS_TITLE' );
		}

		//set Page title
		$document->setTitle($pagetitle);
		$document->setMetadata('keywords', $venue->meta_keywords );
		$document->setDescription( strip_tags($venue->meta_description) );

		//Check if the user has access to the form
		$dellink = JFactory::getUser()->authorise('re.createevent');

		//Printfunction
		$params->def( 'print', !$mainframe->getCfg( 'hidePrint' ) );
		$params->def( 'icons', $mainframe->getCfg( 'icons' ) );

		if ( $pop ) {
			$params->set( 'popup', 1 );
		}

		//Generate Venuedescription
		if (!empty ($venue->locdescription)) {
			//execute plugins
			$venuedescription = JHTML::_('content.prepare', $venue->locdescription);
		}

		//build the url
		if(!empty($venue->url) && strtolower(substr($venue->url, 0, 7)) != "http://") {
			$venue->url = 'http://'.$venue->url;
		}

		//prepare the url for output
		if (strlen(htmlspecialchars($venue->url, ENT_QUOTES)) > 35) {
			$venue->urlclean = substr( htmlspecialchars($venue->url, ENT_QUOTES), 0 , 35).'...';
		} else {
			$venue->urlclean = htmlspecialchars($venue->url, ENT_QUOTES);
		}

		//create flag
		if ($venue->country) {
			$venue->countryimg = RedeventHelperOutput::getFlag( $venue->country );
		}

		$this->assignRef('upcomingvenueevents' , $upcomingvenueevents);
		$this->assignRef('params' , $params);
		$this->assignRef('venue' , $venue);
		$this->assignRef('venuedescription' , 		$venuedescription);
		$this->assignRef('elsettings' , 			$elsettings);
		$this->assignRef('item' , 					$item);
		$this->assignRef('pagetitle' , 				$pagetitle);
		$this->assignRef('task' , 					$task);
		$this->assignRef('print_link' , 			$print_link);
		$this->assignRef('dellink' , 				$dellink);
		$this->assign('action',   JRoute::_(RedeventHelperRoute::getUpcomingVenueEventsRoute($venue->slug)));

		parent::display($tpl);
	}
}
