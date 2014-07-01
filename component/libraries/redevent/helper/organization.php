<?php
/**
 * @package    Redevent.Library
 * @copyright  Copyright (C) 2008 - 2014 redCOMPONENT.com. All rights reserved.
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
	 * @return object
	 */
	public static function getSettings($orgId)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('*');
		$query->from('#__redevent_organizations');
		$query->where('organization_id = ' . (int) $orgId);

		$db->setQuery($query);
		$res = $db->loadObject();

		unset($res->id);
		unset($res->organization_id);
		unset($res->checked_out);
		unset($res->checked_out_time);

		return $res;
	}
}
