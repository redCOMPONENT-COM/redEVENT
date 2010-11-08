<?php
/** 
 * @copyright Copyright (C) 2008 redCOMPONENT.com. All rights reserved. 
 * @license can be read in this package of software in the file license.txt or 
 * read on http://redcomponent.com/license.txt  
 * Developed by email@recomponent.com - redCOMPONENT.com 
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

// Set the table directory
JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.DS.'tables');

//Require helperfile
require_once (JPATH_COMPONENT_SITE.DS.'helpers'.DS.'helper.php');
require_once (JPATH_COMPONENT_SITE.DS.'helpers'.DS.'log.php');
require_once (JPATH_COMPONENT_SITE.DS.'helpers'.DS.'route.php');
require_once (JPATH_COMPONENT_SITE.DS.'helpers'.DS.'recurrence.php');
require_once (JPATH_COMPONENT_SITE.DS.'helpers'.DS.'customfields.php');
require_once (JPATH_COMPONENT_SITE.DS.'helpers'.DS.'tags.php');
require_once (JPATH_COMPONENT_SITE.DS.'helpers'.DS.'countries.php');
require_once (JPATH_COMPONENT_SITE.DS.'classes'.DS.'user.class.php');
require_once (JPATH_COMPONENT_SITE.DS.'classes'.DS.'useracl.class.php');
require_once (JPATH_COMPONENT_SITE.DS.'classes'.DS.'image.class.php');
require_once (JPATH_COMPONENT_SITE.DS.'classes'.DS.'output.class.php');
require_once (JPATH_COMPONENT_SITE.DS.'classes'.DS.'attendee.class.php');
require_once (JPATH_COMPONENT_SITE.DS.'classes'.DS.'attachment.class.php');
require_once (JPATH_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'error.class.php');

// redform
include_once(JPATH_SITE.DS.'components'.DS.'com_redform'.DS.'redform.core.php');

//perform cleanup if it wasn't done today (archive, delete, recurrence)
redEVENTHelper::cleanup();


// Require the controller
require_once (JPATH_COMPONENT.DS.'controller.php');

// Require specific controller if requested
if($controller = JRequest::getWord('controller')) {
    $path = JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php';
    if (file_exists($path)) {
        require_once $path;
    } else {
        $controller = '';
    }
}

// Create the controller
$classname	= 'RedeventController'.ucfirst($controller);
$controller = new $classname( );

// Perform the Request task
$controller->execute( JRequest::getVar('task') );

// Redirect if set by the controller
$controller->redirect();
?>
