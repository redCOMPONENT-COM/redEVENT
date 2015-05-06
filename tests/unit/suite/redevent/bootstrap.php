<?php
/**
 * Created by PhpStorm.
 * User: julien
 * Date: 12/11/14
 * Time: 4:38 PM
 */
require_once dirname(dirname(dirname(__FILE__))) . '/bootstrap.php';

$redcoreLoader = JPATH_LIBRARIES . '/redcore/bootstrap.php';

if (!file_exists($redcoreLoader))
{
	throw new Exception(JText::_('COM_REDEVENT_REDCORE_REQUIRED'), 404);
}

include_once $redcoreLoader;

// Bootstraps redCORE
RBootstrap::bootstrap();

// Register redFORM
RLoader::registerPrefix('Rdf', JPATH_LIBRARIES . '/redform');

// Register library prefix
RLoader::registerPrefix('Redevent', JPATH_LIBRARIES. '/redevent');
