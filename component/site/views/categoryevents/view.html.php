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
 * HTML View class for the Categoryevents View
 *
 * @package Joomla
 * @subpackage redEVENT
 * @since 0.9
 */
class RedeventViewCategoryevents extends JView
{
	/**
	 * Creates the Categoryevents View
	 *
	 * @since 0.9
	 */
	function display( $tpl=null )
	{
		$mainframe = &JFactory::getApplication();
		$option = JRequest::getCmd('option');

		//initialize variables
		$document 	= & JFactory::getDocument();
		$menu		= & JSite::getMenu();
		$elsettings = & RedeventHelper::config();
		$item    	= $menu->getActive();
		$params 	= & $mainframe->getParams();
		$uri 		= & JFactory::getURI();
		$pathway 	= & $mainframe->getPathWay();

		if (!$this->getLayout()) {
			$this->setLayout($params->get('default_list_layout'));
		}

		/* Check if the item is an object */
		if (!is_object($item)) {
			$item = new StdClass;
			$item->title = '';
		}

		//add css file
    if (!$params->get('custom_css')) {
      $document->addStyleSheet('media/com_redevent/css/redevent.css');
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
		$limit       	= $mainframe->getUserStateFromRequest('com_redevent.categoryevents.limit', 'limit', $params->def('display_num', 0), 'int');
		$task 			= JRequest::getWord('task');
		$pop			= JRequest::getBool('pop');

		//get data from model
		$rows 		= & $this->get('Data');
		$customs 	= & $this->get('ListCustomFields');
		$customsfilters 	= & $this->get('CustomFilters');
		$category 	= & $this->get('Category');
		$total 		= & $this->get('Total');

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
		if (!$item->title) $document->setTitle( $category->catname );
		else $document->setTitle( $item->title.' - '.$category->catname );
    	$document->setMetadata( 'keywords', $category->meta_keywords );
    	$document->setDescription( strip_tags($category->meta_description) );

    	//Print function
		$params->def( 'print', !$mainframe->getCfg( 'hidePrint' ) );
		$params->def( 'icons', $mainframe->getCfg( 'icons' ) );

		if ( $pop ) {
			$params->set( 'popup', 1 );
		}

		//add alternate feed link
		$link    = RedeventHelperRoute::getCategoryEventsRoute($category->slug).'&format=feed';
		$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
		$document->addHeadLink(JRoute::_($link.'&type=rss'), 'alternate', 'rel', $attribs);
		$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
		$document->addHeadLink(JRoute::_($link.'&type=atom'), 'alternate', 'rel', $attribs);

		if ($task == 'archive') {
			$link = RedeventHelperRoute::getCategoryEventsRoute($category->slug, 'archive');
			$pathway->addItem( JText::_('COM_REDEVENT_ARCHIVE' ).' - '.$category->catname, JRoute::_($link));
			$print_link = JRoute::_( $link.'&pop=1&tmpl=component');
		} else {
			$link = RedeventHelperRoute::getCategoryEventsRoute($category->slug);
			$pathway->addItem( $category->catname, JRoute::_($link));
			$print_link = JRoute::_( $link.'&pop=1&tmpl=component');
		}
		$thumb_link = RedeventHelperRoute::getCategoryEventsRoute($category->slug, null, 'thumb');
		$list_link  = RedeventHelperRoute::getCategoryEventsRoute($category->slug, null, 'default');

		//Check if the user has access to the form
		$dellink = JFactory::getUser()->authorise('re.manageevents');

		// Create the pagination object
		jimport('joomla.html.pagination');
		$pageNav = new JPagination($total, $limitstart, $limit);

		//Generate Categorydescription
		if (empty ($category->catdescription)) {
			$catdescription = JText::_('COM_REDEVENT_NO_DESCRIPTION' );
		} else {
			//execute plugins
			$catdescription = JHTML::_('content.prepare', $category->catdescription);
		}

		//create select lists
		$lists	= $this->_buildSortLists($elsettings);

		$state    =& $this->get( 'state' );
		$filter_customs   = $state->get('filter_customs');

		$this->assign('lists', 						$lists);
		$this->assign('action', JRoute::_('index.php?option=com_redevent&view=categoryevents&id='.$category->id));

		$this->assignRef('rows' , 					$rows);
		$this->assignRef('customs',     $customs);
		$this->assignRef('noevents' , 				$noevents);
		$this->assignRef('category' , 				$category);
		$this->assignRef('print_link' , 			$print_link);
		$this->assignRef('params' , 				$params);
		$this->assignRef('dellink' , 				$dellink);
		$this->assignRef('task' , 					$task);
		$this->assignRef('catdescription' , 		$catdescription);
		$this->assignRef('pageNav' , 				$pageNav);
		$this->assignRef('elsettings' , 			$elsettings);
		$this->assignRef('item' , 					$item);
		$this->assignRef('config',      $elsettings);
		$this->assignRef('thumb_link',  $thumb_link);
		$this->assignRef('list_link',   $list_link);
		$this->assignRef('customsfilters',     $customsfilters);
		$this->assignRef('state',                   $state);
		$this->assign('filter_customs',      $filter_customs);

		$cols = explode(',', $params->get('lists_columns', 'date, title, venue, city, category'));
		$cols = RedeventHelper::validateColumns($cols);
		$this->assign('columns',        $cols);

		parent::display($tpl);
	}

	function _buildSortLists($elsettings)
	{
    $app    = & JFactory::getApplication();
		$params = $app->getParams();

		// Table ordering values
		$filter_order		= JRequest::getCmd('filter_order', 'x.dates');
		$filter_order_Dir	= JRequest::getCmd('filter_order_Dir', 'ASC');

		$state = $this->get('state');

    $filter          = $state->get('filter');
    $filter_type     = $state->get('filter_type');
    $filter_category = $state->get('filter_category');
    $filter_venue    = $state->get('filter_venue');
    $filter_event    = $state->get('filter_event');


		$sortselects = array();
		if ($params->get('filter_type_event', 1))	$sortselects[]	= JHTML::_('select.option', 'title', JText::_('COM_REDEVENT_FILTER_SELECT_EVENT') );
		if ($params->get('filter_type_venue', 1))	$sortselects[] 	= JHTML::_('select.option', 'venue', JText::_('COM_REDEVENT_FILTER_SELECT_VENUE') );
		if ($params->get('filter_type_city', 1))	$sortselects[] 	= JHTML::_('select.option', 'city', JText::_('COM_REDEVENT_FILTER_SELECT_CITY') );
		if ($params->get('filter_type_category', 1))	$sortselects[] 	= JHTML::_('select.option', 'type', JText::_('COM_REDEVENT_FILTER_SELECT_CATEGORY') );

		if (count($sortselects) == 0) {
			$sortselect = false;
		}
		else if (count($sortselects) == 1) {
			$sortselect = '<input type="hidden" name="filter_type" value="'.$sortselects[0]->value.'" />';
		}
		else {
			$sortselect 	= JHTML::_('select.genericlist', $sortselects, 'filter_type', 'size="1" class="inputbox"', 'value', 'text', $filter_type );
		}

		// venue filter
		$options = array(JHTML::_('select.option', '', JText::_('COM_REDEVENT_FILTER_SELECT_VENUE') ));
		$options = array_merge($options, $this->get('VenuesOptions'));
		$lists['venuefilter'] = JHTML::_('select.genericlist', $options, 'filter_venue', 'size="1" class="inputbox dynfilter"', 'value', 'text', $filter_venue );

		// events filter
		if ($params->get('lists_filter_event', 0))
		{
			$options = array(JHTML::_('select.option', '', JText::_('COM_REDEVENT_FILTER_SELECT_EVENT') ));
			$options = array_merge($options, $this->get('EventsOptions'));
			$lists['eventfilter'] = JHTML::_('select.genericlist', $options, 'filter_event', 'size="1" class="inputbox dynfilter"', 'value', 'text', $filter_event );
		}

		$lists['order_Dir'] 	= $filter_order_Dir;
		$lists['order'] 		= $filter_order;
		$lists['filter'] 		= $filter;
		$lists['filter_type'] 	= $sortselect;

		return $lists;
	}
}
