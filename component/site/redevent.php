<?php
/** 
 * @copyright Copyright (C) 2008 redCOMPONENT.com. All rights reserved. 
 * @license can be read in this package of software in the file license.txt or 
 * read on http://redcomponent.com/license.txt  
 * Developed by email@recomponent.com - redCOMPONENT.com 
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

//Require helperfile
require_once (JPATH_COMPONENT_SITE.DS.'helpers'.DS.'helper.php');
require_once (JPATH_COMPONENT_SITE.DS.'helpers'.DS.'log.php');
require_once (JPATH_COMPONENT_SITE.DS.'classes'.DS.'user.class.php');
require_once (JPATH_COMPONENT_SITE.DS.'classes'.DS.'image.class.php');
require_once (JPATH_COMPONENT_SITE.DS.'classes'.DS.'output.class.php');
require_once (JPATH_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'error.class.php');

//perform cleanup if it wasn't done today (archive, delete, recurrence)
redEVENTHelper::cleanup();

// Set the table directory
JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.DS.'tables');

// Require the controller
require_once (JPATH_COMPONENT.DS.'controller.php');

// Create the controller
$controller = JRequest::getWord('view', '');
if ($controller == 'redevent') {
	$controller = 'details';
}

/* Custom redirect */
if (in_array($controller, array('details', 'signup', 'confirmation', 'upcomingevents', 'upcomingvenueevents', 'calendar'))) {
	/* Require specific controller if requested */
	require_once (JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php');
	
}
else $controller = '';

$classname  = 'RedeventController'.$controller;
$controller = new $classname( );

// Perform the Request task
$controller->execute( JRequest::getVar('task', JRequest::getWord('view', null), 'default', 'cmd') );

// Redirect if set by the controller
$controller->redirect();
?>
