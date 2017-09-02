<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * redEVENT Component events Model
 *
 * @package  Redevent.admin
 * @since    2.0
 */
class RedeventModelEvents extends RModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_events';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'events_limit';

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
				'obj.title',
				'obj.published',
				'id', 'obj.id', 'obj.language',
				'cat.id', 'cat.name', 't.name',
				// Filters
				'title', 'published', 'category', 'venue', 'template', 'language'
			);
		}

		parent::__construct($config);
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

		$result = $this->getCategories($result);

		return $result;
	}

	/**
	 * Method to get a JDatabaseQuery object for retrieving the data set from a database.
	 *
	 * @return  JDatabaseQuery   A JDatabaseQuery object to retrieve the data set.
	 *
	 * @since   12.2
	 */
	protected function getListQuery()
	{
		$db = $this->_db;
		$query = $db->getQuery(true);

		$query->select('obj.*, u.email, u.name AS author, u2.name as editor, x.id AS xref');
		$query->select('t.name AS template_name');
		$query->select('cat.checked_out AS cchecked_out, cat.name AS catname');
		$query->from('#__redevent_events AS obj ');
		$query->innerJoin('#__redevent_event_template AS t ON t.id = obj.template_id');
		$query->join('LEFT', '#__redevent_event_category_xref AS xcat ON xcat.event_id = obj.id');
		$query->join('LEFT', '#__redevent_categories AS cat ON cat.id = xcat.category_id');
		$query->join('LEFT', '#__redevent_event_venue_xref AS x ON x.eventid = obj.id');
		$query->join('LEFT', '#__redevent_venues AS loc ON loc.id = x.venueid');
		$query->join('LEFT', '#__users AS u ON u.id = obj.created_by');
		$query->join('LEFT', '#__users AS u2 ON u2.id = obj.modified_by');
		$query->group('obj.id');

		// Join over the language
		$query->select('l.title AS language_title');
		$query->join('LEFT', $db->quoteName('#__languages') . ' AS l ON l.lang_code = obj.language');

		// Get the WHERE and ORDER BY clauses for the query
		$query = $this->buildContentWhere($query);

		$order = $this->getState('list.ordering', 'obj.title');
		$dir = $this->getState('list.direction', 'asc');
		$query->order($db->qn($order) . ' ' . $dir);

		return $query;
	}

	/**
	 * Build the where clause
	 *
	 * @param   JDatabaseQuery  $query  query
	 *
	 * @return  JDatabaseQuery
	 */
	private function buildContentWhere($query)
	{
		$filter_state = $this->getState('filter.published', '');

		if (is_numeric($filter_state))
		{
			if ($filter_state == '1')
			{
				$query->where('obj.published = 1');
			}
			elseif ($filter_state == '0')
			{
				$query->where('obj.published = 0');
			}
			elseif ($filter_state == '-1')
			{
				$query->where('obj.published = -1');
			}
			else
			{
				$query->where('obj.published >= 0');
			}
		}
		elseif ($filter_state != '*')
		{
			$query->where('obj.published >= 0');
		}

		$filter_language = $this->getState('filter.language');

		if ($filter_language)
		{
			$query->where('obj.language = ' . $this->_db->quote($filter_language));
		}

		$search = $this->getState('filter.search');

		if ($search)
		{
			if (strpos($search, 'id:') === 0)
			{
				$query->where('obj.id = ' . substr($search, 3));
			}
			else
			{
				$like = $this->_db->quote('%' . $search . '%');

				$parts = array();
				$parts[] = 'LOWER(obj.title) LIKE ' . $like;
				$parts[] = 'obj.course_code LIKE ' . $like;
				$parts[] = 'LOWER(loc.venue) LIKE ' . $like;
				$parts[] = 'LOWER(loc.city) LIKE ' . $like;
				$parts[] = 'LOWER(cat.name) LIKE ' . $like;

				$query->where(implode(' OR ', $parts));
			}
		}

		if ($category = $this->getState('filter.category'))
		{
			$query->where('cat.id = ' . (int) $category);
		}

		if ($venue = $this->getState('filter.venue'))
		{
			$query->where('loc.id = ' . (int) $venue);
		}

		if ($template_id = $this->getState('filter.template'))
		{
			$query->where('t.id = ' . (int) $template_id);
		}

		if ($aclCheck = $this->getState('filter.acl'))
		{
			$acl = RedeventUserAcl::getInstance();
			$categoryIds = $acl->getManagedCategories();

			if (!$categoryIds)
			{
				$query->where('0');
			}
			else
			{
				$query->where('cat.id IN (' . implode(',', $categoryIds) . ')');
			}
		}

		return $query;
	}

	/**
	 * adds categories property to event rows
	 *
	 * @param   array  $rows  rows of events
	 *
	 * @return array
	 */
	private function getCategories($rows)
	{
		for ($i = 0, $n = count($rows); $i < $n; $i++)
		{
			$query = $this->_db->getQuery(true);

			$query->select('c.id, c.name, c.checked_out, c.published')
				->from('#__redevent_categories as c')
				->join('INNER', '#__redevent_event_category_xref as x ON x.category_id = c.id')
				->where('x.event_id = ' . $this->_db->Quote($rows[$i]->id))
				->order('c.ordering');

			$this->_db->setQuery($query);
			$rows[$i]->categories = $this->_db->loadObjectList();
		}

		return $rows;
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return	string  A store id.
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id	.= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.language');
		$id .= ':' . $this->getState('filter.category');
		$id .= ':' . $this->getState('filter.venue');
		$id	.= ':' . $this->getState('filter.published');
		$id	.= ':' . $this->getState('filter.acl');

		return parent::getStoreId($id);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * @param   string  $ordering   Ordering column
	 * @param   string  $direction  Direction
	 *
	 * @return  void
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Forcing default values
		parent::populateState($ordering ?: 'obj.title', $direction ?: 'asc');
	}

	/**
	 * archive past xrefs
	 *
	 * @param   array  $event_ids  events ids to archive.
	 *
	 * @return array
	 */
	public function archivePast($event_ids = array())
	{
		if (!count($event_ids))
		{
			return array('sessions' => 0, 'events' => 0);
		}

		// First archive past sessions
		$query = $this->_db->getQuery(true)
			->update('#__redevent_event_venue_xref AS x')
			->set('x.published = -1')
			->where('DATE_SUB(NOW(), INTERVAL 1 DAY) > (IF (x.enddates, x.enddates, x.dates))')
			->where('x.eventid IN (' . implode(', ', $event_ids) . ')');

		$this->_db->setQuery($query);
		$this->_db->execute();

		$archivedSessions = $this->_db->getAffectedRows();

		// Then archive events that don't have published sessions any more
		$query = $this->_db->getQuery(true)
			->update('#__redevent_events AS e')
			->join('LEFT', '#__redevent_event_venue_xref AS x ON x.eventid = e.id AND x.published <> -1')
			->set('e.published = -1')
			->where('x.id IS NULL')
			->where('e.id IN (' . implode(', ', $event_ids) . ')');

		$this->_db->setQuery($query);
		$this->_db->execute();

		$archivedEvents = $this->_db->getAffectedRows();

		return array('sessions' => $archivedSessions, 'events' => $archivedEvents);
	}

	/**
	 * Retrieve a list of events, venues and times
	 *
	 * @return array
	 */
	public function getEventVenues()
	{
		$events_id = array();

		if (!($items = $this->getItems()))
		{
			return false;
		}

		$events_id = JArrayHelper::getColumn($items, 'id');

		$db = $this->_db;
		$query = $db->getQuery(true);

		$query->select('x.eventid, COUNT(*) AS total');
		$query->select('SUM(CASE WHEN x.published = 1 THEN 1 ELSE 0 END) as published');
		$query->select('SUM(CASE WHEN x.published = 0 THEN 1 ELSE 0 END) as unpublished');
		$query->select('SUM(CASE WHEN x.published = -1 THEN 1 ELSE 0 END) as archived');
		$query->select('SUM(CASE WHEN x.featured = 1 THEN 1 ELSE 0 END) as featured');
		$query->from('#__redevent_event_venue_xref AS x');
		$query->where('x.eventid IN (' . implode(', ', $events_id) . ')');
		$query->group('x.eventid');

		$db->setQuery($query);
		$sessionStats = $db->loadObjectList();

		$eventSessionsStats = array();

		foreach ((array) $sessionStats as $stat)
		{
			$eventSessionsStats[$stat->eventid] = $stat;
		}

		return $eventSessionsStats;
	}
}
