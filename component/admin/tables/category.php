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

defined('_JEXEC') or die('Restricted access');

/**
 * EventList categories Model class
 *
 * The hierachical structure uses the The Nested Set Model (Modified Preorder Tree Traversal)
 * see http://dev.mysql.com/tech-resources/articles/hierarchical-data.html for reference
 *
 * @package     Joomla
 * @subpackage  redEVENT
 * @since       0.9
*/
class RedeventTableCategory extends FOFTable
{
	/**
	 * constructor
	 *
	 * @param   string    $table  name of the table
	 * @param   string    $key    table primary key
	 * @param   database  &$db    A database connector object
	 */
	public function __construct($table, $key, &$db)
	{
		parent::__construct('#__redevent_categories', 'id', $db);
		$this->setColumnAlias('enabled', 'published');
	}

	/**
	 * overrides check
	 *
	 * @see FOFTable::check()
	 *
	 * @return boolean
	 */
	public function check()
	{
		// Not typed in a category name?
		if (trim($this->catname) == '')
		{
			$this->_error = JText::_('COM_REDEVENT_ADD_NAME_CATEGORY');
			RedeventError::raiseWarning('REDEVENT_GENERIC_ERROR', $this->_error);

			return false;
		}

		$alias = JFilterOutput::stringURLSafe($this->catname);

		if (empty($this->alias) || $this->alias === $alias)
		{
			$this->alias = $alias;
		}

		return true;
	}

	/**
	 * override bind function
	 *
	 * @param   mixed   $src     data
	 * @param   string  $ignore  An optional array or space separated list of properties to ignore while binding.
	 *
	 * @return boolean
	 */
	public function bind($src, $ignore = '')
	{
		// If the source value is an object, get its accessible properties.
		if (is_object($src))
		{
			$src = get_object_vars($src);
		}

		// Bind the rules.

		if (isset($src['rules']) && is_array($src['rules']))
		{
			$filtered = array();
			foreach ((array) $src['rules'] as $action => $ids)
			{
				// Build the rules array.
				$filtered[$action] = array();
				foreach ($ids as $id => $p)
				{
					if ($p !== '')
					{
						$filtered[$action][$id] = ($p == '1' || $p == 'true') ? true : false;
					}
				}
			}
			$rules = new JAccessRules($filtered);
			$this->setRules($rules);
		}

		return parent::bind($src, $ignore);
	}

	/**
	 * overrides store function, with the tree rebuild function
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return boolean
	 *
	 * @see FOFTable::store()
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
		$this->_db->setQuery('SELECT id FROM #__redevent_categories WHERE parent_id = ' . $this->_db->Quote($parent));
		$children = $this->_db->loadResultArray();

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
		$this->_db->setQuery('UPDATE #__redevent_categories SET lft=' . $left . ', rgt=' .
			$right . ' WHERE id=' . $parent
		);
		$this->_db->query();

		// Return the right value of this node + 1
		return $right + 1;
	}

	/**
	 * Method to compute the default name of the asset.
	 * The default name is in the form `table_name.id`
	 * where id is the value of the primary key of the table.
	 *
	 * @return      string
	 *
	 * @since       2.5
	 **/
	protected function _getAssetName()
	{
		$k = $this->_tbl_key;

		return 'com_redevent.category.' . (int) $this->$k;
	}

	/**
	 * Method to return the title to use for the asset table.
	 *
	 * @return      string
	 *
	 * @since       2.5
	 */
	protected function _getAssetTitle()
	{
		return $this->catname;
	}

	/**
	 * Method to get the asset-parent-id of the item
	 *
	 * @return      int
	 */
	protected function _getAssetParentId()
	{
		// We will retrieve the parent-asset from the Asset-table
		$assetParent = JTable::getInstance('Asset');

		// Default: if no asset-parent can be found we take the global asset
		$assetParentId = $assetParent->getRootId();

		// Find the parent-asset
		if (($this->parent_id)&& !empty($this->parent_id))
		{
			// The item has a category as asset-parent
			$assetParent->loadByName('com_redevent.category.' . (int) $this->parent_id);
		}
		else
		{
			// The item has the component as asset-parent
			$assetParent->loadByName('com_redevent');
		}

		// Return the found asset-parent-id
		if ($assetParent->id)
		{
			$assetParentId = $assetParent->id;
		}

		return $assetParentId;
	}
}
