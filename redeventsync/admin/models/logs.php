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
	protected function populateState()
	{
		$order = $this->getUserStateFromRequest($this->getHash() . 'filter_order', 'filter_order', 'date', 'none', true);
		$order_Dir = $this->getUserStateFromRequest($this->getHash() . 'filter_order_Dir', 'filter_order_Dir', 'DESC', 'none', true);

		$this->setState('filter_order', $order);
		$this->setState('filter_order_Dir', $order_Dir);

		$filter = $this->getUserStateFromRequest($this->getHash() . 'type', 'type', '');
		$this->setState('type', $filter);

		$filter = $this->getUserStateFromRequest($this->getHash() . 'date', 'date', '');
		$this->setState('date', $filter);

		$filter = $this->getUserStateFromRequest($this->getHash() . 'direction', 'direction', '');
		$this->setState('direction', $filter);

		$filter = $this->getUserStateFromRequest($this->getHash() . 'transactionid', 'transactionid', '');
		$this->setState('transactionid', $filter);

		$filter = $this->getUserStateFromRequest($this->getHash() . 'status', 'status', '');
		$this->setState('status', $filter);
	}

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
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('*');
		$query->from('#__redeventsync_logs');

		if ($filter = $this->getState('type'))
		{
			$search = $db->quote('%' . $filter . '%');
			$query->where('type LIKE ' . $search);
		}

		if ($filter = $this->getState('date'))
		{
			$search = $db->quote('%' . $filter . '%');
			$query->where('date LIKE ' . $search);
		}

		if ($filter = $this->getState('direction'))
		{
			$query->where('direction = ' . (int) $filter);
		}

		if ($filter = $this->getState('transactionid'))
		{
			$search = $db->quote('%' . $filter . '%');
			$query->where('transactionid LIKE ' . $search);
		}

		if ($filter = $this->getState('status'))
		{
			$search = $db->quote('%' . $filter . '%');
			$query->where('status LIKE ' . $search);
		}

		$table = $this->getTable();
		$tableKey = $table->getKeyName();

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
