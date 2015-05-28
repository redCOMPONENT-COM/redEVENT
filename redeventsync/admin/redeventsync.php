<?php
/**
 * @package     Redcomponent.redeventsync
 * @subpackage  com_redeventsync
 * @copyright   Copyright (C) 2013-2015 redCOMPONENT.com
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die();

$loader = JPATH_LIBRARIES . '/redeventsync/bootstrap.php';

if (!file_exists($loader))
{
	throw new Exception(JText::_('COM_redeventsync_LIB_INIT_FAILED'), 404);
}

include_once $loader;

// Bootstraps redEVENTSYNC
ResyncBootstrap::bootstrap();

$jinput = JFactory::getApplication()->input;

// Require the base controller
require_once JPATH_COMPONENT . '/controller.php';

// Execute the controller
$controller = JControllerLegacy::getInstance('redeventsync');
$controller->execute($jinput->get('task'));
$controller->redirect();
