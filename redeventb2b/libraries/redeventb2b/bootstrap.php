<?php
/**
 * @package    Redeventb2b.Library
 *
 * @copyright  Copyright (C) 2013 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

$redcoreLoader = JPATH_LIBRARIES . '/redcore/bootstrap.php';

if (!file_exists($redcoreLoader) || !JPluginHelper::isEnabled('system', 'redcore'))
{
	throw new Exception(JText::_('COM_REDFORM_REDCORE_INIT_FAILED'), 404);
}

include_once $redcoreLoader;

/**
 * Redeventb2b bootstrap class
 *
 * @package  Redeventb2b.Library
 * @since    3.0
 */
class Redeventb2bBootstrap
{
	/**
	 * Effectively bootstraps Redform
	 *
	 * @return void
	 */
	public static function bootstrap()
	{
		if (!defined('REDEVENTB2B_BOOTSTRAPPED'))
		{
			// Sets bootstrapped variable, to avoid bootstrapping rEDEVENT twice
			define('REDEVENTB2B_BOOTSTRAPPED', 1);

			define('REDEVENTB2B_LOG_DIRECTION_INCOMING', 0);
			define('REDEVENTB2B_LOG_DIRECTION_OUTGOING', 1);

			// Bootstraps redCORE
			RBootstrap::bootstrap();

			// For Joomla! 2.5 compatibility we load bootstrap2
//			if (version_compare(JVERSION, '3.0', '<') && JFactory::getApplication()->isAdmin() && JFactory::getApplication()->input->get('view') == 'config')
//			{
//				RHtmlMedia::setFramework('bootstrap2');
//			}

			// Register library prefix
			RLoader::registerPrefix('Redeventb2b', __DIR__);
			RLoader::registerPrefix('Redevent', JPATH_LIBRARIES . '/redevent');
			RLoader::registerPrefix('Rdf', JPATH_LIBRARIES . '/redform');
			RLoader::registerPrefix('Redmember', JPATH_LIBRARIES . '/redmember');

			// Make available the fields
			JFormHelper::addFieldPath(JPATH_LIBRARIES . '/redeventb2b/form/fields');

			// Make available the form rules
			JFormHelper::addRulePath(JPATH_LIBRARIES . '/redeventb2b/form/rules');
		}
	}
}
