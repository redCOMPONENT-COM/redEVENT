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
				'title', 'obj.title',
				'published', 'obj.published',
				'id', 'obj.id',
				'language', 'obj.language',
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
		$query->select('cat.checked_out AS cchecked_out, cat.name AS catname');
		$query->from('#__redevent_events AS obj ');
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
			elseif ($filter_state == '0' )
			{
				$query->where('obj.published = 0');
			}
			else
			{
				$query->where('obj.published >= 0');
			}
		}
		else
		{
			$query->where('obj.published >= 0');
		}

		$filter_language = $this->getState('filter.language');

		if ($filter_language)
		{
			$query->where('obj.language = ' . $db->quote($filter_language));
		}

		$search = $this->getState('filter.search');

		if ($search)
		{
			$like = $db->quote('%' . $search . '%');

			$parts = array();
			$parts[] = 'LOWER(obj.title) LIKE ' . $like;
			$parts[] = 'obj.course_code LIKE ' . $like;
			$parts[] = 'LOWER(loc.venue) LIKE ' . $like;
			$parts[] = 'LOWER(loc.city) LIKE ' . $like;
			$parts[] = 'LOWER(cat.name) LIKE ' . $like;

			$query->where(implode(' OR ', $parts));
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

			$query->select('c.id, c.name, c.checked_out')
				->from('#__redevent_categories as c')
				->join('INNER', '#__redevent_event_category_xref as x ON x.category_id = c.id')
				->where('c.published = 1')
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
		$id	.= ':' . $this->getState('filter.published');

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
	public function populateState($ordering = 'obj.title', $direction = 'asc')
	{
		parent::populateState($ordering, $direction);
	}

	/**
	 * Method to (un)publish a event
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function publish($cid = array(), $publish = 1)
	{
		$user 	=& JFactory::getUser();
		$userid = (int) $user->get('id');

		if (count( $cid ))
		{
			$cids = implode( ',', $cid );

			$query = 'UPDATE #__redevent_events'
				. ' SET published = '. (int) $publish
				. ' WHERE id IN ('. $cids .')'
				. ' AND ( checked_out = 0 OR ( checked_out = ' .$userid. ' ) )'
			;

			$this->_db->setQuery( $query );

			if (!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
			else {
				$query = 'UPDATE #__redevent_event_venue_xref'
					. ' SET published = '. (int) $publish
					. ' WHERE eventid IN ('. $cids .')'
					. '   AND published > -1 ' // do not change state of archived session
				;
				$this->_db->setQuery( $query );

				if (!$this->_db->query()) {
					$this->setError($this->_db->getErrorMsg());
					return false;
				}
			}

			// for finder plugins
			$dispatcher	= JDispatcher::getInstance();
			JPluginHelper::importPlugin('finder');
			foreach ($cid as $row_id)
			{
				$obj = new stdclass;
				$obj->id = $row_id;
				// Trigger the onFinderAfterDelete event.
				$dispatcher->trigger('onFinderChangeState', array('com_redevent.event', $cid, $publish));
			}
		}
	}

	/**
	 * archive past xrefs
	 *
	 * @param $event_ids
	 * @return unknown_type
	 */
	function archive($event_ids = array())
	{
		if (!count($event_ids)) {
			return true;
		}

		$db = & $this->_db;

    $nulldate = '0000-00-00';

		// update xref to archive
		$query = ' UPDATE #__redevent_event_venue_xref AS x '
		. ' SET x.published = -1 '
		. ' WHERE DATE_SUB(NOW(), INTERVAL 1 DAY) > (IF (x.enddates <> '.$nulldate.', x.enddates, x.dates))'
		. '   AND x.eventid IN (' . implode(', ', $event_ids) . ')'
		;
		$db->SetQuery( $query );
		$db->Query();

		// update events to archive (if no more published xref)
		$query = ' UPDATE #__redevent_events AS e '
		. ' LEFT JOIN #__redevent_event_venue_xref AS x ON x.eventid = e.id AND x.published <> -1 '
		. ' SET e.published = -1 '
		. ' WHERE x.id IS NULL '
		. '   AND e.id IN (' . implode(', ', $event_ids) . ')'
		;
		$db->SetQuery( $query );
		$db->Query();
		return true;
	}

	/**
	 * Method to remove a event
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	0.9
	 */
	function delete($cid = array())
	{
		$result = false;

		if (count( $cid ))
		{
			// first, we don't delete events that have attendees, to preserve records integrity. admin should delete attendees separately first
			$cids = implode( ',', $cid );

			$query = ' SELECT e.id, e.title '
			       . ' FROM #__redevent_events AS e '
			       . ' INNER JOIN #__redevent_event_venue_xref AS x ON x.eventid = e.id '
			       . ' INNER JOIN #__redevent_register AS r ON r.xref = x.id '
			       . ' WHERE e.id IN ('. $cids .')'
			       ;
			$this->_db->setQuery($query);
			$res = $this->_db->loadObjectList();
			if ($res || count($res))
			{
				// can't delete
				$this->setError(Jtext::_('COM_REDEVENT_ERROR_EVENT_REMOVE_EVENT_HAS_ATTENDEES'));
				return false;
			}

			$query = ' DELETE e.*, xcat.*, x.*, rp.*, r.*, sr.*, spg.* '
			       . ' FROM #__redevent_events AS e '
			       . ' LEFT JOIN #__redevent_event_category_xref AS xcat ON xcat.event_id = e.id '
			       . ' LEFT JOIN #__redevent_event_venue_xref AS x ON x.eventid = e.id '
			       . ' LEFT JOIN #__redevent_repeats AS rp on rp.xref_id = x.id '
			       . ' LEFT JOIN #__redevent_recurrences AS r on r.id = rp.recurrence_id '
			       . ' LEFT JOIN #__redevent_sessions_roles AS sr on sr.xref = x.id '
			       . ' LEFT JOIN #__redevent_sessions_pricegroups AS spg on spg.xref = x.id '
					   . ' WHERE e.id IN ('. $cids .')'
					   ;

			$this->_db->setQuery( $query );

			if(!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}

			// for finder plugins
			$dispatcher	= JDispatcher::getInstance();
			JPluginHelper::importPlugin('finder');
			foreach ($cid as $row_id)
			{
				$obj = new stdclass;
				$obj->id = $row_id;
				// Trigger the onFinderAfterDelete event.
				$dispatcher->trigger('onFinderAfterDelete', array('com_redevent.event', $obj));
			}
		}

		return true;
	}

	/**
	 * Retrieve a list of events, venues and times
	 *
	 * @return array
	 */
	public function getEventVenues()
	{
		$events_id = array();

		foreach ((array) $this->getItems() as $e)
		{
			$events_id[] = $e->id;
		}

		if (empty($events_id))
		{
			return false;
		}

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
