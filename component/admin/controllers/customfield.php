<?php
/**
 * @version 1.0 $Id: cleanup.php 30 2009-05-08 10:22:21Z roland $
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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.controller');

/**
 * Joomla redEVENT Component Controller
 *
 * @package		redEVENT
 * @since 2.0
 */
class RedeventControllerCustomfield extends JController
{
  
  function __construct()
	{
		parent::__construct();

		// Register Extra tasks
		$this->registerTask( 'add',  'display' );
		$this->registerTask( 'edit', 'display' );
		$this->registerTask( 'apply', 'save' );
	}
  
  
	function display() {
	
	  switch($this->getTask())
		{
			case 'add'     :
			{
				JRequest::setVar( 'hidemainmenu', 1 );
				JRequest::setVar( 'layout', 'form'  );
				JRequest::setVar( 'view'  , 'customfield');
				JRequest::setVar( 'edit', false );

				// Checkout the project
				$model = $this->getModel('customfield');
				$model->checkout();
			} break;
			case 'edit'    :
			{
				JRequest::setVar( 'hidemainmenu', 1 );
				JRequest::setVar( 'layout', 'form'  );
				JRequest::setVar( 'view'  , 'customfield');
				JRequest::setVar( 'edit', true );

				// Checkout the project
				$model = $this->getModel('customfield');
				$model->checkout();
			} break;
		}
		parent::display();
	}
	
  function save()
	{
		$post	= JRequest::get('post');
		$cid	= JRequest::getVar( 'cid', array(0), 'post', 'array' );
		$post['id'] = (int) $cid[0];

		$model = $this->getModel('customfield');

		if ($returnid = $model->store($post)) {
			$msg = JText::_( 'Custom field Saved' );
		} else {
			$msg = JText::_( 'Error Saving Custom field' ).$model->getError();
		}

		// Check the table in so it can be edited.... we are done with it anyway
		$model->checkin();
		
		if ( !$returnid || $this->getTask() == 'save' ) {
			$link = 'index.php?option=com_redevent&view=customfields';
		}
		else {
			$link = 'index.php?option=com_redevent&controller=customfield&task=edit&cid[]='.$returnid;
		}
		$this->setRedirect($link, $msg);
	}

	function remove()
	{
		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		if (count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to delete' ) );
		}

		$model = $this->getModel('customfield');
		
		if(!$model->delete($cid)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect( 'index.php?option=com_redevent&view=customfields' );
	}


	function publish()
	{
		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		if (count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to publish' ) );
		}

		$model = $this->getModel('customfield');
		if(!$model->publish($cid, 1)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}
    $link = 'index.php?option=com_redevent&view=customfields';
		$this->setRedirect($link);
	}


	function unpublish()
	{
		
		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		if (count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'Select an item to unpublish' ) );
		}

		$model = $this->getModel('customfield');
		if(!$model->publish($cid, 0)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}
    $link = 'index.php?option=com_redevent&view=customfields';
		$this->setRedirect($link);
	}

	function cancel()
	{
		// Checkin the project
		$model = $this->getModel('customfield');
		$model->checkin();

		$this->setRedirect( 'index.php?option=com_redevent&view=customfields' );
	}


	function orderup()
	{
		$model = $this->getModel('customfield');
		$model->move(-1);

		$this->setRedirect( 'index.php?option=com_redevent&view=customfields');
	}

	function orderdown()
	{
		$model = $this->getModel('customfield');
		$model->move(1);

		$this->setRedirect( 'index.php?option=com_redevent&view=customfields');
	}

	function saveorder()
	{
		$cid 	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		$order 	= JRequest::getVar( 'order', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);
		JArrayHelper::toInteger($order);

		$model = $this->getModel('customfield');
		$model->saveorder($cid, $order);

		$msg = 'New ordering saved';
		$this->setRedirect( 'index.php?option=com_redevent&view=customfields', $msg );
	}
}
?>
