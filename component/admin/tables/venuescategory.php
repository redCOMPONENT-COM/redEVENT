<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevent Venues category Table class
 *
 * The hierachical structure uses the The Nested Set Model (Modified Preorder Tree Traversal)
 * see http://dev.mysql.com/tech-resources/articles/hierarchical-data.html for reference
 *
 * @package  Redevent.admin
 * @since    0.9
 */
class RedeventTableVenuesCategory extends RedeventTable
{
	/**
	 * The name of the table with category
	 *
	 * @var string
	 * @since 0.9.1
	 */
	protected $_tableName = 'redevent_venues_categories';

	/**
	 * The primary key of the table
	 *
	 * @var string
	 * @since 0.9.1
	 */
	protected $_tableKey = 'id';

	/**
	 * Field name to publish/unpublish table registers. Ex: state
	 *
	 * @var  string
	 */
	protected $_tableFieldState = 'published';

	/**
	 * Checks that the object is valid and able to be stored.
	 *
	 * This method checks that the parent_id is non-zero and exists in the database.
	 * Note that the root node (parent_id = 0) cannot be manipulated with this class.
	 *
	 * @return  boolean  True if all checks pass.
	 */
	public function check()
	{
		// Not typed in a category name?
		if (trim($this->name) == '')
		{
			$this->setError(JText::_('COM_REDEVENT_ADD_NAME_CATEGORY'));

			return false;
		}

		$alias = JFilterOutput::stringURLSafe($this->name);

		if (empty($this->alias) || $this->alias === $alias)
		{
			$this->alias = $alias;
		}

		return true;
	}

	/**
	 * Method to store a node in the database table.
	 *
	 * @param   boolean $updateNulls True to update null values as well.
	 *
	 * @return  boolean  True on success.
	 */
	public function store($updateNulls = false)
	{
		if (parent::store($updateNulls))
		{
			$this->rebuildTree();
		}
		else
		{
			return false;
		}

		return true;
	}

	/**
	 * rebuild category tree
	 *
	 * @return void
	 */
	public function rebuildTree()
	{
		$this->_rebuildTree(0, 0);
	}

	/**
	 * recursive function to build the tree
	 *
	 * @param   object   $parent  parent category
	 * @param   integer  $left    left value
	 *
	 * @return number
	 */
	protected function _rebuildTree($parent, $left)
	{
		// The right value of this node is the left value + 1
		$right = $left + 1;

		// Get all children of this node
		$db = $this->_db;
		$query = $db->getQuery(true);

		$query->select('id');
		$query->from('#__redevent_venues_categories');
		$query->where('parent_id = ' . $this->_db->Quote($parent));
		$query->order('ordering');

		$db->setQuery($query);
		$children = $db->loadColumn();

		foreach ((array) $children as $child_id)
		{
			/**
			 * Recursive execution of this function for each
			 * child of this node
			 * $right is the current right value, which is
			 * incremented by the rebuild_tree function
			 */
			$right = $this->_rebuildTree($child_id, $right);
		}

		// We've got the left value, and now that we've processed
		// the children of this node we also know the right value
		$db = $this->_db;
		$query = $db->getQuery(true);

		$query->update('#__redevent_venues_categories');
		$query->set('lft = ' . $left);
		$query->set('rgt = ' . $right);
		$query->where('id = ' . $parent);

		$db->setQuery($query);
		$db->execute();

		// Return the right value of this node + 1
		return $right + 1;
	}

	/**
	 * Called before delete().
	 *
	 * @param   mixed $pk An optional primary key value to delete.  If not set the instance property value is used.
	 *
	 * @return  boolean  True on success.
	 */
	protected function beforeDelete($pk = null)
	{
		// Initialise variables.
		$k = $this->_tbl_key;

		// Received an array of ids?
		if (is_array($pk))
		{
			// Sanitize input.
			JArrayHelper::toInteger($pk);
			$pk = RHelperArray::quote($pk);
			$pk = implode(',', $pk);
		}

		$pk = (is_null($pk)) ? $this->$k : $pk;

		// If no primary key is given, return false.
		if ($pk === null)
		{
			return false;
		}

		// Check if there are subcategories to these categories
		if (!$this->haveNoChildren($pk))
		{
			$this->setError('COM_REDEVENT_VENUECATEGORY_DELETE_ERROR_HAS_SUBCATEGORIES');

			return false;
		}

		// Check if there are events assigned to these categories
		if (!$this->haveNoVenues($pk))
		{
			$this->setError('COM_REDEVENT_VENUECATEGORY_DELETE_ERROR_HAS_VENUES');

			return false;
		}

		return parent::beforeDelete($pk);
	}

	/**
	 * Check that specified categogies have subcategories
	 *
	 * @param   array $quotedIds quoted ids
	 *
	 * @return boolean
	 */
	private function haveNoChildren($quotedIds)
	{
		$db = $this->_db;
		$query = $db->getQuery(true);

		$query->select('COUNT(*)');
		$query->from('#__redevent_venues_categories');
		$query->where('parent_id IN (' . $quotedIds . ')');

		$db->setQuery($query);
		$res = $db->loadResult();

		return $res ? false : true;
	}

	/**
	 * Check that specified categogries have no events assigned
	 *
	 * @param   array $quotedIds quoted ids
	 *
	 * @return boolean
	 */
	private function haveNoVenues($quotedIds)
	{
		$db = $this->_db;
		$query = $db->getQuery(true);

		$query->select('COUNT(*)');
		$query->from('#__redevent_venue_category_xref');
		$query->where('category_id IN (' . $quotedIds . ')');

		$db->setQuery($query);
		$res = $db->loadResult();

		return $res ? false : true;
	}

	/**
	 * Called after delete().
	 *
	 * @param   mixed $pk An optional primary key value to delete.  If not set the instance property value is used.
	 *
	 * @return  boolean  True on success.
	 */
	protected function afterDelete($pk = null)
	{
		$this->rebuildTree();

		return parent::afterDelete($pk);
	}
}
