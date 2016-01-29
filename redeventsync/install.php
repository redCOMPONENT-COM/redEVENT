<?php
/**
 * @package     Redeventsync
 * @subpackage  Installer
 *
 * @copyright   Copyright (C) 2013 - 2015 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

// Find redCORE installer to use it as base system
if (!class_exists('Com_RedcoreInstallerScript'))
{
	$searchPaths = array(
		// Discover install
		JPATH_ADMINISTRATOR . '/components/com_redcore',
		// Uninstall
		JPATH_LIBRARIES . '/redcore'
	);

	if ($redcoreInstaller = JPath::find($searchPaths, 'install.php'))
	{
		require_once $redcoreInstaller;
	}
	else
	{
		throw new Exception('REDCORE IS REQUIRED', 500);
	}
}

/**
 * Class Com_redformInstallerScript
 *
 * @package     Redeventsync
 * @subpackage  Installer
 * @since       3.0
 */
class Com_RedeventsyncInstallerScript extends Com_RedcoreInstallerScript
{
}
