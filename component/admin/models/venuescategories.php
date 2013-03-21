<?php
/**
 * @version 1.0 $Id: categories.php 160 2009-05-29 16:16:39Z julien $
 * @package Joomla
 * @subpackage redEVENT
 * @copyright redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license GNU/GPL, see LICENSE.php
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

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

/**
 * EventList Component Categories Model
 *
 * @package Joomla
 * @subpackage redEVENT
 * @since		0.9
 */
class RedeventModelVenuescategories extends JModel
{
	/**
	 * Category data array
	 *
	 * @var array
	 */
	var $_data = null;

	/**
	 * Category total
	 *
	 * @var integer
	 */
	var $_total = null;

	/**
	 * Pagination object
	 *
	 * @var object
	 */
	var $_pagination = null;

	/**
	 * Categorie id
	 *
	 * @var int
	 */
	var $_id = null;

	/**
	 * Constructor
	 *
	 * @since 0.9
	 */
	function __construct()
	{
		parent::__construct();

		$mainframe = &JFactory::getApplication();
		$option = JRequest::getCmd('option');

		$limit      = $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
		$this->setState('limit', $limit);
		$limitstart = $mainframe->getUserStateFromRequest( $option.'.venuescategories.limitstart', 'limitstart', 0, '', 'int' );
		// In case limit has been changed, adjust it
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
		$this->setState('limitstart', $limitstart);

		$filter_state = $mainframe->getUserStateFromRequest( $option.'.filter_state', 'filter_state', '', 'word' );
		$this->setState('filter_state', $filter_state);

		$filter       = $mainframe->getUserStateFromRequest( $option.'.filter', 'filter', '', 'int' );
		$this->setState('filter', $filter);

		$search       = $mainframe->getUserStateFromRequest( $option.'.search', 'search', '', 'string' );
		$search       = $this->_db->getEscaped( trim(JString::strtolower( $search ) ) );
		$this->setState('search', $search);

		$filter_language = $mainframe->getUserStateFromRequest( $option.'.filter_language', 'filter_language', '', 'string' );
		$this->setState('filter_language', $filter_language);

		$filter_order		= $mainframe->getUserStateFromRequest( $option.'.venuescategories.filter_order', 'filter_order', 'c.ordering', 'cmd' );
		$this->setState('filter_order', $filter_order);

		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $option.'.venuescategories.filter_order_Dir', 'filter_order_Dir', '', 'word' );
		$this->setState('filter_order_Dir', $filter_order_Dir);

