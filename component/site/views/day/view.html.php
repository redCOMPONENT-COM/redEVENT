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
 * @subpackage EventList
 * @since 0.9
 */
class RedeventViewDay extends JView
{
	/**
	 * Creates the Day View
	 *
	 * @since 0.9
	 */
	function display( $tpl = null )
	{
		global $mainframe;

		//initialize variables
		$document 	= & JFactory::getDocument();
		$elsettings = & redEVENTHelper::config();
		$menu		= & JSite::getMenu();
		$item    	= $menu->getActive();
		$params 	= & $mainframe->getParams();

		//add css file
		$document->addStyleSheet($this->baseurl.'/components/com_redevent/assets/css/eventlist.css');
		$document->addCustomTag('<!--[if IE]><style type="text/css">.floattext{zoom:1;}, * html #eventlist dd { height: 1%; }</style><![endif]-->');
		
    // add js
    JHTML::_('behavior.mootools');
    // for filter hint
    $document->addScript($this->baseurl.'/components/com_redevent/assets/js/eventsfilterhint.js');

		// get variables
		$limitstart	= JRequest::getVar('limitstart', 0, '', 'int');
		$limit		= JRequest::getVar('limit', $params->get('display_num'), '', 'int');

		$pop			= JRequest::getBool('pop');
		$pathway 		= & $mainframe->getPathWay();

		//get data from model
		$rows 		= & $this->get('Data');
		$total 		= & $this->get('Total');
		$day	= & $this->get('Day');
		
		$daydate = strftime( $elsettings->formatdate, strtotime( $day ));

		//are events available?
		if (!$rows) {
			$noevents = 1;
		} else {
			$noevents = 0;
		}

		//params
		if ($item) $params->def( 'page_title', $item->name);

		if ( $pop ) {//If printpopup set true
			$params->set( 'popup', 1 );
		}

		$print_link = JRoute::_('index.php?view=day&tmpl=component&pop=1');

		//pathway
		if ($item) $pathway->setItemName( 1, $item->name );

		//Set Page title
		if ($item && !$item->name) {
			$document->setTitle($params->get('page_title'));
			$document->setMetadata( 'keywords' , $params->get('page_title') );
		}

		//Check if the user has access to the form
		$maintainer = ELUser::ismaintainer();
		$genaccess 	= ELUser::validate_user( $elsettings->evdelrec, $elsettings->delivereventsyes );

		if ($maintainer || $genaccess ) $dellink = 1;

		//add alternate feed link
		$link    = 'index.php?option=com_redevent&view=eventlist&format=feed';
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
		$this->assignRef('noevents' , 				$noevents);
		$this->assignRef('print_link' , 			$print_link);
		$this->assignRef('params' , 				$params);
		$this->assignRef('dellink' , 				$dellink);
		$this->assignRef('pageNav' , 				$pageNav);
		$this->assignRef('page' , 					$page);
		$this->assignRef('elsettings' , 			$elsettings);
		$this->assignRef('lists' , 					$lists);
		$this->assignRef('daydate' , 				$daydate);

		parent::display($tpl);

	}//function ListEvents end

	/**
	 * Manipulate Data
	 *
	 * @access public
	 * @return object $rows
	 * @since 0.9
	 */
	function &getRows()
	{
		$count = count($this->rows);

		if (!$count) {
			return;
		}
				
		$k = 0;
		foreach($this->rows as $key => $row)
		{
			$row->odd   = $k;
			
			$this->rows[$key] = $row;
			$k = 1 - $k;
		}

		return $this->rows;
	}

	/**
	 * Method to build the sortlists
	 *
	 * @access private
	 * @return array
	 * @since 0.9
	 */
	function _buildSortLists()
	{
    $app = & JFactory::getApplication();
    
		$elsettings = & redEVENTHelper::config();
		
		$filter_order		= JRequest::getCmd('filter_order', 'x.dates');
		$filter_order_Dir	= JRequest::getWord('filter_order_Dir', 'ASC');

    $filter     = $app->getUserState('com_redevent.day.filter');
    $filter_type  = $app->getUserState('com_redevent.day.filter_type');

		$sortselects = array();
		$sortselects[]	= JHTML::_('select.option', 'title', $elsettings->titlename );
		$sortselects[] 	= JHTML::_('select.option', 'venue', $elsettings->locationname );
		$sortselects[] 	= JHTML::_('select.option', 'city', $elsettings->cityname );
		if ($elsettings->showcat) {
			$sortselects[] 	= JHTML::_('select.option', 'type', $elsettings->catfroname );
		}
		$sortselect 	= JHTML::_('select.genericlist', $sortselects, 'filter_type', 'size="1" class="inputbox"', 'value', 'text', $filter_type );

		$lists['order_Dir'] 	= $filter_order_Dir;
		$lists['order'] 		= $filter_order;
		$lists['filter'] 		= $filter;
		$lists['filter_type'] 	= $sortselect;

		return $lists;
	}
}
?>