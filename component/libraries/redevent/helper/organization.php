<?php
/**
 * @package    Redevent.Library
 * @copyright  Copyright (C) 2008 - 2020 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Helper class for Organization
 *
 * @package  Redevent.Library
 * @since    2.5
 */
class RedeventHelperOrganization
{
	/**
	 * Return org settings
	 *
	 * @param   int  $orgId  org id
	 *
	 * @return JRegistry
	 */
	public static function getSettings($orgId)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('*');
		$query->from('#__redevent_organizations');
		$query->where('organization_id = ' . (int) $orgId);

		$db->setQuery($query);

		if ($res = $db->loadObject())
		{
			unset($res->id);
			unset($res->organization_id);
			unset($res->checked_out);
			unset($res->checked_out_time);
		}

		return new JRegistry($res);
	}

	/**
	 * Return array of user organizations as orgId => [organization_id, level]
	 *
	 * @param   int  $userId  user id
	 *
	 * @return array
	 */
	public static function getUserOrganizations($userId)
	{
		$userData = RedmemberApi::getUser($userId);

		return $userData->getOrganizations();
	}
}
