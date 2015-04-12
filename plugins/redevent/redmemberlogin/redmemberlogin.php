<?php
/**
 * @package    Redevent.Plugin
 *
 * @copyright  Copyright (C) 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
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
class plgRedeventRedmemberlogin extends JPlugin
{
	/**
	 * The plugin identifier.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $context = 'redmemberlogin';

	/**
	 * Redirects to redmember registration
	 *
	 * @param   string  $message  the redirect message
	 *
	 * @return bool true on success
	 */
	public function onRequireUserBeforeRegistration($message)
	{
		$uri = JFactory::getURI();
		JFactory::getApplication()->redirect('index.php?option=com_redmember&view=rmlogin&return=' . base64_encode($uri->toString()), $message);
	}
}
