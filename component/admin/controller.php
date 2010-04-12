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
 * EventList Component Controller
 *
 * @package Joomla
 * @subpackage EventList
 * @since 0.9
 */
class RedEventController extends JController
{
	function __construct()
	{
		parent::__construct();

		// Register Extra task
		$this->registerTask( 'applycss', 	'savecss' );
	}

	/**
	 * Display the view
	 */
	function display()
	{
		parent::display();

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
			$mainframe->redirect('index.php?option='.$option, JText::_('OPERATION FAILED').': '.JText::_('CONTENT EMPTY'));
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
					$mainframe->redirect('index.php?option='.$option.'&view=editcss', JText::_('CSS FILE SUCCESSFULLY ALTERED'));
					break;

				case 'savecss'  :
				default         :
					$mainframe->redirect('index.php?option='.$option, JText::_('CSS FILE SUCCESSFULLY ALTERED') );
					break;
			}
		} else {
			$mainframe->redirect('index.php?option='.$option, JText::_('OPERATION FAILED').': '.JText::sprintf('FAILED TO OPEN FILE FOR WRITING', $file));
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
	 * Clears log file
	 *
	 */
	function clearlog()
	{
		RedeventHelperLog::clear();
		$msg = JText::_('LOG CLEARED');
		$this->setRedirect('index.php?option=com_redevent&view=log', $msg);
		$this->redirect();
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
      $msg = JText::sprintf( 'EVENTLIST IMPORT SUCCESS', $result['events'], $result['categories'], $result['venues']);
      $this->setRedirect( $link, $msg );
    }
  }
  
  /**
   * triggers the autoarchive function
   * 
   */
  function autoarchive()
  {
  	$res = redEVENTHelper::cleanup(1);
    $msg = JText::_('AUTOARCHIVE DONE');
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
  	$model = &JModel::getInstance('sample',  'RedEventModel');
  	$model->create();
    $this->setRedirect( 'index.php?option=com_redevent', JText::_('Sample data created') );
  }
}
?>