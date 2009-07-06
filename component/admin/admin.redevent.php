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

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$document = JFactory::getDocument();
if ($document->getType() == 'html') {
	$document->addCustomTag( '<script type="text/javascript" src="'.JURI::root().'administrator/components/com_redform/js/jquery.js"></script>' );
	$document->addCustomTag( '<script type="text/javascript">jQuery.noConflict();</script>' );
	$document->addCustomTag( '<script type="text/javascript" src="'.JURI::root().'administrator/components/com_redform/js/jquery.random.js"></script>' );
}

//Require classes
require_once (JPATH_COMPONENT_SITE.DS.'helpers'.DS.'log.php');
require_once (JPATH_COMPONENT_SITE.DS.'helpers'.DS.'helper.php');
require_once (JPATH_COMPONENT_SITE.DS.'classes'.DS.'image.class.php');
require_once (JPATH_COMPONENT_SITE.DS.'classes'.DS.'output.class.php');
require_once (JPATH_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'admin.class.php');
require_once (JPATH_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'error.class.php');

// Set the table directory
JTable::addIncludePath(JPATH_COMPONENT.DS.'tables');

// Require the base controller
require_once (JPATH_COMPONENT.DS.'controller.php');

// Require specific controller if requested
if( $controller = JRequest::getWord('controller') ) {
	$path = JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php';
	if (file_exists($path)) {
		require_once $path;
	} else {
		$controller = '';
	}
}

//Create the controller
$classname  = 'RedEventController'.$controller;
$controller = new $classname( );

// Perform the Request task
$controller->execute( JRequest::getWord('task', 'redevent'));
$controller->redirect();
?>