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
				// Ordering
				'obj.title', 'e.title',
				'obj.published',
				'obj.id',
				'obj.language',
				'obj.dates',
				'obj.session_code',
				'obj.featured', 'obj.registrationend', 'v.venue', 'obj.note',
				// Filters
				'event', 'venue', 'category', 'published', 'id', 'language', 'dates', 'session_code',
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
		$id	.= ':' . $this->getState('filter.venue');
		$id	.= ':' . $this->getState('filter.category');
		$id	.= ':' . $this->getState('filter.published');

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

		$query->select('obj.*')
			->select('e.title AS event_title, e.checked_out as event_checked_out, e.registra')
			->select('v.venue, v.checked_out as venue_checked_out')
			->from('#__redevent_event_venue_xref AS obj')
			->join('INNER', '#__redevent_events AS e ON obj.eventid = e.id')
			->join('LEFT', '#__redevent_event_category_xref AS xcat ON xcat.event_id = e.id')
			->join('LEFT', '#__redevent_venues AS v ON v.id = obj.venueid')
			->group('obj.id');

		$this->buildContentWhere($query);

		// Add the list ordering clause.
		if ($this->getState('list.ordering', 'obj.dates') == 'obj.dates')
		{
			$order = 'obj.dates ' . $this->getState('list.direction', 'ASC') . ', obj.times ' . $this->getState('list.direction', 'ASC');
		}
		else
		{
			$order = $this->getState('list.ordering', 'obj.dates') . ' '
				. $this->getState('list.direction', 'ASC');
		}

		$query->order($order);

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

		switch ($filter_state = $this->getState('filter.published'))
		{
			case '0':
			case '1':
			case '-1':
				$query->where('obj.published = ' . $filter_state);
				break;

			case '*':
				break;

			case '2':
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

		$filter_venueid = $this->getState('filter.venue', '');

		if (is_numeric($filter_venueid))
		{
			$query->where('obj.venueid = ' . $filter_venueid);
		}

		$filter_categoryid = $this->getState('filter.category', '');

		if (is_numeric($filter_categoryid))
		{
			$query->where('xcat.category_id = ' . $filter_categoryid);
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

		$noreg = new stdclass;
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
	 * @param   array  $cid       ids to modify
	 * @param   int    $featured  set featured on or off
	 *
	 * @return    boolean    True on success
	 */
	public function featured($cid = array(), $featured = 1)
	{
		if (count($cid))
		{
			$cids = implode(',', $cid);

			$query = $this->_db->getQuery(true);

			$query->update('#__redevent_event_venue_xref')
				->set('featured = ' . (int) $featured)
				->where('id IN (' . $cids . ')');

			$this->_db->setQuery($query);
			$this->_db->execute();
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
		parent::populateState($ordering ?: 'obj.dates', $direction ?: 'desc');

		$app = JFactory::getApplication();

		if ($value = $app->input->getInt('eventid', 0))
		{
			$this->setState('filter.event', $value);
		}
	}
}
