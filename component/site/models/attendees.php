<?php
/**
 * @version 1.0 $Id: eventlist.php 1027 2009-09-27 21:50:56Z julien $
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
// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

/**
 * redEVENT Component Attendees Model
 *
 * @package Joomla
 * @subpackage redEVENT
 * @since		2.0
 */
class RedEventModelAttendees extends JModel
{
	
	protected $_xref = 0;
	
	protected $_session = null;
	
	/**
	 * Constructor
	 *
	 * @since 0.9
	 */
	function __construct()
	{
		parent::__construct();
		
		$mainframe = & JFactory::getApplication();
		
		$xref = JRequest::getInt('xref');
		$this->setXref((int)$xref);
		
		$filter_order     = $mainframe->getUserStateFromRequest( 'com_redevent.attendees.filter_order', 'filter_order', 'default_column_name', 'cmd' );
		$filter_order_Dir = $mainframe->getUserStateFromRequest( 'com_redevent.attendees.filter_order_Dir', 'filter_order_Dir', 'asc', 'word' );

		$this->setState('filter_order', $filter_order);
		$this->setState('filter_order_Dir', $filter_order_Dir);
		
	}
	
	/**
	 * Method to set the details id
	 *
	 * @access	public
	 * @param	int	details ID number
	 */
	function setXref($xref)
	{
		// Set new details ID and wipe data
		$this->_xref    = intval($xref);
		$this->_session = null;
	}
	
	function getReminderEvents($days = 14)
	{
		$app = &JFactory::getApplication();
		$params = $app->getParams('com_redevent');
		
		$query = ' SELECT x.id, e.title '
		       . ' FROM #__redevent_events AS e '
		       . ' INNER JOIN #__redevent_event_venue_xref AS x ON x.eventid = e.id '
		       . ' WHERE DATEDIFF(x.dates, NOW()) = '.$days
//		       . '   AND (e.reminder = 2'.($params->get('reminder_default', 1) == 1 ? ' OR e.reminder = 0 ' : '' ).') '
		       ;
		$this->_db->setQuery($query);
		$res = $this->_db->loadObjectList();
		return $res;			
	}
	
	/**
	 * return array of attendees emails indexed by sid
	 * 
	 * @param int $xref
	 * @return array
	 */
	function getAttendeesEmails($xref)
	{		
		$query = ' SELECT r.sid '
		       . ' FROM #__redevent_register AS r '
		       . ' WHERE r.xref = '.$xref
		       . '   AND r.confirmed = 1 '
		       ;
		$this->_db->setQuery($query);
		$res = $this->_db->loadResultArray();
		
		if (!count($res)) {
			return false;
		}
		
		$emails = array();
		$rfcore = new RedFormCore();
		$answers = $rfcore->getSidsFieldsAnswers($res);
		foreach ($answers as $sid => $a)
		{
			foreach ($a as $field)
			{
				if ($field->fieldtype == 'email')
				{
					$emails[$sid] = $field->answer;
					break;
				}
			}
		}
		return $emails;
	}
	
