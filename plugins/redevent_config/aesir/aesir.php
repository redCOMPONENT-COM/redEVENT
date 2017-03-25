<?php
/**
 * @package     Redevent.Plugin
 * @subpackage  Config.Aesir
 *
 * @copyright   Copyright (C) 2017 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

// Import library dependencies
jimport('joomla.plugin.plugin');

/**
 * Specific parameters for redEVENT.
 *
 * @package     Redevent.Plugin
 * @subpackage  Config.Aesir
 * @since       2.5
 */
class PlgRedevent_ConfigAesir extends JPlugin
{
	/**
	 * The plugin identifier.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $context = 'aesir';

	/**
	 * Alters component parameters
	 *
	 * @param   JRegistry  &$params  parameters
	 *
	 * @return boolean true on success
	 */
	public function onGetRedeventConfig(&$params)
	{
		$params->merge($this->params);
	}
}
