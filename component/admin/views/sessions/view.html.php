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
		global $mainframe, $option;

		$document = &JFactory::getDocument();
		
		
		ELAdmin::setMenu();
        
		$db		 = &JFactory::getDBO();
		$uri	 = &JFactory::getURI();
		$state = &$this->get('state');
		$settings = ELAdmin::config();

		$filter_order		= $state->get('filter_order');
		$filter_order_Dir	= $state->get('filter_order_Dir');
		$search				  = $state->get('search');
		$filter_state		= $state->get('filter_state');
		$filter_featured		= $state->get('filter_featured');

		// Get data from the model
		$items		= & $this->get( 'Data' );
		$event		= & $this->get( 'Event' );
		$total		= & $this->get( 'Total' );
		$pagination = & $this->get( 'Pagination' );
		
		// table ordering
		$lists['order_Dir'] = $filter_order_Dir;
		$lists['order']     = $filter_order;

		// search filter
		$lists['search']= $search;
		
		//publish unpublished filter
		$options = array( JHTML::_('select.option', '', JText::_('- Select state -')),
		                  JHTML::_('select.option', 'published', JText::_('Published')),
		                  JHTML::_('select.option', 'unpublished', JText::_('Unpublished')),
		                  JHTML::_('select.option', 'archived', JText::_('Archived')),
		                  JHTML::_('select.option', 'notarchived', JText::_('Not archived')),
		                  );
		$lists['state']	= JHTML::_('select.genericlist', $options, 'filter_state', 'class="inputbox" onchange="submitform();" size="1"', 'value', 'text', $filter_state );
		
		//featured filter
		$options = array( JHTML::_('select.option', '', JText::_('- Select featured -')),
		                  JHTML::_('select.option', 'featured', JText::_('Com_redevent_Featured')),
		                  JHTML::_('select.option', 'unfeatured', JText::_('Com_redevent_not_Featured')),
		                  );
		$lists['featured']	= JHTML::_('select.genericlist', $options, 'filter_featured', 'class="inputbox" onchange="submitform();" size="1"', 'value', 'text', $filter_featured );
		
		$document->addStyleSheet('components/com_redevent/assets/css/redeventbackend.css');
		$document->setTitle(JText::sprintf('COM_REDEVENT_PAGETITLE_SESSIONS', $event->title));
		
		// Set toolbar items for the page
		JToolBarHelper::title(   JText::sprintf( 'COM_REDEVENT_TITLE_SESSIONS', $event->title ), 're-sessions' );
		JToolBarHelper::addNewX();
		JToolBarHelper::custom('copy', 'copy', 'copy', 'copy', true);
		JToolBarHelper::editListX();
		JToolBarHelper::deleteList();
		JToolBarHelper::spacer();
		JToolBarHelper::publish();
		JToolBarHelper::unpublish();
		JToolBarHelper::archiveList();
		JToolBarHelper::spacer();
		JToolBarHelper::custom('featured', 'featured', 'featured', 'COM_REDEVENT_FEATURE', true);
		JToolBarHelper::custom('unfeatured', 'unfeatured', 'unfeatured', 'COM_REDEVENT_UNFEATURE', true);
		JToolBarHelper::spacer();
		JToolBarHelper::custom('back', 'back', 'back', 'COM_REDEVENT_BACK', false);
		
		$this->assignRef('user',		JFactory::getUser());
		$this->assignRef('lists',		$lists);
		$this->assignRef('items',		$items);
		$this->assignRef('event',		$event);
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
?>
