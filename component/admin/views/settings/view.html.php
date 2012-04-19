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
 * @subpackage redEVENT
 * @since 0.9
 */
class RedEventViewSettings extends JView {

	function display($tpl = null) {

		$mainframe = &JFactory::getApplication();

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
			JError::raiseWarning( 'REDEVENT_GENERIC_ERROR', JText::_('COM_REDEVENT_ALERTNOTAUTH'));
			$mainframe->redirect( 'index.php?option=com_redevent&view=redevent' );
		}

		// fail if checked out not by 'me'
		if ($model->isCheckedOut( $user->get('id') )) {
			JError::raiseWarning( 'REDEVENT_GENERIC_ERROR', JText::_('COM_REDEVENT_EDITED_BY_ANOTHER_ADMIN' ));
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

		$document->setTitle(JText::_('COM_REDEVENT_PAGETITLE_SETTINGS'));
		//add css, js and submenu to document
		$document->setBuffer($contents, 'modules', 'submenu');
		$document->addScript( JURI::base().'components/com_redevent/assets/js/settings.js' );
		$document->addStyleSheet('components/com_redevent/assets/css/redeventbackend.css');

		//create the toolbar
		JToolBarHelper::title( JText::_('COM_REDEVENT_SETTINGS' ), 'settings' );
		JToolBarHelper::apply();
		JToolBarHelper::spacer();
		JToolBarHelper::save('save');
		JToolBarHelper::spacer();
		JToolBarHelper::cancel();
		JToolBarHelper::spacer();
		//JToolBarHelper::help( 'el.settings', true );

		//Get global parameters
		$table =& JTable::getInstance('component');
		$table->loadByOption( 'com_redevent' );
		$globalparams = new JParameter( $table->params, JPATH_ADMINISTRATOR.DS.'components'.DS.'com_redevent'.DS.'config.xml' );

		// tabs for global params
		jimport('joomla.html.pane');
		$tabs = & JPane::getInstance('tabs');
		
		//assign data to template
		$this->assignRef('elsettings'	, $elsettings);
		$this->assignRef('WarningIcon'	, $this->WarningIcon());
		$this->assignRef('request_url'	, $uri->toString());
		$this->assignRef('globalparams'	, $globalparams);
		$this->assignRef('params',        JComponentHelper::getParams('com_redevent'));
		$this->assignRef('tabs',          $tabs);
		
		parent::display($tpl);

	}

	function WarningIcon()
	{
		$mainframe = &JFactory::getApplication();

		$url = $mainframe->isAdmin() ? $mainframe->getSiteURL() : JURI::base();
		$tip = '<img src="'.$url.'includes/js/ThemeOffice/warning.png" border="0"  alt="" />';

		return $tip;
	}
}
