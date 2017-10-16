<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

namespace Redevent\Model;

defined('_JEXEC') or die('Restricted access');


/**
 * redEVENT Component events Model
 *
 * @package  Redevent.admin
 * @since    2.0
 */
class AbstractEventsModel extends \RModelList
{
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

		$query->select('obj.*, u.email, u.name AS author, u2.name as editor');
		$query->select('t.name AS template_name');
		$query->select(
			'CASE WHEN CHAR_LENGTH(obj.alias) THEN CONCAT_WS(\':\', obj.id, obj.alias) ELSE obj.id END as slug'
		);
		$query->from('#__redevent_events AS obj ');
		$query->innerJoin('#__redevent_event_template AS t ON t.id = obj.template_id');
		$query->leftJoin('#__redevent_event_category_xref AS xcat ON xcat.event_id = obj.id');
		$query->leftJoin('#__redevent_categories AS cat ON cat.id = xcat.category_id');
		$query->leftJoin('#__redevent_event_venue_xref AS x ON x.eventid = obj.id');
		$query->leftJoin('#__redevent_venues AS loc ON loc.id = x.venueid');
		$query->leftJoin('#__users AS u ON u.id = obj.created_by');
		$query->leftJoin('#__users AS u2 ON u2.id = obj.modified_by');

		// Join over the language
		$query->select('l.title AS language_title');
		$query->join('LEFT', $db->quoteName('#__languages') . ' AS l ON l.lang_code = obj.language');

		// Get the WHERE and ORDER BY clauses for the query
		$query = $this->buildContentWhere($query);

		$order = $this->getState('list.ordering', 'obj.title');
		$dir = $this->getState('list.direction', 'asc');
		$query->order($db->qn($order) . ' ' . $dir);

		$query->group('obj.id, l.lang_code');

		return $query;
	}

	/**
	 * Build the where clause
	 *
	 * @param   JDatabaseQuery  $query  query
	 *
	 * @return  JDatabaseQuery
	 */
	protected function buildContentWhere($query)
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

		$acl = \RedeventUserAcl::getInstance();
		$user = \JFactory::getUser();

		$aclCheck = $this->getState('filter.acl');

		if ($aclCheck && !$acl->superuser() && !$user->authorise('core.edit', 'com_redevent'))
		{
			$categoryIds = $acl->getManagedCategories();

			if (empty($categoryIds))
			{
				$query->where('obj.created_by = ' . $user->get('id'));
			}
			else
			{
				$query->where(
					'(cat.id IN (' . implode(',', $categoryIds) . ')'
					. ' OR obj.created_by = ' . $user->get('id') . ')'
				);
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
	protected function getCategories($rows)
	{
		$db = $this->_db;
		$gids = \JFactory::getUser()->getAuthorisedViewLevels();
		$gids = implode(',', $gids);

		for ($i = 0, $n = count($rows); $i < $n; $i++)
		{
			$query = $db->getQuery(true);

			$query->select('c.id, c.name AS name, c.color, c.checked_out, c.published');
			$query->select('CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as slug');
			$query->from('#__redevent_categories as c');
			$query->join('INNER', '#__redevent_event_category_xref as xcat ON xcat.category_id = c.id');
			$query->where('c.published = 1');
			$query->where('xcat.event_id = ' . $this->_db->Quote($rows[$i]->id));
			$query->group('c.id');
			$query->order('c.ordering');

			if ($this->getState('filter.acl'))
			{
				$query->where('c.access IN (' . $gids . ')');
			}

			if ($this->getState('filter.language'))
			{
				$query->where(
					'(c.language in (' . $db->quote(\JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ') OR c.language IS NULL)'
				);
			}

			$db->setQuery($query);
			$rows[$i]->categories = $db->loadObjectList();
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
}
