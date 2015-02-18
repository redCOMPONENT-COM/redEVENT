<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * redEVENT Component sessions Model
 *
 * @package  Redevent.admin
 * @since    2.0
 */
class RedeventModelSessions extends RModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_sessions';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'sessions_limit';

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
				'obj.title', 'e.title',
				'published', 'obj.published',
				'id', 'obj.id',
				'language', 'obj.language',
				'dates', 'obj.dates',
				'session_code', 'obj.session_code',
				'obj.featured', 'obj.registrationend', 'v.venue',
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
		$id	.= ':' . $this->getState('filter.event');

		return parent::getStoreId($id);
	}

	/**
	 * Gets an array of objects from the results of database query.
	 *
	 * @param   string   $query       The query.
	 * @param   integer  $limitstart  Offset.
	 * @param   integer  $limit       The number of records.
	 *
	 * @return  array  An array of results.
	 *
	 * @since   11.1
	 */
	protected function _getList($query, $limitstart = 0, $limit = 0)
	{
		$result = parent::_getList($query, $limitstart, $limit);

		if (!$result)
		{
			return $result;
		}

		$result = $this->addAttendeesStats($result);

		return $result;
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  object  Query object
	 */
	protected function getListQuery()
	{
		$query = $this->_db->getQuery(true);

		$query->select('obj.*, 0 AS checked_out')
			->select('e.title AS event_title, e.checked_out as event_checked_out, e.registra')
			->select('v.venue, v.checked_out as venue_checked_out')
			->from('#__redevent_event_venue_xref AS obj')
			->join('INNER', '#__redevent_events AS e ON obj.eventid = e.id')
			->join('LEFT', '#__redevent_venues AS v ON v.id = obj.venueid');

		$this->buildContentWhere($query);

		// Add the list ordering clause.
		$query->order($this->_db->escape($this->getState('list.ordering', 'obj.dates')) . ' ' . $this->_db->escape($this->getState('list.direction', 'ASC')));

		return $query;
	}

	/**
	 * Method to build the where clause of the query
	 *
	 * @param   JDatabaseQuery  $query  query
	 *
	 * @return  JDatabaseQuery
	 */
	protected function buildContentWhere($query)
	{
		$filter_eventid = $this->getState('filter.event', '');

		if (is_numeric($filter_eventid))
		{
			$query->where('obj.eventid = ' . $filter_eventid);
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
				$search = $this->_db->Quote('%' . $this->_db->escape($search, true) . '%');
				$query->where('(LOWER(e.title) LIKE ' . $search . ' OR '
					. ' LOWER(obj.title) LIKE ' . $search . ' OR '
					. ' LOWER(obj.session_code) LIKE ' . $search . ')');
			}
		}

		$filter_state = $this->getState('filter.published', '');

		if (is_numeric($filter_state))
		{
			if ($filter_state == '1')
			{
				$query->where('obj.published = 1');
			}
			elseif ($filter_state == '0' )
			{
				$query->where('obj.published = 0');
			}
		}

		$filter_state = $this->getState('filter.published');

		switch ($filter_state)
		{
			case '0':
			case '1':
			case '-1':
				$query->where('obj.published = ' . $filter_state);
				break;

			case '*':
				break;

			default:
				// Not archived
				$query->where('(obj.published = 0 OR obj.published = 1)');
		}

		switch ($this->getState('filter_featured'))
		{
			case 'featured':
				$query->where('obj.featured = 1');
				break;

			case 'unfeatured':
				$query->where('obj.featured = 0');
				break;
		}

		$filter_language = $this->getState('filter.language');

		if ($filter_language)
		{
			$query->where('obj.language = ' . $this->_db->quote($filter_language));
		}

		$filter_venueid = $this->getState('filter.venueid', '');

		if (is_numeric($filter_venueid))
		{
			$query->where('obj.venueid = ' . $filter_venueid);
		}

		return $query;
	}

	/**
	 * adds attendees stats to session
	 *
	 * @param   array  $result  rows to add stats to
	 *
	 * @return array
	 */
	private function addAttendeesStats($result)
	{
		$ids = array();

		foreach ($result as $session)
		{
			$ids[] = $session->id;
		}

		$query = $this->_db->getQuery(true);

		$query->select('x.id, COUNT(*) AS total, SUM(r.waitinglist) AS waiting, SUM(1-r.waitinglist) AS attending')
			->from('#__redevent_event_venue_xref AS x')
			->join('LEFT', '#__redevent_register AS r ON x.id = r.xref')
			->where('x.id IN (' . implode(', ', $ids) . ')')
			->where('r.confirmed = 1')
			->where('r.cancelled = 0')
			->group('r.xref');

		$this->_db->setQuery($query);
		$res = $this->_db->loadObjectList('id');

		$noreg = new stdclass();
		$noreg->total = 0;
		$noreg->waiting = 0;
		$noreg->attending = 0;

		foreach ($result as &$session)
		{
			if (isset($res[$session->id]))
			{
				$session->attendees = $res[$session->id];
			}
			else
			{
				$session->attendees = $noreg;
			}
		}

		return $result;
	}

	/**
	 * Method to (un)feature
	 *
	 * @access    public
	 * @return    boolean    True on success
	 * @since     0.9
	 */
	function featured($cid = array(), $featured = 1)
	{
		$user = JFactory::getUser();

		if (count($cid))
		{
			$cids = implode(',', $cid);

			$query = 'UPDATE #__redevent_event_venue_xref'
				. ' SET featured = ' . (int) $featured
				. ' WHERE id IN (' . $cids . ')';
			$this->_db->setQuery($query);
			if (!$this->_db->query())
			{
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}
		return true;
	}

	/**
	 * Override for eventid param in request
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		parent::populateState($ordering, $direction);

		$app = JFactory::getApplication();

		if ($value = $app->input->getInt('eventid', 0))
		{
			$this->setState('filter.event', $value);
		}
	}

	/**
	 * Get the filter form
	 *
	 * @param   array    $data      data
	 * @param   boolean  $loadData  load current data
	 *
	 * @return  JForm/false  the JForm object or false
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$form = parent::getForm($data, $loadData);

		if ($form && $this->getState('filter.event'))
		{
			$form->setValue('event', 'filter', $this->getState('filter.event'));
		}

		return $form;
	}
}
