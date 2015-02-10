<?php
/**
 * @package     Redevent
 * @subpackage  Install
 *
 * @copyright   Copyright (C) 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

// Find redCORE installer to use it as base system
if (!class_exists('Com_RedcoreInstallerScript'))
{
	$searchPaths = array(
		// Install
		dirname(__FILE__) . '/redCORE',
		// Discover install
		JPATH_ADMINISTRATOR . '/components/com_redcore'
	);

	if ($redcoreInstaller = JPath::find($searchPaths, 'install.php'))
	{
		require_once $redcoreInstaller;
	}
	else
	{
		throw new Exception(JText::_('COM_REDEVENT_INSTALLER_ERROR_REDFORM_IS_REQUIRED'), 500);
	}
}

/**
 * Custom installation of Redevent.
 *
 * @package     Redevent
 * @subpackage  Install
 * @since       3.0
 */
class Com_RedeventInstallerScript extends Com_RedcoreInstallerScript
{
}
