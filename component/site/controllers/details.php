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
class RedEventControllerDetails extends RedEventController
{
	/**
	 * Constructor
	 *
	 * @since 0.9
	 */
	function __construct() {
		parent::__construct();
		$this->registerTask( 'details', 'display' );
	}

	/**
	 * Display the view
	 * 
	 * @since 0.9
	 */
	function display() {
		
		if (JRequest::getVar('format', 'html') == 'html')
		{
		/* Create the view object */
		$view = $this->getView('details', 'html');
		$this->addModelPath(JPATH_BASE.DS.'administrator'.DS.'components'.DS.'com_redevent'.DS.'models');
		
		/* Standard model */
		$view->setModel( $this->getModel( 'details', 'RedeventModel' ), true );
		$view->setModel( $this->getModel( 'waitinglist', 'RedeventModel' ));
		$view->setModel( $this->getModel( 'event', 'RedeventModel' ));
		$view->setLayout('default');
		
		
		/* Now display the view. */
		$view->display();
		}
		else {
			parent::display();
		}
	}
}
?>