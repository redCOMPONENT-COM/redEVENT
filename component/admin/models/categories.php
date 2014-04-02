<?php
/**
 * @version     2.5
 * @package     Joomla
 * @subpackage  redEVENT
 * @copyright   redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license     GNU/GPL, see LICENSE.php
 * redEVENT is based on EventList made by Christoph Lukes from schlu.net
 * redEVENT can be downloaded from www.redcomponent.com
 * redEVENT is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * redEVENT is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with redEVENT; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * EventList Component Categories Model
 *
 * @package     Joomla
 * @subpackage  redEVENT
 * @since       0.9
*/
class RedeventModelCategories extends FOFModel
{
	/**
	 * constructor
	 *
	 * @param   array  $config  config
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$app    = JFactory::getApplication();
		$db     = JFactory::getDbo();
		$option = $this->input->get('option');

		// Get vars
		$filter_order		= $app->getUserStateFromRequest($option . '.categories.filter_order', 		'filter_order', 	'c.lft', 'cmd');
		$this->setState('filter_order', $filter_order);

		$filter_order_Dir	= $app->getUserStateFromRequest($option . '.categories.filter_order_Dir',	'filter_order_Dir',	'', 'word');
		$this->setState('filter_order_Dir', $filter_order_Dir);

		$filter_state 		= $app->getUserStateFromRequest($option . '.categories.filter_state', 		'filter_state', 	'*', 'word');
		$this->setState('filter_state', $filter_state);

		$search 			= $app->getUserStateFromRequest($option . '.categories.search', 			'search', 			'', 'string');
		$search 			= $db->getEscaped(trim(JString::strtolower($search)));
		$this->setState('search', $search);
	}

	/**
	 * (non-PHPdoc)
	 * @see FOFModel::getItemList()
	 */
	public function &getItemList($overrideLimits = false, $group = '')
	{
		$list = parent::getItemList($overrideLimits, $group);

		if (!$list || !count($list))
		{
			return $this->list;
		}

		// Assigned events count
		$count = count($this->list);

		for($i = 0; $i < $count; $i++)
		{
			$category =& $this->list[$i];
			$category->assignedevents = $this->_countcatevents($category->id);
		}

		return $this->list;
	}

	/**
	 * Method to build the query for the categories
	 *
	 * @param   boolean  $overrideLimits  true to override limits
	 *
	 * @return integer
	 */
	public function buildQuery($overrideLimits = false)
	{
		$db = &JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('c.*, (COUNT(parent.catname) - 1) AS depth, p.catname as parent_name');
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
		$query = $this->_buildContentWhere($query);

		if (!$overrideLimits)
		{
			$order = $this->getState('filter_order', 'c.lft', 'cmd');

			$dir = $this->getState('filter_order_Dir', '', 'cmd');
			$query->order($db->qn($order) . ' ' . $dir);
		}

		return $query;
	}

	/**
	 * Method to build the where clause of the query for the categories
	 *
	 * @param   JDatabaseQuery  $query  query
	 *
	 * @return  JDatabaseQuery
	 */
	protected function _buildContentWhere($query)
	{
		$mainframe = &JFactory::getApplication();
		$option = JRequest::getCmd('option');
		$search = $this->getState('search');

		if ($filter_state = $this->getState('filter_state', ''))
		{
			if ($filter_state == 'P')
			{
				$query->where('c.published = 1');
			}
			elseif ($filter_state == 'U' )
			{
				$query->where('c.published = 0');
			}
		}

		$filter_language = $this->getState('language');

		if ($filter_language)
		{
			$this->setState('language', $filter_language);
			$query->where('c.language = ' . $this->_db->quote($filter_language));
		}

		if ($search)
		{
			$query->where('LOWER(c.catname) LIKE \'%' . $search . '%\'');
		}

		return $query;
	}

	/**
	 * overide to add an integration to finder
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
	 * get attachements
	 *
	 * @see FOFModel::onAfterGetItem()
	 */
	protected function onAfterGetItem(&$record)
	{
		if ($record)
		{
			$files = RedeventHelperAttachment::getAttachments('category' . $record->id);
			$record->attachments = $files;
		}
	}

	/**
	 * add attachements
	 *
	 * @see FOFModel::onAfterSave()
	 */
	protected function onAfterSave(&$table)
	{
		parent::onAfterSave($table);

		// Attachments
		RedeventHelperAttachment::store('category' . $table->id);

		// Trigger the onFinderAfterSave event.
		$dispatcher = JDispatcher::getInstance();
		$results = $dispatcher->trigger('onFinderAfterSave', array($this->option . '.' . $this->name, $table, $this->_isNewRecord));
	}

	/**
	 * Method to count the nr of assigned events to the category
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	0.9
	 */
	protected function _countcatevents($id)
	{
		$query = 'SELECT COUNT( * )'
		. ' FROM #__redevent_event_category_xref AS x'
		. ' WHERE x.category_id = ' . (int) $id;

		$this->_db->setQuery($query);
		$number = $this->_db->loadResult();

		return $number;
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
	 * export venues
	 *
	 * @param   array  $categories  filter
	 *
	 * @return array
	 */
	public function export($categories = null)
	{
		$where = array();

		if (count($where))
		{
			$where = ' WHERE ' . implode(' AND ', $where);
		}
		else
		{
			$where = '';
		}

		$query = ' SELECT c.id, c.catname, c.alias, c.catdescription, c.meta_description, c.meta_keywords,  '
		. ' c.color, c.image, c.published, c.access,  '
		. ' c.event_template, c.ordering  '
		. ' FROM #__redevent_categories AS c '
		. $where;
		$this->_db->setQuery($query);

		$results = $this->_db->loadAssocList();

		return $results;
	}

	/**
	 * import categories in database
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
			$v = $this->getTable();
			$v->bind($r);

			if (isset($r->id) && $r->id)
			{
				// Load existing data
				$found = $v->load($r->id);

				// Discard if set to ignore duplicate
				if ($found && $duplicate_method == 'ignore')
				{
					$count['ignored']++;
					continue;
				}
			}

			// Bind submitted data
			$v->bind($r);

			if ($duplicate_method == 'update' && $found)
			{
				$updating = 1;
			}
			else
			{
				// To be sure to create a new record
				$v->id = null;
				$updating = 0;
			}

			// Store !
			if (!$v->check())
			{
				$app->enqueueMessage(JText::_('COM_REDEVENT_IMPORT_ERROR') . ': ' . $v->getError(), 'error');
				continue;
			}

			if (!$v->store())
			{
				$app->enqueueMessage(JText::_('COM_REDEVENT_IMPORT_ERROR') . ': ' . $v->getError(), 'error');
				continue;
			}

			if ($updating)
			{
				$count['updated']++;
			}
			else
			{
				$count['added']++;
			}
		}

		return $count;
	}
}
