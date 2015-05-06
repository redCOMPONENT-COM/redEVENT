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
class RedEventModelAttendees extends RModel
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

		$mainframe = JFactory::getApplication();

		$xref = JRequest::getInt('xref');
		$this->setXref((int)$xref);

		$filter_order     = $mainframe->getUserStateFromRequest( 'com_redevent.attendees.filter_order', 'filter_order', 'r.id', 'cmd' );
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
		$app = JFactory::getApplication();
		$params = $app->getParams('com_redevent');

		$query = ' SELECT x.id, e.title '
		       . ' , CASE WHEN CHAR_LENGTH(x.title) THEN CONCAT_WS(\' - \', e.title, x.title) ELSE e.title END as full_title '
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
	 * @param int $include_wl include waiting list
	 * @return array
	 */
	function getAttendeesEmails($xref, $include_wl)
	{
		$query = ' SELECT r.sid '
		       . ' FROM #__redevent_register AS r '
		       . ' WHERE r.xref = '.$xref
		       . '   AND r.confirmed = 1 '
		       . ($include_wl == 0 ? ' AND r.waitinglist = 0 ' : '')
		       . '   AND r.cancelled = 0 '
		       ;
		$this->_db->setQuery($query);
		$res = $this->_db->loadColumn();

		if (!count($res)) {
			return false;
		}

		$emails = array();
		$rfcore = RdfCore::getInstance();
		$emails = $rfcore->getSubmissionContactEmails($res);

		return $emails;
	}

	function getSession()
	{
		if (empty($this->_session))
		{
			$query = ' SELECT e.title, e.registra, e.showfields, e.redform_id, e.id as eventid, e.course_code, e.show_names, '
			       . '      x.dates, x.enddates, x.times, x.endtimes, x.id as xref, '
             . ' CASE WHEN CHAR_LENGTH(x.title) THEN CONCAT_WS(\' - \', e.title, x.title) ELSE e.title END as full_title, '
			       . '      v.venue, '
			       . ' CASE WHEN CHAR_LENGTH(e.alias) THEN CONCAT_WS(\':\', x.id, e.alias) ELSE x.id END as slug, '
             . ' CASE WHEN CHAR_LENGTH(x.alias) THEN CONCAT_WS(\':\', x.id, x.alias) ELSE x.id END as xslug '
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
            . ' AND r.cancelled = 0 '
						;
		$db->setQuery($query);
		$submitters = $db->loadObjectList('submit_key');

		if ($submitters === null)
		{
			$msg = JText::_('COM_REDEVENT_ERROR_GETTING_ATTENDEES');
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
				RedeventError::raiseWarning('error', JText::_('COM_REDEVENT_Cannot_load_fields').$db->getErrorMsg());
				return null;
			}

			if (count($fields))
			{
				$table_fields = array();
				$fields_names = array();
				foreach ($fields as $key => $field) {
					$table_fields[] = 'a.field_'. $field->id;
					$fields_names['field_'. $field->id] = $field->field_header;
				}

				$query  = ' SELECT ' . implode(', ', $table_fields)
				        . ' , s.submit_key, s.id '
				        . ' FROM #__redevent_register AS r '
				        . ' INNER JOIN #__rwf_submitters AS s ON r.sid = s.id '
				        . ' INNER JOIN #__rwf_forms_' . $fields[0]->form_id . ' AS a ON s.answer_id = a.id '
				        . ' WHERE r.xref = ' . $this->_xref
				        . ' AND r.confirmed = 1'
				        . ' AND r.cancelled = 0 '
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
					RedeventError::raiseWarning('error', JText::_('COM_REDEVENT_Cannot_load_registered_users').' '.$db->getErrorMsg());
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
        	$msg = JText::_('COM_REDEVENT_ERROR_REGISTRATION_WITHOUT_SUBMITTER') . ': ' . $answer->id;
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

		if (empty($session->showfields))
		{
			return false;
		}

		$query = $this->_db->getQuery(true);

		$query->select('f.id, f.field, ff.form_id')
			->select('CASE WHEN (CHAR_LENGTH(field_header) > 0) THEN field_header ELSE field END AS field_header')
			->from('#__rwf_form_field AS ff')
			->join('INNER', '#__rwf_fields AS f ON ff.field_id = f.id')
			->where('form_id = '. $this->_db->Quote($session->redform_id))
			->where('ff.published = 1')
			->order('ff.ordering');

		if (!$all_fields)
		{
			$query->where('f.id in ('.$session->showfields. ')');
		}

		$this->_db->setQuery($query);
		$res = $this->_db->loadObjectList();

		return $res;
	}

	/**
	 * return true if user allowed to manage attendees
	 *
	 * @return boolean
	 */
  function getManageAttendees()
  {
  	$acl = RedeventUserAcl::getInstance();
  	return $acl->canManageAttendees($this->_xref);
  }

	/**
	 * return true if user allowed to manage attendees
	 *
	 * @return boolean
	 */
  function getViewAttendees()
  {
  	$acl = RedeventUserAcl::getInstance();
  	return $acl->canViewAttendees($this->_xref);
  }


  /**
   * return roles for the session
   *
   * @return array
   */
  function getRoles()
  {
  	$query = ' SELECT u.name, u.username, rr.usertype, rr.fields, '
  	       . '  r.name AS role, sr.role_id, sr.user_id '
  	       . ' FROM #__redevent_sessions_roles AS sr '
  	       . ' INNER JOIN #__users AS u ON u.id = sr.user_id '
  	       . ' INNER JOIN #__redevent_roles AS r on r.id = sr.role_id '
  	       . ' LEFT JOIN #__redevent_roles_redmember AS rr ON rr.role_id = r.id '
  	       . ' WHERE sr.xref = ' . $this->_db->Quote($this->_xref)
  	       . ' ORDER BY r.ordering ASC, u.name ASC'
  	       ;
  	$this->_db->setQuery($query);
  	$res = $this->_db->loadObjectList();

//   	if ($res && JComponentHelper::isEnabled('com_redmember'))
  	if ($res && file_exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_redmember'))
  	{
  		$uids = array();
  		$types = array();
  		foreach ($res as $r)
  		{
  			$uids[] = $r->user_id;
  			if ($r->usertype) {
  				$types[] = $r->usertype;
  			}
  		}

  		// user data from redmember
  		$query = ' SELECT *, user_id '
  		       . ' FROM #__redmember_users '
  		       . ' WHERE user_id IN (' . implode(',', $uids).')'
  		       ;
  		$this->_db->setQuery($query);
  		$rm_users = $this->_db->loadObjectList('user_id');

  		// all fields from redmember
  		$query = ' SELECT *, field_id '
  		       . ' FROM #__redmember_fields '
  		       . ' ORDER by ordering '
  		       ;
  		$this->_db->setQuery($query);
  		$rm_fields = $this->_db->loadObjectList('field_id');

  		foreach ($res as $k => $r)
  		{
 				$info = array();
  			if (isset($rm_users[$r->user_id]))
  			{
  				$ufields = explode(',', $r->fields);
  				foreach ($ufields as $f)
  				{
  					if (isset($rm_fields[$f]))
  					{
  						$fdb_name = $rm_fields[$f]->field_dbname;
  						$info[$rm_fields[$f]->field_name] = $rm_users[$r->user_id]->$fdb_name;
  					}
  				}
  			}
  			$res[$k]->rminfo = $info;
  		}
  	}

  	return $res;
  }
}
