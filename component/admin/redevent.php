<?php
/**
 * @package    Redevent.Admin
 * @copyright  redEVENT (C) 2008-2013 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

$redcoreLoader = JPATH_LIBRARIES . '/redcore/bootstrap.php';

if (!file_exists($redcoreLoader) || !JPluginHelper::isEnabled('system', 'redcore'))
{
	throw new Exception(JText::_('COM_REDEVENT_REDCORE_REQUIRED'), 404);
}

include_once $redcoreLoader;

// Bootstraps redCORE
RBootstrap::bootstrap();

// Bootstraps Redevent application
$redEVENTLoader = JPATH_LIBRARIES . '/redevent/bootstrap.php';
require_once $redEVENTLoader;

RedeventBootstrap::bootstrap();

// Register backend prefix
RLoader::registerPrefix('Redevent', __DIR__);

$app = JFactory::getApplication();

// Instanciate and execute the front controller.
$controller = JControllerLegacy::getInstance('Redevent');

// Check access.
if (!JFactory::getUser()->authorise('core.manage', 'com_redevent'))
{
	$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');

	return false;
}

$controller->execute($app->input->get('task'));
$controller->redirect();
