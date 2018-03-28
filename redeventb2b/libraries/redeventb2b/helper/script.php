<?php
/**
 * @package    Redeventb2b.Library
 * @copyright  Copyright (C) 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redeventb2b script Helper
 *
 * @package  Redeventb2b.Library
 * @since __deploy_version__
 */
class Redeventb2bHelperScript
{
	/**
	 * Load script, with version number
	 *
	 * @param   string  $filename   file name
	 * @param   string  $extension  optional extension
	 *
	 * @return void
	 *
	 * @since __deploy_version__
	 */
	public static function load($filename, $extension = null)
	{
		$extension = $extension ?: 'com_redeventb2b';
		$toLoad = "$extension/$filename";

		$path = RHelperAsset::script($toLoad, false, true, true);
		$path .= '?v=' . self::getScriptsVersion();

		JFactory::getDocument()->addScript($path);
	}

	/**
	 * B2b route
	 *
	 * @return string
	 */
	public static function getScriptsVersion()
	{
		$xml = self::getManifest();

		return (int) $xml->scriptsVersion;
	}

	/**
	 * Get manifest xml
	 *
	 * @return SimpleXMLElement
	 *
	 * @since  __deploy_version__
	 */
	private static function getManifest()
	{
		return RComponentHelper::getComponentManifestFile('com_redeventb2b');
	}
}
