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
				'r.id', 'r.xref', 'r.eventid', 'r.uregdate',
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
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.xref');
		$id	.= ':' . $this->getState('filter.confirmed');
		$id .= ':' . $this->getState('filter.waiting');
		$id	.= ':' . $this->getState('filter.cancelled');

		return parent::getStoreId($id);
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
		$query->select('s.answer_id, s.id AS submitter_id, s.price, s.currency');
		$query->select('u.username, u.name, u.email');
		$query->select('pg.name as pricegroup');
		$query->select('p.paid, p.status');
		$query->select('fo.activatepayment');
		$query->select('x.dates, x.times, x.maxattendees');
		$query->select('e.id AS eventid, e.course_code, e.title');
		$query->select('v.venue');
		$query->select('auth.username AS creator');
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
		$query->join('LEFT', '(SELECT MAX(id) as id, submit_key FROM #__rwf_payment GROUP BY submit_key) AS latest_payment ON latest_payment.submit_key = s.submit_key');
		$query->join('LEFT', '#__rwf_payment AS p ON p.id = latest_payment.id');

		$this->buildWhere($query);

		$query->order($this->_db->escape($this->getState('list.ordering', 'r.uregdate')) . ' ' . $this->_db->escape($this->getState('list.direction', 'DESC')));

		return $query;
	}

	/**
	 * Add where part from filters
	 *
	 * @param   JDatabaseQuery  &$query  query
	 *
	 * @return JDatabaseQuery
	 */
	private function buildWhere(&$query)
	{
		if (is_numeric($this->getState('filter.confirmed')))
		{
			switch ($this->getState('filter.confirmed'))
			{
				case 0:
					$query->where('r.confirmed = 0');
					break;
				case 1:
					$query->where('r.confirmed = 1');
					break;
			}
		}

		if (is_numeric($this->getState('filter.waiting')))
		{
			switch ($this->getState('filter.waiting'))
			{
				case 1:
					$query->where('r.waitinglist = 0');
					break;
				case 2:
					$query->where('r.waitinglist = 1');
					break;
			}
		}

		if (is_numeric($this->getState('filter.cancelled')))
		{
			switch ($this->getState('filter.cancelled'))
			{
				case 0:
					$query->where('r.cancelled = 0');
					break;
				case 1:
					$query->where('r.cancelled = 1');
					break;
			}
		}

		if ($this->getState('filter.search'))
		{
			$query->where('(u.name LIKE "%' . $this->getState('filter.search') . '%"'
				. ' OR u.username LIKE "%' . $this->getState('filter.search') . '%"'
				. ' OR u.email LIKE "%' . $this->getState('filter.search') . '%"'
			. ')');
		}

		if ($this->getState('filter.session'))
		{
			$query->where('r.xref = ' . $this->getState('filter.session'));
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

		$query->select('r.id AS rid, e.redform_id,r.xref AS xref_id');
		$query->from('#__redevent_register AS r');
		$query->join('INNER', '#__redevent_event_venue_xref AS x ON x.id = r.xref');
		$query->join('INNER', '#__redevent_events AS e ON e.id = x.eventid');
		$query->where('r.id IN (' . implode(',', $cid) . ')');
		$db->setQuery($query);
		$res = $db->loadObjectList();

		// Let's group
		$xrefs = array();

		foreach ($res as $r)
		{
			@$xrefs[$r->xref_id][] = $r->rid;
		}

		// Now call thje waiting list model per session
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
}
