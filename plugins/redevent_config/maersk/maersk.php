<?php
/**
 * @package     Redevent.Plugin
 * @subpackage  Config.Maersk
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
 * @subpackage  Config.Maersk
 * @since       2.5
 */
class plgRedevent_configMaersk extends JPlugin
{
	/**
	 * The plugin identifier.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $context = 'maersk';

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
		$params->set('b2b_sessions_display_num', $this->params->get('b2b_sessions_display_num', 20));
		$params->set('b2b_members_display_num', $this->params->get('b2b_members_display_num', 20));
	}
}