		$array = JRequest::getVar('cid',  0, '', 'array');
		$this->setId((int)$array[0]);
	}

	/**
	 * Method to set the category identifier
	 *
	 * @access	public
	 * @param	int Category identifier
	 */
	function setId($id)
	{
		// Set id and wipe data
		$this->_id	 = $id;
		$this->_data = null;
	}

	/**
	 * Method to get categories item data
	 *
	 * @access public
	 * @return array
	 */
	function getData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$query = $this->_buildQuery();
			$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));

			$k = 0;
			$count = count($this->_data);
			for($i = 0; $i < $count; $i++)
			{
				$category =& $this->_data[$i];

				$category->assignedvenues = $this->_countcatvenues( $category->id );

				$k = 1 - $k;
			}
		}

		return $this->_data;
	}

	/**
	 * Method to get the total nr of the categories
	 *
	 * @access public
	 * @return integer
	 */
	function getTotal()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_total))
		{
			$query = $this->_buildQuery();
			$this->_total = $this->_getListCount($query);
		}

		return $this->_total;
	}

	/**
	 * Method to get a pagination object for the categories
	 *
	 * @access public
	 * @return integer
	 */
	function getPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_pagination))
		{
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination( $this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
		}

		return $this->_pagination;
	}

	/**
	 * Method to build the query for the categories
	 *
	 * @access private
	 * @return integer
	 * @since 0.9
	 */
	function _buildQuery()
	{
		$db = &JFactory::getDbo();
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
		$query->join('LEFT', $db->quoteName('#__languages').' AS lg ON lg.lang_code = c.language');

		// Get the WHERE and ORDER BY clauses for the query
		$query = $this->_buildContentWhere($query);
		$query = $this->_buildContentOrderBy($query);

		return $query;
	}

	/**
	 * Method to build the orderby clause of the query for the categories
	 *
	 * @access private
	 * @return string
	 * @since 0.9
	 */
	function _buildContentOrderBy($query)
	{
		$filter_order		= $this->getState('filter_order');
		$filter_order_Dir	= $this->getState('filter_order_Dir');

		$query->order($filter_order.' '.$filter_order_Dir.', c.ordering');

		return $query;
	}

	/**
	 * Method to build the where clause of the query for the categories
	 *
	 * @access private
	 * @return string
	 * @since 0.9
	 */
	function _buildContentWhere($query)
	{
		$filter_state 		= $this->getState('filter_state');
		$filter 			= $this->getState('filter');
		$search 			= $this->getState('search');

		if ( $filter_state ) {
			if ( $filter_state == 'P' ) {
				$query->where('c.published = 1');
			} else if ($filter_state == 'U' ) {
				$query->where('c.published = 0');
			}
		}

		if ($search) {
			$query->where(' LOWER(c.name) LIKE \'%'.$search.'%\' ');
		}

		$filter_language = $this->getState('filter_language');
		if ($filter_language) {
			// 			$this->setState('filter_language', $filter_language);
			$query->where('c.language = '.$this->_db->quote($filter_language));
		}

		return $query;
	}

	/**
	 * Method to (un)publish a category
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	0.9
	 */
	function publish($cid = array(), $publish = 1)
	{
		$user 	=& JFactory::getUser();

		if (count( $cid ))
		{
			$cids = implode( ',', $cid );

			$query = 'UPDATE #__redevent_venues_categories'
				. ' SET published = ' . (int) $publish
				. ' WHERE id IN ('. $cids .')'
				. ' AND ( checked_out = 0 OR ( checked_out = ' . (int) $user->get('id'). ' ) )'
			;
			$this->_db->setQuery( $query );
			if (!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}

			// for finder plugins
			$dispatcher	= JDispatcher::getInstance();
			JPluginHelper::importPlugin('finder');

			// Trigger the onFinderCategoryChangeState event.
			$dispatcher->trigger('onFinderCategoryChangeState', array('com_redevent.venue_category', $cids, $publish));
		}
		return true;
	}

	/**
	 * Method to move a category
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	0.9
	 */
	function move($direction)
	{
		$row =& JTable::getInstance('redevent_venues_categories', '');

		if (!$row->load( $this->_id ) ) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		if (!$row->move( $direction )) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		return true;
	}

	/**
	 * Method to order categories
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	0.9
	 */
	function saveorder($cid = array(), $order)
	{
		$row =& JTable::getInstance('redevent_venues_categories', '');

		// update ordering values
		for( $i=0; $i < count($cid); $i++ )
		{
			$row->load( (int) $cid[$i] );

			if ($row->ordering != $order[$i])
			{
				$row->ordering = $order[$i];
				if (!$row->store()) {
					$this->setError($this->_db->getErrorMsg());
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Method to count the nr of venues events to the category
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	0.9
	 */
	function _countcatvenues($id)
	{
		$query = 'SELECT COUNT( v.id )'
				.' FROM #__redevent_venues_categories AS c '
				.' INNER JOIN #__redevent_venues_categories AS child ON child.lft BETWEEN c.lft AND c.rgt '
        .' INNER JOIN #__redevent_venue_category_xref AS xv ON xv.category_id = child.id '
        .' INNER JOIN #__redevent_venues AS v ON v.id = xv.venue_id '
				.' WHERE c.id = ' . (int)$id
				;

		$this->_db->setQuery($query);
		$number = $this->_db->loadResult();

    return $number;
	}


	/**
	 * Method to remove a venues category
	 *
	 * @access	public
	 * @return	string $msg
	 * @since	0.9
	 */
	function delete($cid)
	{
		$cids = implode( ',', $cid );

		$query = 'SELECT c.id, c.name, COUNT( xv.category_id ) AS numvenues'
				. ' FROM #__redevent_venues_categories AS c'
				. ' LEFT JOIN #__redevent_venue_category_xref AS xv ON xv.category_id = c.id'
				. ' WHERE c.id IN ('. $cids .')'
				. ' GROUP BY c.id'
				;
		$this->_db->setQuery( $query );

		if (!($rows = $this->_db->loadObjectList())) {
			RedeventError::raiseError( 500, $this->_db->stderr() );
			return false;
		}

		$err = array();
		$cid = array();
		foreach ($rows as $row) {
			if ($row->numvenues == 0) {
				$cid[] = $row->id;
			} else {
				$err[] = $row->name;
			}
		}

		if (count( $cid ))
		{
			$cids = implode( ',', $cid );
			$query = 'DELETE FROM #__redevent_venues_categories'
					. ' WHERE id IN ('. $cids .')';

			$this->_db->setQuery( $query );

			if(!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}

			$table = JTable::getInstance('redevent_venues_categories', '');
			$table->rebuildTree();
		}

		if (count( $err )) {
			$cids 	= implode( ', ', $err );
    		$msg 	= JText::sprintf( 'COM_REDEVENT_VENUES_ASSIGNED_CATEGORY_S', $cids );
    		return $msg;
		} else {
			$total 	= count( $cid );
			$msg 	= $total.' '.JText::_('COM_REDEVENT_CATEGORIES_DELETED');
			return $msg;
		}
	}
}
