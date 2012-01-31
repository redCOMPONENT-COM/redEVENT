<?php
/**
 * @version 1.0 $Id: archive.php 30 2009-05-08 10:22:21Z roland $
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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view');

/**
 * HTML View class for redevent component
 *
 * @static
 * @package		redevent
 * @since 2.0
 */
class RedeventViewSessions extends JView
{
	function display($tpl = null)
	{
		$mainframe = &JFactory::getApplication();
		$option = JRequest::getCmd('option');

		$document = &JFactory::getDocument();
		
		
		ELAdmin::setMenu();
        
		$db		 = &JFactory::getDBO();
		$uri	 = &JFactory::getURI();
		$state = &$this->get('state');
		$settings = ELAdmin::config();

		$filter_order		= $state->get('filter_order');
		$filter_order_Dir	= $state->get('filter_order_Dir');
		$search          = $state->get('search');
		$filter_state    = $state->get('filter_state');
		$filter_featured = $state->get('filter_featured');
		$eventid         = $state->get('eventid');
		$venueid         = $state->get('venueid');

		// Get data from the model
		$items		= & $this->get( 'Data' );
		$event		= & $this->get( 'Event' );
		$venue		= & $this->get( 'Venue' );
		$total		= & $this->get( 'Total' );
		$pagination = & $this->get( 'Pagination' );
		
		// table ordering
		$lists['order_Dir'] = $filter_order_Dir;
		$lists['order']     = $filter_order;

		// search filter
		$lists['search']= $search;
		
		//publish unpublished filter
		$options = array( JHTML::_('select.option', '', ' - '.JText::_('COM_REDEVENT_Select_state').' - '),
		                  JHTML::_('select.option', 'published', JText::_('COM_REDEVENT_Published')),
		                  JHTML::_('select.option', 'unpublished', JText::_('COM_REDEVENT_Unpublished')),
		                  JHTML::_('select.option', 'archived', JText::_('COM_REDEVENT_Archived')),
		                  JHTML::_('select.option', 'notarchived', JText::_('COM_REDEVENT_Not_archived')),
		                  );
		$lists['state']	= JHTML::_('select.genericlist', $options, 'filter_state', 'class="inputbox" onchange="submitform();" size="1"', 'value', 'text', $filter_state );
		
		//featured filter
		$options = array( JHTML::_('select.option', '', ' - '.JText::_('COM_REDEVENT_Select_featured').' - '),
		                  JHTML::_('select.option', 'featured', JText::_('Com_redevent_Featured')),
		                  JHTML::_('select.option', 'unfeatured', JText::_('Com_redevent_not_Featured')),
		                  );
		$lists['featured']	= JHTML::_('select.genericlist', $options, 'filter_featured', 'class="inputbox" onchange="submitform();" size="1"', 'value', 'text', $filter_featured );
		
		$options = $this->get('groupsoptions');
		$options = array_merge(array(JHTML::_('select.option', '', ' - '.JText::_('COM_REDEVENT_SESSIONS_filter_group_select').' - ')), $options);
		$lists['filter_group']	= JHTML::_('select.genericlist', $options, 'filter_group', 'class="inputbox" onchange="submitform();" size="1"', 'value', 'text', $state->get('filter_group'));
		
		$options = array(JHTML::_('select.option', 0, JText::_('COM_REDEVENT_SESSIONS_filter_group_select_view')), 
		                 JHTML::_('select.option', 1, JText::_('COM_REDEVENT_SESSIONS_filter_group_select_manage')), );
		$lists['filter_group_manage']	= JHTML::_('select.genericlist', $options, 'filter_group_manage', 'class="inputbox" onchange="submitform();" size="1"', 'value', 'text', $state->get('filter_group_manage'));
		
		$document->addStyleSheet('components/com_redevent/assets/css/redeventbackend.css');
		
		// Set toolbar items for the page
		if ($eventid) {
			$document->setTitle(JText::sprintf('COM_REDEVENT_PAGETITLE_SESSIONS_EVENT', $event->title));
			JToolBarHelper::title(   JText::sprintf( 'COM_REDEVENT_TITLE_SESSIONS_EVENT', $event->title ), 're-sessions' );
		}
		else {
			$document->setTitle(JText::sprintf('COM_REDEVENT_PAGETITLE_SESSIONS'));
			JToolBarHelper::title(   JText::sprintf( 'COM_REDEVENT_TITLE_SESSIONS'), 're-sessions' );			
		}
		if ($event && $event->id) {
			JToolBarHelper::addNewX();
		}
		JToolBarHelper::custom('copy', 'copy', 'copy', 'copy', true);
		JToolBarHelper::editListX();
		JToolBarHelper::deleteList(JText::_('COM_REDEVENT_SESSIONS_REMOVE_CONFIRM_MESSAGE'));
		JToolBarHelper::spacer();
		JToolBarHelper::publish();
		JToolBarHelper::unpublish();
		JToolBarHelper::archiveList();
		JToolBarHelper::spacer();
		JToolBarHelper::custom('featured', 'featured', 'featured', 'COM_REDEVENT_FEATURE', true);
		JToolBarHelper::custom('unfeatured', 'unfeatured', 'unfeatured', 'COM_REDEVENT_UNFEATURE', true);
		JToolBarHelper::spacer();
		JToolBarHelper::custom('back', 'back', 'back', 'COM_REDEVENT_BACK', false);
		
		// event 
		JHTML::_('behavior.modal', 'a.modal');
		$js = "
		window.addEvent('domready', function(){
		
			$('ev-reset-button').addEvent('click', function(){
				$('eventid').value = 0;
				$('eventid_name').value = '".JText::_('COM_REDEVENT_SESSIONS_EVENT_FILTER_ALL')."';
				$('adminForm').submit();
			});
			
			$('venue-reset-button').addEvent('click', function(){
				$('venueid').value = 0;
				$('venueid_name').value = '".JText::_('COM_REDEVENT_SESSIONS_VENUE_FILTER_ALL')."';
				$('adminForm').submit();
			});
			
		});
		
		function elSelectEvent(id, title, field) {
			document.getElementById(field).value = id;
			document.getElementById(field+'_name').value = title;
			document.getElementById('sbox-window').close();
			$('adminForm').submit();
		}
		
		function elSelectVenue(id, title, field) {
			document.getElementById(field).value = id;
			document.getElementById(field+'_name').value = title;
			document.getElementById('sbox-window').close();
			$('adminForm').submit();
		}";
		$document->addScriptDeclaration($js);
		
		$uri->delVar('eventid');
		$uri->delVar('venueid');
		$this->assignRef('user',		JFactory::getUser());
		$this->assignRef('lists',		$lists);
		$this->assignRef('items',		$items);
		$this->assignRef('event',		$event);
		$this->assignRef('venue',		$venue);
		$this->assignRef('eventid',		$eventid);
		$this->assignRef('venueid',		$venueid);
		$this->assignRef('settings',    $settings);
		$this->assignRef('pagination',	$pagination);
		$this->assignRef('request_url',	$uri->toString());

		parent::display($tpl);
	}
	
	/**
	 * returns toggle image link for session feature
	 * 
	 * @param object $row
	 * @param int $i
	 * @return string html
	 */
	function featured( &$row, $i )
	{
		$params = array('border' => 0);
		$img 	= $row->featured ? JHTML::image('administrator/components/com_redevent/assets/images/icon-16-featured.png', JText::_('COM_REDEVENT_SESSION_FEATURED'), array('border' => 0))
		                       : JHTML::image('administrator/components/com_redevent/assets/images/icon-16-unfeatured.png', JText::_('COM_REDEVENT_SESSION_NOT_FEATURED'), array('border' => 0));
		$task 	= $row->featured ? 'unfeatured' : 'featured';
		$action = $row->featured ? JText::_( 'COM_REDEVENT_FEATURE' ) : JText::_( 'COM_REDEVENT_UNFEATURE' );

		$href = '
		<a href="javascript:void(0);" onclick="return listItemTask(\'cb'. $i .'\',\''. $task .'\')" title="'. $action .'">
		'. $img .'</a>'
		;

		return $href;
	}
}
