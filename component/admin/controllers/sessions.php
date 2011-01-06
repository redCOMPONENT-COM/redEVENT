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
 * EventList Component Events Controller
 *
 * @package Joomla
 * @subpackage EventList
 * @since 0.9
 */
class RedEventControllerSessions extends RedEventController
{	
	/**
	 * Constructor
	 *
	 * @since 0.9
	 */
	function __construct()
	{
		parent::__construct();

		// Register Extra task
		$this->registerTask( 'apply', 		'save' );
		$this->registerTask( 'copy',	 	'edit' );
		$this->registerTask( 'add',	 	'edit' );
	}
	
	function editxref()
	{
    JRequest::setVar( 'view', 'session' );
    JRequest::setVar( 'layout', 'default' );
		
    parent::display();
	}
	
	function savexref()
	{		
    // Check for request forgeries
    JRequest::checkToken() or die( 'Invalid Token' );
        
    $post = JRequest::get( 'post' );
    $post['details'] = JRequest::getVar('details', '', 'post', 'string', JREQUEST_ALLOWRAW);
    
    $model = $this->getModel('session');
    if ($returnid = $model->savexref($post)) 
    {
			/* Check if people need to be moved on or off the waitinglist */
			$model_wait = $this->getModel('waitinglist');
			$model_wait->setXrefId($returnid);
			$model_wait->UpdateWaitingList();
			
			$cache = &JFactory::getCache('com_redevent');
			$cache->clean();
			
      $msg = 'saved session';
      $this->setRedirect('index.php?option=com_redevent&controller=sessions&task=closexref&tmpl=component&xref='. $returnid, $msg);      
    }
    else {
    	$msg = 'error saving: '. $model->getError() ;
      $this->setRedirect('index.php?option=com_redevent&controller=sessions&task=editxref&tmpl=component&xref='. $returnid,  $msg, 'error');
    }
	}
	
  function closexref()
  {
    JRequest::setVar( 'view', 'session' );
    JRequest::setVar( 'layout', 'closexref' );
    
    parent::display();
  }
}
?>