<?php
/**
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

jimport('joomla.application.component.model');

/**
 * Redevent Model Categories
 *
 * @package     Joomla
 * @subpackage  redEVENT
 * @since       0.9
 */
class RedeventModelCategories extends JModel
{
	/**
	 * category to use as a base for queries
	 *
	 * @var unknown_type
	 */
	protected $_parent = null;

	/**
	 * Categories data array
	 *
	 * @var array
	 */
	protected $_data = null;

	/**
	 * Categories total
	 *
	 * @var integer
	 */
	protected $_total = null;

	/**
	 * Pagination object
	 *
	 * @var object
	 */
	protected $_pagination = null;

	/**
	 * Constructor
	 *
	 * @since 0.9
	 */
	public function __construct()
	{
		parent::__construct();

		$app = JFactory::getApplication();

		// Get the paramaters of the active menu item
		$params = $app->getParams();

		if ($params->get('parentcategory', 0))
		{
			$this->setParent($params->get('parentcategory', 0));
		}

		// Get the number of events from database
		$limit			= JRequest::getInt('limit', $params->get('cat_num'));
		$limitstart		= JRequest::getInt('limitstart');

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);

		$this->setState('filter.language', $app->getLanguageFilter());
	}

	/**
	 * set the parent category id
	 *
	 * @param   int  $id  parent category id
	 *
	 * @return boolean
	 */
	public function setParent($id)
	{
		$sub = ' SELECT id, lft, rgt FROM #__redevent_categories WHERE id = ' . $this->_db->Quote((int) $id);
		$this->_db->setQuery($sub);
		$obj = $this->_db->loadObject();

		if (!$obj)
		{
			JError::raiseWarning(0, JText::_('COM_REDEVENT_PARENT_CATEGORY_NOT_FOUND'));
		}
		else
		{
			$this->_parent = $obj;
			$this->_categories = null;
		}

		return true;
	}

	/**
	 * Method to get the Categories
	 *
	 * @access public
	 * @return array
	 */
	public function &getData( )
	{
		$elsettings = redEVENTHelper::config();

		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$query = $this->_buildQuery();
			$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));

			$k = 0;
			$count = count($this->_data);

			for ($i = 0; $i < $count; $i++)
			{
				$category = $this->_data[$i];

				// Create target link
				$task 	= JRequest::getWord('task');

				$category->linktext = JText::_('COM_REDEVENT_SHOW_EVENTS');

				$category->linktarget = RedeventHelperRoute::getCategoryEventsRoute($category->slug);

				$k = 1 - $k;
			}
		}

		return $this->_data;
	}

	/**
	 * Total nr of Venues
	 *
	 * @access public
	 * @return integer
	 */
	public function getTotal()
	{
		// Lets load the total nr if it doesn't already exist
		if (empty($this->_total))
		{
			$query = $this->_buildQuery();
			$this->_total = $this->_getListCount($query);
		}

		return $this->_total;
	}

	/**
	 * Method to load the Categories
	 *
	 * @access private
	 * @return array
	 */
	protected function _buildQuery()
	{
		// Initialize some vars
		$mainframe = &JFactory::getApplication();
		$params   = & $mainframe->getParams('com_redevent');
		$user		= & JFactory::getUser();
		$gid		= max($user->getAuthorisedViewLevels());

		$acl = &UserAcl::getInstance();
		$gids = $acl->getUserGroupsIds();

		if (!is_array($gids) || !count($gids))
		{
			$gids = array(0);
		}

		$gids = implode(',', $gids);

		// Check archive task and ensure that only categories get selected if they contain a published/archived event
		$task 	= JRequest::getVar('task', '', '', 'string');

		if ($task == 'archive')
		{
			$count = 'CASE WHEN x.published = -1 THEN 1 ELSE 0 END';
		}
		else
		{
			$count = 'CASE WHEN x.published = 1 THEN 1 ELSE 0 END';
		}

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('c.*, SUM(' . $count . ') AS assignedevents');
		$query->select('CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as slug');
		$query->from('#__redevent_categories AS c');
		$query->join('LEFT', '#__redevent_categories AS child ON child.lft BETWEEN c.lft AND c.rgt');
		$query->join('LEFT', '#__redevent_event_category_xref AS xcat ON xcat.category_id = child.id');
		$query->join('LEFT', '#__redevent_event_venue_xref AS x ON x.eventid = xcat.event_id');
		$query->join('LEFT', '#__redevent_events AS e ON x.eventid = e.id');
		$query->join('LEFT', '#__redevent_groups_categories AS gc ON gc.category_id = c.id AND gc.group_id IN (' . $gids . ')');
		$query->where('child.published = 1');
		$query->where('child.access <= ' . $gid);

		if ($params->get('display_all_categories', 0) == 0)
		{
			if ($task == 'archive')
			{
				$query->where('x.published = -1');
			}
			else
			{
				$query->where(' x.published = 1');
			}
		}

		if ($this->_parent)
		{
			$query->where('c.parent_id = ' . $this->_db->Quote($this->_parent->id));
		}

		$query->where('(c.private = 0 OR gc.id IS NOT NULL) ');

		if ($this->getState('filter.language'))
		{
			$query->where('(e.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ') OR e.language IS NULL)');
			$query->where('(c.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ') OR c.language IS NULL)');
		}

		$query->group('c.id ');
		$query->order('c.ordering ASC');

		return $query;
	}
}
