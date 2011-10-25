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
 * EventList Component Settings Controller
 *
 * @package Joomla
 * @subpackage EventList
 * @since 0.9
 */
class RedEventControllerSettings extends RedEventController
{
	/**
	 * Constructor
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
	function __construct()
	{
		parent::__construct();

		// Register Extra task
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
		global $option;

		$model = $this->getModel('settings');

		$model->checkin();

		$this->setRedirect( 'index.php?option='.$option.'&view=redevent' );
	}

	/**
	 * logic to create the edit venue view
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
	function edit( )
	{
		JRequest::setVar( 'view', 'settings' );

		parent::display();

		$model = $this->getModel('settings');

		$model->checkout();
	}

	/**
	 * saves the venue in the database
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
	function save()
	{
		// Check for request forgeries
		JRequest::checkToken() or die( 'Invalid Token' );

		// Sanitize
		$task	= JRequest::getVar('task');
		$post 	= JRequest::get( 'post' );

		//get model
		$model 	= $this->getModel('settings');

		if ($model->store($post)) {
			$msg	= JText::_('COM_REDEVENT_SETTINGS_SAVE');
		} else {
			$msg	= JText::_('COM_REDEVENT_SAVE_SETTINGS_FAILED');
		}

		switch ($task)
		{
			case 'apply':
				$link = 'index.php?option=com_redevent&controller=settings&task=edit';
				break;

			default:
				$link = 'index.php?option=com_redevent&view=redevent';
				break;
		}
		$model->checkin();

		$this->setRedirect( $link, $msg );
	}
}
?>