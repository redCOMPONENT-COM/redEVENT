<?php
/**
 * @package     Reditem.Library
 * @subpackage  Cli
 *
 * @copyright   Copyright (C) 2012 - 2017 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

// Must be called from the command line
('cli' === php_sapi_name()) or die;

error_reporting(E_ALL);
ini_set('display_errors', 1);

defined('_JEXEC') || define('_JEXEC', 1);

if (!defined('REDEVENTSYNC_BOOTSTRAPPED'))
{
	// Sets bootstrapped variable, to avoid bootstrapping rEDEVENT twice
	define('REDEVENTSYNC_BOOTSTRAPPED', 1);

	define('REDEVENTSYNC_LOG_DIRECTION_INCOMING', 0);
	define('REDEVENTSYNC_LOG_DIRECTION_OUTGOING', 1);

	// Bootstraps redCORE
	RBootstrap::bootstrap();

	// Register library prefix
	RLoader::registerPrefix('Resync', JPATH_LIBRARIES . '/redeventsync');

	// Make available the fields
	JFormHelper::addFieldPath(JPATH_LIBRARIES . '/redeventsync/form/fields');

	// Make available the form rules
	JFormHelper::addRulePath(JPATH_LIBRARIES . '/redeventsync/form/rules');
}
