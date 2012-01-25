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

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

/**
 * View class for the EventList Venueedit screen
 *
 * @package Joomla
 * @subpackage EventList
 * @since 0.9
 */
class RedEventViewVenue extends JView {

	function display($tpl = null)
	{
		$mainframe = &JFactory::getApplication();
		$params = JComponentHelper::getParams('com_redevent');

		// Load pane behavior
		jimport('joomla.html.pane');
		JHTML::_('behavior.framework');

		//initialise variables
		$editor 	= & JFactory::getEditor();
		$document	= & JFactory::getDocument();
		$pane		  = & JPane::getInstance('sliders');
		$tabs 		= & JPane::getInstance('tabs');
		$user 		= & JFactory::getUser();
		$settings	= JComponentHelper::getParams('com_redevent');

		//get vars
		$cid 			= JRequest::getInt( 'cid' );
    $url    = JURI::root();

		$document->setTitle(JText::_('COM_REDEVENT_PAGETITLE_EDITVENUE'));
		//add css and js to document
		$document->addScript('../includes/js/joomla/popup.js');
		$document->addStyleSheet('../includes/js/joomla/popup.css');
		$document->addStyleSheet('components/com_redevent/assets/css/redeventbackend.css');

    $document->addScript($url.'/components/com_redevent/assets/js/attachments.js');
		$document->addScriptDeclaration('var removemsg = "'.JText::_('COM_REDEVENT_ATTACHMENT_CONFIRM_MSG').'";' );
    
		// Get data from the model
		$model		= & $this->getModel();
		$row      	= & $this->get( 'Data');

		// fail if checked out not by 'me'
		if ($row->id) {
			if ($model->isCheckedOut( $user->get('id') )) {
				JError::raiseWarning( 'REDEVENT_GENERIC_ERROR', $row->venue.' '.JText::_('COM_REDEVENT_EDITED_BY_ANOTHER_ADMIN' ));
				$mainframe->redirect( 'index.php?option=com_redevent&view=venues' );
			}
		}

		$task = JRequest::getVar('task');
		
		//create the toolbar
		if ($task == 'copy') {
		  	JToolBarHelper::title( JText::_('COM_REDEVENT_COPY_VENUE'), 'venuesedit');		
		} elseif ( $cid ) {
			JToolBarHelper::title( JText::_('COM_REDEVENT_EDIT_VENUE' ), 'venuesedit' );

			//makes data safe
			JFilterOutput::objectHTMLSafe( $row, ENT_QUOTES, 'locdescription' );

		} else {
			JToolBarHelper::title( JText::_('COM_REDEVENT_ADD_VENUE' ), 'venuesedit' );

			//set the submenu
			JSubMenuHelper::addEntry( JText::_('COM_REDEVENT' ), 'index.php?option=com_redevent');
			JSubMenuHelper::addEntry( JText::_('COM_REDEVENT_EVENTS' ), 'index.php?option=com_redevent&view=events');
			JSubMenuHelper::addEntry( JText::_('COM_REDEVENT_VENUES' ), 'index.php?option=com_redevent&view=venues');
			JSubMenuHelper::addEntry( JText::_('COM_REDEVENT_CATEGORIES' ), 'index.php?option=com_redevent&view=categories');
			JSubMenuHelper::addEntry( JText::_('COM_REDEVENT_ARCHIVESCREEN' ), 'index.php?option=com_redevent&view=archive');
			JSubMenuHelper::addEntry( JText::_('COM_REDEVENT_GROUPS' ), 'index.php?option=com_redevent&view=groups');
			JSubMenuHelper::addEntry( JText::_('COM_REDEVENT_HELP' ), 'index.php?option=com_redevent&view=help');
			if ($user->get('gid') > 24) {
				JSubMenuHelper::addEntry( JText::_('COM_REDEVENT_SETTINGS' ), 'index.php?option=com_redevent&controller=settings&task=edit');
			}
		}
		JToolBarHelper::apply();
		JToolBarHelper::spacer();
		JToolBarHelper::save();
		JToolBarHelper::spacer();
		JToolBarHelper::cancel();
		JToolBarHelper::spacer();
		JToolBarHelper::help( 'el.editvenues', true );

		$lists = array();
    // categories selector
    $selected = array();
    foreach ((array) $row->categories as $cat) {
      $selected[] = $cat;
    }
    $lists['categories'] = JHTML::_('select.genericlist', (array) $this->get('Categories'), 'categories[]', 'class="inputbox" multiple="multiple" size="10"', 'value', 'text', $selected); 
        
    $countries = array();
    $countries[] = JHTML::_('select.option', '', JText::_('COM_REDEVENT_Select_country'));
    $countries = array_merge($countries, redEVENTHelperCountries::getCountryOptions());
    $lists['countries'] = JHTML::_('select.genericlist', $countries, 'country', 'class="inputbox"', 'value', 'text', $row->country );
    unset($countries);    
    
    $pinpointicon = ELOutput::pinpointicon2( $row );
	
		if ($task == 'copy') 
		{
			$row->id = null;
			$row->venue .= ' '.JText::_('COM_REDEVENT_copy');
			$row->alias = '';
		}
		
		//assign data to template
		$this->assignRef('row'      	, $row);
		$this->assignRef('pane'      	, $pane);
		$this->assignRef('tabs'      	, $tabs);
		$this->assignRef('editor'      	, $editor);
		$this->assignRef('settings'     , $settings);
		$this->assignRef('params'     , $params);
    $this->assignRef('lists'      , $lists);
		$this->assignRef('imageselect' 	, $imageselect);
    $this->assignRef('pinpointicon', $pinpointicon);
		$this->assignRef('access'	, redEVENTHelper::getAccesslevelOptions());
		$this->assignRef('form'      	, $this->get('form'));

		parent::display($tpl);
	}
}
?>