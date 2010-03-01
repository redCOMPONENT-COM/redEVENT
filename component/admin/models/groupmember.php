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
 * EventList Component Group Model
 *
 * @package Joomla
 * @subpackage EventList
 * @since		0.9
 */
class RedEventModelGroupmember extends JModel
{
	/**
	 * Event id
	 *
	 * @var int
	 */
	var $_id = null;

	/**
	 * Event data array
	 *
	 * @var array
	 */
	var $_data = null;

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
		$this->set('group_id', $group_id);
		
		$array = JRequest::getVar('cid',  0, '', 'array');		
		$this->setId((int)$array[0]);
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

	function getGroup()
	{
		if (empty($this->_group))
		{
			$query = ' SELECT id, name '
			       . ' FROM #__redevent_groups '
			       . ' WHERE id = '. $this->_db->Quote($this->get('group_id'))
			            ;
			$this->_db->setQuery($query);
			$this->_group = $this->_db->loadObject();
		}
		return $this->_group;
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
	 * Method to load content data
	 *
	 * @access	private
	 * @return	boolean	True on success
	 * @since	0.9
	 */
	function _loadData()
	{
		//Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$query = 'SELECT *'
					. ' FROM #__redevent_groupmembers'
					. ' WHERE id = '.$this->_id
					;
			$this->_db->setQuery($query);
			$this->_data = $this->_db->loadObject();
			
			return (boolean) $this->_data;
		}
		return true;
	}

	/**
	 * Method to initialise the group data
	 *
	 * @access	private
	 * @return	boolean	True on success
	 * @since	0.9
	 */
	function _initData()
	{
		//Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$group = new stdClass();
			$group->id					= 0;
			$group->group_id		= null;
			$group->member			= null;
			$group->is_admin		= 0;
			$group->manage_events	= 0;
			$group->manage_xrefs		= 0;
			$group->edit_venues	= 0;
			$group->receive_registrations	= 0;
			$this->_data				= $group;
			return (boolean) $this->_data;
		}
		return true;
	}

	/**
	 * Method to checkin/unlock the item
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	0.9
	 */
	function checkin()
	{
		if ($this->_id)
		{
			$group = & JTable::getInstance('redevent_groupmembers', '');
			return $group->checkin($this->_id);
		}
		return false;
	}


	
	/**
	 * Method to checkout/lock the item
	 *
	 * @access	public
	 * @param	int	$uid	User ID of the user checking the item out
	 * @return	boolean	True on success
	 * @since	0.9
	 */
	function checkout($uid = null)
	{
		if ($this->_id)
		{
			// Make sure we have a user id to checkout the group with
			if (is_null($uid)) {
				$user	=& JFactory::getUser();
				$uid	= $user->get('id');
			}
			// Lets get to it and checkout the thing...
			$obj = & JTable::getInstance('redevent_groupmembers', '');
//			print_r($obj);exit;
//			$test = $obj->checkout($uid, $this->_id);
			return $obj->checkout($uid, $this->_id);
		}
		return false;
	}

	/**
	 * Tests if the event is checked out
	 *
	 * @access	public
	 * @param	int	A user id
	 * @return	boolean	True if checked out
	 * @since	0.9
	 */
	function isCheckedOut( $uid=0 )
	{
		if ($this->_loadData())
		{
			if ($uid) {
				return ($this->_data->checked_out && $this->_data->checked_out != $uid);
			} else {
				return $this->_data->checked_out;
			}
		} elseif ($this->_id < 1) {
			return false;
		} else {
			RedeventError::raiseWarning( 0, 'Unable to Load Data');
			return false;
		}
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
		$row =& JTable::getInstance('redevent_groupmembers', '');

		//Bind the form fields to the table
		if (!$row->bind($data)) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		// Make sure the data is valid
		if (!$row->check()) {
			$this->setError($row->getError());
			return false;
		}

		//Store the table to the database
		if (!$row->store()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		return $row->id;
	}
}
?>