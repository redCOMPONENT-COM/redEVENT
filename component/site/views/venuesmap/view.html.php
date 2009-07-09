<?php
/**
 * @version 1.1 $Id: view.html.php 407 2007-09-21 16:03:39Z schlu $
 * @package Joomla
 * @subpackage EventList
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
 * @subpackage EventList
 * @since 0.9
 */
class RedeventViewVenuesmap extends JView
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
		$elsettings = & redEVENTHelper::config();
    $uri        = & JFactory::getURI();

		//get menu information
		$menu		= & JSite::getMenu();
		$item    	= $menu->getActive();
		$params 	= & $mainframe->getParams();
    $task       = JRequest::getWord('task');
    	
    if ($item) {
      $title = $item->name;
    }
    else {
      $title = JText::_('Venues map');
      $params->set('page_title', $title);
    }

		//add css file
		$document->addStyleSheet($this->baseurl.'/components/com_redevent/assets/css/eventlist.css');
		$document->addCustomTag('<!--[if IE]><style type="text/css">.floattext{zoom:1;}, * html #eventlist dd { height: 1%; }</style><![endif]-->');

		// specific for eventsmap
    JHTML::_('behavior.mootools');
    $document->addScript('http://www.google.com/jsapi?key='.trim($elsettings->gmapkey));
    $document->addScript($this->baseurl.'/components/com_redevent/assets/js/venuesmap.js');
    $document->addScript($this->baseurl.'/components/com_redevent/assets/js/markermanager.js');
    $document->addScript($this->baseurl.'/components/com_redevent/assets/js/labeled_marker.js');
    
    // filters
    $vcat = $mainframe->getUserStateFromRequest('com_redevent.venuemap.vcat', 'vcat', $params->def('vcat', 0), 'int');
    $cat = $mainframe->getUserStateFromRequest('com_redevent.venuemap.cat', 'cat', $params->def('cat', 0), 'int');

		$rows 		= & $this->get('Data');
				
//    $cmodel = &JModel::getInstance('countriesmap', 'RedeventModel');
//    $countries = $cmodel->getData();
    $countries = $this->get('Countries');

		//Add needed scripts if the lightbox effect is enabled
		if ($elsettings->lightbox == 1) {
  			JHTML::_('behavior.modal');
		}

		//pathway
		$pathway 	= & $mainframe->getPathWay();
		$pathway->setItemName(1, $title);
		
	  if ( $task == 'archive' ) {
      $pathway->addItem(JText::_( 'ARCHIVE' ), JRoute::_('index.php?view=venues&task=archive') );
      $pagetitle = $params->get('page_title').' - '.JText::_( 'ARCHIVE' );
      $print_link = JRoute::_('index.php?view=venues&task=archive&pop=1&tmpl=component');
    } else {
      $pagetitle = $params->get('page_title');
      $print_link = JRoute::_('index.php?view=venues&pop=1&tmpl=component');
    }
    
    $lists = array();
    
    // venues categories
    $vcat_options = redEVENTHelper::getVenuesCatOptions(false);
    array_unshift($vcat_options, JHTML::_('select.option', 0, JText::_('ALL')));
    $lists['venuescats'] = JHTML::_('select.genericlist', $vcat_options, 'vcat', '', 'value', 'text', $vcat);
    
    // events categories
    $cat_options = redEVENTHelper::getEventsCatOptions(false);
    array_unshift($cat_options, JHTML::_('select.option', 0, JText::_('ALL')));
    $lists['eventscats'] = JHTML::_('select.genericlist', $cat_options, 'cat', '', 'value', 'text', $cat);
    
		//Set Page title
		$mainframe->setPageTitle( $pagetitle );
   	$mainframe->addMetaTag( 'title' , $pagetitle );
   	$document->setMetadata('keywords', $pagetitle );
   	
   	$ajaxurl = 'index.php?option=com_redevent&view=venue&format=raw';
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
    $this->assign('action',           $uri->toString());
    $this->assign('ajaxurl',          $ajaxurl);

		parent::display($tpl);
	}
}
?>