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
		$category = $this->_getCategory();
		$venue    = $this->_getVenue();
		$event    = $this->_createEvent($category);
		$xref     = $this->_createXref($event, $venue);
		return true;
	}
	
	/**
	 * return a category id
	 * @return int
	 */
	function _getCategory()
	{
		$query = ' SELECT id '
		       . ' FROM #__redevent_categories '
		       . ' WHERE published = 1 '
		       ;
		$this->_db->setQuery($query, 0, 1);
		$res = $this->_db->loadResult();
		
		if (!$res) {
			return $this->_createCategory();
		}
		else {
			return $res;
		}
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
			$this->setError(JText::_('COM_REDEVENT_Error_creating_sample_category'));
			return false;
		}
	}

	
	/**
	 * return a venue id
	 * @return int
	 */
	function _getvenue()
	{
		$query = ' SELECT id '
		       . ' FROM #__redevent_venues '
		       . ' WHERE published = 1 '
		       ;
		$this->_db->setQuery($query, 0, 1);
		$res = $this->_db->loadResult();
		
		if (!$res) {
			return $this->_createVenue();
		}
		else {
			return $res;
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
			$this->setError(JText::_('COM_REDEVENT_Error_creating_sample_venue'));
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
		$event = &JTable::getInstance('redevent_events', '');
		$event->title          = JText::_('COM_REDEVENT_SAMPLE_EVENT_TITLE');
		$event->datdescription = JText::_('COM_REDEVENT_SAMPLE_EVENT_DESCRIPTION');
		$event->published      = 1;
		$event->redform_id     = 1;
		
		$event->registra       = 1;
		$event->unregistra     = 0;
		$event->juser          = 0;
		
		$event->notify_on_list_subject    = JText::_('COM_REDEVENT_SAMPLE_EVENT_NOTIFY_ON_LIST_SUBJECT');
		$event->notify_on_list_body       = JText::_('COM_REDEVENT_SAMPLE_EVENT_NOTIFY_ON_LIST_BODY');
		$event->notify_off_list_subject   = JText::_('COM_REDEVENT_SAMPLE_EVENT_NOTIFY_OFF_LIST_SUBJECT');
		$event->notify_off_list_body      = JText::_('COM_REDEVENT_SAMPLE_EVENT_NOTIFY_OFF_LIST_BODY');
		
		$event->notify                 = 1;
		$event->activate               = 0;
		$event->notify_subject         = JText::_('COM_REDEVENT_SAMPLE_EVENT_NOTIFY_SUBJECT');
		$event->notify_body            = JText::_('COM_REDEVENT_SAMPLE_EVENT_NOTIFY_BODY');
		$event->notify_confirm_subject = JText::_('COM_REDEVENT_SAMPLE_EVENT_NOTIFY_CONFIRM_SUBJECT');
		$event->notify_confirm_body    = JText::_('COM_REDEVENT_SAMPLE_EVENT_NOTIFY_CONFIRM_BODY');
		
		$event->review_message       = JText::_('COM_REDEVENT_SAMPLE_EVENT_REVIEW_MESSAGE');
		$event->confirmation_message = JText::_('COM_REDEVENT_SAMPLE_EVENT_CONFIRMATION_MESSAGE');
		
		$event->show_names           = 0;
		$event->showfields           = '';
		
		$event->submission_types         = 'webform';
		$event->submission_type_email    = null;
		$event->submission_type_external = null;
		$event->submission_type_phone    = null;
		$event->max_multi_signup			   = 1;
		$event->submission_type_formal_offer = null;
		$event->submission_type_formal_offer_subject = null;
		$event->submission_type_formal_offer_body    = null;
		$event->submission_type_email_body           = null;
		$event->submission_type_email_pdf            = null;
		$event->submission_type_formal_offer_pdf     = null;
		$event->submission_type_webform              = JText::_('COM_REDEVENT_SAMPLE_EVENT_WEBFORM');
		$event->submission_type_email_subject        = null;
		$event->submission_type_webform_formal_offer = null;
// 		$event->show_submission_type_webform_formal_offer = 0;
		
		$event->send_pdf_form = 0;
		$event->pdf_form_data = 0;
		
		$event->paymentaccepted   = JText::_('COM_REDEVENT_SAMPLE_EVENT_PAYMENTACCEPTED');
		$event->paymentprocessing = JText::_('COM_REDEVENT_SAMPLE_EVENT_PAYMENTPROCESSING');
		
		if ($event->check() && $event->store()) 
		{
		  $query = ' INSERT INTO #__redevent_event_category_xref (event_id, category_id) VALUES (' . $this->_db->Quote($event->id) . ', '. $this->_db->Quote($category) . ')';
		  $this->_db->setQuery($query);
	    if (!$this->_db->query()) {
	      $this->setError($this->_db->getErrorMsg());
	      return false;     
	    }		  
			return $event->id;
		}
		else {
			$this->setError(JText::_('COM_REDEVENT_Error_creating_sample_event'));
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
			$this->setError(JText::_('COM_REDEVENT_Error_creating_sample_event_session'));
			return false;
		}
	}

}