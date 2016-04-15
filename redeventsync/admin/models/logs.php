<?php
/**
 * @package     Redeventsync
 * @subpackage  Admin
 * @copyright   Copyright (C) 2013 - 2015 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die();

/**
 * Class RedeventsyncModelLogs
 *
 * @package     Redeventsync
 * @subpackage  Admin
 * @since       3.0
 */
class RedeventsyncModelLogs extends RModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_logs';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'logs_limit';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitstartField = 'auto';

	/**
	 * Constructor.
	 *
	 * @param   array  $config  Configs
	 *
	 * @see     JController
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'type', 'obj.type',
				'id', 'obj.id',
				'date', 'obj.date',
				'transactionid', 'obj.transactionid',
				'direction', 'obj.direction',
				'status', 'obj.status',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return  string       A store id.
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.from');
		$id .= ':' . $this->getState('filter.to');
		$id .= ':' . $this->getState('filter.direction');
		$id .= ':' . $this->getState('filter.type');

		return parent::getStoreId($id);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		parent::populateState('obj.id', 'desc');
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
	 * Archive old logs
	 *
	 * @return mixed
	 */
	public function archiveold()
	{
		$params = JComponentHelper::getParams('com_redeventsync');

		// How many do we archive at once
		$limit = 30000;

		$db = $this->_db;

		$querySelect = $db->getQuery(true)
			->select('*')
			->from($db->quoteName('#__redeventsync_logs'))
			->where('DATEDIFF(NOW() , ' . $db->quoteName('date') . ') > 30')
			->order('date ASC');

		$db->setQuery($querySelect, 0, $limit);
		$rows = $db->loadAssocList();

		if (!$rows)
		{
			return 0;
		}

		$defaultFolder = JPATH_ADMINISTRATOR . '/components/com_redeventsync/archive';
		$folder = $params->get('archive_path') ?: $defaultFolder;
		$folder = file_exists($folder) ? $folder : $defaultFolder;

		$date = JFactory::getDate(end($rows)['date']);
		$fp = fopen($folder . '/archive_' . $date->format('Ymd-his') . '.csv', 'w');

		fputcsv($fp, array_keys($rows[0]));

		foreach ($rows as $row)
		{
			fputcsv($fp, $row);
		}

		fclose($fp);

		$affected = $db->getAffectedRows();

		$queryDelete = $db->getQuery(true)
			->delete($db->quoteName('#__redeventsync_logs'))
			->where('DATEDIFF(NOW() , ' . $db->quoteName('date') . ') > 30')
			->order('date ASC LIMIT ' . $limit);

		$db->setQuery($queryDelete);
		$db->execute();

		return $affected;
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  object  Query object
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db    = $this->_db;
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select($this->getState('list.select', 'obj.*'));
		$query->from($db->qn('#__redeventsync_logs', 'obj'));

		// Filter: like / search
		$search = $this->getState('filter.search', '');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('obj.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->Quote('%' . $db->escape($search, true) . '%');
				$or = array(
					'obj.message LIKE ' . $search,
					'obj.transactionid LIKE ' . $search,
					'obj.status LIKE ' . $search,
				);
				$query->where('(' . implode(' OR ', $or) . ')');
			}
		}

		if ($filter = $this->getState('filter.from'))
		{
			$search = $db->quote($filter);
			$query->where('date >= ' . $search);
		}

		if ($filter = $this->getState('filter.to'))
		{
			$search = $db->quote($filter);
			$query->where('date <= ' . $search);
		}

		if ($filter = $this->getState('filter.type'))
		{
			$search = $db->Quote('%' . $db->escape($filter, true) . '%');
			$query->where('obj.type LIKE ' . $search);
		}

		if (is_numeric($filter = $this->getState('filter.direction')))
		{
			$query->where('direction = ' . (int) $filter);
		}

		// Add the list ordering clause.
		$query->order($db->escape($this->getState('list.ordering', 'obj.id')) . ' ' . $db->escape($this->getState('list.direction', 'DESC')));

		return $query;
	}
}
