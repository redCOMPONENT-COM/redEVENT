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
	 * @return void
	 */
	public function onGetRegistrationAdminEmails($attendee_id, &$emails)
	{
		$fromB2b = JFactory::getApplication()->input->get('from') == 'b2b';

		if ($fromB2b)
		{
			// Add user making the reservation
			$this->addCurrentUser($emails);
		}
		else
		{
			$this->addOrganizationAdmins($attendee_id, $emails);
		}
	}

	/**
	 * add current user email
	 *
	 * @param   array  &$emails  admin emails
	 *
	 * @return void
	 */
	protected function addCurrentUser(&$emails)
	{
		$user = JFactory::getUser();

		if (!$user)
		{
			return;
		}

		$emails[] = array('name' => $user->get('name'), 'email' => $user->get('email'));
	}

	/**
	 * add organization admin users emails
	 *
	 * @param   int    $attendee_id  attendee id from register table
	 * @param   array  &$emails      admin emails
	 *
	 * @throws Exception
	 *
	 * @return void
	 */
	protected function addOrganizationAdmins($attendee_id, &$emails)
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
