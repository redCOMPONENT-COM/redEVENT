<?php
/**
 * @package    Redevent.Site
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevents Component my events Model
 *
 * @package  Redevent.Site
 * @since    2.0
 */
class RedeventModelMyevents extends RModelList
{
	/**
	 * Method to get the Events
	 *
	 * @return array
	 */
	public function getItems()
	{
		// Get a storage key.
		$store = $this->getStoreId();

		// Try to load the data from internal storage.
		if (isset($this->cache[$store]))
		{
			return $this->cache[$store];
		}

		$items = parent::getItems();
		$items = $this->addCategories($items);

		// Add the items to the internal cache.
		$this->cache[$store] = $items;

		return $this->cache[$store];
	}

	/**
	 * Method to cache the last query constructed.
	 *
	 * This method ensures that the query is constructed only once for a given state of the model.
	 *
	 * @return  JDatabaseQuery  A JDatabaseQuery object
	 */
	protected function getListQuery()
	{
		$query = $this->_buildQueryEventsSelect();

		// Get the WHERE and ORDER BY clauses for the query
		$query = $this->_buildEventListWhere($query);
		$query = $this->_buildEventListOrderBy($query);

		return $query;
	}

	/**
	 * build base select and joins
	 *
	 * @return JDatabaseQuery
	 */
	protected function _buildQueryEventsSelect()
	{
		$db      = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('a.id, a.title, a.created, a.datdescription, a.registra, a.unregistra, a.course_code, a.published');
		$query->select('c.name AS catname, c.id AS catid');
		$query->select('CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug');
		$query->select('CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as categoryslug');
		$query->from('#__redevent_events AS a');
		$query->join('LEFT', '#__redevent_event_category_xref AS xcat ON xcat.event_id = a.id');
		$query->join('LEFT', '#__redevent_categories AS c ON c.id = xcat.category_id');
		$query->group('a.id');

		return $query;
	}

	/**
	 * Build the order clause
	 *
	 * @param   JDatabaseQuery  $query  query
	 *
	 * @return string
	 */
	protected function _buildEventListOrderBy($query)
	{
		$filter_order = $this->getState('filter_order');
		$filter_order_dir = $this->getState('filter_order_dir');

		$query->order($filter_order . ' ' . $filter_order_dir . ', a.title');

		return $query;
	}

	/**
	 * Build the where clause
	 *
	 * @param   JDatabaseQuery  $query  query object
	 *
	 * @return string
	 */
	protected function _buildEventListWhere($query)
	{
		$acl = RedeventUserAcl::getInstance();

		if (!$acl->superuser())
		{
			$ids = $acl->getCanEditEvents();

			if ($ids && count($ids))
			{
				$query->where('a.id IN (' . implode(",", $ids) . ')');
			}
			else
			{
				$query->where('0');

				return $query;
			}
		}

		$filter = $this->getState('filter');

		// Clean filter variables
		$filter = JString::strtolower($filter);
		$filter = $this->_db->Quote('%' . $this->_db->escape($filter, true) . '%', false);

		$or = array(
			'LOWER( a.title ) LIKE ' . $filter,
			'LOWER( c.name ) LIKE ' . $filter
		);

		$query->where('(' . implode(' OR ', $or) . ')');

		return $query;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$mainframe = JFactory::getApplication();

		// Get the paramaters of the active menu item
		$params = $mainframe->getParams('com_redevent');

		// Get the number of events from database
		$limit = $mainframe->getUserStateFromRequest('com_redevent.myevents.limit', 'limit', $params->def('display_num', 0), 'int');
		$limitstart_events = $mainframe->input->get('limitstart', 0, '', 'int');

		$this->setState('list.limit', $limit);
		$this->setState('list.start', $limitstart_events);

		// Get the filter request variables
		$this->setState('filter_order', $mainframe->input->getCmd('filter_order_events', 'a.title'));
		$this->setState('filter_order_dir', $mainframe->input->getCmd('filter_order_Dir', 'ASC'));

		$filter = $mainframe->getUserStateFromRequest('com_redevent.myevents.filter', 'filter', '', 'string');
		$filter_type = $mainframe->getUserStateFromRequest('com_redevent.myevents.filter_type', 'filter_type', '', 'string');

		$this->setState('filter', $filter);
		$this->setState('filter_type', $filter_type);
	}

	/**
	 * Method to get a store id based on the model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  An identifier string to generate the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since   11.1
	 */
	protected function getStoreId($id = '')
	{
		$id = parent::getStoreId($id);
		$id .= ':' . $this->getState('filter');

		return $id;
	}

	/**
	 * adds categories property to event rows
	 *
	 * @param   array  $rows  rows of events
	 *
	 * @return array
	 */
	protected function addCategories($rows)
	{
		if (empty($rows))
		{
			return $rows;
		}

		$gids = JFactory::getUser()->getAuthorisedViewLevels();
		$gids = implode(',', $gids);

		for ($i = 0, $n = count($rows); $i < $n; $i++)
		{
			$db = $this->_db;
			$query = $db->getQuery(true);

			$query->select('c.id, c.name AS name, c.color');
			$query->select('CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as slug');
			$query->from('#__redevent_categories as c');
			$query->join('INNER', '#__redevent_event_category_xref as xcat ON xcat.category_id = c.id');
			$query->where('c.published = 1');
			$query->where('xcat.event_id = ' . $this->_db->Quote($rows[$i]->id));
			$query->where('c.access IN (' . $gids . ')');
			$query->group('c.id');
			$query->order('c.ordering');

			if ($this->getState('filter.language'))
			{
				$query->where('(c.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ') OR c.language IS NULL)');
			}

			$db->setQuery($query);

			$rows[$i]->categories = $db->loadObjectList();
		}

		return $rows;
	}

	/**
	 * Method to get a pagination object for the events
	 *
	 * @return integer
	 */
	public function getPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->pagination))
		{
			$this->pagination = new RedeventAjaxPagination(
				$this->getTotal(), $this->getState('list.start'), $this->getState('list.limit')
			);
		}

		return $this->pagination;
	}
}
