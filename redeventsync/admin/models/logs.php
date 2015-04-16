<?php
/**
 * @package     Redcomponent.redeventsync
 * @subpackage  com_redeventsync
 * @copyright   Copyright (C) 2013 redCOMPONENT.com
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die();

class RedeventsyncModelLogs extends FOFModel
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

	/**
	 * Builds the SELECT query
	 *
	 * @param   boolean  $overrideLimits  Are we requested to override the set limits?
	 *
	 * @return  JDatabaseQuery
	 */
	public function buildQuery($overrideLimits = false)
	{
		$query = parent::buildQuery($overrideLimits);
		$table = $this->getTable();
		$tableKey = $table->getKeyName();
		$db = JFactory::getDbo();

		if (!$overrideLimits)
		{
			$order = $db->qn($tableKey);

			if ($this->getTableAlias())
			{
				$order = $db->qn($this->getTableAlias()) . '.' . $order;
			}

			$dir = $this->getState('filter_order_Dir', 'ASC', 'cmd');
			$query->order($order . ' ' . $dir);
		}

		return $query;
	}
}
