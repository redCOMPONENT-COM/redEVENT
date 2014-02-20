<?php
/**
 * @package     Redevent.Plugin
 * @subpackage  Config.Ibcweb
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
 * @subpackage  Config.Ibcweb
 * @since       2.5
 */
class plgRedevent_configIbcweb extends JPlugin
{
	/**
	 * The plugin identifier.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $context = 'ibcweb';

	/**
	 * Alters component parameters
	 *
	 * @param   JRegistry  $params  parameters
	 *
	 * @return bool true on success
	 */
	public function onGetRedeventConfig(&$params)
	{
		$params->set('disable_waitinglist_status_email', 1);
	}
}
