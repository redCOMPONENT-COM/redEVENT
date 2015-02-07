<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * redEVENT Component Categories Model
 *
 * @package  Redevent.admin
 * @since    0.9
*/
class RedeventModelCategories extends RModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_categories';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'categories_limit';

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

		for ($i = 0, $count = count($result); $i < $count; $i++)
		{
			$category =& $result[$i];
			$category->assignedevents = $this->countCategoryEvents($category->id);
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

		$query->select('c.*, (COUNT(parent.name) - 1) AS depth, p.name as parent_name');
		$query->select('u.name AS editor');
		$query->select('g.title AS groupname');
		$query->from('#__redevent_categories AS parent, #__redevent_categories AS c');
		$query->join('LEFT', '#__redevent_categories AS p ON c.parent_id = p.id');
		$query->join('LEFT', '#__usergroups AS g ON g.id = c.access');
		$query->join('LEFT', '#__users AS u ON u.id = c.checked_out');
		$query->where('(c.lft BETWEEN parent.lft AND parent.rgt)');
		$query->group('c.id');

		// Join over the asset groups.
		$query->select('ag.title AS access_level');
		$query->join('LEFT', '#__viewlevels AS ag ON ag.id = c.access');

		// Join over the language
		$query->select('l.title AS language_title');
		$query->join('LEFT', $db->quoteName('#__languages') . ' AS l ON l.lang_code = c.language');

		// Get the WHERE clause for the query
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
		$search = $this->getState('filter.search');

		$filter_state = $this->getState('filter.published', '');

		if (is_numeric($filter_state))
		{
			if ($filter_state == '1')
			{
				$query->where('c.published = 1');
			}
			elseif ($filter_state == '0' )
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

		return $query;
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

		if (is_array($pks) && !empty($pks))
		{
			// For finder plugins
			$dispatcher	= JDispatcher::getInstance();
			JPluginHelper::importPlugin('finder');

			// Trigger the onFinderCategoryChangeState event.
			$dispatcher->trigger('onFinderCategoryChangeState', array('com_redevent.category', $pks, $state));
		}

		return true;
	}

	/**
	 * Method to count the number of assigned events to the category
	 *
	 * @param   int  $id  category id
	 *
	 * @return int
	 */
	protected function countCategoryEvents($id)
	{
		$db = $this->_db;
		$query = $db->getQuery(true);

		$query->select('COUNT(*)');
		$query->from('#__redevent_event_category_xref');
		$query->where('category_id = ' . (int) $id);

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
	public function populateState($ordering = 'c.lft', $direction = 'asc')
	{
		parent::populateState($ordering, $direction);
	}
}
