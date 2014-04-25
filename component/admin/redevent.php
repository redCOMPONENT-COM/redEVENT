<?php
/**
 * @package    Redevent.Admin
 * @copyright  redEVENT (C) 2008-2013 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU General Public License version 2 or later, see LICENSE.
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

// Register library prefix
JLoader::registerPrefix('R', JPATH_LIBRARIES . '/redcore');
RLoader::registerPrefix('Redevent', JPATH_LIBRARIES . '/redevent');
RLoader::registerPrefix('Rdf', JPATH_LIBRARIES . '/redform');

//Require classes
require_once (JPATH_COMPONENT_SITE.DS.'helpers'.DS.'recurrence.php');
require_once (JPATH_COMPONENT_SITE.DS.'helpers'.DS.'route.php');
require_once (JPATH_COMPONENT_SITE.DS.'models'.DS.'eventhelper.php');
require_once (JPATH_COMPONENT_SITE.DS.'classes'.DS.'image.class.php');
require_once (JPATH_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'admin.class.php');
require_once (JPATH_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'error.class.php');

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
else
{
	FOFDispatcher::getTmpInstance('com_redevent')->dispatch();
}
