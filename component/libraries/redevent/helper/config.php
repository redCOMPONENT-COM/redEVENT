<?php
/**
 * @package    Redevent.Library
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevent config helper
 *
 * @package  Redevent.Library
 * @since    3.0
 */
class RedeventHelperConfig
{
	/**
	 * Pulls settings from database and stores in an static object
	 *
	 * @return object
	 */
	public static function config()
	{
		static $config;

		if (empty($config))
		{
			$config = JComponentHelper::getParams('com_redevent');

			// See if there are any plugins that wish to alter the configuration (client specific demands !)
			JPluginHelper::importPlugin('redevent_config');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('onGetRedeventConfig', array(&$config));
		}

		return $config;
	}

	/**
	 * Return a config value
	 *
	 * @param   string  $key      config key
	 * @param   mixed   $default  default value
	 *
	 * @return mixed
	 */
	public static function get($key, $default = null)
	{
		$config = self::config();

		return $config->get($key, $default);
	}
}
