<?php
/**
 * @version 1.0 $Id$
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
 * EventList Component attendees Model
 *
 * @package Joomla
 * @subpackage redEVENT
 * @since		0.9
 */
class RedEventModelAttendees extends JModel
{
	/**
	 * Events data array
	 *
	 * @var array
	 */
	var $_data = null;

	/**
	 * Events total
	 *
	 * @var integer
	 */
	var $_total = null;

	/**
	 * Events total
	 *
	 * @var integer
	 */
	var $_event = null;

	/**
	 * Pagination object
	 *
	 * @var object
	 */
	var $_pagination = null;

	/**
	 * Events id
	 *
	 * @var int
	 */
	var $_eventid = null;

	var $_xref = null;
	/**
	 * Constructor
	 *
	 * @since 0.9
	 */
	function __construct()
	{
		parent::__construct();

		$mainframe = &JFactory::getApplication();

		$option = JRequest::getCmd('option');

		$limit		= $mainframe->getUserStateFromRequest( $option.'limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart = $mainframe->getUserStateFromRequest( $option.'limitstart', 'limitstart', 0, 'int' );

		$filter_confirmed = $mainframe->getUserStateFromRequest( $option.'.attendees.filter_confirmed', 'filter_confirmed', 0, 'int' );
		$filter_waiting   = $mainframe->getUserStateFromRequest( $option.'.attendees.filter_waiting',   'filter_waiting'  , 0, 'int' );
		$filter_cancelled = $mainframe->getUserStateFromRequest( $option.'.attendees.filter_cancelled', 'filter_cancelled', 0, 'int' );

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
		$this->setState('filter_confirmed', $filter_confirmed);
		$this->setState('filter_waiting',   $filter_waiting);
		$this->setState('filter_cancelled', $filter_cancelled);

		//set unlimited if export or print action | task=export or task=print
		$this->setState('unlimited', JRequest::getString('task'));

		$eventid = JRequest::getInt('eventid');
		$this->setId($eventid);

		$xref = JRequest::getInt('xref');
		if ($xref) {
			$this->setXref($xref);
		}
	}

	/**
	 * Method to set the category identifier
	 *
	 * @access	public
	 * @param	int Category identifier
	 */
	function setId($eventid)
	{
		// Set id and wipe data
		$this->_eventid	    = $eventid;
		$this->_data 	= null;
	}

	function setXref($xref)
	{
		// Set id and wipe data
		$this->_xref	    = $xref;
		$this->_data 	= null;

		// set eventid
		$query = ' SELECT eventid FROM #__redevent_event_venue_xref WHERE id = '. $this->_db->Quote($xref);
		$this->_db->setQuery($query);
		$this->setId($this->_db->loadResult());
	}

	/**
	 * Method to get attendees list data
	 *
	 * @return array
	 */
	public function getData()
	{
		$db = JFactory::getDBO();
		// Lets load the content if it doesn't already exist
		$query = $this->buildQuery();

		if ($this->getState('unlimited') == '')
		{
			$db->setQuery($query, $this->getState('limitstart'), $this->getState('limit'));
			$this->_data = $db->loadObjectList();
		}
		else
		{
			$db->setQuery($query);
			$this->_data = $db->loadObjectList();
		}

		return $this->_data;
	}

	/**
	 * Method to get the total nr of the attendees
	 *
	 * @return integer
	 */
	function getTotal()
	{
		// Lets load the content if it doesn't already exist
		$query = $this->buildQuery();
		$this->_total = $this->_getListCount($query);

		return $this->_total;
	}

	/**
	 * Method to get a pagination object for the events
	 *
	 * @access public
	 * @return integer
	 */
	function getPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_pagination))
		{
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination( $this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
		}

		return $this->_pagination;
	}

	/**
	 * Method to build the query for the attendees
	 *
	 * @return integer
	 */
	protected function buildQuery()
	{
		$db = JFactory::getDbo();

		// Build attendees list query
		$query = $db->getQuery(true);

		$query->select('r.*, r.id as attendee_id');
		$query->select('s.answer_id, s.id AS submitter_id, s.price, s.currency');
		$query->select('a.id AS eventid, a.course_code');
		$query->select('pg.name as pricegroup');
		$query->select('fo.activatepayment');
		$query->select('p.paid, p.status');
		$query->select('u.username, u.name, u.email');
		$query->from('#__redevent_register AS r');
		$query->join('INNER', '#__redevent_event_venue_xref AS x ON r.xref = x.id');
		$query->join('INNER', '#__redevent_events AS a ON x.eventid = a.id');
		$query->join('INNER', '#__rwf_submitters AS s ON r.sid = s.id');
		$query->join('INNER', '#__rwf_forms AS fo ON fo.id = a.redform_id');
		$query->join('LEFT', '#__redevent_sessions_pricegroups AS spg ON spg.id = r.sessionpricegroup_id');
		$query->join('LEFT', '#__redevent_pricegroups AS pg ON pg.id = spg.pricegroup_id');
		$query->join('LEFT', '#__users AS u ON r.uid = u.id');
		$query->join('LEFT', '(SELECT MAX(id) as id, submit_key FROM #__rwf_payment GROUP BY submit_key) AS latest_payment ON latest_payment.submit_key = s.submit_key');
		$query->join('LEFT', '#__rwf_payment AS p ON p.id = latest_payment.id');
		$query->group('r.id');

		// Add associated form fields
		$query = $this->queryAddFormFields($query);

		// Get the ORDER BY and WHERE clause for the query
		$query = $this->buildContentOrderBy($query);
		$query = $this->buildContentWhere($query);

		return $query;
	}

	/**
	 * Add form fields to the query
	 *
	 * @param   JDatabaseQuery  $query  the query
	 *
	 * @return JDatabaseQuery
	 */
	protected function queryAddFormFields(JDatabaseQuery $query)
	{
		// Join the form table
		$event = $this->getEvent();
		$query->join('INNER', '#__rwf_forms_' . $event->redform_id . ' AS f ON s.answer_id = f.id');

		// Add fields
		if ($this->getState('getAllFormFields', false))
		{
			return $this->queryAddAllFormFields($query);
		}
		else
		{
			return $this->queryAddEventShowFormFields($query);
		}
	}

	/**
	 * Add all form fields to the query
	 *
	 * @param   JDatabaseQuery  $query  the query
	 *
	 * @return JDatabaseQuery
	 */
	protected function queryAddAllFormFields(JDatabaseQuery $query)
	{
		$db = JFactory::getDbo();
		$query_fields = $db->getQuery(true);

		$query_fields->select('f.id');
		$query_fields->from('#__redevent_events AS e');
		$query_fields->join('INNER', '#__rwf_fields AS f ON f.form_id = e.redform_id');
		$query_fields->where('e.id = '. $db->Quote($this->_eventid));

		$db->setQuery($query_fields);
		$formFields = $db->loadColumn();

		foreach ($formFields as $fieldId)
		{
			$column = 'f.field_' . trim($fieldId);
			$query->select($column);
		}

		return $query;
	}

	/**
	 * Add all form fields to the query
	 *
	 * @param   JDatabaseQuery  $query  the query
	 *
	 * @return JDatabaseQuery
	 */
	protected function queryAddEventShowFormFields(JDatabaseQuery $query)
	{
		$db = JFactory::getDbo();
		$query_fields = $db->getQuery(true);

		$query_fields->select('e.redform_id, e.showfields');
		$query_fields->from('#__redevent_events AS e');
		$query_fields->where('e.id = '. $db->Quote($this->_eventid));

		$db->setQuery($query_fields, 0, 1);
		$formFields = $db->loadObject();

		if ($formFields && !empty($formFields->showfields))
		{
			$fields = explode(',', $formFields->showfields);

			// Add each field in select
			foreach ($fields as $f)
			{
				$column = 'f.field_' . trim($f);
				$query->select($column);
			}
		}

		return $query;
	}

	function getRedFormFrontFields()
	{
		// get redform form and fields to show
		$q = ' SELECT e.showfields '
		   . ' FROM #__redevent_events AS e '
		   . ' WHERE e.id = '. $this->_db->Quote($this->_eventid)
		   ;
		$this->_db->setQuery($q, 0, 1);
		$res = $this->_db->loadResult();
		if (empty($res)) {
			return null;
		}
		$list = array();
	  foreach (explode(',', $res) as $f) {
	  	$list[] = $this->_db->Quote($f);
	  }
		// get redform form and fields to show
		$q = ' SELECT f.id, f.field '
		   . '      , CASE WHEN (CHAR_LENGTH(f.field_header) > 0) THEN f.field_header ELSE f.field END AS field_header '
		   . ' FROM #__rwf_fields AS f '
		   . ' WHERE f.id IN ('.implode(',', $list) .')'
		   . ' ORDER BY f.ordering '
		   ;
		$this->_db->setQuery($q);
		$res = $this->_db->loadObjectList();
		//echo '<pre>';print_r($res); echo '</pre>';exit;
		return $res;
	}

	/**
	 * Method to build the orderby clause of the query for the attendees
	 *
	 * @param   JDatabaseQuery  $query  the query
	 *
	 * @return JDatabaseQuery
	 */
	protected function buildContentOrderBy($query)
	{
		$mainframe = JFactory::getApplication();
		$option    = JRequest::getCmd('option');

		$filter_order     = $mainframe->getUserStateFromRequest($option . '.attendees.filter_order', 'filter_order', 'r.confirmdate', 'cmd');
		$filter_order_Dir = $mainframe->getUserStateFromRequest($option . '.attendees.filter_order_Dir', 'filter_order_Dir', 'ASC', 'word');

		$query->order($filter_order.' '.$filter_order_Dir.', r.confirmdate DESC');

		return $query;
	}

	/**
	 * Method to build the where clause of the query for the attendees
	 *
	 * @param   JDatabaseQuery  $query  the query
	 *
	 * @return JDatabaseQuery
	 */
	protected function buildContentWhere($query)
	{
		$mainframe = JFactory::getApplication();
		$option = JRequest::getCmd('option');

		$xref = JRequest::getInt('xref');

		if ($xref)
		{
			$query->where('r.xref = ' . $xref);
		}
		elseif (!is_null($this->_xref) && $this->_xref > 0)
		{
			$query->where('r.xref = ' . $this->_xref);
		}
		elseif (!is_null($this->_eventid) && $this->_eventid > 0)
		{
			$query->where('x.eventid = ' . $this->_eventid);
		}

		switch ($this->getState('filter_confirmed', 0))
		{
			case 1:
				$query->where('r.confirmed = 1');
				break;
			case 2:
				$query->where('r.confirmed = 0');
				break;
		}

		switch ($this->getState('filter_waiting', 0))
		{
			case 1:
				$query->where('r.waitinglist = 0');
				break;
			case 2:
				$query->where('r.waitinglist = 1');
				break;
		}

		switch ($this->getState('filter_cancelled', 0))
		{
			case 0:
				$query->where('r.cancelled = 0');
				break;
			case 1:
				$query->where('r.cancelled = 1');
				break;
		}

		return $query;
	}

	/**
	 * Get event data
	 *
	 * @access public
	 * @return object
	 * @since 0.9
	 */
	function getEvent()
	{
		if (empty($this->_event))
		{
			$query = ' SELECT x.eventid, x.maxattendees, e.title, x.dates, e.redform_id , x.id AS xref, e.showfields, e.course_code  '
			       . ' , e.activate, v.venue '
			       . ' FROM #__redevent_events e '
			       . ' LEFT JOIN #__redevent_event_venue_xref x	ON x.eventid = e.id '
			       . ' LEFT JOIN #__redevent_venues AS v ON x.venueid = v.id '
			       ;
			if (!is_null($this->_xref) && $this->_xref > 0) {
				$query .= ' WHERE x.id = '.$this->_xref;
			}

			$this->_db->setQuery( $query );
			$this->_event = $this->_db->loadObject();
		}

		return $this->_event;
	}

	/**
	 * Cancel registrations
	 *
	 * @access public
	 * @return true on success
	 * @since 0.9
	 */
	function cancelreg($cid = array())
	{
		if (count( $cid ))
		{
			$ids = implode(',', $cid);

			$query = ' UPDATE #__redevent_register AS r '
             . '   SET r.cancelled = 1, r.waitinglist = 1 '
             . ' WHERE r.id IN ('.implode(', ', $cid).')'
             ;
			$this->_db->setQuery( $query );

			if (!$this->_db->query())
			{
				RedeventError::raiseError( 1001, $this->_db->getErrorMsg() );
				return false;
			}

			// Upate waiting list for all cancelled regs
			$db      = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('xref');
			$query->from('#__redevent_register');
			$query->where('id IN (' . implode(', ', $cid) . ')');

			$db->setQuery($query);
			$xrefs = $db->loadColumn();

			$xrefs = array_unique($xrefs);

			// now update waiting list for all updated sessions
			foreach ($xrefs as $xref)
			{
				$model_wait = JModel::getInstance('waitinglist', 'RedeventModel');
				$model_wait->setXrefId($xref);

				if (!$model_wait->UpdateWaitingList())
				{
					$this->setError($model_wait->getError());

					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Un-cancel registration
	 *
	 * @access public
	 * @return true on success
	 * @since 0.9
	 */
	function uncancelreg($cid = array())
	{
		if (count( $cid ))
		{
			$ids = implode(',', $cid);

			$query = ' UPDATE #__redevent_register AS r '
             . '   SET r.cancelled = 0, r.waitinglist = 1 ' // We put user on waiting list, to make sure they won't take back places from no cancelled attendees
             . ' WHERE r.id IN ('.implode(', ', $cid).')'
             ;
			$this->_db->setQuery( $query );

			if (!$this->_db->query()) {
				RedeventError::raiseError( 1001, $this->_db->getErrorMsg() );
				return false;
			}

			// Upate waiting list for all un-cancelled regs
			$db      = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('xref');
			$query->from('#__redevent_register');
			$query->where('id IN (' . implode(', ', $cid) . ')');

			$db->setQuery($query);
			$xrefs = $db->loadColumn();

			$xrefs = array_unique($xrefs);

			// Now update waiting list for all updated sessions
			foreach ($xrefs as $xref)
			{
				$model_wait = JModel::getInstance('waitinglist', 'RedeventModel');
				$model_wait->setXrefId($xref);

				if (!$model_wait->UpdateWaitingList())
				{
					$this->setError($model_wait->getError());

					return false;
				}
			}
		}
		return true;
	}

	/**
	 * Check if we are allowed to delete those registrations
	 *
	 * @param   array  $cid  registrations ids
	 *
	 * @return bool
	 */
	public function canDelete($cid = array())
	{
		if (count($cid))
		{
			$ids = implode(',', $cid);
			$form = $this->getForm();

			$query = ' SELECT r.id '
				. ' FROM #__redevent_register AS r '
				. ' LEFT JOIN #__rwf_submitters AS s ON r.sid = s.id '
				. ' LEFT JOIN #__rwf_forms_'.$form->id .' AS f ON f.id = s.answer_id '
				. ' WHERE r.id IN (' . implode(', ', $cid) . ')'
				. '   AND r.cancelled = 1 ';
			;
			$this->_db->setQuery($query);
			$res = $this->_db->loadColumn();

			if (!$res || !count($res) == count($cid))
			{
				$this->setError(JText::_('COM_REDEVENT_CANT_DELETE_REGISTRATIONS'));
				return false;
			}

			return true;
		}
		return true;
	}

	/**
	 * Delete registered users
	 *
	 * @access public
	 * @return true on success
	 * @since 0.9
	 */
	function remove($cid = array())
	{
		if (count( $cid ))
		{
			$ids = implode(',', $cid);
			$form = $this->getForm();

			$query = ' DELETE s, f, r '
        . ' FROM #__redevent_register AS r '
        . ' LEFT JOIN #__rwf_submitters AS s ON r.sid = s.id '
        . ' LEFT JOIN #__rwf_forms_'.$form->id .' AS f ON f.id = s.answer_id '
        . ' WHERE r.id IN ('.implode(', ', $cid).')'
        . '   AND r.cancelled = 1 ';
        ;
			$this->_db->setQuery( $query );

			if (!$this->_db->query()) {
				RedeventError::raiseError( 1001, $this->_db->getErrorMsg() );
				return false;
			}
		}
		return true;
	}

	/**
	 * Delete registered users
	 *
	 * @access public
	 * @param array int attendee ids
	 * @param int id of xref destination
	 * @return true on success
	 * @since 2.0
	 */
	function move($cid, $dest)
	{
		if (count( $cid ))
		{
			$ids = implode(',', $cid);
			$form = $this->getForm();

			$query = ' UPDATE #__redevent_register SET xref = '.$dest
			       . ' WHERE id IN ('.implode(', ', $cid).')'
			       ;
			$this->_db->setQuery( $query );

			if (!$this->_db->query()) {
				RedeventError::raiseError( 1001, $this->_db->getErrorMsg() );
				return false;
			}
		}
		return true;
	}

	/**
	 * confirm attendees
	 *
	 * @param $cid array of attendees id to confirm
	 * @return boolean true on success
	 */
	function confirmattendees($cid = array())
  {
    if (count( $cid ))
    {
      $ids = implode(',', $cid);
      $date = JFactory::getDate();

      $query = 'UPDATE #__redevent_register SET confirmed = 1, confirmdate = '.$this->_db->Quote($date->toSql()).' WHERE id IN ('. $ids .') ';
      $this->_db->setQuery( $query );

      if (!$this->_db->query()) {
        RedeventError::raiseError( 1001, $this->_db->getErrorMsg() );
        return false;
      }
    }
    return true;
  }


  /**
   * unconfirm attendees
   *
   * @param $cid array of attendees id to unconfirm
   * @return boolean true on success
   */
  function unconfirmattendees($cid = array())
  {
    if (count( $cid ))
    {
      $ids = implode(',', $cid);

      $query = 'UPDATE #__redevent_register SET confirmed = 0 WHERE id IN ('. $ids .') ';
      $this->_db->setQuery( $query );

      if (!$this->_db->query()) {
        RedeventError::raiseError( 1001, $this->_db->getErrorMsg() );
        return false;
      }
    }
    return true;
  }

  function getForm()
  {
  	if ($this->_eventid)
  	{
	  	$query = ' SELECT f.* '
	  	       . ' FROM #__redevent_events AS e '
	  	       . ' INNER JOIN #__rwf_forms AS f ON e.redform_id = f.id '
	  	       . ' WHERE e.id = '. $this->_db->Quote($this->_eventid)
	  	       ;
  	}
  	else if ($this->_xref)
  	{
	  	$query = ' SELECT f.* '
	  	       . ' FROM #__redevent_events AS e '
	  	       . ' INNER JOIN #__redevent_event_venue_xref AS x ON x.eventid = e.id '
	  	       . ' INNER JOIN #__rwf_forms AS f ON e.redform_id = f.id '
	  	       . ' WHERE x.id = '. $this->_db->Quote($this->_xref)
	  	       ;

  	}
  	else
  	{
  		JError::raisewarning(0, 'No event or session id !');
  		return false;
  	}
  	$this->_db->setQuery($query, 0, 1);
  	$res = $this->_db->loadObject();
//  	echo '<pre>';print_r($this->_db->getQuery()); echo '</pre>';exit;
		return $res;
  }

	function getDateTimeLocation()
	{
		$q = ' SELECT x.*, v.venue '
		   . ' FROM #__redevent_event_venue_xref x '
		   . ' LEFT JOIN #__redevent_venues v ON v.id = x.venueid '
		   ;
		if (!is_null($this->_eventid) && $this->_eventid > 0) {
			$q .= ' WHERE x.eventid = '.$this->_eventid;
		}
		else if (!is_null($this->_xref) && $this->_xref > 0) {
			$q .= ' WHERE x.id = '.$this->_xref;
		}
		$q .= '	ORDER BY v.venue, x.dates';
		$this->_db->setQuery($q);
		return $this->_db->loadObjectList();
	}

	/**
	 * returns redform fields
	 * @param boolean $all set true to return all fields
	 * @return array
	 */
	function getFields($all = false)
	{
		$event = $this->getEvent();
		$rfcore = new RedformCore();
		return $rfcore->getFields($event->redform_id);
	}

	function getEmails($cids = null)
	{
		$where = array( 'r.xref = ' . $this->_xref);
		if (is_array($cids) && !empty($cids)) {
			$where[] = ' r.id IN ('.implode(',', $cids).')';
		}
		else {
			$where[] = ' r.confirmed = 1 ';
		}

		// need to get sids for redform core
		$query = ' SELECT r.sid '
						. ' FROM #__redevent_register AS r '
						. ' INNER JOIN #__rwf_submitters AS s ON s.id = r.sid '
            . ' WHERE '.implode(' AND ', $where)
						;
		$this->_db->setQuery($query);
		$sids = $this->_db->loadResultArray();

		if (empty($sids)) {
			return false;
		}
		$rfcore = new RedformCore();
		$answers = $rfcore->getSidsFieldsAnswers($sids);

		$emails = array();
		foreach ($answers as $fields)
		{
			$res = array();
			foreach ($fields as $field)
			{
				switch ($field->fieldtype)
				{
					case 'username':
						$res['username'] = $field->answer;
						break;

					case 'fullname':
						$res['fullname'] = $field->answer;
						break;

					case 'email':
						$res['email'] = $field->answer;
						break;
				}
			}
			if (!isset($res['email'])) {
				JError::raiseWarning(0, JText::_('COM_REDEVENT_EMAIL_ATTENDEES_NO_EMAIL_FIELD'));
				return false;
			}
			if ( (!isset($res['fullname']) || empty($res['fullname'])) && isset($res['username'])) {
				$res['fullname'] = $res['username'];
			}
			$emails[] = $res;
		}
//		echo '<pre>';print_r($emails); echo '</pre>';exit;
		return $emails;
	}

	/**
	 * send mail to selected attendees
	 *
	 * @param array $cid attendee ids
	 * @param string $subject
	 * @param string $body
	 * @param string $from
	 * @param string $fromname
	 * @param string $replyto
	 * @return boolean
	 */
	function sendMail($cid, $subject, $body, $from = null, $fromname = null, $replyto = null)
	{
		$app = &JFactory::getApplication();
		$emails = $this->getEmails($cid);

		$taghelper = new RedeventTags();
		$taghelper->setXref($this->_xref);
  	$subject = $taghelper->ReplaceTags($subject);
  	$body    = $taghelper->ReplaceTags($body);

  	$mailer = & JFactory::getMailer();
  	$mailer->setSubject($subject);
  	$mailer->MsgHTML('<html><body>'.$body.'</body></html>');


  	if (!empty($from) && JMailHelper::isEmailAddress($from))
  	{
  		$fromname = !empty($fromname) ? $fromname : $app->getCfg('sitename');
  		$mailer->setSender(array($from, $fromname));
  	}

  	$res = true;

  	foreach ($emails as $e)
  	{
			$mailer->clearAllRecipients();
			if (isset($e['fullname'])) {
				$mailer->addAddress( $e['email'], $e['fullname'] );
			}
			else {
				$mailer->addAddress( $e['email'] );
			}

	  	if (!$mailer->send())
	  	{
	  		JError::raiseWarning(JText::sprintf('COM_REDEVENT_EMAIL_ATTENDEES_ERROR_SENDING_EMAIL_TO'), $e['email']);
	  		$res = false;
	  	}
  	}
  	return true;
	}
}
