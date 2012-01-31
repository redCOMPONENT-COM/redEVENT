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
class RedeventControllerPricegroups extends JController
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
				JRequest::setVar( 'view'  , 'pricegroup');
				JRequest::setVar( 'edit', false );

				// Checkout the project
				$model = $this->getModel('pricegroup');
				$model->checkout();
			} break;
			case 'edit'    :
			{
				JRequest::setVar( 'hidemainmenu', 1 );
				JRequest::setVar( 'layout', 'form'  );
				JRequest::setVar( 'view'  , 'pricegroup');
				JRequest::setVar( 'edit', true );

				// Checkout the project
				$model = $this->getModel('pricegroup');
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

		$model = $this->getModel('pricegroup');

		if ($returnid = $model->store($post)) {
			$msg = JText::_( 'COM_REDEVENT_PRICEGROUPS_PRICEGROUP_SAVED' );
		} else {
			$msg = JText::_( 'COM_REDEVENT_PRICEGROUPS_PRICEGROUP_SAVE_ERROR' ).$model->getError();
		}

		// Check the table in so it can be edited.... we are done with it anyway
		$model->checkin();
		
		if ( !$returnid || $this->getTask() == 'save' ) {
			$link = 'index.php?option=com_redevent&view=pricegroups';
		}
		else {
			$link = 'index.php?option=com_redevent&controller=pricegroups&task=edit&cid[]='.$returnid;
		}
		$this->setRedirect($link, $msg);
	}

	function remove()
	{
		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		if (count( $cid ) < 1) {
			JError::raiseError(500, JText::_('COM_REDEVENT_Select_an_item_to_delete' ) );
		}

		$model = $this->getModel('pricegroup');
		
		if(!$model->delete($cid)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect( 'index.php?option=com_redevent&view=pricegroups' );
	}

	function cancel()
	{
		// Checkin the project
		$model = $this->getModel('pricegroup');
		$model->checkin();

		$this->setRedirect( 'index.php?option=com_redevent&view=pricegroups' );
	}


	function orderup()
	{
		$model = $this->getModel('pricegroup');
		$model->move(-1);

		$this->setRedirect( 'index.php?option=com_redevent&view=pricegroups');
	}

	function orderdown()
	{
		$model = $this->getModel('pricegroup');
		$model->move(1);

		$this->setRedirect( 'index.php?option=com_redevent&view=pricegroups');
	}

	function saveorder()
	{
		$cid 	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		$order 	= JRequest::getVar( 'order', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);
		JArrayHelper::toInteger($order);

		$model = $this->getModel('pricegroup');
		$model->saveorder($cid, $order);

		$msg = 'New ordering saved';
		$this->setRedirect( 'index.php?option=com_redevent&view=pricegroups', $msg );
	}
}
