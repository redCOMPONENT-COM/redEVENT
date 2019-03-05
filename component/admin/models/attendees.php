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
	protected $session = null;

	/**
	 * redform fields
	 *
	 * @var array
	 */
	protected $redformFields = null;

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
				'r.confirmed', 'r.waitinglist', 'r.cancelled', 'r.uregdate', 'r.confirmdate',
				'u.username', 'u.email', 'paid',
				// Filters
				'cancelled', 'confirmed', 'waitinglist'
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
		$id	.= ':' . $this->getState('filter.search');

		return parent::getStoreId($id);
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   11.1
	 */
	public function getItems()
	{
		if ($this->getState('streamOutput') == 'csv')
		{
			$this->setState('list.limit', 0);
			$this->setState('list.limitstart', 0);
		}

		$items = parent::getItems();

		$items = $this->addPaymentInfo($items);

		foreach ($items as &$item)
		{
			$item->uniqueid = RedeventHelper::getRegistrationUniqueId($item);
		}

		// Get the storage key.
		$store = $this->getStoreId();

		// Add back the items to the internal cache.
		$this->cache[$store] = $items;

		return $this->cache[$store];
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
		$query->select('s.answer_id, s.id AS submitter_id, s.price, s.vat, s.currency');
		$query->select('a.id AS eventid, a.course_code');
		$query->select('pg.name as pricegroup');
		$query->select('fo.activatepayment');
		$query->select('u.username, u.name, u.email');
		$query->select('CASE WHEN pr.id IS NULL THEN 1 ELSE 0 END AS paid');
		$query->from('#__redevent_register AS r');
		$query->join('INNER', '#__redevent_event_venue_xref AS x ON r.xref = x.id');
		$query->join('INNER', '#__redevent_events AS a ON x.eventid = a.id');
		$query->join('INNER', '#__redevent_event_template AS t ON t.id =  a.template_id');
		$query->join('INNER', '#__rwf_submitters AS s ON r.sid = s.id');
		$query->join('INNER', '#__rwf_forms AS fo ON fo.id = t.redform_id');
		$query->join('LEFT', '#__redevent_sessions_pricegroups AS spg ON spg.id = r.sessionpricegroup_id');
		$query->join('LEFT', '#__redevent_pricegroups AS pg ON pg.id = spg.pricegroup_id');
		$query->join('LEFT', '#__users AS u ON r.uid = u.id');
		$query->join('LEFT', '#__rwf_payment_request AS pr ON pr.submission_id = s.id AND pr.paid = 0');

		// Join on redform cart to filter by invoice id
		$query->join('LEFT', '#__rwf_payment_request AS pr2 ON pr2.submission_id = s.id')
			->join('LEFT', '#__rwf_cart_item AS ci ON ci.payment_request_id = pr2.id')
			->join('LEFT', '#__rwf_cart AS cart ON cart.id = ci.cart_id');

		$query->group('r.id');

		// Add associated form fields
		$query = $this->queryAddFormFields($query);

		// Get the WHERE clause for the query
		$query = $this->buildContentWhere($query);

		$query->order(
			$this->_db->escape($this->getState('list.ordering', 'r.confirmdate'))
			. ' ' . $this->_db->escape($this->getState('list.direction', 'DESC'))
		);

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
			case 1:
				$query->where('r.cancelled = 1');
				break;
			case 2:
				$query->where('r.cancelled = 0');
				break;
		}

		if ($this->getState('filter.search'))
		{
			$where = array(
				'u.name LIKE "%' . $this->getState('filter.search') . '%"',
				'u.username LIKE "%' . $this->getState('filter.search') . '%"',
				'u.email LIKE "%' . $this->getState('filter.search') . '%"',
				'cart.invoice_id LIKE "%' . $this->getState('filter.search') . '%"',
				'CONCAT(a.course_code, "-", x.id, "-", r.id) LIKE "%' . $this->getState('filter.search') . '%"'
			);

			$query->where('(' . implode(' OR ', $where) . ')');
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
				->select('e.title, t.redform_id, t.activate, t.showfields')
				->select('t.redform_id')
				->select('v.venue');
			$query->from('#__redevent_event_venue_xref AS x');
			$query->join('INNER', '#__redevent_events AS e ON e.id = x.eventid');
			$query->join('INNER', '#__redevent_event_template AS t ON t.id = e.template_id');
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

		if ($value = $app->input->getInt('xref', 0))
		{
			$this->setState('filter.session', $value);
		}
	}

	/**
	 * Cancel registrations
	 *
	 * @param   array  $cid  cids
	 *
	 * @return true on success
	 */
	public function cancelreg($cid = array())
	{
		if (count($cid))
		{
			$query = $this->_db->getQuery(true);

			$query->update('#__redevent_register AS r')
				->set('r.cancelled = 1')
				->set('r.waitinglist = 1')
				->where('r.id IN (' . implode(', ', $cid) . ')');
			$this->_db->setQuery($query);

			$this->_db->execute();

			// Update waiting list for all cancelled regs
			$updated = $this->getRowsByIds($cid);
			$sessionIds = array_unique(JArrayHelper::getColumn($updated, 'xref'));
			$this->updateWaitingLists($sessionIds);

			// Generate negative payment request if already paid
			foreach (JArrayHelper::getColumn($updated, 'sid') as $sid)
			{
				$helper = new RdfPaymentTurnsubmission($sid);
				$helper->turn();
				$helper->processRefund();
			}

			foreach ($cid as $attendee_id)
			{
				JPluginHelper::importPlugin('redevent');
				$dispatcher = JDispatcher::getInstance();
				$dispatcher->trigger('onAttendeeModified', array($attendee_id));
			}
		}

		return true;
	}

	/**
	 * Cancel registrations
	 *
	 * @param   array  $cid  cids
	 *
	 * @return true on success
	 */
	public function cancelMultipleReg($cid = array())
	{
		if (count($cid))
		{
			$submitKeys = $this->getAttendeesSubmitKeys($cid);
			$escapedKeys = array_map(array($this->_db, 'q'), $submitKeys);

			$query = $this->_db->getQuery(true);

			$query->update('#__redevent_register AS r')
				->set('r.cancelled = 1')
				->set('r.waitinglist = 1')
				->where('r.submit_key IN (' . implode(', ', $escapedKeys) . ')');
			$this->_db->setQuery($query);

			$this->_db->execute();

			// Update waiting list for all cancelled regs
			$updated = $this->getRowsByIds($cid);
			$sessionIds = array_unique(JArrayHelper::getColumn($updated, 'xref'));
			$this->updateWaitingLists($sessionIds);

			// Generate negative payment request if already paid
			foreach ($submitKeys as $submitKey)
			{
				$helper = new RdfPaymentTurnmultiple($submitKey);
				$helper->turn();
				$helper->processRefund();
			}

			foreach ($this->getMultipleAttendeesIds($cid) as $attendee_id)
			{
				JPluginHelper::importPlugin('redevent');
				$dispatcher = JDispatcher::getInstance();
				$dispatcher->trigger('onAttendeeModified', array($attendee_id));
			}
		}

		return true;
	}

	/**
	 * Un-cancel registration
	 *
	 * @param   array  $cid  cids
	 *
	 * @return true on success
	 */
	public function uncancelreg($cid = array())
	{
		if (count($cid))
		{
			$query = $this->_db->getQuery(true);

			$query->update('#__redevent_register AS r')
				->set('r.cancelled = 0')
				->set('r.waitinglist = 1') // We put user on waiting list, to make sure they won't take back places from no cancelled attendees
				->where('r.id IN (' . implode(', ', $cid) . ')');
			$this->_db->setQuery($query);

			$this->_db->execute();

			// Update waiting list for all un-cancelled regs
			$sessionIds = $this->getAttendeesSessionIds($cid);
			$this->updateWaitingLists($sessionIds);

			// Update payment request
			foreach ($cid as $attendeeId)
			{
				$attendee = RedeventEntityAttendee::load($attendeeId);
				$attendee->updatePaymentRequests();
			}
		}

		return true;
	}

	/**
	 * Get attendees sessions ids
	 *
	 * @param   mixed  $pks  ids
	 *
	 * @return mixed
	 */
	private function getAttendeesSessionIds($pks)
	{
		$rows = $this->getRowsByIds($pks);

		return $rows ? array_unique(JArrayHelper::getColumn($rows, 'xref')) : false;
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
			$model = RModel::getAdminInstance('Waitinglist');
			$model->setXrefId($sessionId);
			$model->updateWaitingList();
		}
	}

	/**
	 * Move registered users
	 *
	 * @param   array  $cid   int attendee ids
	 * @param   int    $dest  id of session destination
	 *
	 * @return true on success
	 */
	public function move($cid, $dest)
	{
		if (count($cid))
		{
			$query = $this->_db->getQuery(true);

			$query->update('#__redevent_register')
				->set('xref = ' . $dest)
				->where('id IN (' . implode(', ', $cid) . ')');

			$this->_db->setQuery($query);

			$this->_db->execute();
		}

		return true;
	}

	/**
	 * confirm attendees
	 *
	 * @param   array  $cid  array of attendees id to confirm
	 *
	 * @return boolean true on success
	 */
	public function confirmattendees($cid = array())
	{
		if (count($cid))
		{
			foreach ($cid as $id)
			{
				$attendee = new RedeventAttendee($id);
				$attendee->confirm();
			}
		}

		return true;
	}

	/**
	 * unconfirm attendees
	 *
	 * @param   array  $cid  array of attendees id to unconfirm
	 *
	 * @return boolean true on success
	 */
	public function unconfirmattendees($cid = array())
	{
		if (count($cid))
		{
			$ids = implode(',', $cid);

			$query = $this->_db->getQuery(true);

			$query->update('#__redevent_register')
				->set('confirmed = 0')
				->where('id IN (' . implode(', ', $cid) . ')');

			$this->_db->setQuery($query);
			$this->_db->execute();
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

	/**
	 * Add payment info to items
	 *
	 * @param   array  $items  items
	 *
	 * @return array
	 */
	protected function addPaymentInfo($items)
	{
		if (!$items)
		{
			return $items;
		}

		$sids = array();

		foreach ($items as $item)
		{
			$sids[] = $item->sid;
		}

		$paymentRequests = RdfCore::getSubmissionsPaymentRequests($sids);

		foreach ($items as &$item)
		{
			$item->paid = 1;

			if (isset($paymentRequests[$item->sid]))
			{
				$item->paymentRequests = $paymentRequests[$item->sid];

				foreach ($paymentRequests[$item->sid] as $pr)
				{
					if ($pr->paid == 0)
					{
						$item->paid = 0;
						break;
					}
				}
			}
			else
			{
				$item->paymentRequests = false;
			}

			if ($item->cancelled == 1
				&& $this->getState('streamOutput') == 'csv'
				&& RedeventHelperConfig::get('attendees_export_csv_cancelled_as_unpaid', 0))
			{
				$item->paid = 0;
			}
		}

		return $items;
	}

	/**
	 * Get rows by ids
	 *
	 * @param   int[]  $ids  ids
	 *
	 * @return mixed
	 */
	private function getRowsByIds($ids)
	{
		if (!$ids)
		{
			return false;
		}

		JArrayHelper::toInteger($ids);
		$id = RHelperArray::quote($ids);
		$id = implode(',', $id);

		$query = $this->_db->getQuery(true)
			->select('*')
			->from('#__redevent_register')
			->where('id IN (' . $id . ')');

		$this->_db->setQuery($query);
		$res = $this->_db->loadObjectList();

		return $res;
	}

	/**
	 * Get submit keys matching attendees ids
	 *
	 * @param   integer[]  $cid  attendee ids
	 *
	 * @return string[]
	 */
	private function getAttendeesSubmitKeys($cid)
	{
		$query = $this->_db->getQuery(true)
			->select('DISTINCT submit_key')
			->from('#__redevent_register')
			->where('id IN (' . implode(",", $cid) . ')');

		$this->_db->setQuery($query);

		return $this->_db->loadColumn();
	}

	/**
	 * Get booked together attendee ids
	 *
	 * @param   integer[]  $cid  attendee ids
	 *
	 * @return integer[]
	 */
	private function getMultipleAttendeesIds($cid)
	{
		$query = $this->_db->getQuery(true)
			->select('DISTINCT rj.id')
			->from('#__redevent_register as r')
			->innerJoin('#__redevent_register as rj On rj.submit_key = r.submit_key')
			->where('r.id IN (' . implode(",", $cid) . ')');

		$this->_db->setQuery($query);

		return $this->_db->loadColumn();
	}
}
