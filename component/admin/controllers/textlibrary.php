<?php
/**
 * @version 1.0 $Id: admin.class.php 662 2008-05-09 22:28:53Z schlu $
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
 * redEVENT Text Library Controller
 *
 * @package Joomla
 * @subpackage redEVENT
 * @since 0.9
 */
class RedEventControllerTextLibrary extends RedEventController {
	/**
	 * Constructor
	 *
	 * @since 0.9
	 */
	function __construct() {
		parent::__construct();

		// Register Extra task
		$this->registerTask( 'save', 'textlibrary' );
		$this->registerTask( 'apply', 'textlibrary' );
		$this->registerTask( 'delete', 'textlibrary' );
	}

	function TextLibrary() {
		JRequest::setVar('view', 'textlibrary');
		JRequest::setVar('layout', 'default');
		
		parent::display();
	}
}
?>