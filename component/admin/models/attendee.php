<?php
/**
 * @version 1.0 $Id: group.php 298 2009-06-24 07:42:35Z julien $
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
 * redEvent Component attendee Model
 *
 * @package Joomla
 * @subpackage redEvent
 * @since		2.0
 */
class RedEventModelAttendee extends JModel
{
	/**
	 * Booking id
	 *
	 * @var int
	 */
	var $_id = null;

	/**
	 * xref
	 * @var int
	 */
	var $_xref = null;
	/**
	 * Booking data array
	 *
	 * @var array
	 */
	var $_data = null;

	/**
	 * Constructor
	 *
	 * @since 0.9
	 */
	function __construct()
	{
		parent::__construct();

		$cid = JRequest::getVar( 'cid', array(0), '', 'array' );
		JArrayHelper::toInteger($cid, array(0));
		$this->setId($cid[0]);

		$xref = JRequest::getVar( 'xref', 0, '', 'int' );
		$this->setXref($xref);
	}

	/**
	 * Method to set the identifier
	 *
	 * @access	public
	 * @param	int ac identifier
	 */
	function setId($id)
	{
		// Set ac id and wipe data
		$this->_id	    = $id;
		$this->_data	= null;
	}

	/**
	 * Method to set the identifier
	 *
	 * @access	public
	 * @param	int ac identifier
	 */
	function setXref($xref)
	{
		// Set ac id
		$this->_xref	= intval($xref);
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

	function _initData()
	{
		$obj = & JTable::getInstance('redevent_register', '');
		
	  // get form id and answer id
		$query = ' SELECT a.redform_id as form_id, a.course_code, x.id as xref '
		       . ' FROM #__redevent_event_venue_xref AS x '
		       . ' INNER JOIN #__redevent_events AS a ON a.id =  x.eventid '
		       . ' WHERE x.id = '.$this->_xref
				;
		$this->_db->setQuery($query);
		$ac = $this->_db->loadObject();
		$obj->form_id      = $ac->form_id;
		$obj->course_code  = $ac->course_code;
		$obj->xref         = $this->_xref;
		$obj->answers = null;

		$this->_data = $obj;
    return true;
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
		if (!$this->_id) {
			return false;
		}
		if (empty($this->_data))
		{
		  // get form id and answer id
			$query = ' SELECT r.*, s.form_id, a.course_code, sp.price, sp.pricegroup_id '
			       . ' FROM #__redevent_register AS r '
			       . ' INNER JOIN #__rwf_submitters AS s ON s.id =  r.sid '
			       . ' INNER JOIN #__redevent_event_venue_xref AS x ON x.id =  r.xref '
			       . ' INNER JOIN #__redevent_events AS a ON a.id =  x.eventid '
			       . ' LEFT JOIN #__redevent_sessions_pricegroups AS sp ON sp.xref =  x.id AND sp.pricegroup_id = r.pricegroup_id'
			       . ' WHERE r.id = '.$this->_id
					;
			$this->_db->setQuery($query);
			$this->_data = $this->_db->loadObject();
			
			if (!$this->_data) {
			  echo $this->_db->getErrorMsg();
			}
			
			return (boolean) $this->_data;
			
		}
		return true;
	}

	/**
	 * Tests if the row is checked out
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
			RedEventError::raiseWarning( 0, 'Unable to Load Data');
			return false;
		}
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
			$ac = & JTable::getInstance('redevent_register', '');
			return $ac->checkin($this->_id);
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
			// Make sure we have a user id to checkout the ac with
			if (is_null($uid)) {
				$user	=& JFactory::getUser();
				$uid	= $user->get('id');
			}
			// Lets get to it and checkout the thing...
			$ac = & JTable::getInstance('redevent_register', '');
			return $ac->checkout($uid, $this->_id);
		}
		return false;
	}

	/**
	 * Method to store the attendee
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function store($data)
	{	
		$xref = intval($data['xref']);
		$pricegroup = intval($data['pricegroup_id']);
		$id = JRequest::getInt('id');
		
	  // get price
		$query = ' SELECT pg.price, a.activate '
		       . ' FROM #__redevent_event_venue_xref AS x '
		       . ' INNER JOIN #__redevent_events AS a ON a.id =  x.eventid '
		       . ' LEFT JOIN #__redevent_sessions_pricegroups AS pg ON pg.xref =  x.id AND pg.pricegroup_id = '.$pricegroup
		       . ' WHERE x.id = '.$xref
				   ;
		$this->_db->setQuery($query);
		$details = $this->_db->loadObject();
				
		// first save redform data	
		$rfcore = new redFormCore();
		$result = $rfcore->saveAnswers('redevent', array('baseprice' => $details->price, 'edit' => 1));	
  	if (!$result) {
  		$msg = JText::_('COM_REDEVENT_REGISTRATION_REDFORM_SAVE_FAILED');
  		$this->setError($msg.' - '.$rfcore->getError());
  		return false;
  	}
  	// adding to data for register saving
  	$data['submit_key'] = $result->submit_key;
  	$data['sid'] = $result->posts[0]['sid'];
  	
		if ($details->activate == 0) // no activation 
		{
			$data['confirmed'] = 1;
			$data['confirmdate'] = gmdate('Y-m-d H:i:s');
			$data['paymentstart'] = gmdate('Y-m-d H:i:s');
		}
  	
		$row =& JTable::getInstance('redevent_register', '');
		if ($id) {
			$row->load($id);
		}
				
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
	
	function getPricegroups()
	{
		$query = ' SELECT sp.*, p.name ' 
		       . ' FROM #__redevent_sessions_pricegroups AS sp '
		       . ' INNER JOIN #__redevent_pricegroups AS p ON p.id = sp.pricegroup_id ' 
		       . ' WHERE sp.xref = ' . $this->_db->Quote($this->_xref)
		       . ' ORDER BY p.ordering '
		       ;
		$this->_db->setQuery($query);
		$res = $this->_db->loadObjectList();
		return $res;
	}
}
?>