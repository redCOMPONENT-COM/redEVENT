<?php
/**
 * @package     Redevent.Plugin
 * @subpackage  Config.Example
 *
 * @copyright   Copyright (C) 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

// Import library dependencies
jimport('joomla.plugin.plugin');

/**
 * Specific parameters for redEVENT.
 *
 * @package     Redevent.Plugin
 * @subpackage  Config.Example
 * @since       2.5
 */
class PlgRedevent_ConfigExample extends JPlugin
{
	/**
	 * The plugin identifier.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $context = 'example';

	/**
	 * Alters component parameters
	 *
	 * @param   JRegistry  &$params  parameters
	 *
	 * @return bool true on success
	 */
	public function onGetRedeventConfig(&$params)
	{
		// Add some fixed values
		$params->set('param_a', 0);
		$params->set('param_b', 1);

		// Merge params from plugin
		$params->merge($this->params);
	}
}
