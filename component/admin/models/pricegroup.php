<?php
/**
 * @version 1.0 $Id: cleanup.php 298 2009-06-24 07:42:35Z julien $
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


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

/**
 * Joomla Redevent Component Model
 *
 * @author Julien Vonthron <julien.vonthron@gmail.com>
 * @package   Redevent
 * @since 0.1
 */
class RedeventModelPricegroup extends JModel
{
  /**
   * item id
   *
   * @var int
   */
  var $_id = null;

  /**
   * Project data
   *
   * @var array
   */
  var $_data = null;

  /**
   * Constructor
   *
   * @since 0.1
   */
  function __construct()
  {
    parent::__construct();

    $array = JRequest::getVar('cid', array(0), '', 'array');
    $edit = JRequest::getVar('edit',true);
    if($edit)
      $this->setId((int)$array[0]);
  }

  /**
   * Method to set the item identifier
   *
   * @access  public
   * @param int item identifier
   */
  function setId($id)
  {
    // Set item id and wipe data
    $this->_id    = $id;
    $this->_data  = null;
  }
  


  /**
   * Method to get an item
   *
   * @since 0.1
   */
  function &getData()
  {
    // Load the item data
    if (!$this->_loadData()) $this->_initData();

    return $this->_data;
  }
  
	/**
	 * Method to remove an item
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	0.1
	 */
	function delete($cid = array())
	{
		$result = false;

		if (count( $cid ))
		{
			JArrayHelper::toInteger($cid);
			
			$query = ' SELECT r.id ' 
			       . ' FROM #__redevent_sessions_pricegroups AS r ' 
			       . ' WHERE r.pricegroup_id IN (' . implode(',', $cid).')';
			$this->_db->setQuery($query);
			$res = $this->_db->loadResult();
			
			if ($res) {
				$this->setError(0, 'COM_REDEVENT_PRICEGROUPS_DELETE_ERROR_PRICEGROUP_ASSIGNED');
				return false;
			}
				
			JArrayHelper::toInteger($cid);
			$cids = implode( ',', $cid );
			$query = 'DELETE FROM #__redevent_pricegroups'
				. ' WHERE id IN ( '.$cids.' )';
			
			$this->_db->setQuery( $query );
			if(!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}

		return true;
	}

	/**
	 * Method to load content competition data
	 *
	 * @access	private
	 * @return	boolean	True on success
	 * @since	0.1
	 */
	function _loadData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$query = ' SELECT r.* '.
			         ' FROM #__redevent_pricegroups AS r ' .
			         ' WHERE r.id = '.(int) $this->_id;
			$this->_db->setQuery($query);
			$this->_data = $this->_db->loadObject();
			return (boolean) $this->_data;
		}
		return true;
	}

	/**
	 * Method to initialise the competition data
	 *
	 * @access	private
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function _initData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$object = new stdClass();
			$object->id          = 0;
			$object->name        = null;
			$object->alias       = null;
			$object->checked_out = 0;
			$object->checked_out_time	= 0;
			$object->ordering    = 0;
			$this->_data         =  $object;
			return (boolean) $this->_data;
		}
		return true;
	}
	

  /**
   * Method to checkin/unlock the item
   *
   * @access  public
   * @return  boolean True on success
   * @since 0.9
   */
  function checkin()
  {
    if ($this->_id)
    {
      $group = & JTable::getInstance('Redevent_pricegroups', '');
      return $group->checkin($this->_id);
    }
    return false;
  }


  
  /**
   * Method to checkout/lock the item
   *
   * @access  public
   * @param int $uid  User ID of the user checking the item out
   * @return  boolean True on success
   * @since 0.9
   */
  function checkout($uid = null)
  {
    if ($this->_id)
    {
      // Make sure we have a user id to checkout the group with
      if (is_null($uid)) {
        $user =& JFactory::getUser();
        $uid  = $user->get('id');
      }
      // Lets get to it and checkout the thing...
      $group = & JTable::getInstance('Redevent_pricegroups', '');
      return $group->checkout($uid, $this->_id);
    }
    return false;
  }

  /**
   * Tests if the event is checked out
   *
   * @access  public
   * @param int A user id
   * @return  boolean True if checked out
   * @since 0.9
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
   * Method to store the item
   *
   * @access  public
   * @return  false|int id on success
   * @since 1.5
   */
  function store($data)
  {
    $row =& $this->getTable('Redevent_pricegroups','');
    
    // Bind the form fields to the items table
    if (!$row->bind($data)) {
      $this->setError($this->_db->getErrorMsg());
      return false;
    }

    // Create the timestamp for the date
    $row->checked_out_time = gmdate('Y-m-d H:i:s');

    // if new item, order last
    if (!$row->id) {
      $row->ordering = $row->getNextOrder(  );
    }

    // Make sure the item is valid
    if (!$row->check()) {
      $this->setError($row->getError());
      return false;
    }

    // Store the item to the database
    if (!$row->store()) {
      $this->setError($this->_db->getErrorMsg());
      return false;
    }
    
    return $row->id;
  }

  /**
   * Method to move an item
   *
   * @access  public
   * @return  boolean True on success
   * @since 1.5
   */
  function move($direction)
  {
    $row =& $this->getTable('Redevent_pricegroups','');
    if (!$row->load($this->_id)) {
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
   * Method to save item order
   *
   * @access  public
   * @return  boolean True on success
   * @since 1.5
   */
  function saveorder($cid = array(), $order)
  {
    $row =& $this->getTable('Redevent_pricegroups','');

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
}
?>
