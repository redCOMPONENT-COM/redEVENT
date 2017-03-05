<?php
/**
 * @package     Redevent.Plugin
 * @subpackage  Config.Ibc
 *
 * @copyright   Copyright (C) 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

// Import library dependencies
jimport('joomla.plugin.plugin');

/**
 * Specific parameters for redEVENT.
 *
 * @package     Redevent.Plugin
 * @subpackage  Config.Example
 * @since       3.1.3
 */
class PlgRedevent_ConfigIbc extends JPlugin
{
	/**
	 * The plugin identifier.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $context = 'ibc';

	/**
	 * Alters component parameters
	 *
	 * @param   JRegistry  &$params  parameters
	 *
	 * @return bool true on success
	 */
	public function onGetRedeventConfig(&$params)
	{
		$params->set('ga_emails_domain_filter', $this->params->get('ga_emails_domain_filter'));
		$params->set('disable_waitinglist_status_email', 1);
	}
}
