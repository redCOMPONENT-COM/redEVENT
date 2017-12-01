<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * redEVENT Component Venues Categories Model
 *
 * @package  Redevent.admin
 * @since    0.9
 */
class RedeventModelVenuescategories extends RModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_venuescategories';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'venuescategories_limit';

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
				'name', 'c.name',
				'ordering', 'c.ordering',
				'published', 'c.published',
				'id', 'c.id',
				'access', 'c.access',
				'parent_id', 'c.parent_id',
				'lft', 'c.lft',
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

		for ($i = 0, $count = count($result); $i < $count; $i++)
		{
			$category =& $result[$i];
			$category->assignedvenues = $this->countCategoryVenues($category->id);
		}

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

		$query->select('c.*, (COUNT(parent.name) - 1) AS depth, c.access, u.name AS editor');
		$query->select('p.name as parent_name, g.title AS groupname');
		$query->from('#__redevent_venues_categories AS parent, #__redevent_venues_categories AS c');
		$query->join('LEFT', '#__redevent_venues_categories AS p ON p.id = c.parent_id');
		$query->join('LEFT', '#__users AS u ON u.id = c.checked_out');
		$query->join('LEFT', '#__usergroups AS g ON g.id = c.access');
		$query->where('c.lft BETWEEN parent.lft AND parent.rgt');
		$query->group('c.id');

		// Join over the asset groups.
		$query->select('ag.title AS access_level');
		$query->join('LEFT', '#__viewlevels AS ag ON ag.id = c.access');

		// Join over the language
		$query->select('lg.title AS language_title');
		$query->join('LEFT', $db->quoteName('#__languages') . ' AS lg ON lg.lang_code = c.language');

		// Get the WHERE and ORDER BY clauses for the query
		$query = $this->buildContentWhere($query);
		$query = $this->buildContentOrderBy($query);

		return $query;
	}

	/**
	 * Method to build the where clause of the query for the categories
	 *
	 * @param   JDatabaseQuery  $query  query
	 *
	 * @return  JDatabaseQuery
	 */
	private function buildContentWhere($query)
	{
		$db = $this->_db;
		$search = $this->getState('filter.search');

		$filter_state = $this->getState('filter.published', '');

		if (is_numeric($filter_state))
		{
			if ($filter_state == '1')
			{
				$query->where('c.published = 1');
			}
			elseif ($filter_state == '0')
			{
				$query->where('c.published = 0');
			}
		}

		$filter_language = $this->getState('filter.language');

		if ($filter_language)
		{
			$query->where('c.language = ' . $db->quote($filter_language));
		}

		if ($search)
		{
			$query->where('LOWER(c.name) LIKE \'%' . $search . '%\'');
		}

		if ($this->getState('filter.acl'))
		{
			$access = JFactory::getUser()->getAuthorisedViewLevels();

			if (empty($access))
			{
				$query->where('0');
			}
			else
			{
				$query->where('c.access IN (' . implode(",", $access) . ')');
			}
		}

		return $query;
	}

	/**
	 * Method to build the order clause of the query for the categories
	 *
	 * @param   JDatabaseQuery  $query  query
	 *
	 * @return  JDatabaseQuery
	 */
	private function buildContentOrderBy($query)
	{
		$db = $this->_db;

		$order = $this->getState('list.ordering');
		$dir = $this->getState('list.direction');
		$query->order($db->qn($order) . ' ' . $dir);

		return $query;
	}

	/**
	 * Method to count the number of venues to the category
	 *
	 * @param   int  $id  venue category id
	 *
	 * @return integer
	 */
	private function countCategoryVenues($id)
	{
		$db = $this->_db;
		$query = $db->getQuery(true);

		$query->select('COUNT(*)');
		$query->from('#__redevent_venues_categories AS c');
		$query->join('INNER', '#__redevent_venues_categories AS child ON child.lft BETWEEN c.lft AND c.rgt');
		$query->join('INNER', '#__redevent_venue_category_xref AS xv ON xv.category_id = child.id');
		$query->join('INNER', '#__redevent_venues AS v ON v.id = xv.venue_id');
		$query->where('c.id = ' . (int) $id);

		$db->setQuery($query);
		$res = $db->loadResult();

		return $res;
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

		if ($this->getState('filter.acl'))
		{
			$id .= ':' . serialize(JFactory::getUser()->getAuthorisedViewLevels());
		}

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
		parent::populateState($ordering ?: 'c.lft', $direction ?: 'asc');
	}
}
