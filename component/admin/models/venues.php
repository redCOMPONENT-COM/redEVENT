<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * redEVENT Component textsnippets Model
 *
 * @package  Redevent.admin
 * @since    0.9
 */
class RedEventModelVenues extends RModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_venues';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'venues_limit';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitstartField = 'auto';

	/**
	 * for import
	 * @var array
	 */
	private $_cats   = null;

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
				'name', 'c.name',
				'ordering', 'c.ordering',
				'published', 'c.published',
				'id', 'c.id',
				'access', 'c.access'
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

		$result = $this->_additionals($result);

		return $result;
	}

	/**
	 * Method to cache the last query constructed.
	 *
	 * This method ensures that the query is constructed only once for a given state of the model.
	 *
	 * @return JDatabaseQuery A JDatabaseQuery object
	 */
	protected function getListQuery()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('l.*, u.email, u.name AS author');
		$query->from('#__redevent_venues AS l');
		$query->join('LEFT', '#__users AS u ON u.id = l.created_by');

		// Join over the asset groups.
		$query->select('ag.title AS access_level');
		$query->join('LEFT', '#__viewlevels AS ag ON ag.id = l.access');

		// Join over the language
		$query->select('lg.title AS language_title');
		$query->join('LEFT', $db->quoteName('#__languages') . ' AS lg ON lg.lang_code = l.language');

		// Get the WHERE and ORDER BY clauses for the query
		$query = $this->buildContentWhere($query);

		$order = $this->getState('list.ordering');
		$dir = $this->getState('list.direction');
		$query->order($db->qn($order) . ' ' . $dir);

		return $query;
	}

	/**
	 * Method to build the where clause of the query for the categories
	 *
	 * @param   JDatabaseQuery  $query  query
	 *
	 * @return  JDatabaseQuery
	 */
	protected function buildContentWhere($query)
	{
		$filter_state 		= $this->getState('filter_state');
		$filter 			= $this->getState('filter');
		$search 			= $this->getState('search');

		/*
		* Filter state
		*/
		if ( $filter_state ) {
			if ($filter_state == 'P') {
				$query->where('l.published = 1');
			} else if ($filter_state == 'U') {
				$query->where('l.published = 0');
			} else {
				$query->where('l.published >= 0');
			}
		}

		/*
		* Search venues
		*/
		if ($search && $filter == 1)
		{
			$query->where(' LOWER(l.venue) LIKE \'%' . $search . '%\' OR LOWER(l.venue_code) LIKE \'%' . $search . '%\'');
		}

		/*
		* Search city
		*/
		if ($search && $filter == 2) {
			$query->where(' LOWER(l.city) LIKE \'%'.$search.'%\' ');
		}

		$filter_language = $this->getState('filter_language');
		if ($filter_language) {
			// 			$this->setState('filter_language', $filter_language);
			$query->where('l.language = '.$this->_db->quote($filter_language));
		}

		return $query;
	}

	/**
	 * Method to get the userinformation of edited/submitted venues
	 *
	 * @access private
	 * @return object
	 * @since 0.9
	 */
	function _additionals($rows)
	{
		/*
		* Get editor name
		*/
		$count = count($rows);

		for ($i=0, $n=$count; $i < $n; $i++) {

			$query = 'SELECT name'
				. ' FROM #__users'
				. ' WHERE id = '.$rows[$i]->modified_by
				;

			$this->_db->setQuery( $query );
			$rows[$i]->editor = $this->_db->loadResult();

			/*
			* Get nr of assigned events
			*/
			$query = 'SELECT COUNT( id )'
				.' FROM #__redevent_event_venue_xref'
				.' WHERE venueid = ' . (int)$rows[$i]->id
				;

			$this->_db->setQuery($query);
			$rows[$i]->assignedevents = $this->_db->loadResult();

			// get categories
			$query =  ' SELECT c.id, c.name, c.checked_out '
              . ' FROM #__redevent_venues_categories as c '
              . ' INNER JOIN #__redevent_venue_category_xref as x ON x.category_id = c.id '
              . ' WHERE c.published = 1 '
              . '   AND x.venue_id = ' . $this->_db->Quote($rows[$i]->id)
              . ' ORDER BY c.ordering'
              ;
      $this->_db->setQuery( $query );

      $rows[$i]->categories = $this->_db->loadObjectList();
		}

		return $rows;
	}

	/**
	 * Publish/Unpublish items
	 *
	 * @param   mixed    $pks    id or array of ids of items to be published/unpublished
	 * @param   integer  $state  New desired state
	 *
	 * @return  boolean
	 */
	public function publish($pks = null, $state = 1)
	{
		if (!parent::publish($pks, $state))
		{
			return false;
		}

		if (!$pks)
		{
			return true;
		}

		if (!is_array($pks))
		{
			$pks = array($pks);
		}

		$dispatcher	= JDispatcher::getInstance();
		JPluginHelper::importPlugin('finder');
		JPluginHelper::importPlugin('redevent');

		foreach ($pks as $id)
		{
			$dispatcher->trigger('onFinderChangeState', array('com_redevent.venue', $id, $state));
			$dispatcher->trigger('onAfterVenueSaved', array($id));
		}
	}

	/**
	 * export venues
   *
	 * @param array $categories filter
	 * @return array
	 */
	public function export($categories = null)
	{
		$where = array();

		if ($categories) {
			$where[] = " (xc.category_id = ". implode(" OR xc.category_id = ", $categories).') ';
		}

		if (count($where)) {
			$where = ' WHERE '.implode(' AND ', $where);
		}
		else {
			$where = '';
		}

		$query = ' SELECT v.id, v.venue, v.alias, v.url, v.street, v.plz, v.city, v.state, v.country, v.latitude, v.longitude, '
				. ' v.locdescription, v.meta_description, v.meta_keywords, v.locimage, v.map, v.published,  '
				. '    u.name as creator_name, u.email AS creator_email '
				. ' FROM #__redevent_venues AS v '
				. ' LEFT JOIN #__redevent_venue_category_xref AS xc ON xc.venue_id = v.id '
				. ' LEFT JOIN #__users AS u ON v.created_by = u.id '
				. $where
				. ' GROUP BY v.id '
				;
		$this->_db->setQuery($query);

		$results = $this->_db->loadAssocList();

		$query = ' SELECT xc.venue_id, GROUP_CONCAT(c.name SEPARATOR "#!#") AS categories_names '
				. ' FROM #__redevent_venue_category_xref AS xc '
				. ' LEFT JOIN #__redevent_venues_categories AS c ON c.id = xc.category_id '
				. ' GROUP BY xc.venue_id '
				;
		$this->_db->setQuery($query);

		$cats = $this->_db->loadObjectList('venue_id');
		foreach ($results as $k => $r)
		{
			if (isset($cats[$r['id']]))
			{
				$results[$k]['categories_names'] = $cats[$r['id']]->categories_names;
			}
			else
			{
				$results[$k]['categories_names'] = null;
			}
		}
		return $results;
	}

  /**
	 * insert venues database
	 *
	 * @param array $records
	 * @param string $duplicate_method method for handling duplicate record (ignore, create_new, update)
	 * @return boolean true on success
	 */
	public function import($records, $duplicate_method = 'ignore')
	{
		$app = JFactory::getApplication();
		$count = array('added' => 0, 'updated' => 0, 'ignored' => 0);

		foreach ($records as $r)
		{
			$v = $this->getTable('RedEvent_venues', '');
			$v->bind($r);

			if (isset($r->id) && $r->id)
			{
				// load existing data
				$found = $v->load($r->id);

				// discard if set to ignore duplicate
				if ($found && $duplicate_method == 'ignore') {
					$count['ignored']++;
					continue;
				}
			}
			// bind submitted data
			$v->bind($r);
			if ($duplicate_method == 'update' && $found) {
				$updating = 1;
			}
			else {
				$v->id = null; // to be sure to create a new record
				$updating = 0;
			}

			// store !
			if (!$v->check()) {
				$app->enqueueMessage(JText::_('COM_REDEVENT_IMPORT_ERROR').': '.$v->getError(), 'error');
				continue;
			}
			if (!$v->store()) {
				$app->enqueueMessage(JText::_('COM_REDEVENT_IMPORT_ERROR').': '.$v->getError(), 'error');
				continue;
			}

			// categories relations
			$cats = explode('#!#', $r->categories_names);
			$cats_ids = array();
			foreach ($cats as $c)
			{
				$cats_ids[] = $this->_getCatId($c);
			}
			$v->setCategories($cats_ids);

			if ($updating) {
				$count['updated']++;
			}
			else {
				$count['added']++;
			}
		}
		return $count;
	}

	/**
	 * Return cat id matching name, creating if needed
	 *
	 * @param string $name
	 * @return id cat id
	 */
	private function _getCatId($name)
	{
		$id = array_search($name, $this->_getCats());
		if ($id === false) // doesn't exist, create it
		{
			$new = JTable::getInstance('RedEvent_venues_categories', '');
			$new->name = $name;
			$new->store();
			$id = $new->id;
			$this->_cats[$id] = $name;
		}
		return $id;
	}

	/**
	 * returns array of current cats names indexed by ids
	 *
	 * @return array
	 */
	private function _getCats()
	{
		if (empty($this->_cats))
		{
			$this->_cats = array();
			$query = ' SELECT id, name FROM #__redevent_venues_categories ';
			$this->_db->setQuery($query);
			$res = $this->_db->loadObjectList();
			foreach ((array) $res as $r)
			{
				$this->_cats[$r->id] = $r->name;
			}
		}
		return $this->_cats;
	}
}
