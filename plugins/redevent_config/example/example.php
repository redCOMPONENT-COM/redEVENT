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
class plgRedevent_configExample extends JPlugin
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
	 * @param   JRegistry  $params  parameters
	 *
	 * @return bool true on success
	 */
	public function onGetRedeventConfig(&$params)
	{
		$params->set('b2b_show_open', 0);
		$params->set('redirect_search_unique_result_to_details', 1);
	}
}
