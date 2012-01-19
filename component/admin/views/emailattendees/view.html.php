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
 * View class for the email attendess screen
 *
 * @package Joomla
 * @subpackage redEVENT
 * @since 2.0
 */
class RedEventViewEmailattendees extends JView {

	function display($tpl = null)
	{
		$mainframe = &JFactory::getApplication();
		
		$editor 	= & JFactory::getEditor();
		$settings = JComponentHelper::getParams('com_redevent');
		
		$cids = JRequest::getVar('cid', array(), 'post');
		JArrayHelper::toInteger($cids);
		
		//add toolbar
		JToolBarHelper::title( JText::_( 'COM_REDEVENT_EMAIL_ATTENDEES_TITLE' ), 'users' );
		JToolBarHelper::custom('sendemail', 'send.png', 'send.png', 'COM_REDEVENT_ATTENDEES_TOOLBAR_EMAIL_SEND', false);
		JToolBarHelper::cancel('cancelemail');
		
		$model = JModel::getInstance('attendees', 'redeventmodel');
		
		$emails = $model->getEmails($cids);
		$event  = $model->getEvent();
//		echo '<pre>';print_r($emails); echo '</pre>';exit;
		
		$this->assignRef('editor'		, $editor);
		$this->assignRef('cids'		  , $cids);
		$this->assignRef('emails'		, $emails);
		$this->assignRef('event'		, $event);
		$this->assignRef('settings'	, $settings);
		$this->assignRef('xref'	    , JRequest::getInt('xref'));
		
		parent::display($tpl);
  }
}
?>