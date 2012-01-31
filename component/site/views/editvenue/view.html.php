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
 * HTML View class for the Editevents View
 *
 * @package Joomla
 * @subpackage EventList
 * @since 0.9
 */
class RedeventViewEditvenue extends JView
{
	/**
	 * Creates the output for venue submissions
	 *
	 * @since 0.5
	 * @param int $tpl
	 */
	function display( $tpl=null )
	{
		$mainframe = &JFactory::getApplication();

		$editor 	  = & JFactory::getEditor();
		$document 	= & JFactory::getDocument();
		$elsettings = & redEVENTHelper::config();
		$params 	  = & $mainframe->getParams();
		$acl        = UserAcl::getInstance();

		// Get requests
		$id				= JRequest::getInt('id');

		if ($id && !$acl->canEditVenue($id)) {
			echo JText::_('COM_REDEVENT_USER_NOT_ALLOWED_TO_EDIT_THIS_VENUE');
			return;
		}
		else if (!$id && !$acl->canAddVenue()) {
			echo JText::_('COM_REDEVENT_USER_NOT_ALLOWED_TO_ADD_VENUE');
			return;			
		}
		
		//Get Data from the model
		$row 		= $this->Get('Venue');
		JFilterOutput::objectHTMLSafe( $row, ENT_QUOTES, 'locdescription' );

		JHTML::_('behavior.formvalidation');
		JHTML::_('behavior.tooltip');
		JHTML::_('behavior.mootools');

		//add css file
    if (!$params->get('custom_css')) {
      $document->addStyleSheet($this->baseurl.'/components/com_redevent/assets/css/redevent.css');
    }
    else {
      $document->addStyleSheet($params->get('custom_css'));     
    }
		$document->addCustomTag('<!--[if IE]><style type="text/css">.floattext{zoom:1;}, * html #eventlist dd { height: 1%; }</style><![endif]-->');

		$document->addScript('components/com_redevent/assets/js/attachments.js' );
		$document->addScriptDeclaration('var removemsg = "'.JText::_('COM_REDEVENT_ATTACHMENT_CONFIRM_MSG').'";' );
		
		// Get the menu object of the active menu item
		$menu		= & JSite::getMenu();
		$item    	= $menu->getActive();
		$params 	= & $mainframe->getParams('com_redevent');

		$id ? $title = JText::_('COM_REDEVENT_EDIT_VENUE' ) : $title = JText::_('COM_REDEVENT_ADD_VENUE' );

		//pathway
		$pathway 	= & $mainframe->getPathWay();
		$pathway->addItem($title, '');

		//Set Title
		$document->setTitle($title);

		//editor user
//		$editoruser = ELUser::editoruser();
		$editoruser = true;
		
		//transform <br /> and <br> back to \r\n for non editorusers
		if (!$editoruser) {
			$row->locdescription = redEVENTHelper::br2break($row->locdescription);
		}

		//Get image
		$limage = redEVENTImage::flyercreator($row->locimage);

		//Set the info image
		$infoimage = JHTML::_('image', 'components/com_redevent/assets/images/icon-16-hint.png', JText::_('COM_REDEVENT_NOTES' ) );

		$lists = array();
    // categories selector
    $selected = array();
    foreach ((array)$row->categories as $cat) {
      $selected[] = $cat;
    }
    $options = (array) $this->get('CategoryOptions');
    $lists['categories'] = JHTML::_('select.genericlist', 
                                    $options, 
                                    'categories[]', 
                                    'class="inputbox validate-categories" multiple="multiple" size="'.min(3, max(10, count($options))).'"', 
                                    'value', 'text', $selected);
        
    // published state selector
    $canpublish = $acl->canPublishVenue($id);
    $published = array( JHTML::_('select.option', '1', JText::_('COM_REDEVENT_PUBLISHED')),
                         JHTML::_('select.option', '0', JText::_('COM_REDEVENT_UNPUBLISHED')),
                       );
    $lists['published'] = JHTML::_('select.radiolist', $published, 'published', '', 'value', 'text', $row->published);
    
    // gmap pinpoint    
		$document->addScript('http://maps.google.com/maps/api/js?sensor=false');
    $document->addStyleSheet(JURI::root().'/components/com_redevent/assets/css/gmapsoverlay.css', 'text/css');
		$document->addScript(JURI::root().'/components/com_redevent/assets/js/gmapspinpoint.js');
    
		$this->assignRef('row' , 					$row);
		$this->assignRef('editor' , 				$editor);
		$this->assignRef('editoruser' , 			$editoruser);
		$this->assignRef('limage' , 				$limage);
		$this->assignRef('infoimage' , 				$infoimage);
		$this->assignRef('elsettings' , 			$elsettings);
    $this->assignRef('lists' ,           $lists);
		$this->assignRef('item' , 					$item);
		$this->assignRef('params',      $params);
		$this->assignRef('canpublish',  $canpublish);
		$this->assignRef('access'	, redEVENTHelper::getAccesslevelOptions());

		parent::display($tpl);

	}
}
