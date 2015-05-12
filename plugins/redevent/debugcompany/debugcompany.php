<?php
/**
 * @package     Redevent.Plugin
 * @subpackage  Redevent.Maerskregistration
 *
 * @copyright   Copyright (C) 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */


defined('JPATH_BASE') or die;

// Import library dependencies
jimport('joomla.plugin.plugin');

require_once JPATH_SITE . '/components/com_redmember/lib/redmemberlib.php';

/**
 * debug
 *
 * @package     Redevent.Plugin
 * @subpackage  Redevent.Maerskregistration
 * @since       2.5
 */
class plgRedeventDebugcompany extends JPlugin
{
	public function onUserSaved($id, $isNew)
	{
		$mailer = JFactory::getMailer();
		$mailer->AddAddress('julien@redweb.dk');

		$mailer->setSubject('user updated: ' . $id);

		$user = RedmemberLib::getUserData($id);

		$uri = $actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

		$mailer->msgHTML(sprintf('from: %s<br>%s', $uri, print_r($user, true)));

		$mailer->send();
	}
}
