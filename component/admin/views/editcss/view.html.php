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
 * View class for the EventList CSS edit screen
 *
 * @package Joomla
 * @subpackage redEVENT
 * @since 0.9
 */
class RedEventViewEditcss extends JView {

	function display($tpl = null) {

		$mainframe = &JFactory::getApplication();

		//initialise variables
		$document	= & JFactory::getDocument();
		$user 		= & JFactory::getUser();
		
		//only admins have access to this view
		if ($user->get('gid') < 24) {
			JError::raiseWarning( 'REDEVENT_GENERIC_ERROR', JText::_('COM_REDEVENT_ALERTNOTAUTH'));
			$mainframe->redirect( 'index.php?option=com_redevent&view=redevent' );
		}

		//get vars
		$option		= JRequest::getVar('option');
		$filename	= 'redevent.css';
		$path		= JPATH_SITE.DS.'components'.DS.'com_redevent'.DS.'assets'.DS.'css';
		$css_path	= $path.DS.$filename;

		//create the toolbar
		JToolBarHelper::title( JText::_('COM_REDEVENT_EDIT_CSS' ), 'cssedit' );
		JToolBarHelper::apply( 'applycss' );
		JToolBarHelper::spacer();
		JToolBarHelper::save( 'savecss' );
		JToolBarHelper::spacer();
		JToolBarHelper::cancel();
		JToolBarHelper::spacer();
		JToolBarHelper::help( 'el.editcss', true );
		
		JRequest::setVar( 'hidemainmenu', 1 );

		//add css to document
		$document->addStyleSheet('components/com_redevent/assets/css/redeventbackend.css');

		//read the the stylesheet
		jimport('joomla.filesystem.file');
		$content = JFile::read($css_path);
		
		jimport('joomla.client.helper');
		$ftp =& JClientHelper::setCredentialsFromRequest('ftp');

		if ($content !== false)
		{
			$content = htmlspecialchars($content, ENT_COMPAT, 'UTF-8');
		}
		else
		{
			$msg = JText::sprintf('COM_REDEVENT_FAILED_TO_OPEN_FILE_FOR_WRITING', $css_path);
			$mainframe->redirect('index.php?option='.$option, $msg);
		}

		//assign data to template
		$this->assignRef('css_path'		, $css_path);
		$this->assignRef('content'		, $content);
		$this->assignRef('filename'		, $filename);
		$this->assignRef('ftp'			, $ftp);
		

		parent::display($tpl);
	}
}