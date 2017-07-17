<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * redEVENT Component registrations Model
 *
 * @package  Redevent.admin
 * @since    2.0
 */
class RedeventModelRegistrations extends RModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_registrations';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitField = 'registrations_limit';

	/**
	 * Limitstart field used by the pagination
	 *
	 * @var  string
	 */
	protected $limitstartField = 'auto';

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
				'r.id', 'r.xref', 'r.eventid', 'r.uregdate', 'u.username',
				'r.confirmed', 'r.waiting', 'r.cancelled', 'r.origin', 'r.waitinglist', 'e.title', 'paid',
				'r.origin',
				// Filters
				'session', 'venue', 'origin', 'xref', 'confirmed', 'waiting', 'cancelled'
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
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.xref');
		$id	.= ':' . $this->getState('filter.confirmed');
		$id .= ':' . $this->getState('filter.waiting');
		$id	.= ':' . $this->getState('filter.cancelled');
		$id	.= ':' . $this->getState('filter.origin');
		$id	.= ':' . $this->getState('filter.venue');

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
		$items = parent::getItems();

		$items = $this->addPaymentInfo($items);

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
	 */
	protected function getListQuery()
	{
		$db = $this->_db;
		$query = $db->getQuery(true);

		$query->select('r.*, r.id as attendee_id');
		$query->select('s.answer_id, s.id AS submitter_id, s.price, s.vat, s.currency');
		$query->select('u.username, u.name, u.email');
		$query->select('pg.name as pricegroup');
		$query->select('fo.activatepayment');
		$query->select('x.dates, x.times, x.maxattendees');
		$query->select('e.id AS eventid, e.course_code, e.title');
		$query->select('v.venue');
		$query->select('auth.username AS creator');
		$query->select('CASE WHEN pr.id IS NULL THEN 1 ELSE 0 END AS paid');
		$query->from('#__redevent_register AS r');
		$query->join('LEFT', '#__redevent_sessions_pricegroups AS spg ON spg.id = r.sessionpricegroup_id');
		$query->join('LEFT', '#__redevent_pricegroups AS pg ON pg.id = spg.pricegroup_id');
		$query->join('LEFT', '#__redevent_event_venue_xref AS x ON r.xref = x.id');
		$query->join('LEFT', '#__redevent_venues AS v ON x.venueid = v.id');
		$query->join('LEFT', '#__redevent_events AS e ON x.eventid = e.id');
		$query->join('LEFT', '#__users AS u ON r.uid = u.id');
		$query->join('LEFT', '#__users AS auth ON auth.id = e.created_by');
		$query->join('LEFT', '#__rwf_submitters AS s ON r.sid = s.id');
		$query->join('LEFT', '#__rwf_forms AS fo ON fo.id = s.form_id');
		$query->join('LEFT', '#__rwf_payment_request AS pr ON pr.submission_id = s.id AND pr.paid = 0');

		// Join on redform cart to filter by invoice id
		$query->join('LEFT', '#__rwf_payment_request AS pr2 ON pr2.submission_id = s.id')
			->join('LEFT', '#__rwf_cart_item AS ci ON ci.payment_request_id = pr2.id')
			->join('LEFT', '#__rwf_cart AS cart ON cart.id = ci.cart_id');

		$this->buildWhere($query);

		$query->order(
			$this->_db->escape($this->getState('list.ordering', 'r.uregdate'))
			. ' ' . $this->_db->escape($this->getState('list.direction', 'DESC'))
		);

		return $query;
	}

	/**
	 * Add where part from filters
	 *
	 * @param   JDatabaseQuery  $query  query
	 *
	 * @return JDatabaseQuery
	 */
	private function buildWhere(&$query)
	{
		$filterConfirmed = $this->getState('filter.confirmed');

		if (!empty($filterConfirmed))
		{
			switch ($filterConfirmed)
			{
				case "unconfirmed":
					$query->where('r.confirmed = 0');
					break;
				case "confirmed":
					$query->where('r.confirmed = 1');
					break;
			}
		}

		$filterWaiting = $this->getState('filter.waiting');

		if (!empty($filterWaiting))
		{
			switch ($this->getState($filterWaiting))
			{
				case "attending":
					$query->where('r.waitinglist = 0');
					break;
				case "waiting":
					$query->where('r.waitinglist = 1');
					break;
			}
		}

		$filterCancelled = $this->getState('filter.cancelled', 0);

		switch ($filterCancelled)
		{
			case 1:
				$query->where('r.cancelled = 1');
				break;
			case 2:
				$query->where('r.cancelled = 0');
				break;
		}

		$filterSearch = $this->getState('filter.search');

		if ($filterSearch)
		{
			$where = array(
				'u.name LIKE "%' . $this->getState('filter.search') . '%"',
				'u.username LIKE "%' . $this->getState('filter.search') . '%"',
				'u.email LIKE "%' . $this->getState('filter.search') . '%"',
				's.submit_key = "' . $this->getState('filter.search') . '"',
				'cart.invoice_id LIKE "%' . $this->getState('filter.search') . '%"',
				'cart.reference = "' . $this->getState('filter.search') . '"',
				'CONCAT(e.course_code, "-", x.id, "-", r.id) LIKE "%' . $filterSearch . '%"'
			);

			if (strstr($filterSearch, 'payment:'))
			{
				$search = substr($filterSearch, strlen('payment:'));
				$query->innerJoin('#__rwf_payment AS pay ON pay.cart_id = cart.id');
				$where[] = 'pay.data LIKE ' . $this->_db->q('%' . $search . '%');
			}

			$query->where('(' . implode(' OR ', $where) . ')');
		}

		$filterOrigin = $this->getState('filter.origin');

		if ($filterOrigin)
		{
			$query->where('r.origin LIKE "%' . $filterOrigin . '%"');
		}

		$filterVenue = $this->getState('filter.venue');

		if (is_numeric($filterVenue))
		{
			$query->where('x.venueid = ' . $filterVenue);
		}

		$filterSession = $this->getState('filter.session');

		if ($filterSession)
		{
			$query->where('r.xref = ' . $filterSession);
		}

		return $query;
	}

	/**
	 * toggle registrations on and off the wainting list
	 *
	 * @param   array    $cid  register_ids
	 * @param   boolean  $on   set true to put on waiting list, false to take off
	 *
	 * @return boolean
	 */
	public function togglewaiting($cid, $on)
	{
		if (!count($cid))
		{
			return true;
		}

		// We need to group by xref
		$db = $this->_db;
		$query = $db->getQuery(true);

		$query->select('r.id AS rid, t.redform_id,r.xref AS xref_id');
		$query->from('#__redevent_register AS r');
		$query->join('INNER', '#__redevent_event_venue_xref AS x ON x.id = r.xref');
		$query->join('INNER', '#__redevent_events AS e ON e.id = x.eventid');
		$query->join('INNER', '#__redevent_event_template AS t ON t.id =  e.template_id');
		$query->where('r.id IN (' . implode(',', $cid) . ')');
		$db->setQuery($query);
		$res = $db->loadObjectList();

		// Let's group
		$xrefs = array();

		foreach ($res as $r)
		{
			@$xrefs[$r->xref_id][] = $r->rid;
		}

		// Now call the waiting list model per session
		foreach ($xrefs as $xref => $rids)
		{
			$model = RModel::getAdminInstance('waitinglist');
			$model->setXrefId($xref);

			if ($on)
			{
				$res = $model->putOnWaitingList($rids);
			}
			else
			{
				$res = $model->putOffWaitingList($rids);
			}

			if (!$res)
			{
				$this->setError($model->getError());

				return false;
			}
		}

		return true;
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
		}

		return $items;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * @param   string $ordering  An optional ordering field.
	 * @param   string $direction An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   3.2.4
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		return parent::populateState($ordering ?: 'r.uregdate', $direction ?: 'desc');
	}
}
