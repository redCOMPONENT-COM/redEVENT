<?php
/**
 * @version 1.1 $Id: view.html.php 407 2007-09-21 16:03:39Z schlu $
 * @package Joomla
 * @subpackage redEVENT
 * @copyright (C) 2005 - 2008 Christoph Lukes
 * @license GNU/GPL, see LICENSE.php
 * EventList is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * EventList is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with EventList; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the Venues View
 *
 * @package Joomla
 * @subpackage redEVENT
 * @since 0.9
 */
class RedeventViewVenuesmap extends RViewSite
{
	/**
	 * Creates the Venuesview
	 *
	 * @since 0.9
	 */
	function display( $tpl = null )
	{
		$mainframe = & JFactory::getApplication();

		$document 	= & JFactory::getDocument();
		$elsettings = & RedeventHelper::config();
    $uri        = & JFactory::getURI();

		//get menu information
		$menu		= & JSite::getMenu();
		$item    	= $menu->getActive();
		$params 	= & $mainframe->getParams();
    $task       = JRequest::getWord('task');

    if ($item) {
      $title = $item->title;
    }
    else {
      $title = JText::_('COM_REDEVENT_Venues_map');
      $params->set('page_title', $title);
    }

		//add css file
    if (!$params->get('custom_css')) {
      $document->addStyleSheet('media/com_redevent/css/redevent.css');
    }
    else {
      $document->addStyleSheet($params->get('custom_css'));
    }
		$document->addCustomTag('<!--[if IE]><style type="text/css">.floattext{zoom:1;}, * html #eventlist dd { height: 1%; }</style><![endif]-->');

		// specific for eventsmap
    JHTML::_('behavior.framework');
		$document->addScript('https://maps.google.com/maps/api/js?sensor=false');
    $document->addScript($this->baseurl.'/components/com_redevent/assets/js/markermanager.js');
    $document->addScript($this->baseurl.'/components/com_redevent/assets/js/venuesmap.js');

    // filters
    $vcat = $mainframe->getUserStateFromRequest('com_redevent.venuesmap.vcat', 'vcat', $params->def('vcat', 0), 'int');
    $cat = $mainframe->getUserStateFromRequest('com_redevent.venuesmap.cat', 'cat', $params->def('cat', 0), 'int');
    $custom = $this->get('CustomFilters');
    $filter_customs   = $mainframe->getUserStateFromRequest('com_redevent.venuesmap.filter_customs', 'filtercustom', array(), 'array');

		$rows 		= & $this->get('Data');
    $countries = $this->get('Countries');

		//Add needed scripts if the lightbox effect is enabled
		JHTML::_('behavior.modal');

		//pathway
		$pathway 	= & $mainframe->getPathWay();

	  if ( $task == 'archive' ) {
      $pathway->addItem(JText::_('COM_REDEVENT_ARCHIVE' ), JRoute::_('index.php?view=venues&task=archive') );
      $pagetitle = $params->get('page_title').' - '.JText::_('COM_REDEVENT_ARCHIVE' );
      $print_link = JRoute::_('index.php?view=venues&task=archive&pop=1&tmpl=component');
    } else {
      $pagetitle = $params->get('page_title');
      $print_link = JRoute::_('index.php?view=venues&pop=1&tmpl=component');
    }

    $lists = array();

    // venues categories
    $vcat_options = RedeventHelper::getVenuesCatOptions(false);
    array_unshift($vcat_options, JHTML::_('select.option', 0, JText::_('COM_REDEVENT_ALL')));
    $lists['venuescats'] = JHTML::_('select.genericlist', $vcat_options, 'vcat', '', 'value', 'text', $vcat);

    // events categories
    $cat_options = RedeventHelper::getEventsCatOptions(false);
    array_unshift($cat_options, JHTML::_('select.option', 0, JText::_('COM_REDEVENT_ALL')));
    $lists['eventscats'] = JHTML::_('select.genericlist', $cat_options, 'cat', '', 'value', 'text', $cat);

    $lists['customfilters'] = $custom;

		//Set Page title
		$this->document->setTitle($pagetitle);
   	$document->setMetadata('keywords', $pagetitle );

   	$ajaxurl = 'index.php?option=com_redevent&view=venue&tmpl=component';
   	if ($vcat) {
   		$ajaxurl .= '&vcat=' . $vcat;
   	}
    if ($cat) {
      $ajaxurl .= '&cat=' . $vcat;
    }

		$this->assignRef('rows' , 				$rows);
    $this->assignRef('countries' ,    $countries);
		$this->assignRef('params' , 			$params);
		$this->assignRef('item' , 				$item);
		$this->assignRef('elsettings' , 	$elsettings);
		$this->assignRef('task' , 				$task);
		$this->assignRef('pagetitle' , 		$pagetitle);
    $this->assignRef('lists' ,        $lists);
    $this->assign('action',           JRoute::_('index.php?option=com_redevent&view=venuesmap'));
    $this->assign('ajaxurl',          $ajaxurl);
		$this->assign('filter_customs', 			$filter_customs);

		parent::display($tpl);
	}
}
