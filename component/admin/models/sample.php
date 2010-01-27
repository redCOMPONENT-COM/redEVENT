<?php
/**
 * @version 1.0 $Id: redevent.php 30 2009-05-08 10:22:21Z roland $
 * @package Joomla
 * @subpackage EventList
 * @copyright (C) 2005 - 2008 Christoph Lukes
 * @license GNU/GPL, see LICENSE.php
 * EventList is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * EventList is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with EventList; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

/**
 * EventList Component Home Model
 *
 * @package Joomla
 * @subpackage EventList
 * @since		0.9
 */
class RedEventModelSample extends JModel
{
	/**
	 * creates sample data for redevent
	 * @return bool
	 */
	function create()
	{
		$category = $this->_createCategory();
		$venue    = $this->_createVenue();
		$event    = $this->_createEvent($category);
		$xref     = $this->_createXref($event, $venue);
		return true;
	}
	
	/**
	 * creates a sample category
	 * 
	 * @return category id
	 */
	function _createCategory()
	{
		$row = &JTable::getInstance('redevent_categories', '');
		$row->catname        = 'Category S1';
		$row->catdescription = 'Sample category';
		$row->color          = '#00DD00';
		$row->published      = 1;
		
		if ($row->check() && $row->store()) {
			return $row->id;
		}
		else {
			$this->setError(JText::_('Error creating sample category'));
			return false;
		}
	}
	
	/**
	 * creates a sample venue
	 * 
	 * @return venue id
	 */
	function _createVenue()
	{
		$row = &JTable::getInstance('redevent_venues', '');
		$row->venue        = 'Venue S1';
		$row->locdescription = 'Sample venue';
		$row->published      = 1;
		
		if ($row->check() && $row->store()) {
			return $row->id;
		}
		else {
			$this->setError(JText::_('Error creating sample venue'));
			return false;
		}
	}
	
	/**
	 * creates a sample event
	 * @param int $category 
	 * @return unknown_type
	 */
	function _createEvent($category)
	{
		$row = &JTable::getInstance('redevent_events', '');
		$row->title          = 'Event S1';
		$row->datdescription = '<b>Sample event</b><br/><br/>[venues]';
		$row->published      = 1;
		$row->redform_id     = 1;
		
		if ($row->check() && $row->store()) 
		{
		  $query = ' INSERT INTO #__redevent_event_category_xref (event_id, category_id) VALUES (' . $this->_db->Quote($row->id) . ', '. $this->_db->Quote($category) . ')';
		  $this->_db->setQuery($query);
	    if (!$this->_db->query()) {
	      $this->setError($this->_db->getErrorMsg());
	      return false;     
	    }		  
			return $row->id;
		}
		else {
			$this->setError(JText::_('Error creating sample event'));
			return false;
		}
	}
	
	/**
	 * creates a sample event
	 * 
	 * @return xref id
	 */
	function _createXref($event, $venue)
	{
		$row = &JTable::getInstance('redevent_eventvenuexref', '');
		$row->eventid        = $event;
		$row->venueid        = $venue;
		$row->details        = 'Sample date';
		$row->dates          = strftime('%Y-%m-%d', strtotime('+3 days'));
		$row->times          = '14:00';
		$row->enddates       = strftime('%Y-%m-%d', strtotime('+4 days'));
		$row->endtimes       = '15:00';
		$row->published      = 1;
		
		if ($row->check() && $row->store()) 
		{
			return $row->id;
		}
		else {
			$this->setError(JText::_('Error creating sample event date'));
			return false;
		}
	}

}