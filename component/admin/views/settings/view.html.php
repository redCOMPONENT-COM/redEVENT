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
 * View class for the EventList Settings screen
 *
 * @package Joomla
 * @subpackage EventList
 * @since 0.9
 */
class RedEventViewSettings extends JView {

	function display($tpl = null) {

		global $mainframe;

		//initialise variables
		$document 	= & JFactory::getDocument();
		$acl		= & JFactory::getACL();
		$uri 		= & JFactory::getURI();
		$user 		= & JFactory::getUser();

		//get data from model
		$model		= & $this->getModel();
		$elsettings = & $this->get( 'Data');

		//only admins have access to this view
		if ($user->get('gid') < 24) {
			JError::raiseWarning( 'REDEVENT_GENERIC_ERROR', JText::_( 'ALERTNOTAUTH'));
			$mainframe->redirect( 'index.php?option=com_redevent&view=redevent' );
		}

		// fail if checked out not by 'me'
		if ($model->isCheckedOut( $user->get('id') )) {
			JError::raiseWarning( 'REDEVENT_GENERIC_ERROR', JText::_( 'EDITED BY ANOTHER ADMIN' ));
			$mainframe->redirect( 'index.php?option=com_redevent&view=redevent' );
		}

		JHTML::_('behavior.tooltip');
		JHTML::_('behavior.switcher');
		
		//Build submenu
		$contents = '';
		ob_start();
			require_once(dirname(__FILE__).DS.'tmpl'.DS.'navigation.php');
		$contents = ob_get_contents();
		ob_end_clean();

		//add css, js and submenu to document
		$document->setBuffer($contents, 'modules', 'submenu');
		$document->addScript( JURI::base().'components/com_redevent/assets/js/settings.js' );
		$document->addStyleSheet('components/com_redevent/assets/css/redeventbackend.css');

		//create the toolbar
		JToolBarHelper::title( JText::_( 'SETTINGS' ), 'settings' );
		JToolBarHelper::apply();
		JToolBarHelper::spacer();
		JToolBarHelper::save('save');
		JToolBarHelper::spacer();
		JToolBarHelper::cancel();
		JToolBarHelper::spacer();
		JToolBarHelper::help( 'el.settings', true );

		$accessLists = array();

 	  	//Create custom group levels to include into the public group selectList
 	  	$access   = array();
 	  	$access[] = JHTML::_('select.option', -2, '- disabled -' );
 	  	//$access[] = JHTML::_('select.option', 0 , '- Everybody -' );
 	  	$access[] = JHTML::_('select.option', -1, '- All Registered Users -' );
 	  	//$pub_groups = array_merge( $pub_groups, $acl->get_group_children_tree( null, 'Registered', true ) );
		$access = array_merge( $access, $acl->get_group_children_tree( null, 'USERS', false ) );

		//Create the access control list
		$accessLists['evdel_access']	= JHTML::_('select.genericlist', $access, 'delivereventsyes', 'class="inputbox" size="4"', 'value', 'text', $elsettings->delivereventsyes );
		$accessLists['locdel_access']	= JHTML::_('select.genericlist', $access, 'deliverlocsyes', 'class="inputbox" size="4"', 'value', 'text', $elsettings->deliverlocsyes );
		$accessLists['evpub_access']	= JHTML::_('select.genericlist', $access, 'autopubl', 'class="inputbox" size="4"', 'value', 'text', $elsettings->autopubl );
		$accessLists['locpub_access']	= JHTML::_('select.genericlist', $access, 'autopublocate', 'class="inputbox" size="4"', 'value', 'text', $elsettings->autopublocate );
		$accessLists['ev_edit']			= JHTML::_('select.genericlist', $access, 'eventedit', 'class="inputbox" size="4"', 'value', 'text', $elsettings->eventedit );
		$accessLists['venue_edit']		= JHTML::_('select.genericlist', $access, 'venueedit', 'class="inputbox" size="4"', 'value', 'text', $elsettings->venueedit );

		//Get global parameters
		$table =& JTable::getInstance('component');
		$table->loadByOption( 'com_redevent' );
		$globalparams = new JParameter( $table->params, JPATH_ADMINISTRATOR.DS.'components'.DS.'com_redevent'.DS.'config.xml' );

		//assign data to template
		$this->assignRef('accessLists'	, $accessLists);
		$this->assignRef('elsettings'	, $elsettings);
		$this->assignRef('WarningIcon'	, $this->WarningIcon());
		$this->assignRef('request_url'	, $uri->toString());
		$this->assignRef('globalparams'	, $globalparams);
		$this->assignRef('params',        JComponentHelper::getParams('com_redevent'));
		
		parent::display($tpl);

	}

	function WarningIcon()
	{
		global $mainframe;

		$url = $mainframe->isAdmin() ? $mainframe->getSiteURL() : JURI::base();
		$tip = '<img src="'.$url.'includes/js/ThemeOffice/warning.png" border="0"  alt="" />';

		return $tip;
	}
}