	function getSession()
	{
		if (empty($this->_session)) 
		{
			$query = ' SELECT e.title, e.registra, e.showfields, e.redform_id, e.id as eventid, e.course_code, e.show_names, '
			       . '      x.dates, x.enddates, x.times, x.endtimes, x.id as xref, '
			       . '      v.venue, '
			       . ' CASE WHEN CHAR_LENGTH(e.alias) THEN CONCAT_WS(\':\', x.id, e.alias) ELSE x.id END as slug '		
			       . ' FROM #__redevent_event_venue_xref AS x ' 
			       . ' INNER JOIN #__redevent_events AS e ON x.eventid = e.id '
			       . ' INNER JOIN #__redevent_venues AS v ON x.venueid = v.id '
			       . ' WHERE x.id = ' . $this->_db->Quote($this->_xref)
			       ;
			$this->_db->setQuery($query, 0, 1);
			$this->_session = $this->_db->loadObject();
		}
		return $this->_session;
	}
	
	
	/**
	 * Method to get the registered users
	 *
	 * @access	public
	 * @return	object
	 * @since	2.0
	 * @todo Complete CB integration
	 */
	function getRegisters($all_fields = false, $admin = false) 
	{
		// make sure the init is done
		$session = $this->getSession();
		
	  if (!$session->registra && !$admin) {
	    return null;
	  }
	  
		$db = JFactory::getDBO();

		// first, get all submissions			
		$query = ' SELECT r.*, r.waitinglist, r.confirmed, r.confirmdate, r.submit_key '
						. ' FROM #__redevent_register AS r '
						. ' LEFT JOIN #__users AS u ON r.uid = u.id '
						. ' WHERE r.xref = ' . $this->_xref
            . ' AND r.confirmed = 1'
						;
		$db->setQuery($query);
		$submitters = $db->loadObjectList('submit_key');
		
		if ($submitters === null)
		{
			$msg = JText::_('ERROR GETTING ATTENDEES');
			$this->setError($msg);
			RedeventError::raiseWarning(5, $msg);
			return null;
		}
		else if (empty($submitters)) {
			// no submitters
			return null;
		}
		
		/* At least 1 redFORM field must be selected to show the user data from */
		if ((!empty($session->showfields) || $admin) && $session->redform_id > 0) 
		{
			$fields = $this->getFormFields($all_fields);
			
			if (!$fields) 
			{
				RedeventError::raiseWarning('error', JText::_('Cannot load fields').$db->getErrorMsg());
				return null;
			}			
			
			if (count($fields)) 
			{
				$table_fields = array();
				$fields_names = array();
				foreach ($fields as $key => $field) {
					$table_fields[] = 'a.field_'. $field->id;
					$fields_names['field_'. $field->id] = $field->field;
				}
				
				$query  = ' SELECT ' . implode(', ', $table_fields)
				        . ' , s.submit_key, s.id '
				        . ' FROM #__redevent_register AS r '
				        . ' INNER JOIN #__rwf_submitters AS s ON r.sid = s.id '
				        . ' INNER JOIN #__rwf_forms_' . $fields[0]->form_id . ' AS a ON s.answer_id = a.id '
				        . ' WHERE r.xref = ' . $this->_xref
				        . ' AND r.confirmed = 1'
				        ;
        $filter_order     = $this->getState('filter_order');
        $filter_order_Dir = $this->getState('filter_order_Dir');
        	
        if(!empty($filter_order) && !empty($filter_order_Dir) ){
        	$query .= ' ORDER BY '.$filter_order.' '.$filter_order_Dir;
        }
        else {
        	$query .= ' ORDER BY r.id ASC';
        }				        
				        
				$db->setQuery($query);
				if (!$db->query()) {
					RedeventError::raiseWarning('error', JText::_('Cannot load registered users').' '.$db->getErrorMsg());
					return null;
				}			
				$answers = $db->loadObjectList();
			}
			else {
				$answers = array();
			}
			
		  // add the answers to submitters list
		  $registers = array();
      foreach ($answers as $answer) 
      {
        if (!isset($submitters[$answer->submit_key])) 
        {
        	$msg = JText::_('ERROR REGISTRATION WITHOUT SUBMITTER') . ': ' . $answer->id;
        	$this->setError($msg);
        	RedeventError::raiseWarning(10, $msg);
        	return null;
        }
        // build the object
        $register = new stdclass();
        $register->id = $answer->id;
        $register->attendee_id = $submitters[$answer->submit_key]->id;
        $register->submitter = $submitters[$answer->submit_key];
        $register->answers = $answer;
        $register->fields = $fields_names;
        unset($register->answers->id); // just the fields
        unset($register->answers->submit_key); // just the fields
        $registers[] = $register;
      }
      return $registers;
		}
		return null;
	}
	
	/**
	 * returns the fields to be shown in attendees list
	 * 
	 * @param boolean get all fields
	 * @return array;
	 */
	function getFormFields($all_fields = false)
	{
		// make sure the init is done
		$session = $this->getSession();
		
		if (empty($session->showfields)) {
			return false;
		}
		// load form fields
		$q = ' SELECT id, field, form_id '
			 . ' FROM #__rwf_fields j '
			 . ' WHERE form_id = '. $this->_db->Quote($session->redform_id)
			 . ($all_fields ? '' : '   AND j.id in ('.$session->showfields. ')')
			 . '   AND j.published = 1 '
			 . ' ORDER BY ordering ';
		$this->_db->setQuery($q);
		
		return $this->_db->loadObjectList();
	}	
	
	/**
	 * return true if user allowed to manage attendees
	 * 
	 * @return boolean
	 */
  function getManageAttendees()
  {
  	$acl = UserAcl::getInstance();
  	return $acl->canManageAttendees($this->_xref);
  }
	
	/**
	 * return true if user allowed to manage attendees
	 * 
	 * @return boolean
	 */
  function getViewAttendees()
  {
  	$acl = UserAcl::getInstance();
  	return $acl->canViewAttendees($this->_xref);
  }
}