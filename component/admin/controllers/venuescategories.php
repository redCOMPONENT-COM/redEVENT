<?php
/**
 * @version 1.0 $Id: categories.php 30 2009-05-08 10:22:21Z roland $
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
 * EventList Component Categories Controller
 *
 * @package Joomla
 * @subpackage EventList
 * @since 0.9
 */
class RedEventControllerVenuescategories extends RedEventController
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
		$this->registerTask( 'add'  ,		 	'edit' );
		$this->registerTask( 'apply', 			'save' );
		$this->registerTask( 'accesspublic', 	'access' );
		$this->registerTask( 'accessregistered','access' );
		$this->registerTask( 'accessspecial', 	'access' );
	}

	/**
	 * Logic to save a category
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
	function save()
	{
		// Check for request forgeries
		JRequest::checkToken() or die( 'Invalid Token' );
		
		$task		= JRequest::getVar('task');

		//Sanitize
		$post = JRequest::get( 'post' );
		$post['description'] = JRequest::getVar( 'description', '', 'post', 'string', JREQUEST_ALLOWRAW );
		$post['description']	= str_replace( '<br>', '<br />', $post['description'] );

		$model = $this->getModel('venuescategory');

		if ($returnid = $model->store($post)) {

			switch ($task)
			{
				case 'apply' :
					$link = 'index.php?option=com_redevent&view=venuescategory&cid[]='.$returnid;
					break;

				default :
					$link = 'index.php?option=com_redevent&view=venuescategories';
					break;
			}
			$msg = JText::_('COM_REDEVENT_CATEGORY_SAVED' );

			$cache = &JFactory::getCache('com_redevent');
			$cache->clean();

		} else {

			$msg 	= '';
			$link 	= 'index.php?option=com_redevent&view=venuescategory';
		}

		$model->checkin();

		$this->setRedirect($link, $msg);
	}

	/**
	 * Logic to publish categories
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
	function publish()
	{
		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_('COM_REDEVENT_Select_an_item_to_publish' ) );
		}

		$model = $this->getModel('venuescategories');

		if(!$model->publish($cid, 1)) {
			echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
		}

		$total = count( $cid );
		$msg 	= $total.' '.JText::_('COM_REDEVENT_CATEGORY_PUBLISHED');

		$this->setRedirect( 'index.php?option=com_redevent&view=venuescategories', $msg );
	}

	/**
	 * Logic to unpublish categories
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
	function unpublish()
	{
		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_('COM_REDEVENT_Select_an_item_to_unpublish' ) );
		}

		$model = $this->getModel('venuescategories');

		if(!$model->publish($cid, 0)) {
			echo "<script> alert('".$model->getError()."'); window.history.go(-1); </script>\n";
		}

		$total = count( $cid );
		$msg 	= $total.' '.JText::_('COM_REDEVENT_CATEGORY_UNPUBLISHED');

		$this->setRedirect( 'index.php?option=com_redevent&view=venuescategories', $msg );
	}

	/**
	 * Logic to orderup a category
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
	function orderup()
	{
		$model = $this->getModel('venuescategories');
		$model->move(-1);

		$this->setRedirect( 'index.php?option=com_redevent&view=venuescategories');
	}

	/**
	 * Logic to orderdown a category
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
	function orderdown()
	{
		$model = $this->getModel('venuescategories');
		$model->move(1);

		$this->setRedirect( 'index.php?option=com_redevent&view=venuescategories');
	}

	/**
	 * Logic to mass ordering categories
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
	function saveordercat()
	{
		$cid 	= JRequest::getVar( 'cid', array(0), 'post', 'array' );
		$order 	= JRequest::getVar( 'order', array(0), 'post', 'array' );
		JArrayHelper::toInteger($order, array(0));

		$model = $this->getModel('venuescategories');
		$model->saveorder($cid, $order);

		$msg = 'New ordering saved';
		$this->setRedirect( 'index.php?option=com_redevent&view=venuescategories', $msg );
	}

	/**
	 * Logic to delete categories
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
	function remove()
	{
    $option = JRequest::getCmd('option');

		$cid		= JRequest::getVar( 'cid', array(0), 'post', 'array' );

		if (!is_array( $cid ) || count( $cid ) < 1) {
			JError::raiseError(500, JText::_('COM_REDEVENT_Select_an_item_to_delete' ) );
		}

		$model = $this->getModel('venuescategories');

		$msg = $model->delete($cid);

		$cache = &JFactory::getCache('com_redevent');
		$cache->clean();

		$this->setRedirect( 'index.php?option='. $option .'&view=venuescategories', $msg );
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
		
		$category = & JTable::getInstance('redevent_venues_categories', '');
		$category->bind(JRequest::get('post'));
		$category->checkin();

		$this->setRedirect( 'index.php?option=com_redevent&view=venuescategories' );
	}

	/**
	 * Logic to set the category access level
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
	function access( )
	{
    $option = JRequest::getCmd('option');

		$cid		= JRequest::getVar( 'cid', array(0), 'post', 'array' );
		$id			= $cid[0];
		$task		= JRequest::getVar( 'task' );

		if ($task == 'accesspublic') {
			$access = 0;
		} elseif ($task == 'accessregistered') {
			$access = 1;
		} else {
			$access = 2;
		}

		$model = $this->getModel('venuescategory');
		$model->access( $id, $access );

		$this->setRedirect('index.php?option='. $option .'&view=venuescategories' );
	}

	/**
	 * Logic to create the view for the edit categoryscreen
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
	function edit( )
	{
		JRequest::setVar( 'view', 'venuescategory' );
		JRequest::setVar( 'hidemainmenu', 1 );

		$model 	= $this->getModel('venuescategory');
		$user	=& JFactory::getUser();

		// Error if checkedout by another administrator
		if ($model->isCheckedOut( $user->get('id') )) {
			$this->setRedirect( 'index.php?option=com_redevent&view=venuescategories', JText::_('COM_REDEVENT_EDITED_BY_ANOTHER_ADMIN' ) );
		}

		$model->checkout();
		
		parent::display();
	}
}
