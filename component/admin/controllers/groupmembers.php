<?php
/**
 * @version 1.0 $Id: groups.php 1587 2009-11-17 17:05:42Z julien $
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
 * EventList Component Groups Controller
 *
 * @package Joomla
 * @subpackage EventList
 * @since 0.9
 */
class RedEventControllerGroupmembers extends RedEventController
{
	/**
	 * Constructor
	 *
	 * @since 0.9
	 */
	function __construct()
	{
		parent::__construct();

		$this->registerTask( 'apply', 		'save' );
	}

	/**
	 * logic for cancel an action
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
	function cancel()
	{
		// Check for request forgeries
		JRequest::checkToken() or die( 'Invalid Token' );
		
		$group_id = JRequest::getVar('group_id', 0, '', 'int') or die( 'Missing group id' );
		
		$group = & JTable::getInstance('redevent_groupmembers', '');
		$group->bind(JRequest::get('post'));
		$group->checkin();

		$this->setRedirect( 'index.php?option=com_redevent&view=groupmembers&group_id='. $group_id );
	}

	/**
	 * logic to create the new event screen
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
	function add( )
	{
		global $option;
		$group_id = JRequest::getVar('group_id', 0, '', 'int') or die( 'Missing group id' );

		$this->setRedirect( 'index.php?option='. $option .'&view=groupmember&group_id='. $group_id );
	}

	/**
	 * logic to create the edit event screen
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
	function edit()
	{
		JRequest::setVar( 'view', 'groupmember' );
		JRequest::setVar( 'hidemainmenu', 1 );
		$group_id = JRequest::getVar('group_id', 0, '', 'int') or die( 'Missing group id' );

		$model = $this->getModel('groupmember');
		$user	=& JFactory::getUser();

		// Error if checkedout by another administrator
		if ($model->isCheckedOut( $user->get('id') )) {
			$this->setRedirect( 'index.php?option=com_redevent&view=groupmembers&group_id='. $group_id, JText::_( 'EDITED BY ANOTHER ADMIN' ) );
		}

		$model->checkout();

		parent::display();
	}

	/**
	 * logic to save an event
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
	function save()
	{
		// Check for request forgeries
		JRequest::checkToken() or die( 'Invalid Token' );
		$group_id = JRequest::getVar('group_id', 0, '', 'int') or die( 'Missing group id' );
		$task		= JRequest::getVar('task');

		$post 	= JRequest::get( 'post' );
				
		$isNew = intval($post['id']) ? false : true;
		
		$model = $this->getModel('groupmember');

		if ($returnid = $model->store($post)) 
		{		
			switch ($task)
			{
				case 'apply' :
					$link = 'index.php?option=com_redevent&controller=groups&view=groupmember&group_id='. $group_id.'&hidemainmenu=1&cid[]='.$returnid;
					break;

				default :
					$link 	= 'index.php?option=com_redevent&view=groupmembers&group_id='. $group_id;
					break;
			}
			$msg	= JText::_( 'GROUP MEMBER SAVED');
			
			JPluginHelper::importPlugin( 'redevent' );
			$dispatcher =& JDispatcher::getInstance();
			$res = $dispatcher->trigger( 'onGroupMemberSaved', array( $returnid, $isNew ) );
			
		} else {

			$link 	= 'index.php?option=com_redevent&view=groupmembers&group_id='. $group_id;
			$msg	= '';
	
		}

		$model->checkin();

		$this->setRedirect( $link, $msg );
 	}

	/**
	 * logic to remove a group
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
 	function remove()
	{
		$cid = JRequest::getVar( 'cid', array(0), 'post', 'array' );
		$group_id = JRequest::getVar('group_id', 0, '', 'int') or die( 'Missing group id' );

		$total = count( $cid );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to delete' ) );
		}

		$model = $this->getModel('groupmembers');

		// do the sync first, as we will delete the rows...
		JPluginHelper::importPlugin( 'redevent' );
		$dispatcher =& JDispatcher::getInstance();
		$res = $dispatcher->trigger( 'onGroupMembersRemoved', array( $cid ) );

		if(!$model->delete($cid)) {
			echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
		}

			
		$msg = $total.' '.JText::_( 'GROUP MEMBERS DELETED');

		$this->setRedirect( 'index.php?option=com_redevent&view=groupmembers&group_id='. $group_id, $msg );
	}	
	
	function back()
	{
		$this->setRedirect( 'index.php?option=com_redevent&view=groups');		
	}
}
?>