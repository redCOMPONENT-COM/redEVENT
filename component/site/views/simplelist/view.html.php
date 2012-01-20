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
		$mainframe = &JFactory::getApplication();
		
		//initialize variables
		$document 	= & JFactory::getDocument();
		$elsettings = & redEVENTHelper::config();
		$menu		  = & JSite::getMenu();
		$item    	= $menu->getActive();
		$params 	= & $mainframe->getParams();
		$uri 		  = & JFactory::getURI();
		$pathway 	= & $mainframe->getPathWay();
		$state    =& $this->get( 'state' );

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
		$customs 	= & $this->get('ListCustomFields');
		$customsfilters 	= & $this->get('CustomFilters');
		$pagination =& $this->get('Pagination');

		
		//are events available?
		if (!$rows) {
			$noevents = 1;
		} else {
			$noevents = 0;
		}

		//params
		$params->def( 'page_title', (isset($item->name)? $item->name : JText::_('COM_REDEVENT_Events')));

		if ( $pop ) {//If printpopup set true
			$params->set( 'popup', 1 );
		}
		
		if ( $task == 'archive' ) {
			$pathway->addItem(JText::_('COM_REDEVENT_ARCHIVE' ), JRoute::_('index.php?option=com_redevent&view=simplelist&task=archive') );
			$print_link = JRoute::_('index.php?option=com_redevent&view=simplelist&task=archive&tmpl=component&pop=1');
			$pagetitle = $params->get('page_title').' - '.JText::_('COM_REDEVENT_ARCHIVE' );
		} else {
			$print_link = JRoute::_('index.php?option=com_redevent&view=simplelist&tmpl=component&pop=1');
			$pagetitle = $params->get('page_title');
		}
		$thumb_link = RedeventHelperRoute::getSimpleListRoute(null, 'thumb');
		$list_link = RedeventHelperRoute::getSimpleListRoute();
		
		//Set Page title
		$this->document->setTitle($pagetitle);

		//Check if the user has access to the form
		$maintainer = ELUser::ismaintainer();
		$genaccess 	= ELUser::validate_user( $elsettings->get('evdelrec'), $elsettings->get('delivereventsyes') );

		if ($maintainer || $genaccess ) $dellink = 1;

		//add alternate feed link
		$link    = 'index.php?option=com_redevent&view=simplelist&format=feed';
		$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
		$document->addHeadLink(JRoute::_($link.'&type=rss'), 'alternate', 'rel', $attribs);
		$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
		$document->addHeadLink(JRoute::_($link.'&type=atom'), 'alternate', 'rel', $attribs);
		
		//create select lists
		$lists	= $this->_buildSortLists();
		
    $filter_customs   = $state->get('filter_customs');
				
		$this->assign('lists',  $lists);

		$this->assignRef('rows',        $rows);
		$this->assignRef('customs',     $customs);
		$this->assignRef('customsfilters',     $customsfilters);
		$this->assignRef('task',        $task);
		$this->assignRef('noevents',    $noevents);
		$this->assignRef('print_link',  $print_link);
		$this->assignRef('params',      $params);
		$this->assignRef('dellink',     $dellink);
		$this->assignRef('pageNav',     $pagination);
		$this->assignRef('elsettings',  $elsettings);
		$this->assignRef('pagetitle',   $pagetitle);
		$this->assignRef('config',      $elsettings);
		$this->assignRef('thumb_link',  $thumb_link);
		$this->assignRef('list_link',   $list_link);
		$this->assign('filter_customs',      $filter_customs);
		
		$cols = explode(',', $params->get('lists_columns', 'date, title, venue, city, category'));
		$cols = redEVENTHelper::validateColumns($cols);
		$this->assign('columns',        $cols);

		parent::display($tpl);
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
		$uri = & JFactory::getURI();
		
		// remove previously set filter in get
		$uri->delVar('filter');
		$uri->delVar('filter_type');
		$uri->delVar('filter_category');
		$uri->delVar('filter_venuecategory');
		$uri->delVar('filter_venue');
		$uri->delVar('filter_event');	  
		$uri->delVar('filtercustom');
		
		$elsettings = & redEVENTHelper::config();
		$params     = $app->getParams();
		
		$filter_order		= JRequest::getCmd('filter_order', 'x.dates');
		$filter_order_Dir	= JRequest::getWord('filter_order_Dir', 'ASC');

		$state = $this->get('state');
		
    $filter          = $state->get('filter');
    $filter_type     = $state->get('filter_type');
    $filter_category = $state->get('filter_category');
    $filter_venue    = $state->get('filter_venue');
    $filter_event    = $state->get('filter_event');
    
    $this->assign('action', $uri->toString());
      
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
		// category filter
		$options = array(JHTML::_('select.option', '', JText::_('COM_REDEVENT_FILTER_SELECT_CATEGORY') ));
		$options = array_merge($options, $this->get('CategoriesOptions'));
		$lists['categoryfilter'] = JHTML::_('select.genericlist', $options, 'filter_category', 'size="1" class="inputbox dynfilter"', 'value', 'text', $filter_category );
		
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
		
		$lists['order_Dir']   = $filter_order_Dir;
		$lists['order']       = $filter_order;
		$lists['filter']      = $filter;
		$lists['filter_type'] = $sortselect;

		return $lists;
	}
}