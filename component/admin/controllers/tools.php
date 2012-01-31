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
 * EventList Component Tools Controller
 *
 * @package Joomla
 * @subpackage redevent
 * @since 0.9
 */
class RedEventControllerTools extends RedEventController
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
		$this->registerTask( 'cleaneventimg', 	'delete' );
		$this->registerTask( 'cleanvenueimg', 	'delete' );
	}

	/**
	 * logic to massdelete unassigned images
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
	function delete()
	{
		$task = JRequest::getCmd('task');

		if ($task == 'cleaneventimg') {
			$type = JText::_('COM_REDEVENT_EVENT');
		} else {
			$type = JText::_('COM_REDEVENT_VENUE');
		}

		$model = $this->getModel('tools');

		$total = $model->delete();

		$link = 'index.php?option=com_redevent&view=tools';

		$msg = $total.' '.$type.' '.JText::_('COM_REDEVENT_IMAGES_DELETED');

		$this->setRedirect( $link, $msg );
 	}
 	
 	function checkdb()
 	{
		$model = $this->getModel('tools');
		
		$res = $model->checkdb();
		
		$link = 'index.php?option=com_redevent&view=tools';
		
		if ($res) {
			$msg  = JText::_('COM_REDEVENT_DB_TEST_OK');
			$type = 'message';
		}
		else {
			$msg  = JText::_('COM_REDEVENT_DB_TEST_KO').': '.$model->getError();
			$type = 'error';
		}
		
		$this->setRedirect( $link, $msg, $type ); 		
 	}
 	
 	function fixdb()
 	{
		$model = $this->getModel('tools');
		
		$res = $model->fixdb();
		
		$link = 'index.php?option=com_redevent&view=tools';
		
		if ($res) {
			$msg  = JText::_('COM_REDEVENT_DB_FIX_OK');
			$type = 'message';
		}
		else {
			$msg  = JText::_('COM_REDEVENT_DB_FIX_KO').': '.$model->getError();
			$type = 'error';
		}
		
		$this->setRedirect( $link, $msg, $type ); 		
 	}
}
