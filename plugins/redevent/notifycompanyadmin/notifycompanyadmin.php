<?php
/**
 * @package     Redevent.Plugin
 * @subpackage  Redevent.NotifyCompanyAdmin
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
 * @subpackage  Redevent.NotifyCompanyAdmin
 * @since       2.5
 */
class plgRedeventNotifyCompanyAdmin extends JPlugin
{
	/**
	 * Adds email for admin notifications of registrations
	 *
	 * @param   int    $attendee_id  attendee id from register table
	 * @param   array  &$emails      array of emails to add to
	 *
	 * @throws Exception
	 *
	 * @return void
	 */
	public function onGetRegistrationAdminEmails($attendee_id, &$emails)
	{
		// Make sure redMEMBER is installed !
		if (!file_exists(JPATH_SITE . '/components/com_redmember/lib/redmemberlib.php'))
		{
			throw new Exception('redMEMBER is required', 404);
		}

		require_once JPATH_SITE . '/components/com_redmember/lib/redmemberlib.php';

		// Get Admins
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('uid')->from('#__redevent_register')->where('id = ' . (int) $attendee_id);

		$db->setQuery($query);
		$user_id = $db->loadResult();

		$ids = RedmemberLib::getOrganizationManagers($user_id);

		foreach ($ids as $admin_id)
		{
			$user = JUser::getInstance($admin_id);
			$emails[] = array('name' => $user->get('name'), 'email' => $user->get('email'));
		}
	}
}
