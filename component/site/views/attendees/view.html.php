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
 * HTML Attendees View class of the redEVENT component
 *
 * @package Joomla
 * @subpackage redEVENT
 * @since 2.0
 */
class RedeventViewAttendees extends JView
{
	/**
	 * Creates the output for the details view
	 *
 	 * @since 0.9
	 */
	function display($tpl = null)
	{
		if ($this->getLayout() == 'manageattendees')
		{
			return $this->_displayManageAttendees($tpl);
		}
		
		global $mainframe;
		
		$document 	= JFactory::getDocument();
		$user		= JFactory::getUser();
		$elsettings = redEVENTHelper::config();
		$acl        = UserAcl::getInstance();
		$uri        = & JFactory::getURI();
		
		$row		= $this->get('Session');
		$registers	      = $this->get('Registers');
		$register_fields	= $this->get('FormFields');
				
		//get menu information
		$menu		= & JSite::getMenu();
		$item    	= $menu->getActive();
		if (!$item) $item = $menu->getDefault();
		
		$params 	= & $mainframe->getParams('com_redevent');

		//Check if the id exists
		if (!$row)
		{
			return JError::raiseError( 404, JText::sprintf( 'Session not found' ) );
		}

		//Print
		$pop	= JRequest::getBool('pop');

		$params->def( 'page_title', $row->title. ' - '. JText::_( 'ATTENDEES' ));

		if ( $pop ) {
			$params->set( 'popup', 1 );
		}

		$print_link = JRoute::_('index.php?option=com_redevent&view=attendees&xref='.$row->slug.'&pop=1&tmpl=component');

		//pathway
		$pathway 	= & $mainframe->getPathWay();
		$pathway->addItem( JText::_( 'ATTENDEES' ), JRoute::_('index.php?option=com_redevent&view=attendees&xref='.$row->slug));
		
		//set page title and meta stuff
		$document->setTitle( $item->name.' - '.$row->title );
		        
    //manages attendees
    $manage_attendees  = $this->get('ManageAttendees');
    
    $unreg_check = redEVENTHelper::canUnregister($row->xref);
    
    // lists
    $lists = array();

    /* Call the state object */
    $state =& $this->get( 'state' );

    /* Get the values from the state object that were inserted in the model's construct function */
    $lists['order_Dir'] = $state->get( 'filter_order_Dir' );
    $lists['order']     = $state->get( 'filter_order' );            
		
		//assign vars to jview
		$this->assignRef('row',              $row);
		$this->assignRef('params',           $params);
    $this->assignRef('user',             $user);
		$this->assignRef('manage_attendees', $manage_attendees);
		$this->assignRef('print_link',       $print_link);
		$this->assignRef('registers',        $registers);
		$this->assignRef('registersfields',  $register_fields);
		$this->assignRef('elsettings', 			 $elsettings);
		$this->assignRef('item', 					   $item);
		$this->assignRef('unreg_check',      $unreg_check);
		$this->assignRef('action',           $uri->toString());
		$this->assignRef('lists',            $lists);
		
		$tpl = JRequest::getVar('tpl', $tpl);
		
		parent::display($tpl);
	}

/**
	 * Creates the output for the manage attendees layout
	 *
 	 * @since 2.0
	 */
	function _displayManageAttendees($tpl = null)
	{	
		$mainframe = &JFactory::getApplication();			
		$document 	= JFactory::getDocument();
		$user		= JFactory::getUser();
		$elsettings = redEVENTHelper::config();
		$uri        = & JFactory::getURI();
		
		$row		= $this->get('Session');
		$registers	= $this->get('Registers');
		$regcheck	= $this->get('ManageAttendees');
				
		//get menu information
		$menu		= & JSite::getMenu();
		$item    	= $menu->getActive();
		if (!$item) $item = $menu->getDefault();
		
		$params 	= & $mainframe->getParams('com_redevent');

		//Check if the session exists
		if (!$row)
		{
			return JError::raiseError( 404, JText::sprintf( 'Session not found' ) );
		}

		//Check if user has access to the attendees management
		if (!$regcheck) {
			$mainframe->redirect('index.php',JText::_('Only logged users can access this page'), 'error');
		}

		//add css file
    if (!$params->get('custom_css')) {
      $document->addStyleSheet($this->baseurl.'/components/com_redevent/assets/css/redevent.css');
    }
    else {
      $document->addStyleSheet($params->get('custom_css'));     
    }
		$document->addCustomTag('<!--[if IE]><style type="text/css">.floattext{zoom:1;}, * html #eventlist dd { height: 1%; }</style><![endif]-->');

		$params->def( 'page_title', JText::_( 'Manage attendees' ));

		//pathway
		$pathway 	= & $mainframe->getPathWay();
		$pathway->addItem( JText::_( 'Manage attendees' ). ' - '.$row->title, JRoute::_('index.php?option=com_redevent&view=attendees&layout=manageattendees&id='.$row->slug));
		
		//Check user if he can edit
		$manage_attendees  = $this->get('ManageAttendees');
		
			// add javascript code for cancel button on attendees layout.
			JHTML::_('behavior.mootools');
			$js = " window.addEvent('domready', function(){
		            $$('.unreglink').addEvent('click', function(event){
		                  if (confirm('".JText::_('CONFIRM CANCEL REGISTRATION')."')) {
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
      $document->addScriptDeclaration($js);
		
		//set page title and meta stuff
		$document->setTitle( JText::_( 'Manage attendees' ). ' - '.$row->title );				    
		
    // lists
    $lists = array();

    /* Call the state object */
    $state =& $this->get( 'state' );

    /* Get the values from the state object that were inserted in the model's construct function */
    $lists['order_Dir'] = $state->get( 'filter_order_Dir' );
    $lists['order']     = $state->get( 'filter_order' );    
        
		//assign vars to jview
		$this->assignRef('row', 					$row);
		$this->assignRef('params' , 				$params);
    $this->assignRef('user' ,         $user);
		$this->assignRef('registers' , 				$registers);
		$this->assignRef('elsettings' , 			$elsettings);
		$this->assignRef('item' , 					$item);
    $this->assignRef('manage_attendees' , $manage_attendees);
		$this->assignRef('action',           $uri->toString());
		$this->assignRef('lists',            $lists);
				
		parent::display($tpl);
	}
}
?>