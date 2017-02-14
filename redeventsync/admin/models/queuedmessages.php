<?php
/**
 * @package     Redeventsync
 * @subpackage  Admin
 * @copyright   Copyright (C) 2013 - 2015 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die();

/**
 * Class RedeventsyncModelQueuedmessages
 *
 * @package     Redeventsync
 * @subpackage  Admin
 * @since       3.0
 */
class RedeventsyncModelQueuedmessages extends RModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_queuedmessages';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'queuedmessages_limit';

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
				'errors', 'obj.errors',
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
		$query->from($db->qn('#__redeventsync_queuedmessages', 'obj'));

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
				$query->where('obj.message LIKE ' . $search);
			}
		}

		if ($filter = $this->getState('filter.queued'))
		{
			$search = $db->quote('%' . $filter . '%');
			$query->where('queued LIKE ' . $search);
		}

		// Add the list ordering clause.
		$query->order($db->escape($this->getState('list.ordering', 'obj.id')) . ' ' . $db->escape($this->getState('list.direction', 'DESC')));

		return $query;
	}
}
