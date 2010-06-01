<?php
/**
 * @version 1.0 $Id: redevent_register.php 30 2009-05-08 10:22:21Z roland $
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
 * EventList registration Model class
 *
 * @package Joomla
 * @subpackage EventList
 * @since 0.9
 */
class RedEvent_eventvenuexref extends JTable
{
	/**
	 * Primary Key
	 * @var int
	 */
	var $id 		= null;
	/** @var int */
	var $eventid 		= null;
  /** @var int */
  var $venueid    = null;
  /** @var int */
  var $groupid    = null;
  /** @var string */
  var $dates    = null;
  /** @var string */
  var $enddates    = null;
  /** @var string */
  var $times    = null;
  /** @var string */
  var $endtimes    = null;
  /** @var string */
  var $registrationend  = null;
  /** @var string */
  var $note    = null;
  /** @var string */
  var $details    = '';
  /** @var int */
  var $maxattendees    = 0;
  /** @var int */
  var $maxwaitinglist    = 0;
  /** @var int */
  var $course_credit    = null;
  /** @var int */
  var $course_price    = null;
  /** @var int */
  var $published = 0;
	

	function RedEvent_eventvenuexref(& $db) {
		parent::__construct('#__redevent_event_venue_xref', 'id', $db);
	}
	
	function check()
	{
		// allow price to be null
		if (empty($this->course_price)) {
			$this->course_price = null;
		}
		if ($this->course_credit == '') {
			$this->course_price = null;
		}
		return true;
	}
	
	/**
	 * Generic Publish/Unpublish function
	 *
	 * @access public
	 * @param array An array of id numbers
	 * @param integer 0 if unpublishing, 1 if publishing
	 * @param integer The id of the user performnig the operation
	 * @since 1.0.4
	 */
	function publish( $cid=null, $publish=1, $user_id=0 )
	{
		JArrayHelper::toInteger( $cid );
		$user_id	= (int) $user_id;
		$publish	= (int) $publish;
		$k			= $this->_tbl_key;

		if (count( $cid ) < 1)
		{
			if ($this->$k) {
				$cid = array( $this->$k );
			} else {
				$this->setError("No items selected.");
				return false;
			}
		}
		$cids = $k . '=' . implode( ' OR ' . $k . '=', $cid );

		$query = 'UPDATE '. $this->_tbl
		. ' SET published = ' . (int) $publish
		. ' WHERE ('.$cids.')'
		;

		$checkin = in_array( 'checked_out', array_keys($this->getProperties()) );
		if ($checkin)
		{
			$query .= ' AND (checked_out = 0 OR checked_out = '.(int) $user_id.')';
		}

		$this->_db->setQuery( $query );
		if (!$this->_db->query())
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		if (count( $cid ) == 1 && $checkin)
		{
			if ($this->_db->getAffectedRows() == 1) {
				$this->checkin( $cid[0] );
				if ($this->$k == $cid[0]) {
					$this->published = $publish;
				}
			}
		}
		$this->setError('');
		return true;
	}
	

	/**
	 * Default delete method
	 *
	 * can be overloaded/supplemented by the child class
	 *
	 * @access public
	 * @return true if successful otherwise returns and error message
	 */
	function delete( $oid=null )
	{
		if (!$this->canDelete( $oid ))
		{
			return false;
		}

		$k = $this->_tbl_key;
		if ($oid) {
			$this->$k = intval( $oid );
		}

		$query = 'DELETE FROM '.$this->_db->nameQuote( $this->_tbl ).
				' WHERE '.$this->_tbl_key.' = '. $this->_db->Quote($this->$k);
		$this->_db->setQuery( $query );

		if ($this->_db->query())
		{
			return true;
		}
		else
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
	}
	
	function canDelete($id)
	{
		// can't delete if there are attendees
		$query = ' SELECT COUNT(*) FROM #__redevent_register WHERE xref = '. intval( $id );
		$this->_db->setQuery($query);
		$res = $this->_db->loadResult();
		
		if ($res) {
			$this->setError(JText::_('EVENT DATE HAS ATTENDEES'));	
			return false;		
		}
		
		return true;
	}
}
?>