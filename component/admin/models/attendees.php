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
 * @subpackage EventList
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

		global $mainframe, $option;

		$limit		= $mainframe->getUserStateFromRequest( $option.'limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart = $mainframe->getUserStateFromRequest( $option.'limitstart', 'limitstart', 0, 'int' );

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);

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
	 * Method to get categories item data
	 *
	 * @access public
	 * @return array
	 */
	function getData() {
		$db = JFactory::getDBO();
		// Lets load the content if it doesn't already exist
		$query = $this->_buildQuery();

		if ($this->getState('unlimited') == '') {
			$db->setQuery($query, $this->getState('limitstart'), $this->getState('limit'));
			$this->_data = $db->loadObjectList();
		} else {
			$db->setQuery($query);
			$this->_data = $db->loadObjectList();
		}
		return $this->_data;
	}
	
	/**
	 * Method to get the total nr of the attendees
	 *
	 * @access public
	 * @return integer
	 */
	function getTotal() {
		// Lets load the content if it doesn't already exist
		$query = $this->_buildQuery();
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
	 * @access private
	 * @return integer
	 * @since 0.9
	 */
	function _buildQuery()
	{
		// get redform form and fields to show
		$q = ' SELECT e.redform_id, e.showfields '
		   . ' FROM #__redevent_events AS e '
		   . ' WHERE e.id = '. $this->_db->Quote($this->_eventid)
		   ;
		$this->_db->setQuery($q, 0, 1);
		$res = $this->_db->loadObject();
		
		$rfields = array();
		if ($res && !empty($res->showfields)) 
		{
			$fields = explode(',', $res->showfields);
			foreach ($fields as $f) {
				$rfields[] = 'f.field_'.trim($f);
			}
			$rfields = ', '.implode(',', $rfields);
			$join_rwftable  = ' LEFT JOIN #__rwf_forms_'.$res->redform_id.' AS f ON s.answer_id = f.id ';
		}
		else
		{
			$rfields       = '';
			$join_rwftable = '';
		}
		
		// Get the ORDER BY clause for the query
		$orderby	= $this->_buildContentOrderBy();
		$where		= $this->_buildContentWhere();

		$query = ' SELECT r.*, r.id as attendee_id, u.username, u.name, a.id AS eventid, u.gid, u.email '
		       . ', s.answer_id, r.waitinglist, r.confirmdate, r.confirmed, s.id AS submitter_id, s.price, fo.activatepayment, p.paid, p.status '
		       . ', a.course_code '
		       . $rfields
		       . ' FROM #__redevent_register AS r '
		       . ' LEFT JOIN #__redevent_event_venue_xref AS x ON r.xref = x.id '
		       . ' LEFT JOIN #__redevent_events AS a ON x.eventid = a.id '
		       . ' LEFT JOIN #__users AS u ON r.uid = u.id '
		       . ' LEFT JOIN #__rwf_submitters AS s ON r.sid = s.id '
		       . ' LEFT JOIN #__rwf_forms AS fo ON fo.id = s.form_id '
		       . ' LEFT JOIN #__rwf_payment AS p ON p.submit_key = s.submit_key '
		       . $join_rwftable
		       . $where
		       . $orderby;
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
	 * @access private
	 * @return integer
	 * @since 0.9
	 */
	function _buildContentOrderBy() 
	{
		global $mainframe, $option;
		
		$filter_order		= $mainframe->getUserStateFromRequest( $option.'.attendees.filter_order', 'filter_order', 'r.confirmdate', 'cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $option.'.attendees.filter_order_Dir',	'filter_order_Dir',	'', 'word' );
		return ' ORDER BY '.$filter_order.' '.$filter_order_Dir.', r.confirmdate DESC';
		
	}
	
	/**
	 * Method to build the where clause of the query for the attendees
	 *
	 * @access private
	 * @return string
	 * @since 0.9
	 */
	function _buildContentWhere()
	{
		global $mainframe, $option;

		$xref = JRequest::getInt('xref');

		$where = array();

		if ($xref) {
			$where[] = ' x.id = '. $xref;
		}
		else if (!is_null($this->_xref) && $this->_xref > 0) {
			$where[] = 'x.id = '.$this->_xref;
		}
		else if (!is_null($this->_eventid) && $this->_eventid > 0) {
			$where[] = ' x.eventid = '.$this->_eventid;
		}

		$where 		= ( count( $where ) ? ' WHERE ' . implode( ' AND ', $where ) : '' );
		
		return $where;
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
			$query = ' SELECT x.eventid, e.title, x.dates, e.redform_id , x.id AS xref, e.showfields, e.course_code  '
			       . ' , v.venue '
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
        . ' WHERE r.id IN ('.implode(', ', $cid).')';
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
            
      $query = 'UPDATE #__redevent_register SET confirmed = 1 WHERE id IN ('. $ids .') ';
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
	
	function getFields($select = array())
	{
		$event = $this->getEvent();
		$rfcore = new RedFormCore();
		return $rfcore->getFields($event->redform_id); 
	}
	
/**
	 * Method to get the registered users
	 *
	 * @access	public
	 * @return	object
	 * @since	0.9
	 * @todo Complete CB integration
	 */
	function getRegisters($all_fields = false, $admin = false) 
	{
		// make sure the init is done
		$this->getEvent();
	  
		// first, get all submissions			
		$query = ' SELECT r.*, r.waitinglist, r.confirmed, r.confirmdate, r.submit_key, u.name '
						. ' FROM #__redevent_register AS r '
						. ' INNER JOIN #__rwf_submitters AS s ON s.id = r.sid '
						. ' LEFT JOIN #__users AS u ON r.uid = u.id '
						. ' WHERE r.xref = ' . $this->_xref
            . ' AND r.confirmed = 1'
						;
		$this->_db->setQuery($query);
		$submitters = $this->_db->loadObjectList();
		
		// get answers
		$sids = array();
		if (count($submitters)) 
		{
			foreach ($submitters as $s) 
			{
				$sids[] = $s->sid;
			}
		}
		$event = $this->getEvent();
		$rfcore = new RedFormCore();
		$answers = $rfcore->getSidsAnswers($sids);
		
		// add answers to registers
		foreach ($submitters as $k => $s)
		{
			if (isset($answers[$s->sid])) {
				$submitters[$k]->answers = $answers[$s->sid];
			}
			else {
				$submitters[$k]->answers = null;
			}
		}
		return $submitters;
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
		$rfcore = new RedFormCore();
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
	
	function sendMail($cid, $subject, $body)
	{
		$emails = $this->getEmails($cid);
		
		$mailer = & JFactory::getMailer();
  	$mailer->setSubject($subject);
  	$mailer->MsgHTML($body);
  	
  	$res = true;
  	
  	foreach ($emails as $e)
  	{
//  		$mailer->addAddress($r['email'], $r['name']);
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
?>