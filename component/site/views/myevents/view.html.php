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
defined('_JEXEC') or die ('Restricted access');

jimport('joomla.application.component.view');

/**
 * HTML View class for the my events View
 *
 * @package Joomla
 * @subpackage redevent
 * @since 2.0
 */
class RedeventViewMyevents extends JView
{
    /**
     * Creates the MyItems View
     *
     * @since 1.0
     */
    function display($tpl = null)
    {
        $mainframe = &JFactory::getApplication();
			
        $user = & JFactory::getUser();
        if (!$user->get('id')) {
        	$mainframe->redirect('index.php',JText::_('COM_REDEVENT_Only_logged_users_can_access_this_page'), 'error');
        }
        
        //initialize variables
        $document   = & JFactory::getDocument();
        $elsettings = & redEVENTHelper::config();
        $pathway    = & $mainframe->getPathWay();
        $params     = & $mainframe->getParams();
        $uri        = & JFactory::getURI();
				$acl        = & UserACl::getInstance();

        $menu = & JSite::getMenu();
        $item = $menu->getActive();


        //add css file
		    if (!$params->get('custom_css')) {
		      $document->addStyleSheet($this->baseurl.'/components/com_redevent/assets/css/redevent.css');
		    }
		    else {
		      $document->addStyleSheet($params->get('custom_css'));     
		    }
        $document->addCustomTag('<!--[if IE]><style type="text/css">.floattext{zoom:1;}, * html #eventlist dd { height: 1%; }</style><![endif]-->');

        
				JHTML::_('behavior.mootools');
				$js = " window.addEvent('domready', function(){
			            $$('.deletelink').addEvent('click', function(event){
			                  if (confirm('".JText::_('COM_REDEVENT_CONFIRM_DELETE_DATE')."')) {
	                      	return true;
		                    }
		                    else {
		                    	if (event.preventDefault) {
		                    		event.preventDefault();
													} else {
														event.returnValue = false;
													}
													return false;
	                    	}
			            });		            
		        }); ";
				$document->addScriptDeclaration( $js );
				
        // get variables
        $limitstart   = JRequest::getVar('limitstart', 0, '', 'int');
        $limit        = $mainframe->getUserStateFromRequest('com_redevent.myevents.limit', 'limit', $params->def('display_num', 5), 'int');
        $filter_event = $mainframe->getUserStateFromRequest('com_redevent.myevents.filter_event', 'filter_event', 0, 'int');
        $task = JRequest::getWord('task');
        $pop = JRequest::getBool('pop');

        //get data from model
        $events = & $this->get('Events');
        $venues = & $this->get('Venues');
        $attending = & $this->get('Attending');
        $groups = & $this->get('Groups');

        //paginations
        $events_pageNav = & $this->get('EventsPagination');
        $venues_pageNav = & $this->get('VenuesPagination');
        $attending_pageNav = & $this->get('AttendingPagination');

        //params
        $params->def('page_title', $item->title);

        if ($pop)
        {//If printpopup set true
            $params->set('popup', 1);
        }

        //Set Page title
        $pagetitle = $params->get('page_title', JText::_('COM_REDEVENT_MY_EVENTS'));
				$this->document->setTitle($pagetitle);

        //create select lists
        $lists = $this->_buildSortLists();

        if ($lists['filter'])
        {
            $uri->setVar('filter', $lists['filter']);
            $uri->setVar('filter_type', JRequest::getString('filter_type'));
        } else
        {
            $uri->delVar('filter');
            $uri->delVar('filter_type');
        }
        // events filter
        $hasManagedEvents = false;
        $options = array(JHTML::_('select.option', 0, JText::_('COM_REDEVENT_select_event')));
        if ($ev = $this->get('EventsOptions')) {
        	$hasManagedEvents = count($ev);
        	$options = array_merge($options, $ev);
        }
        $lists['filter_event'] = JHTML::_('select.genericlist', $options, 'filter_event', 'onchange="document.getElementById(\'adminForm\').submit();"', 'value', 'text', $filter_event);
        

        $this->assign('action', $uri->toString());

        $this->assignRef('events', $events);
        $this->assignRef('venues', $venues);
        $this->assignRef('attending', $attending);
        $this->assignRef('groups', $groups);
        $this->assignRef('task', $task);
        $this->assignRef('print_link', $print_link);
        $this->assignRef('params', $params);
        $this->assignRef('dellink', $dellink);
        $this->assignRef('events_pageNav', $events_pageNav);
        $this->assignRef('venues_pageNav', $venues_pageNav);
        $this->assignRef('attending_pageNav', $attending_pageNav);
        $this->assignRef('elsettings', $elsettings);
        $this->assignRef('pagetitle',  $pagetitle);
        $this->assignRef('lists',      $lists);
        $this->assignRef('acl',         $acl);
        $this->assignRef('hasManagedEvents', $hasManagedEvents);
        $this->assignRef('canAddXref',  $acl->canAddXref());
        $this->assignRef('canAddEvent', $acl->canAddEvent());
        $this->assignRef('canAddVenue', $acl->canAddVenue());
        
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
        $elsettings = & redEVENTHelper::config();

        $filter_order = JRequest::getCmd('filter_order', 'x.dates');
        $filter_order_Dir = JRequest::getWord('filter_order_Dir', 'ASC');

        $filter = $this->escape(JRequest::getString('filter'));
        $filter_type = JRequest::getString('filter_type');

        $sortselects = array ();
				$sortselects[]	= JHTML::_('select.option', 'title', JText::_('COM_REDEVENT_FILTER_SELECT_EVENT') );
				$sortselects[] 	= JHTML::_('select.option', 'venue', JText::_('COM_REDEVENT_FILTER_SELECT_VENUE') );
				$sortselects[] 	= JHTML::_('select.option', 'city', JText::_('COM_REDEVENT_FILTER_SELECT_CITY') );
				$sortselects[] 	= JHTML::_('select.option', 'type', JText::_('COM_REDEVENT_FILTER_SELECT_CATEGORY') );

        $sortselect = JHTML::_('select.genericlist', $sortselects, 'filter_type', 'size="1" class="inputbox"', 'value', 'text', $filter_type);

        $lists['order_Dir'] = $filter_order_Dir;
        $lists['order'] = $filter_order;
        $lists['filter'] = $filter;
        $lists['filter_types'] = $sortselect;

        return $lists;
    }
    
	/**
	 * Creates the xref edit button
	 *
	 * @param int xref id
	 * @since 2.0
	 */
	function xrefeditbutton($id, $xref)
	{
		$acl = &UserAcl::getInstance();
		if (!$acl->canEditXref($xref)) {
			return '';
		}
		JHTML::_('behavior.tooltip');

		$image = JHTML::_('image.site', 'calendar_edit.png', 'components/com_redevent/assets/images/', NULL, NULL, JText::_('COM_REDEVENT_EDIT_XREF' ));

		$overlib = JText::_('COM_REDEVENT_EDIT_XREF_TIP' );
		$text = JText::_('COM_REDEVENT_EDIT_XREF' );

		$link 	= 'index.php?option=com_redevent&view=editevent&layout=eventdate&id='.$id.'&xref='.$xref;
		$output	= '<a href="'.JRoute::_($link).'" class="editlinktip hasTip" title="'.$text.'::'.$overlib.'">'.$image.'</a>';

		return $output;
	}
    
	/**
	 * Creates the event edit button
	 *
	 * @param int xref id
	 * @since 2.0
	 */
	function eventeditbutton($id, $xref)
	{
		$acl = &UserAcl::getInstance();
		if (!$acl->canEditEvent($id)) {
			return '';
		}
		JHTML::_('behavior.tooltip');

		$image = JHTML::_('image.site', 'calendar_edit.png', 'components/com_redevent/assets/images/', NULL, NULL, JText::_('COM_REDEVENT_EDIT_EVENT' ));

		$overlib = JText::_('COM_REDEVENT_EDIT_EVENT_TIP' );
		$text = JText::_('COM_REDEVENT_EDIT_EVENT' );

		$link 	= RedeventHelperRoute::getEditEventRoute($id, $xref).'&referer=myevents';
		$output	= '<a href="'.JRoute::_($link).'" class="editlinktip hasTip" title="'.$text.'::'.$overlib.'">'.$image.'</a>';

		return $output;
	}

	/**
	 * Creates the xref edit button
	 *
	 * @param int xref id
	 * @since 2.0
	 */
	function xrefdeletebutton($id)
	{
		JHTML::_('behavior.tooltip');
    $document = & JFactory::getDocument();

		$image = JHTML::_('image.site', 'no.png', 'components/com_redevent/assets/images/', NULL, NULL, JText::_('COM_REDEVENT_DELETE_XREF' ));

		$overlib = JText::_('COM_REDEVENT_DELETE_XREF_TIP' );
		$text = JText::_('COM_REDEVENT_DELETE_XREF' );

		$link 	= 'index.php?option=com_redevent&task=deletexref&xref='.$id;
		$output	= '<a href="'.JRoute::_($link).'" class="deletelink hasTip" title="'.$text.'::'.$overlib.'">'.$image.'</a>';

		return $output;
	}
	
	/**
	 * Creates the attendees edit button
	 *
	 * @param int xref id
	 * @since 2.0
	 */
	function xrefattendeesbutton($id)
	{
		JHTML::_('behavior.tooltip');

		$image = JHTML::_('image.site', 'attendees.png', 'components/com_redevent/assets/images/', NULL, NULL, JText::_('COM_REDEVENT_EDIT_ATTENDEES' ));

		$overlib = JText::_('COM_REDEVENT_EDIT_ATTENDEES_TIP' );
		$text = JText::_('COM_REDEVENT_EDIT_ATTENDEES' );
		$link 	= RedeventHelperRoute::getManageAttendees($id, 'manageattendees');
		$output	= '<a href="'.JRoute::_($link).'" class="editlinktip hasTip" title="'.$text.'::'.$overlib.'">'.$image.'</a>';

		return $output;
	}

	/**
	 * Creates the xref edit button
	 *
	 * @param int $Itemid
	 * @param int $id
	 * @param array $params
	 * @param int $allowedtoedit
	 * @param string $view
	 * @since 0.9
	 */
	function venueeditbutton($id)
	{
		JHTML::_('behavior.tooltip');

		$image = JHTML::_('image.site', 'calendar_edit.png', 'components/com_redevent/assets/images/', NULL, NULL, JText::_('COM_REDEVENT_EDIT_VENUE' ));

		$overlib = JText::_('COM_REDEVENT_EDIT_VENUE_TIP' );
		$text = JText::_('COM_REDEVENT_EDIT_VENUE' );

		$link 	= 'index.php?option=com_redevent&view=editvenue&id='.$id;
		$output	= '<a href="'.JRoute::_($link).'" class="editlinktip hasTip" title="'.$text.'::'.$overlib.'">'.$image.'</a>';

		return $output;
	}
}
?>
