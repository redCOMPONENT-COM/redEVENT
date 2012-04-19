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
 * View class for the EventList Cleanup screen
 *
 * @package Joomla
 * @subpackage redEVENT
 * @since 0.9
 */
class RedEventViewTools extends JView {

	function display($tpl = null) {

		$mainframe = &JFactory::getApplication();

		//initialise variables
		$document		= & JFactory::getDocument();
		$user			= & JFactory::getUser();

		//only admins have access to this view
		if ($user->get('gid') < 24) {
			JError::raiseWarning( 'REDEVENT_GENERIC_ERROR', JText::_('COM_REDEVENT_ALERTNOTAUTH'));
			$mainframe->redirect( 'index.php?option=com_redevent&view=redevent' );
		}

		$document->setTitle(JText::_('COM_REDEVENT_PAGETITLE_TOOLS'));
		//add css and submenu to document
		$document->addStyleSheet('components/com_redevent/assets/css/redeventbackend.css');

    //Create Submenu
    ELAdmin::setMenu();

		//create the toolbar
		JToolBarHelper::title( JText::_('COM_REDEVENT_Tools' ), 'tools' );
		//JToolBarHelper::help( 'redevent.tools', true );

		parent::display($tpl);
	}
}
