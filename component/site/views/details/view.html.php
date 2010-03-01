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
 * HTML Details View class of the EventList component
 *
 * @package Joomla
 * @subpackage EventList
 * @since 0.9
 */
class RedeventViewDetails extends JView
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
		/* Set which page to show */
		$tpl = JRequest::getVar('page', null);
		
		$document 	= JFactory::getDocument();
		$user		= JFactory::getUser();
		$dispatcher = JDispatcher::getInstance();
		$elsettings = redEVENTHelper::config();
		$acl        = UserAcl::getInstance();
		
		$row		= $this->get('Details');
		$registers	= $this->get('Registers');
		$regcheck	= $this->get('Usercheck');
		$formcheck	= $this->get('FormDependencies'); // TODO: still used ?
		$model_event = $this->getModel('Event', 'RedEventModel');
		
		/* Get the message queue */
//		$messages = $mainframe->getMessageQueue();
//		$mainframe->_messageQueue = array();
		
		/* Check if redFORM is installed */
		$redform_install = $model_event->getCheckredFORM();

		/* Get the venues information */
		$this->_venues = $this->get('Venues');
		
		/* This loads the tags replacer */
		JView::loadHelper('tags');
		$tags = new redEVENT_tags();
		$this->assignRef('tags', $tags);
		
		//get menu information
		$menu		= & JSite::getMenu();
		$item    	= $menu->getActive();
		if (!$item) $item = $menu->getDefault();
		
		$params 	= & $mainframe->getParams('com_redevent');

		//Check if the id exists
		if ($row->did == 0)
		{
			return JError::raiseError( 404, JText::sprintf( 'Event #%d not found', $row->did ) );
		}

		//Check if user has access to the details
		if ($elsettings->showdetails == 0) {
			return JError::raiseError( 403, JText::_( 'NO ACCESS' ) );
		}

		//add css file
    if (!$params->get('custom_css')) {
      $document->addStyleSheet($this->baseurl.'/components/com_redevent/assets/css/redevent.css');
    }
    else {
      $document->addStyleSheet($params->get('custom_css'));     
    }
		$document->addCustomTag('<!--[if IE]><style type="text/css">.floattext{zoom:1;}, * html #eventlist dd { height: 1%; }</style><![endif]-->');

		//Print
		$pop	= JRequest::getBool('pop');

		$params->def( 'page_title', JText::_( 'DETAILS' ));

		if ( $pop ) {
			$params->set( 'popup', 1 );
		}

		$print_link = JRoute::_('index.php?option=com_redevent&view=details&id='.$row->slug.'&pop=1&tmpl=component');

		//pathway
		$pathway 	= & $mainframe->getPathWay();
		$pathway->addItem( JText::_( 'DETAILS' ). ' - '.$row->title, JRoute::_('index.php?option=com_redevent&view=details&id='.$row->slug));
		
		//Check user if he can edit
		$allowedtoeditevent = $acl->canEditEvent($row->id);
		
		//Timecheck for registration
		$jetzt = date("Y-m-d");
		$now = strtotime($jetzt);
		$date = strtotime($row->dates);
		$timecheck = $now - $date;
				
		//is the user allready registered at the event
		if ( $regcheck ) {
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
		}		
		
		//Generate Eventdescription
		if (($row->datdescription == '') || ($row->datdescription == '<br />')) {
			$row->datdescription = JText::_( 'NO DESCRIPTION' ) ;
		} else {
			//Execute Plugins
			$row->text	= $row->datdescription;

			JPluginHelper::importPlugin('content');
			$results = $dispatcher->trigger('onPrepareContent', array (& $row, array(), 0));
			$row->datdescription = $row->text;
		}

		// generate Metatags
		$meta_keywords_content = "";
		if (!empty($row->meta_keywords)) {
			$keywords = explode(",",$row->meta_keywords);
			foreach($keywords as $keyword) {
				if ($meta_keywords_content != "") {
					$meta_keywords_content .= ", ";
				}
				if (ereg("[/[/]",$keyword)) {
					$keyword = trim(str_replace("[","",str_replace("]","",$keyword)));
					$buffer = $this->keyword_switcher($keyword, $row, $elsettings->formattime, $elsettings->formatdate);
					if ($buffer != "") {
						$meta_keywords_content .= $buffer;
					} else {
						$meta_keywords_content = substr($meta_keywords_content,0,strlen($meta_keywords_content) - 2);	// remove the comma and the white space
					}
				} else {
					$meta_keywords_content .= $keyword;
				}

			}
		}
		if (!empty($row->meta_description)) {
			$description = explode("[",$row->meta_description);
			$description_content = "";
			foreach($description as $desc) {
					$keyword = substr($desc, 0, strpos($desc,"]",0));
					if ($keyword != "") {
						$description_content .= $this->keyword_switcher($keyword, $row, $elsettings->formattime, $elsettings->formatdate);
						$description_content .= substr($desc, strpos($desc,"]",0)+1);
					} else {
						$description_content .= $desc;
					}

			}
		} else {
			$description_content = "";
		}

		//set page title and meta stuff
		$document->setTitle( $item->name.' - '.$row->title );
		$document->setMetadata('keywords', $meta_keywords_content );
		$document->setDescription( strip_tags($description_content) );

		//build the url
		if(!empty($row->url) && strtolower(substr($row->url, 0, 7)) != "http://") {
			$row->url = 'http://'.$row->url;
		}
		
		/* Get the Venue Dates */
		$venuedates = $this->get('VenueDates');
				
    //add alternate feed link
    $link    = 'index.php?option=com_redevent&view=details&format=feed';
    if (!empty($row->slug)) {
    	$link .= '&id='.$row->slug;
    }

    $attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
    $document->addHeadLink(JRoute::_($link.'&type=rss'), 'alternate', 'rel', $attribs);
    $attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
    $document->addHeadLink(JRoute::_($link.'&type=atom'), 'alternate', 'rel', $attribs);
    
    // check unregistration rights
    $unreg_check = redEVENTHelper::canUnregister($row->xref);
		
		//assign vars to jview
		$this->assignRef('row', 					$row);
		$this->assignRef('params' , 				$params);
    $this->assignRef('user' ,         $user);
		$this->assignRef('allowedtoeditevent' , 	$allowedtoeditevent);
		$this->assignRef('print_link' , 			$print_link);
		$this->assignRef('registers' , 				$registers);
		$this->assignRef('elsettings' , 			$elsettings);
		$this->assignRef('item' , 					$item);
		$this->assignRef('formcheck' ,				$formcheck); // TODO: still used ?
		$this->assignRef('messages' ,				$messages);
		$this->assignRef('redform_install'	, $redform_install);
		$this->assignRef('venuedates'	, $venuedates);
    $this->assignRef('unreg_check' , $unreg_check);
		
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
		
		$row		= $this->get('Details');
		$registers	= $this->get('Registers');
		$regcheck	= $this->get('ManageAttendees');
		$model_event = $this->getModel('Event', 'RedEventModel');
				
		/* Check if redFORM is installed */
		$redform_install = $model_event->getCheckredFORM();
				
		//get menu information
		$menu		= & JSite::getMenu();
		$item    	= $menu->getActive();
		if (!$item) $item = $menu->getDefault();
		
		$params 	= & $mainframe->getParams('com_redevent');

		//Check if the id exists
		if ($row->did == 0)
		{
			return JError::raiseError( 404, JText::sprintf( 'Event #%d not found', $row->did ) );
		}

		//Check if user has access to the attendees management
		if (!$regcheck) {
			return JError::raiseError( 403, JText::_( 'NO ACCESS' ) );
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
		$pathway->addItem( JText::_( 'Manage attendees' ). ' - '.$row->title, JRoute::_('index.php?option=com_redevent&view=details&layout=manageattendees&id='.$row->slug));
		
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
		$document->setTitle( $item->name.' - '.$row->title );				    
		
		//assign vars to jview
		$this->assignRef('row', 					$row);
		$this->assignRef('params' , 				$params);
    $this->assignRef('user' ,         $user);
		$this->assignRef('registers' , 				$registers);
		$this->assignRef('elsettings' , 			$elsettings);
		$this->assignRef('item' , 					$item);
		$this->assignRef('formcheck' ,				$formcheck); // TODO: still used ?
		$this->assignRef('messages' ,				$messages);
		$this->assignRef('redform_install'	, $redform_install);
    $this->assignRef('manage_attendees' , $manage_attendees);
				
		parent::display($tpl);
	}
	
	/**
	 * structures the keywords
	 *
 	 * @since 0.9
	 */
	function keyword_switcher($keyword, $row, $formattime, $formatdate) {
		switch ($keyword) {
			case "catsid":
        // TODO: fix for multiple cats
        //$content = $row->catname;
        $content = '';
				break;
			case "a_name":
				// $content = $row->venue;
				$content = '';
				break;
			case "times":
			case "endtimes":
				$content = '';
				foreach ($this->_venues as $key => $venue) {
					if ($venue->$keyword) {
						$content .= strftime( $formattime ,strtotime( $venue->$keyword ) ).' ';
					}
				}
				break;
			case "dates":
			case "enddates":
				$content = '';
				foreach ($this->_venues as $key => $venue) {
					$content .= strftime( $formatdate ,strtotime( $venue->$keyword ) ).' ';
				}
				break;
			default:
				$content = $row->$keyword;
				break;
		}
		return $content;
	}
}
?>