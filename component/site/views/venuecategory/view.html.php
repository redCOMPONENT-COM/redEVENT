<?php
/**
 * @version 1.0 $Id: view.html.php 736 2009-08-31 09:51:56Z julien $
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
 * HTML View class for the venue category View
 *
 * @package Joomla
 * @subpackage redevent
 * @since 2.0
 */
class RedeventViewVenuecategory extends JView
{
	/**
	 * Creates the venue category View
	 *
	 * @since 2.0
	 */
	function display( $tpl=null ) 
	{
		$mainframe = &JFactory::getApplication();
		$option = JRequest::getCmd('option');

		//initialize variables
		$document 	= & JFactory::getDocument();
		$menu		= & JSite::getMenu();
		$elsettings = & redEVENTHelper::config();
		$item    	= $menu->getActive();
		$params 	= & $mainframe->getParams();
		$uri 		= & JFactory::getURI();
		$pathway 	= & $mainframe->getPathWay();
		
		/* Check if the item is an object */
		if (!is_object($item)) {
			$item = new StdClass;
			$item->title = '';
		}
		
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
		
		// Request variables
		$limitstart		= JRequest::getInt('limitstart');
		$limit       	= $mainframe->getUserStateFromRequest('com_redevent.venuecategory.limit', 'limit', $params->def('display_num', 0), 'int');
		$task 			= JRequest::getWord('task');
		$pop			= JRequest::getBool('pop');
		
		//get data from model
		$rows 		= & $this->get('Data');
		$category 	= & $this->get('Category');
		$total 		= & $this->get('Total');
		$customs 	= & $this->get('ListCustomFields');

		//are events available?
		if (!$rows) {
			$noevents = 1;
		} else {
			$noevents = 0;
		}

		//does the category exist
		if ($category->id == 0) {
			return JError::raiseError( 404, JText::sprintf( 'COM_REDEVENT_Category_d_not_found', $category->id ) );
		}

		//Set Meta data
		if (!$item->title) $document->setTitle( $category->name );
		else $document->setTitle( $item->title.' - '.$category->name );
    	$document->setMetadata( 'keywords', $category->meta_keywords );
    	$document->setDescription( strip_tags($category->meta_description) );

    	//Print function
		$params->def( 'print', !$mainframe->getCfg( 'hidePrint' ) );
		$params->def( 'icons', $mainframe->getCfg( 'icons' ) );

		if ( $pop ) {
			$params->set( 'popup', 1 );
		}
		
		//add alternate feed link
		$link    = 'index.php?option=com_redevent&view=venuecategory&format=feed&id='.$category->id;
		$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
		$document->addHeadLink(JRoute::_($link.'&type=rss'), 'alternate', 'rel', $attribs);
		$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
		$document->addHeadLink(JRoute::_($link.'&type=atom'), 'alternate', 'rel', $attribs);
		
		if ($task == 'archive') {
			$pathway->addItem( JText::_('COM_REDEVENT_ARCHIVE' ).' - '.$category->name, JRoute::_('index.php?option='.$option.'&view=venuecategory&task=archive&id='.$category->slug));
			$link = JRoute::_( 'index.php?option=com_redevent&view=venuecategory&task=archive&id='.$category->slug );
			$print_link = JRoute::_( 'index.php?option=com_redevent&view=venuecategory&id='. $category->id .'&task=archive&pop=1&tmpl=component');
		} else {
			$pathway->addItem( $category->name, JRoute::_('index.php?option='.$option.'&view=venuecategory&id='.$category->slug));
			$link = JRoute::_( 'index.php?option=com_redevent&view=venuecategory&id='.$category->slug );
			$print_link = JRoute::_( 'index.php?option=com_redevent&view=venuecategory&id='. $category->id .'&pop=1&tmpl=component');
		}

		//Check if the user has access to the form
		$maintainer = ELUser::ismaintainer();
		$genaccess 	= ELUser::validate_user( $elsettings->get('evdelrec'), $elsettings->get('delivereventsyes') );

		if ($maintainer || $genaccess ) $dellink = 1;

		// Create the pagination object
		jimport('joomla.html.pagination');
		$pageNav = new JPagination($total, $limitstart, $limit);

		//Generate Categorydescription
		if (empty ($category->description)) {
			$description = JText::_('COM_REDEVENT_NO_DESCRIPTION' );
		} else {
			//execute plugins
			$description = JHTML::_('content.prepare', $category->description);
		}

		//create select lists
		$lists	= $this->_buildSortLists($elsettings);
		$this->assign('lists', 						$lists);
    $this->assign('action',   str_replace('&', '&amp;', $uri->toString()));

		$this->assignRef('rows' , 					$rows);
		$this->assignRef('customs',         $customs);
		$this->assignRef('noevents' ,       $noevents);
		$this->assignRef('category' , 				$category);
		$this->assignRef('print_link' , 			$print_link);
		$this->assignRef('params' , 				$params);
		$this->assignRef('dellink' , 				$dellink);
		$this->assignRef('task' , 					$task);
		$this->assignRef('description' , 		$description);
		$this->assignRef('pageNav' , 				$pageNav);
		$this->assignRef('elsettings' , 			$elsettings);
		$this->assignRef('item' , 					$item);
		
		$cols = explode(',', $params->get('lists_columns', 'date, title, venue, city, category'));
		$cols = redEVENTHelper::validateColumns($cols);
		$this->assign('columns',        $cols);
		
		parent::display($tpl);
	}

	function _buildSortLists($elsettings)
	{
    $app = & JFactory::getApplication();
    
		// Table ordering values
		$filter_order		= JRequest::getCmd('filter_order', 'x.dates');
		$filter_order_Dir	= JRequest::getCmd('filter_order_Dir', 'ASC');

    $filter     = $app->getUserState('com_redevent.venuecategory.filter');
    $filter_type  = $app->getUserState('com_redevent.venuecategory.filter_type');

		$sortselects = array();
		$sortselects[]	= JHTML::_('select.option', 'title', JText::_('COM_REDEVENT_FILTER_SELECT_EVENT') );
		$sortselects[] 	= JHTML::_('select.option', 'venue', JText::_('COM_REDEVENT_FILTER_SELECT_VENUE') );
		$sortselects[] 	= JHTML::_('select.option', 'city', JText::_('COM_REDEVENT_FILTER_SELECT_CITY') );
		$sortselects[] 	= JHTML::_('select.option', 'type', JText::_('COM_REDEVENT_FILTER_SELECT_CATEGORY') );
		$sortselect 	= JHTML::_('select.genericlist', $sortselects, 'filter_type', 'size="1" class="inputbox"', 'value', 'text', $filter_type );

		$lists['order_Dir'] 	= $filter_order_Dir;
		$lists['order'] 		= $filter_order;
		$lists['filter'] 		= $filter;
		$lists['filter_type'] 	= $sortselect;

		return $lists;
	}
}
