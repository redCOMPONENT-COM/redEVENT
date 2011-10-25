<?php
/**
 * @version 1.0 $Id: group.php 1586 2009-11-17 16:39:21Z julien $
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

//no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

/**
 * redEVENT Component Group ACL Model
 *
 * @package Joomla
 * @subpackage redEVENT
 * @since		0.9
 */
class RedEventModelGroupacl extends JModel
{
	/**
	 * group id
	 *
	 * @var int
	 */
	var $_id = null;
	/**
	 * group
	 *
	 * @var int
	 */
	var $_group = null;

	/**
	 * Constructor
	 *
	 * @since 0.9
	 */
	function __construct()
	{
		parent::__construct();

		$group_id = JRequest::getVar('group_id',  0, '', 'int');
		$this->setId($group_id);
	}

	/**
	 * Method to set the identifier
	 *
	 * @access	public
	 * @param	int event identifier
	 */
	function setId($id)
	{
		// Set event id and wipe data
		$this->_id	    = $id;
		$this->_data	= null;
	}

	/**
	 * Logic for the Group edit screen
	 *
	 */
	function &getData()
	{
		if ($this->_loadData())
		{

		}
		else  $this->_initData();

		//$this->_loadData();
		return $this->_data;
	}
	
	/**
	 * get categories where the group has admin access
	 * @return array
	 */
	function getMaintainedCategories()
	{
		$query = ' SELECT category_id FROM #__redevent_groups_categories '
		       . ' WHERE group_id ='. $this->_db->Quote($this->_id)
		       . '   AND accesslevel >= 1'
		       ;
		$this->_db->setQuery($query);
		$res = $this->_db->loadResultArray();
		return $res;
	} 

	/**
	 * get venues where the group has admin access
	 * @return array
	 */
	function getMaintainedVenues()
	{
		$query = ' SELECT venue_id FROM #__redevent_groups_venues '
		       . ' WHERE group_id ='. $this->_db->Quote($this->_id)
		       . '   AND accesslevel >= 1'
		       ;
		$this->_db->setQuery($query);
		$res = $this->_db->loadResultArray();
		return $res;
	} 

	/**
	 * get venues categories where the group has admin access
	 * @return array
	 */
	function getMaintainedVenuesCategories()
	{
		$query = ' SELECT category_id FROM #__redevent_groups_venues_categories '
		       . ' WHERE group_id ='. $this->_db->Quote($this->_id)
		       . '   AND accesslevel >= 1'
		       ;
		$this->_db->setQuery($query);
		$res = $this->_db->loadResultArray();
		return $res;
	} 
	
	/**
	 * Method to store the group
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function store($data)
	{
		$group_id = $data['group_id'];
		$cats = $data['maintaincategories'];
		$venues = $data['maintainvenues'];
		$vcats = $data['maintainvenuescategories'];
				
		// wipe previous records
		$query = 'DELETE FROM #__redevent_groups_categories WHERE group_id = '. $this->_db->Quote((int) $group_id);
		$this->_db->setQuery($query);
		if (!$this->_db->query())
		{
			$this->setError('ERROR DELETING PREVIOUS RECORDS');
			return false;
		}
		// add new records
		foreach ((array) $cats as $cat)
		{
			$obj = &JTable::getInstance('redevent_groupscategories', '');
			$obj->set('group_id', $group_id);
			$obj->set('category_id', $cat);
			$obj->set('accesslevel', 1);
			
			if (!($obj->check() && $obj->store()))
			{
				$this->setError(JText::_('COM_REDEVENT_Error_saving_group_category_acl') .': '. $obj->getError());
				return false;
			}			
		}
		
	  // wipe previous records
		$query = 'DELETE FROM #__redevent_groups_venues WHERE group_id = '. $this->_db->Quote((int) $group_id);
		$this->_db->setQuery($query);
		if (!$this->_db->query())
		{
			$this->setError('ERROR DELETING PREVIOUS RECORDS');
			return false;
		}
		// add new records
		foreach ((array) $venues as $v)
		{
			$obj = &JTable::getInstance('redevent_groupsvenues', '');
			$obj->set('group_id', $group_id);
			$obj->set('venue_id', $v);
			$obj->set('accesslevel', 1);
			
			if (!($obj->check() && $obj->store()))
			{
				$this->setError(JText::_('COM_REDEVENT_Error_saving_group_venue_acl') .': '. $obj->getError());
				return false;
			}			
		}
		
	  // wipe previous records
		$query = 'DELETE FROM #__redevent_groups_venues_categories WHERE group_id = '. $this->_db->Quote((int) $group_id);
		$this->_db->setQuery($query);
		if (!$this->_db->query())
		{
			$this->setError('ERROR DELETING PREVIOUS RECORDS');
			return false;
		}
		// add new records
		foreach ((array) $vcats as $v)
		{
			$obj = &JTable::getInstance('redevent_groupsvenuescategories', '');
			$obj->set('group_id', $group_id);
			$obj->set('category_id', $v);
			$obj->set('accesslevel', 1);
			
			if (!($obj->check() && $obj->store()))
			{
				$this->setError(JText::_('COM_REDEVENT_Error_saving_group_venue_category_acl') .': '. $obj->getError());
				return false;
			}			
		}
		return true;
	}
	
	/**
	 * Get a option list of all categories
	 */
	public function getCategoriesOptions() 
	{
		return ELAdmin::getCategoriesOptions();
	}
	
	/**
	 * Get a option list of all categories
	 */
	public function getVenuesCategoriesOptions() 
	{
	 $query = ' SELECT c.id, c.name, (COUNT(parent.name) - 1) AS depth '
           . ' FROM #__redevent_venues_categories AS c, '
           . ' #__redevent_venues_categories AS parent '
           . ' WHERE c.lft BETWEEN parent.lft AND parent.rgt '
           . ' GROUP BY c.id '
           . ' ORDER BY c.lft;'
           ;
    $this->_db->setQuery($query);
    
    $results = $this->_db->loadObjectList();
    
    $options = array();
    foreach((array) $results as $cat)
    {
      $options[] = JHTML::_('select.option', $cat->id, str_repeat('>', $cat->depth) . ' ' . $cat->name);
    }
		return $options;
	}

	/**
	 * Get a option list of all categories
	 */
	public function getVenuesOptions() 
	{
	 $query = ' SELECT v.id, v.venue '
           . ' FROM #__redevent_venues AS v '
           . ' ORDER BY v.venue'
           ;
    $this->_db->setQuery($query);
    
    $results = $this->_db->loadObjectList();
    
    $options = array();
    foreach((array) $results as $r)
    {
      $options[] = JHTML::_('select.option', $r->id, $r->venue);
    }
		return $options;
	}
	
	/**
	 * returns group id and name
	 * @return array
	 */
	function getGroup()
	{
		if (empty($this->_group))
		{
			$query = ' SELECT id, name '
			       . ' FROM #__redevent_groups '
			       . ' WHERE id = '. $this->_db->Quote($this->get('_id'))
			            ;
			$this->_db->setQuery($query);
			$this->_group = $this->_db->loadObject();
		}
		return $this->_group;
	}
}
?>