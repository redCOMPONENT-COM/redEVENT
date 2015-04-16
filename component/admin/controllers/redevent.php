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

jimport('joomla.application.component.controller');

/**
 * Component default Controller
 *
 * @package Joomla
 * @subpackage redEVENT
 * @since 2.0
 */
class RedeventControllerRedevent extends RControllerAdmin
{
	public function __construct($config = array())
	{
		parent::__construct($config);

		// Register Extra task
		$this->registerTask( 'applycss', 	'savecss' );
	}


	/**
	 * Saves the css
	 *
	 */
	function savecss()
	{
		$mainframe = & JFactory::getApplication();

		JRequest::checkToken() or die( 'Invalid Token' );

		// Initialize some variables
		$option			= JRequest::getVar('option');
		$filename		= JRequest::getVar('filename', '', 'post', 'cmd');
		$filecontent	= JRequest::getVar('filecontent', '', '', '', JREQUEST_ALLOWRAW);

		if (!$filecontent) {
			$mainframe->redirect('index.php?option='.$option, JText::_('COM_REDEVENT_OPERATION_FAILED').': '.JText::_('COM_REDEVENT_CONTENT_EMPTY'));
		}

		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');
		$ftp = JClientHelper::getCredentials('ftp');

		$file = JPATH_SITE.DS.'components'.DS.'com_redevent'.DS.'assets'.DS.'css'.DS.$filename;

		// Try to make the css file writeable
		if (!$ftp['enabled'] && JPath::isOwner($file) && !JPath::setPermissions($file, '0755')) {
			RedeventError::raiseNotice('REDEVENT_GENERIC_ERROR', 'COULD NOT MAKE CSS FILE WRITABLE');
		}

		jimport('joomla.filesystem.file');
		$return = JFile::write($file, $filecontent);

		// Try to make the css file unwriteable
		if (!$ftp['enabled'] && JPath::isOwner($file) && !JPath::setPermissions($file, '0555')) {
			RedeventError::raiseNotice('REDEVENT_GENERIC_ERROR', 'COULD NOT MAKE CSS FILE UNWRITABLE');
		}

		if ($return)
		{
			$task = JRequest::getVar('task');
			switch($task)
			{
				case 'applycss' :
					$mainframe->redirect('index.php?option='.$option.'&view=editcss', JText::_('COM_REDEVENT_CSS_FILE_SUCCESSFULLY_ALTERED'));
					break;

				case 'savecss'  :
				default         :
					$mainframe->redirect('index.php?option='.$option, JText::_('COM_REDEVENT_CSS_FILE_SUCCESSFULLY_ALTERED') );
					break;
			}
		} else {
			$mainframe->redirect('index.php?option='.$option, JText::_('COM_REDEVENT_OPERATION_FAILED').': '.JText::sprintf('COM_REDEVENT_FAILED_ TO_OPEN_FILE_FOR_WRITING_S', $file));
		}
	}

	/**
	 * displays the fast addvenue screen
	 *
	 * @since 0.9
	 */
	function addvenue( )
	{
		//TODO: Implement Access check
		JRequest::setVar( 'view', 'event' );
		JRequest::setVar( 'layout', 'addvenue'  );

		parent::display();
	}

	/**
	 * import eventlist events, categories, and venues.
	 *
	 */
	function importeventlist()
	{
		$model = $this->getModel('import');

		$result = $model->importeventlist();

		$link = 'index.php?option=com_redevent&view=tools';

		if (!$result) {
			$msg = $model->getError();
			$this->setRedirect( $link, $msg, 'error' );
		}
		else {
			$msg = JText::sprintf( 'COM_REDEVENT_EVENTLIST_IMPORT_SUCCESS_D_EVENTS_D_CATEGORIES_D_VENUES', $result['events'], $result['categories'], $result['venues']);
			$this->setRedirect( $link, $msg );
		}
	}

	/**
	 * triggers the autoarchive function
	 *
	 */
	function autoarchive()
	{
		$res = RedeventHelper::cleanup(1);
		$msg = JText::_('COM_REDEVENT_AUTOARCHIVE_DONE');
		$link = 'index.php?option=com_redevent&view=tools';
		$this->setRedirect( $link, $msg );
	}

	function insertevent()
	{
		JRequest::setVar( 'view', 'eventelement' );
		JRequest::setVar( 'layout', 'editors-xtd'  );
		JRequest::setVar( 'filter_state', 'P'  );

		parent::display();
	}

	function sampledata()
	{
		$model = $this->getModel('sample',  'RedeventModel');
		$model->create();
		$this->setRedirect('index.php?option=com_redevent', JText::_('COM_REDEVENT_Sample_data_created'));
	}

	function selectuser()
	{
		JRequest::setVar( 'view', 'selectuser' );

		parent::display();
	}
}
