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
 * HTML View class for the EventList View
 *
 * @package Joomla
 * @subpackage EventList
 * @since 0.9
 */
class RedeventViewSimpleList extends JView
{
	/**
	 * Creates the Simple List View
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
		$uri 		= & JFactory::getURI();
		$pathway 	= & $mainframe->getPathWay();

		//add css file
    if (!$params->get('custom_css')) {
      $document->addStyleSheet($this->baseurl.'/components/com_redevent/assets/css/redevent.css');
    }
    else {
      $document->addStyleSheet($params->get('custom_css'));     
    }
		$document->addCustomTag('<!--[if IE]><style type="text/css">.floattext{zoom:1;}, * html #eventlist dd { height: 1%; }</style><![endif]-->');
		
		// add js
    JHTML::_('behavior.mootools');
    // for filter hint
    $document->addScript($this->baseurl.'/components/com_redevent/assets/js/eventslist.js');

		// get variables
		$task 		= JRequest::getWord('task');
		$pop		= JRequest::getBool('pop');		

		//get data from model
		$rows 	= & $this->get('Data');
		$customs 	= & $this->get('CustomFields');
		$pagination =& $this->get('Pagination');

		//are events available?
		if (!$rows) {
			$noevents = 1;
		} else {
			$noevents = 0;
		}

		//params
		$params->def( 'page_title', (isset($item->name)? $item->name : Jtext::_('Events')));

		if ( $pop ) {//If printpopup set true
			$params->set( 'popup', 1 );
		}

		//pathway
		$pathway->setItemName( 1, (isset($item->name)? $item->name : Jtext::_('Events')) );
		
		if ( $task == 'archive' ) {
			$pathway->addItem(JText::_( 'ARCHIVE' ), JRoute::_('index.php?option=com_redevent&view=simplelist&task=archive') );
			$print_link = JRoute::_('index.php?option=com_redevent&view=simplelist&task=archive&tmpl=component&pop=1');
			$pagetitle = $params->get('page_title').' - '.JText::_( 'ARCHIVE' );
		} else {
			$print_link = JRoute::_('index.php?option=com_redevent&view=simplelist&tmpl=component&pop=1');
			$pagetitle = $params->get('page_title');
		}
		
		//Set Page title
		$mainframe->setPageTitle( $pagetitle );
   	$mainframe->addMetaTag( 'title' , $pagetitle );

		//Check if the user has access to the form
		$maintainer = ELUser::ismaintainer();
		$genaccess 	= ELUser::validate_user( $elsettings->evdelrec, $elsettings->delivereventsyes );

		if ($maintainer || $genaccess ) $dellink = 1;

		//add alternate feed link
		$link    = 'index.php?option=com_redevent&view=simplelist&format=feed';
		$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
		$document->addHeadLink(JRoute::_($link.'&type=rss'), 'alternate', 'rel', $attribs);
		$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
		$document->addHeadLink(JRoute::_($link.'&type=atom'), 'alternate', 'rel', $attribs);
		
		//create select lists
		$lists	= $this->_buildSortLists();
		
		if ($lists['filter']) {
//			//$uri->setVar('filter', JRequest::getString('filter'));
//			//$filter		= $mainframe->getUserStateFromRequest('com_redevent.eventlist.filter', 'filter', '', 'string');
//			$uri->setVar('filter', $lists['filter']);
//			$uri->setVar('filter_type', JRequest::getString('filter_type'));
//		} else {
//			$uri->delVar('filter');
//			$uri->delVar('filter_type');
		}
		
		$this->assign('lists',  $lists);
    $this->assign('action', str_replace('&', '&amp;', $uri->toString()));

		$this->assignRef('rows',        $rows);
		$this->assignRef('customs',     $customs);
		$this->assignRef('task',        $task);
		$this->assignRef('noevents',    $noevents);
		$this->assignRef('print_link',  $print_link);
		$this->assignRef('params',      $params);
		$this->assignRef('dellink',     $dellink);
		$this->assignRef('pageNav',     $pagination);
		$this->assignRef('elsettings',  $elsettings);
		$this->assignRef('pagetitle',   $pagetitle);

		parent::display($tpl);

	}

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

    $filter     = $app->getUserState('com_redevent.eventlist.filter');
    $filter_type  = $app->getUserState('com_redevent.eventlist.filter_type');
      
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
		$lists['filter_types'] 	= $sortselect;

		return $lists;
	}
}
?>