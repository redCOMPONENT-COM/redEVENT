<?php
/**
 * @version 1.0 $Id: redevent_categories.php 160 2009-05-29 16:16:39Z julien $
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

defined('_JEXEC') or die('Restricted access');

/**
 * redEvetnt venues categories Model class
 * 
 * The hierachical structure uses the The Nested Set Model (Modified Preorder Tree Traversal)
 * see http://dev.mysql.com/tech-resources/articles/hierarchical-data.html for reference
 *
 * @package Joomla
 * @subpackage redEvetnt
 * @since 0.9
 */
class RedEvent_venues_categories extends JTable
{
	/**
	 * Primary Key
	 * @var int
	 */
	var $id 				= null;
	/** @var int */
	var $parent_id			= 0;
	/** @var string */
	var $name 			= '';
	/** @var string */
	var $alias	 			= '';
	/** @var string */
	var $description 	= null;
	/** @var string */
	var $meta_description 	= null;
	/** @var string */
	var $meta_keywords		= null;
	/** @var string */
	var $image 				= '';
	/** @var int */
	var $private			= 0;
	/** @var int */
	var $published			= null;
	/** @var int */
	var $checked_out 		= 0;
	/** @var date */
	var $checked_out_time	= 0;
	/** @var int */
	var $access 			= 0;
	/** @var int */
	var $groupid 			= 0;
	/** @var string */
	var $maintainers		= null;
	/** @var int */
	var $ordering 			= null;

	/**
	* @param database A database connector object
	*/
	function redevent_venues_categories(& $db) {
		parent::__construct('#__redevent_venues_categories', 'id', $db);
	}

	// overloaded check function
	function check()
	{
		// Not typed in a category name?
		if (trim( $this->name ) == '') {
			$this->_error = JText::_('COM_REDEVENT_ADD_NAME_CATEGORY' );
			JError::raiseWarning('REDEVENT_GENERIC_ERROR', $this->_error );
			return false;
		}

		$alias = JFilterOutput::stringURLSafe($this->name);

		if(empty($this->alias) || $this->alias === $alias ) {
			$this->alias = $alias;
		}

		/** check for existing name */
		$query = 'SELECT id FROM #__redevent_venues_categories WHERE name = '.$this->_db->Quote($this->name);
		$this->_db->setQuery($query);

		$xid = intval($this->_db->loadResult());
		if ($xid && $xid != intval($this->id)) {
			JError::raiseWarning('REDEVENT_GENERIC_ERROR', JText::sprintf('COM_REDEVENT_CATEGORY_NAME_ALREADY_EXIST', $this->name));
			return false;
		}

		return true;
	}
	
	function store($updateNulls = false)
	{
		if (parent::store($updateNulls)) {
			$this->rebuildTree();			
		}
		else return false;
		
		return true;
	}
	
	function rebuildTree()
	{
		$this->_rebuildTree(0, 0);
	}
	
	function _rebuildTree($parent, $left) {
	   // the right value of this node is the left value + 1
	   $right = $left+1;
	
	   // get all children of this node
	   $this->_db->setQuery('SELECT id FROM #__redevent_venues_categories WHERE parent_id = '.$this->_db->Quote($parent));
	   $children = $this->_db->loadResultArray();
	   foreach((array)$children as $child_id) {
	       // recursive execution of this function for each
	       // child of this node
	       // $right is the current right value, which is
	       // incremented by the rebuild_tree function
	       $right = $this->_rebuildTree($child_id, $right);
	   }
	
	   // we've got the left value, and now that we've processed
	   // the children of this node we also know the right value
	   $this->_db->setQuery('UPDATE #__redevent_venues_categories SET lft='.$left.', rgt='.
	                $right.' WHERE id='.$parent);
	   $this->_db->query();
	
	   // return the right value of this node + 1
	   return $right+1;
	}
}
