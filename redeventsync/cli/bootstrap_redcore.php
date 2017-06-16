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

if (!defined('JPATH_REDCORE'))
{
	define('JPATH_REDCORE', JPATH_LIBRARIES . '/redcore');
}

if ($loadBootstrap && !defined('REDCORE_BOOTSTRAPPED'))
{
	define('REDCORE_BOOTSTRAPPED', 1);
}

if (!defined('REDCORE_LIBRARY_LOADED'))
{
	// Sets bootstrapped variable, to avoid bootstrapping redCORE twice
	define('REDCORE_LIBRARY_LOADED', 1);

	// We are still in Joomla 2.5 or another version so we use alias to prevent errors
	if (!class_exists('Joomla\Registry\Registry'))
	{
		class_alias('JRegistry', 'Joomla\Registry\Registry');
	}

	// Use our own base field
	if (!class_exists('JFormField', false))
	{
		$baseField = JPATH_LIBRARIES . '/redcore/joomla/form/field.php';

		if (file_exists($baseField))
		{
			require_once $baseField;
		}
	}

	// Register the classes for autoload.
	JLoader::registerPrefix('R', JPATH_REDCORE);

	// Setup the RLoader.
	RLoader::setup();

	// Make available the redCORE fields
	JFormHelper::addFieldPath(JPATH_REDCORE . '/form/field');
	JFormHelper::addFieldPath(JPATH_REDCORE . '/form/fields');

	// Make available the redCORE form rules
	JFormHelper::addRulePath(JPATH_REDCORE . '/form/rules');

	// HTML helpers
	JHtml::addIncludePath(JPATH_REDCORE . '/html');
	RHtml::addIncludePath(JPATH_REDCORE . '/html');

	// Load library language
	$lang = JFactory::getLanguage();
	$lang->load('lib_redcore', JPATH_REDCORE);

	// For Joomla! 2.5 compatibility we add some core functions
	if (version_compare(JVERSION, '3.0', '<'))
	{
		RLoader::registerPrefix('J',  JPATH_LIBRARIES . '/redcore/joomla', false, true);
	}

	// Make available the fields
	JFormHelper::addFieldPath(JPATH_LIBRARIES . '/redcore/form/fields');

	// Make available the rules
	JFormHelper::addRulePath(JPATH_LIBRARIES . '/redcore/form/rules');

	// Replaces Joomla database driver for redCORE database driver
	JFactory::$database = null;
	JFactory::$database = RFactory::getDbo();

	// We still need to set translate property to avoid notices as we check it from other functions
	$db = JFactory::getDbo();
	$db->translate = 0;
}
