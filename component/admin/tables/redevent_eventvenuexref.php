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
 * @subpackage redEVENT
 * @since 0.9
 */
class RedEvent_eventvenuexref extends JTable
{
	/**
	 * Primary Key
	 * @var int
	 */
	var $id 		= null;
  /** @var string */
  var $title    = null;
  /** @var string */
  var $alias    = null;
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
  /** @var string */
  var $icaldetails    = '';
  /** @var string override venue name in ical view */
  var $icalvenue    = '';
  /** @var int */
  var $maxattendees    = 0;
  /** @var int */
  var $maxwaitinglist    = 0;
  /** @var int */
  var $course_credit    = null;
  /** @var int */
  var $featured    = 0;
  /**
   * Url for external registration. 
   * Overrides event external registration
   * 
   * @var string
   */
  var $external_registration_url = null;
  /** @var int */
  var $published = 0;
	

	function RedEvent_eventvenuexref(& $db) {
		parent::__construct('#__redevent_event_venue_xref', 'id', $db);
	}
	
	function check()
	{
		if (!$this->eventid) {
			$this->setError(JText::_('COM_REDEVENT_SESSION_EVENTID_IS_REQUIRED'));
			return false;
		}
		// allow credit to be null
		if ($this->course_credit === '') {
			$this->course_credit = null;
		}
		if ($this->times === '') {
			$this->times = null;
		}
		if ($this->endtimes === '') {
			$this->endtimes = null;
		}
				
		$alias = JFilterOutput::stringURLSafe($this->title);

		if (empty($this->alias) && $alias) {
			$this->alias = $alias;
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
			$this->setError(JText::_('COM_REDEVENT_EVENT_DATE_HAS_ATTENDEES'));	
			return false;		
		}
		
		return true;
	}

	/**
	 * override for custom fields
	 */
	function bind( $from, $ignore=array() )
	{
		$fromArray	= is_array( $from );
		$fromObject	= is_object( $from );

		if (!$fromArray && !$fromObject)
		{
			$this->setError( get_class( $this ).'::bind failed. Invalid from argument' );
			return false;
		}
		if (!is_array( $ignore )) {
			$ignore = explode( ' ', $ignore );
		}
		foreach ($this->getProperties() as $k => $v)
		{
			// internal attributes of an object are ignored
			if (!in_array( $k, $ignore ))
			{
				if ($fromArray && isset( $from[$k] )) {
					$this->$k = $from[$k];
				} else if ($fromObject && isset( $from->$k )) {
					$this->$k = $from->$k;
				}
			}
		}
		$customs = $this->_getCustomFieldsColumns();
		foreach ($customs as $c)
		{
			if ($fromArray && isset( $from[$c] )) 
			{				
				$this->$c = is_array($from[$c]) ? implode("\n", $from[$c]) : $from[$c];
			} else if ($fromObject && isset( $from->$c )) {
				$this->$c = is_array($from->$c) ? implode("\n", $from->$c) : $from->$c;
			}
			else {
				$this->$c = '';
			}
		}
		return true;
	}
	
	function _getCustomFieldsColumns()
	{
		$query = ' SELECT CONCAT("custom", id) ' 
		       . ' FROM #__redevent_fields ' 
		       . ' WHERE object_key = ' . $this->_db->Quote('redevent.xref');
		$this->_db->setQuery($query);
		$res = $this->_db->loadResultArray();
		return $res;
	}
	
	function setPrices($prices = array())
	{	
    // first remove current rows
    $query = ' DELETE FROM #__redevent_sessions_pricegroups ' 
           . ' WHERE xref = ' . $this->_db->Quote($this->id);
    $this->_db->setQuery($query);     
    if (!$this->_db->query()) {
    	$this->setError($this->_db->getErrorMsg());
    	return false;
    }
    
    // then recreate them if any
    foreach ((array) $prices as $k => $price)
    {    	
    	if (!isset($price->pricegroup_id) || !isset($price->price)) {
    		continue;
    	}
      $new = & JTable::getInstance('RedEvent_sessions_pricegroups', '');
      $new->set('xref',    $this->id);
      $new->set('pricegroup_id', $price->pricegroup_id);
      $new->set('price', $price->price);
      if (!($new->check() && $new->store())) {
      	$this->setError($new->getError());
      	return false;
      }
    }
	}
}
