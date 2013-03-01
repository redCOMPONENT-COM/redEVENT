<?php
/**
 * @package     Joomla
 * @subpackage  redEVENT
 * @copyright   redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license     GNU/GPL, see LICENSE.php
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

// No direct access
defined('_JEXEC') or die('Restricted access');

if (!defined('DS'))
{
	define('DS', DIRECTORY_SEPARATOR);
}

// Load FOF
include_once JPATH_LIBRARIES . '/fof/include.php';
if (!defined('FOF_INCLUDED'))
{
	JError::raiseError('500', 'FOF is not installed');
}

//Require classes
require_once (JPATH_COMPONENT_SITE.DS.'helpers'.DS.'log.php');
require_once (JPATH_COMPONENT_SITE.DS.'helpers'.DS.'helper.php');
require_once (JPATH_COMPONENT_SITE.DS.'helpers'.DS.'recurrence.php');
require_once (JPATH_COMPONENT_SITE.DS.'helpers'.DS.'route.php');
require_once (JPATH_COMPONENT_SITE.DS.'helpers'.DS.'tags.php');
require_once (JPATH_COMPONENT_SITE.DS.'helpers'.DS.'countries.php');
require_once (JPATH_COMPONENT_SITE.DS.'models'.DS.'eventhelper.php');
require_once (JPATH_COMPONENT_SITE.DS.'classes'.DS.'image.class.php');
require_once (JPATH_COMPONENT_SITE.DS.'classes'.DS.'output.class.php');
require_once (JPATH_COMPONENT_SITE.DS.'classes'.DS.'attachment.class.php');
require_once (JPATH_COMPONENT_SITE.DS.'classes'.DS.'attendee.class.php');
require_once (JPATH_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'admin.class.php');
require_once (JPATH_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'error.class.php');
require_once (JPATH_COMPONENT_ADMINISTRATOR.DS.'customfield'.DS.'includes.php');

// redform
if (!file_exists(JPATH_SITE.DS.'components'.DS.'com_redform'.DS.'redform.core.php')) {
	JError::raiseWarning(0,JText::_('COM_REDEVENT_ERROR_REDFORMCORE_NOT_FOUND'));
}
else {
	include_once(JPATH_SITE.DS.'components'.DS.'com_redform'.DS.'redform.core.php');
}
// Set the table directory
JTable::addIncludePath(JPATH_COMPONENT.DS.'tables');

// Require the base controller
require_once (JPATH_COMPONENT.DS.'controller.php');

// Require specific controller if requested (non fof !)
if( $controller = JRequest::getWord('controller') ) {
	$path = JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php';
	if (file_exists($path)) {
		require_once $path;
	} else {
		$controller = '';
	}

	//Create the controller
	$classname  = 'RedEventController'.$controller;
	$controller = new $classname( );

	// Perform the Request task
	$controller->execute( JRequest::getWord('task', 'redevent'));
	$controller->redirect();
}
else {
	FOFDispatcher::getTmpInstance('com_redevent')->dispatch();
}