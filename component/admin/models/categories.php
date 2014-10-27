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
	protected $limitField = 'fields_limit';

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
		$db = JFactory::getDbo();
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

		$order = $this->getState('filter_order', 'c.lft', 'cmd');

		$dir = $this->getState('filter_order_Dir', '', 'cmd');
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
		$db = JFactory::getDbo();
		$search = $this->getState('search');

		if ($filter_state = $this->getState('filter.published', ''))
		{
			if ($filter_state == '1')
			{
				$query->where('c.published = 1');
			}
			elseif ($filter_state == 'U' )
			{
				$query->where('c.published = 0');
			}
		}

		$filter_language = $this->getState('filter_language');

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
	 * override to add an integration to finder
	 *
	 * @see FOFModel::publish()
	 */
	public function publish($publish = 1, $user = null)
	{
		if (!parent::publish($publish, $user))
		{
			return false;
		}

		if (is_array($this->id_list) && !empty($this->id_list))
		{
			// For finder plugins
			$dispatcher	= JDispatcher::getInstance();
			JPluginHelper::importPlugin('finder');

			// Trigger the onFinderCategoryChangeState event.
			$dispatcher->trigger('onFinderCategoryChangeState', array('com_redevent.category', $this->id_list, $publish));
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
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('COUNT(*)');
		$query->from('#__redevent_event_category_xref');
		$query->where('category_id = ' . (int) $id);

		$db->setQuery($query);
		$res = $db->loadResult();

		return $res;
	}


	/**
	 * Method to remove a category
	 *
	 * @access	public
	 * @return	string $msg
	 * @since	0.9
	 */
	public function delete()
	{
		if (!is_array($this->id_list) || empty($this->id_list))
		{
			return true;
		}
		$cids = implode(',', $this->id_list);

		$query = 'SELECT c.id, c.catname, COUNT( xcat.event_id ) AS numcat'
		. ' FROM #__redevent_categories AS c'
		. ' LEFT JOIN #__redevent_event_category_xref AS xcat ON xcat.category_id = c.id'
		. ' WHERE c.id IN (' . $cids . ')'
		. ' GROUP BY c.id';
		$this->_db->setQuery($query);

		if (!($rows = $this->_db->loadObjectList()))
		{
			RedeventError::raiseError(500, $this->_db->stderr());

			return false;
		}

		$err = array();
		$cid = array();

		foreach ($rows as $row)
		{
			if ($row->numcat == 0)
			{
				$cid[] = $row->id;
			}
			else
			{
				$err[] = $row->catname;
			}
		}

		if (count($cid))
		{
			$cids = implode(',', $cid);
			$query = 'DELETE FROM #__redevent_categories'
			. ' WHERE id IN (' . $cids . ')';

			$this->_db->setQuery($query);

			if (!$this->_db->query())
			{
				$this->setError($this->_db->getErrorMsg());

				return false;
			}

			// Rebuild the tree
			$table = $this->getTable();
			$table->rebuildTree();
		}

		if (count($err))
		{
			$cids 	= implode(', ', $err);
			$msg 	= JText::sprintf('COM_REDEVENT_EVENT_ASSIGNED_CATEGORY_S', $cids);

			return $msg;
		}
		else
		{
			$total 	= count($cid);
			$msg 	= $total . ' ' . JText::_('COM_REDEVENT_CATEGORIES_DELETED');

			return $msg;
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
	public function populateState($ordering = 'f.ordering', $direction = 'asc')
	{
		$app = JFactory::getApplication();

		$filterSearch = $this->getUserStateFromRequest($this->context . '.filter_search', 'search');
		$this->setState('filter.search', $filterSearch);

		$published = $this->getUserStateFromRequest($this->context . '.filter_published', 'published', 1);
		$this->setState('filter.published', $published);

		$language = $this->getUserStateFromRequest($this->context . '.filter_language', 'language');
		$this->setState('filter.language', $language);

		$value = $app->getUserStateFromRequest('global.list.limit', $this->paginationPrefix . 'limit', $app->getCfg('list_limit'), 'uint');
		$limit = $value;
		$this->setState('list.limit', $limit);

		$value = $app->getUserStateFromRequest($this->context . '.limitstart', $this->paginationPrefix . 'limitstart', 0);
		$limitstart = ($limit != 0 ? (floor($value / $limit) * $limit) : 0);
		$this->setState('list.start', $limitstart);

		parent::populateState($ordering, $direction);
	}
}
