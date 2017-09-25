<?php
/**
 * @package     Redevent.Plugin
 * @subpackage  Config.Kolding
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
 * @subpackage  Config.Kolding
 * @since       2.5
 */
class PlgRedevent_ConfigKolding extends JPlugin
{
	/**
	 * The plugin identifier.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $context = 'kolding';

	/**
	 * Alters component parameters
	 *
	 * @param   JRegistry  $params  parameters
	 *
	 * @return boolean true on success
	 */
	public function onGetRedeventConfig(&$params)
	{
		// Add some fixed values
		$params->set('attendees_export_csv_cancelled_as_unpaid', 1);

		return true;
	}
}
