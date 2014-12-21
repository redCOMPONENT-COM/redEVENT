<?php
/**
 * @package    Redevent.Site
 *
 * @copyright  Copyright (C) 2008 - 2014 redCOMPONENT.com. All rights reserved.
 * @license GNU General Public License version 2 or later, see LICENSE.
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

if (!defined('DS'))
{
	define('DS', DIRECTORY_SEPARATOR);
}

$redcoreLoader = JPATH_LIBRARIES . '/redcore/bootstrap.php';

if (!file_exists($redcoreLoader) || !JPluginHelper::isEnabled('system', 'redcore'))
{
	throw new Exception(JText::_('COM_REDEVENT_REDCORE_REQUIRED'), 404);
}

include_once $redcoreLoader;

// Bootstraps redCORE
RBootstrap::bootstrap();

// Register redFORM
RLoader::registerPrefix('Rdf', JPATH_LIBRARIES . '/redform');

// Bootstraps Redevent application
$redEVENTLoader = JPATH_LIBRARIES . '/redevent/bootstrap.php';
require_once $redEVENTLoader;

RedeventBootstrap::bootstrap();

// Perform cleanup if it wasn't done today (archive, delete, recurrence)
RedeventHelper::cleanup();

try
{
	$controller = JControllerLegacy::getInstance('Redevent');
	$controller->execute(JFactory::getApplication()->input->get('task'));
	$controller->redirect();
}
catch (Exception $e)
{
	if (JDEBUG || 1)
	{
		echo 'Exception:'. $e->getMessage();
		echo "<pre>" . $e->getTraceAsString() . "</pre>";
		exit(0);
	}

	throw $e;
}
