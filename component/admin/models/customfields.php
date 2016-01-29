<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * redEVENT Component Custom fields Model
 *
 * @package  Redevent.admin
 * @since    2.0
 */
class RedeventModelCustomfields extends RModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_customfields';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'customfields_limit';

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
				'name', 'obj.name',
				'id', 'obj.id',
				'language', 'obj.language',
				'ordering', 'obj.ordering',
				'type', 'obj.type',
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
		$id	.= ':' . $this->getState('filter.language');
		$id	.= ':' . $this->getState('filter.type');

		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  object  Query object
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select($this->getState('list.select', 'obj.*'));
		$query->from($db->qn('#__redevent_fields', 'obj'));

		// Filter by language
		$language = $this->getState('filter.language');

		if ($language && $language != '*')
		{
			$query->where($db->qn('obj.language') . ' = ' . $db->quote($language));
		}

		// Filter by type
		$type = $this->getState('filter.type');

		if ($type)
		{
			$query->where($db->qn('obj.type') . ' = ' . $db->quote($type));
		}

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
				$query->where('obj.name LIKE ' . $search);
			}
		}

		// Add the list ordering clause.
		$query->order($db->escape($this->getState('list.ordering', 'obj.ordering')) . ' ' . $db->escape($this->getState('list.direction', 'ASC')));

		return $query;
	}

	/**
	 * export
	 *
	 * @return array
	 */
	public function export()
	{
		$db = $this->_db;
		$query = $db->getQuery(true);

		$query->select('t.id, t.name, t.tag, t.type, t.tips, t.searchable');
		$query->select('t.in_lists, t.frontend_edit, t.required, t.object_key');
		$query->select('t.options, t.min, t.max, t.ordering, t.published, t.language');
		$query->from('#__redevent_fields AS t');
		$db->setQuery($query);
		$res = $db->loadAssocList();

		return $res;
	}

	/**
	 * import in database
	 *
	 * @param   array  $records  records
	 * @param   bool   $replace  existing events with same id
	 *
	 * @return int count
	 */
	public function import($records, $replace = false)
	{
		$count = array('added' => 0, 'updated' => 0);

		$tables = $this->_db->getTableFields(array('#__redevent_events', '#__redevent_event_venue_xref'), false);

		// Current event for sessions
		$current = null;

		foreach ($records as $r)
		{
			$row = $this->getTable();
			$row->bind($r);

			if (!$replace)
			{
				$row->id = null;
				$update = 0;
			}
			elseif ($row->id)
			{
				$update = 1;
			}

			// Store !
			if (!$row->check())
			{
				JError::raiseWarning(0, JText::_('COM_REDEVENT_IMPORT_ERROR') . ': ' . $row->getError());
				continue;
			}

			if (!$row->store())
			{
				JError::raiseWarning(0, JText::_('COM_REDEVENT_IMPORT_ERROR') . ': ' . $row->getError());
				continue;
			}

			// Add the field to the object table
			switch ($row->object_key)
			{
				case 'redevent.event':
					$table = '#__redevent_events';
					break;

				case 'redevent.xref':
					$table = '#__redevent_event_venue_xref';
					break;

				default:
					JError::raiseWarning(0, 'undefined custom field object_key');
					break;
			}

			$cols = $tables[$table];

			if (!array_key_exists('custom' . $row->id, $cols))
			{
				switch ($row->type)
				{
					default:
						$columntype = 'TEXT';
				}

				$q = 'ALTER IGNORE TABLE ' . $table . ' ADD COLUMN custom' . $row->id . ' ' . $columntype;

				$this->_db->setQuery($q);

				if (!$this->_db->execute())
				{
					JError::raiseWarning(0, 'failed adding custom field to table');
				}
			}
		}

		return $count;
	}
}
