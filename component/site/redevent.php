<?php
/**
 * @package    Redevent.Site
 *
 * @copyright  Copyright (C) 2008 - 2014 redCOMPONENT.com. All rights reserved.
 * @license GNU General Public License version 2 or later, see LICENSE.
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

// Load FOF
include_once JPATH_LIBRARIES . '/fof/include.php';
if (!defined('FOF_INCLUDED'))
{
	JError::raiseError('500', 'FOF is not installed');
}

// Register library prefix
JLoader::registerPrefix('Redevent', JPATH_LIBRARIES . '/redevent');
JLoader::registerPrefix('RedForm', JPATH_LIBRARIES . '/redform');

// Set the table directory
JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.DS.'tables');

//Require helperfile
require_once (JPATH_COMPONENT_SITE.DS.'helpers'.DS.'helper.php');
require_once (JPATH_COMPONENT_SITE.DS.'helpers'.DS.'log.php');
require_once (JPATH_COMPONENT_SITE.DS.'helpers'.DS.'route.php');
require_once (JPATH_COMPONENT_SITE.DS.'helpers'.DS.'recurrence.php');
require_once (JPATH_COMPONENT_SITE.DS.'helpers'.DS.'tags.php');
require_once (JPATH_COMPONENT_SITE.DS.'helpers'.DS.'countries.php');
require_once (JPATH_COMPONENT_SITE.DS.'classes'.DS.'useracl.class.php');
require_once (JPATH_COMPONENT_SITE.DS.'classes'.DS.'image.class.php');
require_once (JPATH_COMPONENT_SITE.DS.'classes'.DS.'output.class.php');
require_once (JPATH_COMPONENT_SITE.DS.'classes'.DS.'attendee.class.php');
require_once (JPATH_COMPONENT_SITE.DS.'classes'.DS.'attachment.class.php');
require_once (JPATH_COMPONENT_SITE.DS.'classes'.DS.'ajaxpagination.php');
require_once (JPATH_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'error.class.php');

//perform cleanup if it wasn't done today (archive, delete, recurrence)
redEVENTHelper::cleanup();

// Require the controller
require_once (JPATH_COMPONENT.DS.'controller.php');

// Require specific controller if requested
if ($controller = JRequest::getWord('controller'))
{
	$path = JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php';
    if (file_exists($path))
    {
        require_once $path;
    }
    else
    {
        $controller = '';
    }
}

// Create the controller
$classname	= 'RedeventController'.ucfirst($controller);
$controller = new $classname();

// Perform the Request task
$controller->execute(JRequest::getVar('task'));

// Redirect if set by the controller
$controller->redirect();
