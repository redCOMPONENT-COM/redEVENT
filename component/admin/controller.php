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
 * @subpackage redEVENT
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
		throw new Exception('deprecated, use the function from controllers/redevent');
	}

	/**
	 * displays the fast addvenue screen
	 *
	 * @since 0.9
	 */
	function addvenue( )
	{
		throw new Exception('deprecated, use the function from controllers/redevent');
	}

	/**
	 * import eventlist events, categories, and venues.
	 *
	 */
	function importeventlist()
	{
		throw new Exception('deprecated, use the function from controllers/redevent');
	}

	/**
	 * triggers the autoarchive function
	 *
	 */
	function autoarchive()
	{
		throw new Exception('deprecated, use the function from controllers/redevent');
	}

	function insertevent()
	{
		throw new Exception('deprecated, use the function from controllers/redevent');
	}

	function sampledata()
	{
		throw new Exception('deprecated, use the function from controllers/redevent');
	}

	/**
	 * Delete attachment
	 *
	 * @return true on sucess
	 * @access private
	 * @since 1.1
	 */
	function ajaxattachremove()
	{
		throw new Exception('deprecated, use the function from controllers/redevent');
	}
	
	function selectuser()
	{
		throw new Exception('deprecated, use the function from controllers/redevent');
	}
}
