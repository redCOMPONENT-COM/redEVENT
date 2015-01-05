<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * RedEvent Email attendees Category
 *
 * @package  Redevent.admin
 * @since    3.0
 */
class RedeventModelEmailattendees extends RModel
{
	/**
	 * Constructor
	 *
	 * @param   array  $config  An array of configuration options (name, state, dbo, table_path, ignore_request).
	 *
	 * @throws RuntimeException
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$data = JFactory::getApplication()->input->get('filter', array(), 'array');

		if (!$data['session'])
		{
			throw new RuntimeException('mission session id');
		}
		else
		{
			$this->setState('sessionId', (int) $data['session']);

			$this->setState('confirmed', isset($data['filter.confirmed']) ? $data['filter.confirmed'] : 1);
			$this->setState('waiting', isset($data['filter.waiting']) ? $data['filter.waiting'] : 0);
			$this->setState('cancelled', isset($data['filter.cancelled']) ? $data['filter.cancelled'] : 0);
		}

		if ($cids = JFactory::getApplication()->input->get('cid', array(), 'array'))
		{
			JArrayHelper::toInteger($cids);
			$this->setState('cids', $cids);
		}
		else
		{
			$this->setState('cids', array());
		}
	}

	/**
	 * Get attendees emails
	 *
	 * @return array
	 */
	public function getEmails()
	{
		$sids = $this->getSids();
		$rfcore = new RdfCore;

		$emails = array();

		foreach ($sids as $sid)
		{
			if ($contacts = $rfcore->getSidContactEmails($sid))
			{
				$emails = array_merge($emails, $contacts);
			}
		}

		return $emails;
	}

	/**
	 * Get session info
	 *
	 * @return mixed
	 */
	public function getSession()
	{
		$db = $this->_db;
		$query = $db->getQuery(true);

		$query->select('x.dates, x.id AS xref')
			->select('e.title')
			->select('v.venue')
			->from('#__redevent_events AS e')
			->join('INNER', '#__redevent_event_venue_xref AS x ON x.eventid = e.id')
			->join('LEFT', '#__redevent_venues AS v ON x.venueid = v.id')
			->where('x.id = ' . $this->getState('sessionId'));

		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 * Get attendees sids
	 *
	 * @return mixed
	 */
	private function getSids()
	{
		$db = $this->_db;
		$query = $db->getQuery(true);

		$query->select('sid')
			->from('#__redevent_register');

		if ($cids = $this->getState('cids'))
		{
			$cids = RHelperArray::quote($cids);
			$cids = implode(',', $cids);
			$query->where('id IN (' . $cids . ')');
		}
		else
		{
			$query->where('xref = ' . $this->getState('sessionId'));
			$query->where('confirmed = ' . $this->getState('confirmed'));
			$query->where('cancelled = ' . $this->getState('cancelled'));
			$query->where('waitinglist = ' . $this->getState('waiting'));
		}

		$db->setQuery($query);

		return $db->loadColumn();
	}
}
