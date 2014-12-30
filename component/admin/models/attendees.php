<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * redEVENT Component attendees Model
 *
 * @package  Redevent.admin
 * @since    0.9
 */
class RedeventModelAttendees extends RModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_attendees';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'attendees_limit';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitstartField = 'auto';

	/**
	 * Session data
	 *
	 * @var object
	 */
	var $session = null;

	/**
	 * redform fields
	 *
	 * @var array
	 */
	var $redformFields = null;

	/**
	 * Constructor.
	 *
	 * @param   array  $config  Configs
	 *
	 * @see     JController
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'r.id', 'x.eventid', 'x.xref',
				'r.confirmed', 'r.waiting', 'r.cancelled'
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return  string       A store id.
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.session');
		$id	.= ':' . $this->getState('filter.confirmed');
		$id .= ':' . $this->getState('filter.waiting');
		$id	.= ':' . $this->getState('filter.cancelled');

		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  object  Query object
	 *
	 * @throws RuntimeException
	 */
	protected function getListQuery()
	{
		$db = $this->_db;

		if (!$this->getState('filter.session'))
		{
			throw new RuntimeException('No session selected');
		}

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

		// Get the WHERE clause for the query
		$query = $this->buildContentWhere($query);

		$query->order($this->_db->escape($this->getState('list.ordering', 'r.confirmdate')) . ' ' . $this->_db->escape($this->getState('list.direction', 'DESC')));

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
		$session = $this->getSession();
		$query->join('INNER', '#__rwf_forms_' . $session->redform_id . ' AS f ON s.answer_id = f.id');

		// Select fields
		foreach ($this->getRedformFields() as $field)
		{
			$column = 'f.field_' . $field->fieldId;
			$query->select($column);
		}

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
		$query->where('r.xref = ' . $this->getState('filter.session'));

		switch ($this->getState('filter.confirmed', 0))
		{
			case 1:
				$query->where('r.confirmed = 1');
				break;
			case 2:
				$query->where('r.confirmed = 0');
				break;
		}

		switch ($this->getState('filter.waiting', 0))
		{
			case 1:
				$query->where('r.waitinglist = 0');
				break;
			case 2:
				$query->where('r.waitinglist = 1');
				break;
		}

		switch ($this->getState('filter.cancelled', 0))
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
	 * Get session data
	 *
	 * @return object
	 */
	public function getSession()
	{
		if (empty($this->session))
		{
			$query = $this->_db->getQuery(true);

			$query->select('x.eventid, x.maxattendees, x.dates, x.id AS xref')
				->select('e.title, e.redform_id, e.activate, e.showfields')
				->select('v.venue');
			$query->from('#__redevent_event_venue_xref AS x');
			$query->join('INNER', '#__redevent_events AS e ON e.id = x.eventid');
			$query->join('LEFT', '#__redevent_venues AS v ON x.venueid = v.id');
			$query->where('x.id = ' . $this->getState('filter.session'));

			$this->_db->setQuery($query);
			$this->session = $this->_db->loadObject();
		}

		return $this->session;
	}

	/**
	 * List of selected redform fields for frontend list
	 *
	 * @return array
	 */
	public function getSelectedFrontRedformFields()
	{
		$list = trim($this->getSession()->showfields);

		return $list ? explode(',', $list) : array();
	}

	/**
	 * Override for xref param in request
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		parent::populateState($ordering, $direction);

		$app = JFactory::getApplication();

		if ($value = $app->input->getInt('session', 0))
		{
			$this->setState('filter.session', $value);
		}
	}

	/**
	 * Get the filter form
	 *
	 * @param   array    $data      data
	 * @param   boolean  $loadData  load current data
	 *
	 * @return  JForm/false  the JForm object or false
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$form = parent::getForm($data, $loadData);

		if ($form && $this->getState('filter.session'))
		{
			$form->setValue('session', 'filter', $this->getState('filter.session'));
			$form->setFieldAttribute('session', 'event', $this->getSession()->eventid, 'filter');
		}

		return $form;
	}

	/**
	 * Cancel registrations
	 *
	 * @access public
	 * @return true on success
	 * @since 0.9
	 */
	public function cancelreg($cid = array())
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
			$db      = $this->_db;
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
				$model_wait = RModel::getAdminInstance('Waitinglist');
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
	public function uncancelreg($cid = array())
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
			$db      = $this->_db;
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
				$model_wait = RModel::getAdminInstance('Waitinglist');
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

	public function delete($pks = null)
	{
		$sessionIds = $this->getAttendeesSessionIds($pks);

		if (!parent::delete($pks))
		{
			return false;
		}

		$this->updateWaitingLists($sessionIds);

		foreach ($pks as $attendee_id)
		{
			JPluginHelper::importPlugin('redevent');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('onAttendeeDeleted', array($attendee_id));
		}

		return parent::delete($pks);
	}

	private function getAttendeesSessionIds($pks)
	{
		// Sanitize input.
		JArrayHelper::toInteger($pks);
		$pk = RHelperArray::quote($pks);
		$pk = implode(',', $pk);

		$query = $this->_db->getQuery(true);

		$query->select('DISTINCT xref');
		$query->from('#__redevent_register');
		$query->where('id IN (' . $pk .  ')');

		$this->_db->setQuery($query);
		$res = $this->_db->loadColumn();

		return $res;
	}

	/**
	 * Update sessions waiting list
	 *
	 * @param   array  $sessionIds  sessions ids
	 *
	 * @return void
	 */
	private function updateWaitingLists($sessionIds)
	{
		foreach ($sessionIds as $sessionId)
		{
			$model_wait = $this->getModel('waitinglist');
			$model_wait->setXrefId($sessionId);
			$model_wait->UpdateWaitingList();
		}
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

	/**
	 * returns redform fields
	 *
	 * @return array
	 */
	public function getRedformFields()
	{
		if (!$this->redformFields)
		{
			$rfcore = RdfCore::getInstance();
			$this->redformFields = $rfcore->getFields($this->getSession()->redform_id);
		}

		return $this->redformFields;
	}

	/**
	 * returns redform fields selected in event front fields
	 *
	 * @return array
	 */
	public function getFrontRedformFields()
	{
		$rfcore = RdfCore::getInstance();

		return $rfcore->getFields($this->getSession()->redform_id);
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

		if (empty($sids))
		{
			return false;
		}

		$rfcore = RdfCore::getInstance();
		$submissionemails = $rfcore->getSubmissionContactEmails($sids);

		$emails = array();

		foreach ($submissionemails as $sub)
		{
			foreach ($sub as $e)
			{
				if (!isset($emails[$e['email']]))
				{
					$emails[$e['email']] = $e;
				}
			}
		}

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
