<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * redEVENT Component venues Model
 *
 * @package  Redevent.admin
 * @since    0.9
 */
class RedeventModelVenues extends RModelList
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
				'venue', 'obj.venue',
				'ordering', 'obj.ordering',
				'published', 'obj.published',
				'id', 'obj.id',
				'access', 'obj.access',
				'venue_code', 'obj.venue_code',
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

		$result = $this->additionals($result);

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
		$db = $this->_db;
		$query = $db->getQuery(true);

		$query->select('obj.*, u.email, u.name AS author, umodified.name AS editor');
		$query->from('#__redevent_venues AS obj');
		$query->join('LEFT', '#__users AS u ON u.id = obj.created_by');
		$query->join('LEFT', '#__users AS umodified ON umodified.id = obj.modified_by');

		// Join over the asset groups.
		$query->select('ag.title AS access_level');
		$query->join('LEFT', '#__viewlevels AS ag ON ag.id = obj.access');

		// Join over the language
		$query->select('lg.title AS language_title');
		$query->join('LEFT', $db->quoteName('#__languages') . ' AS lg ON lg.lang_code = obj.language');

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
		$db = $this->_db;

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

		$filter_language = $this->getState('filter.language');

		if ($filter_language)
		{
			$query->where('obj.language = ' . $db->quote($filter_language));
		}

		$search = $this->getState('filter.search');

		if ($search)
		{
			$like = $db->quote('%' . $search . '%');
			$query->where('(LOWER(obj.venue) LIKE ' . $like . ' OR LOWER(obj.city) LIKE ' . $like . ')');
		}

		if ($aclCheck = $this->getState('filter.acl'))
		{
			$acl = RedeventUserAcl::getInstance();
			$ids = $acl->getAllowedForEventsVenues();

			if (!$ids)
			{
				$query->where('0');
			}
			else
			{
				$query->where('obj.id IN (' . implode(',', $ids) . ')');
			}
		}

		return $query;
	}

	/**
	 * Method to get additional informations
	 *
	 * @param   array  $rows  rows
	 *
	 * @return array
	 */
	private function additionals($rows)
	{
		$count = count($rows);
		$db = $this->_db;

		for ($i = 0, $n = $count; $i < $n; $i++)
		{
			$query = $db->getQuery(true);

			$query->select('COUNT(id)');
			$query->from('#__redevent_event_venue_xref');
			$query->where('venueid = ' . (int) $rows[$i]->id);

			$db->setQuery($query);
			$rows[$i]->assignedevents = $db->loadResult();

			$query = $db->getQuery(true);

			$query->select('c.id, c.name, c.checked_out');
			$query->from('#__redevent_venues_categories AS c');
			$query->join('INNER', '#__redevent_venue_category_xref as x ON x.category_id = c.id');
			$query->where('c.published = 1');
			$query->where('x.venue_id = ' . (int) $rows[$i]->id);
			$query->order('c.ordering');

			$db->setQuery($query);
			$rows[$i]->categories = $db->loadObjectList();
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
		parent::populateState($ordering ?: 'obj.venue', $direction ?: 'asc');
	}
}
