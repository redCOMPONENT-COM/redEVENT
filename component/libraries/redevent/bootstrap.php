<?php
/**
 * @package    Redevent.Library
 *
 * @copyright  Copyright (C) 2009 - 2014 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

$redcoreLoader = JPATH_LIBRARIES . '/redcore/bootstrap.php';

if (!file_exists($redcoreLoader) || !JPluginHelper::isEnabled('system', 'redcore'))
{
	throw new Exception(JText::_('COM_REDSHOPB_REDCORE_INIT_FAILED'), 404);
}

include_once $redcoreLoader;

// Load library language
$lang = JFactory::getLanguage();
$lang->load('lib_redevent', __DIR__);

/**
 * Redevent bootstrap class
 *
 * @package     Redevent
 * @subpackage  System
 * @since       3.0
 */
class RedeventBootstrap
{
	/**
	 * Effectively bootstraps Redevent.
	 *
	 * @return void
	 */
	public static function bootstrap()
	{
		if (!defined('REDEVENT_BOOTSTRAPPED'))
		{
			// Sets bootstrapped variable, to avoid bootstrapping rEDEVENT twice
			define('REDEVENT_BOOTSTRAPPED', 1);

			// Bootstraps redCORE
			RBootstrap::bootstrap();

			// Bootstraps redFORM
			/**
			 * @todo: check why this doesnt work: RLoader::registerPrefix('Rdf', JPATH_LIBRARIES . '/redform');
			 */
			require_once JPATH_LIBRARIES . '/redform/bootstrap.php';
			RdfBootstrap::bootstrap();

			// Register library prefix
			RLoader::registerPrefix('Redevent', __DIR__);

			// Make available the fields
			JFormHelper::addFieldPath(JPATH_LIBRARIES . '/redevent/form/fields');
			JFormHelper::addFieldPath(JPATH_LIBRARIES . '/redevent/form/field');

			// Make available the form rules
			JFormHelper::addRulePath(JPATH_LIBRARIES . '/redevent/form/rule');

			// Use bootstrap3
			/* RHtmlMedia::setFramework('bootstrap3'); */
		}
	}
}
