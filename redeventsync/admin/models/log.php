<?php
/**
 * @package     Redcomponent.redeventsync
 * @subpackage  com_redeventsync
 * @copyright   Copyright (C) 2013 redCOMPONENT.com
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die();

class RedeventsyncModelLog extends FOFModel
{
	/**
	 * Clear logs
	 *
	 * @throws Exception
	 *
	 * @return boolean
	 */
	public function clear()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->delete();
		$query->from('#__redeventsync_logs');

		$db->setQuery($query);

		if (!$db->query())
		{
			throw new Exception('Error deleting logs: ' . $db->getErrorMsg());
		}

		return true;
	}
}
