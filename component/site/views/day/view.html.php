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
 * HTML View class for the Day View
 *
 * @package Joomla
 * @subpackage Redevent
 * @since 0.9
 */
class RedeventViewDay extends RViewSite
{
	/**
	 * Creates the Day View
	 *
	 * @since 0.9
	 */
	function display( $tpl = null )
	{
		$mainframe = JFactory::getApplication();

		//initialize variables
		$document 	= JFactory::getDocument();
		$elsettings = RedeventHelper::config();
		$menu		= $mainframe->getMenu();
		$item    	= $menu->getActive();
		$params 	= $mainframe->getParams();
    $uri    =JFactory::getURI();

		//add css file
    if (!$params->get('custom_css')) {
      $document->addStyleSheet('media/com_redevent/css/redevent.css');
    }
    else {
      $document->addStyleSheet($params->get('custom_css'));
    }
		$document->addCustomTag('<!--[if IE]><style type="text/css">.floattext{zoom:1;}, * html #eventlist dd { height: 1%; }</style><![endif]-->');

    // add js
    JHTML::_('behavior.framework');
    // for filter hint
    $document->addScript($this->baseurl.'/components/com_redevent/assets/js/eventslist.js');

		// get variables
		$limitstart	= JRequest::getVar('limitstart', 0, '', 'int');
		$limit		= JRequest::getVar('limit', $params->get('display_num'), '', 'int');

		$pop			= JRequest::getBool('pop');
		$pathway 		= $mainframe->getPathWay();

		//get data from model
		$rows 		= $this->get('Data');
		$customs 	= $this->get('ListCustomFields');
		$total 		= $this->get('Total');
		$day	= $this->get('Day');

		$daydate = strftime( $elsettings->get('formatdate', '%d.%m.%Y'), strtotime( $day ));

		//are events available?
		if (!$rows) {
			$noevents = 1;
		} else {
			$noevents = 0;
		}

		//params
		if ($item) $params->def( 'page_title', $item->title);

		if ( $pop ) {//If printpopup set true
			$params->set( 'popup', 1 );
		}

		$print_link = JRoute::_('index.php?view=day&tmpl=component&pop=1');

		//pathway
		$pathway->addItem($daydate, '');

		//Set Page title
		if ($item && !$item->title) {
			$document->setTitle($params->get('page_title'));
			$document->setMetadata( 'keywords' , $params->get('page_title') );
		}

		//Check if the user has access to the form
		$dellink = JFactory::getUser()->authorise('re.createevent');

		//add alternate feed link
		$link    = 'index.php?option=com_redevent&view=simplelist&format=feed';
		$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
		$document->addHeadLink(JRoute::_($link.'&type=rss'), 'alternate', 'rel', $attribs);
		$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
		$document->addHeadLink(JRoute::_($link.'&type=atom'), 'alternate', 'rel', $attribs);

		// Create the pagination object
		$page = $total - $limit;

		jimport('joomla.html.pagination');
		$pageNav = new JPagination($total, $limitstart, $limit);

		//create select lists
		$lists	= $this->_buildSortLists();

		$this->assign('lists' , 					$lists);

		$this->assignRef('rows' , 					$rows);
		$this->assignRef('customs',     $customs);
		$this->assignRef('noevents' , 				$noevents);
		$this->assignRef('print_link' , 			$print_link);
		$this->assignRef('params' , 				$params);
		$this->assignRef('dellink' , 				$dellink);
		$this->assignRef('pageNav' , 				$pageNav);
		$this->assignRef('page' , 					$page);
		$this->assignRef('elsettings' , 			$elsettings);
		$this->assignRef('lists' , 					$lists);
		$this->assignRef('daydate' , 				$daydate);
		$this->assign('action',   JRoute::_(RedeventHelperRoute::getDayRoute(JRequest::getInt('id'))));

		$cols = explode(',', $params->get('lists_columns', 'date, title, venue, city, category'));
		$cols = RedeventHelper::validateColumns($cols);
		$this->assign('columns',        $cols);

		parent::display($tpl);

	}//function ListEvents end

	/**
	 * Method to build the sortlists
	 *
	 * @access private
	 * @return array
	 * @since 0.9
	 */
	function _buildSortLists()
	{
    $app = JFactory::getApplication();

		$elsettings = RedeventHelper::config();

		$filter_order		= JRequest::getCmd('filter_order', 'x.dates');
		$filter_order_Dir	= JRequest::getWord('filter_order_Dir', 'ASC');

    $filter     = $app->getUserState('com_redevent.day.filter');
    $filter_type  = $app->getUserState('com_redevent.day.filter_type');

		$sortselects = array();
		$sortselects[]	= JHTML::_('select.option', 'title', JText::_('COM_REDEVENT_FILTER_SELECT_EVENT') );
		$sortselects[] 	= JHTML::_('select.option', 'venue', JText::_('COM_REDEVENT_FILTER_SELECT_VENUE') );
		$sortselects[] 	= JHTML::_('select.option', 'city', JText::_('COM_REDEVENT_FILTER_SELECT_CITY') );
		$sortselects[] 	= JHTML::_('select.option', 'type', JText::_('COM_REDEVENT_FILTER_SELECT_CATEGORY') );
		$sortselect 	= JHTML::_('select.genericlist', $sortselects, 'filter_type', 'size="1" class="inputbox"', 'value', 'text', $filter_type );

		$this->orderDir 	= $filter_order_Dir;
		$this->order 		= $filter_order;
		$lists['filter'] 		= $filter;
		$lists['filter_types'] 	= $sortselect;

		return $lists;
	}
}
