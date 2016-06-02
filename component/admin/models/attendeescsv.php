<?php
/**
 * @package    Redevent.admin
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * RedEvent attendees csv Model
 *
 * @package  Redevent.admin
 * @since    3.0
 */
class RedeventModelAttendeescsv extends RModelAdmin
{
	/**
	 * Method to get a single record.
	 *
	 * @param   int  $pk  Record Id
	 *
	 * @return  mixed
	 */
	public function getItem($pk = null)
	{
		return false;
	}

	/**
	 * Get attendees according to filters
	 *
	 * @param   int    $form_id           form id
	 * @param   array  $events            filter by events ids
	 * @param   int    $category_id       filter by category
	 * @param   int    $venue_id          filter by venue
	 * @param   int    $state_filter      filter session state
	 * @param   int    $filter_attending  filter attending state
	 *
	 * @return bool|mixed
	 */
	public function getRegisters($form_id, $events = null, $category_id = 0, $venue_id = 0, $state_filter = 0, $filter_attending = 0)
	{
		$query = $this->_db->getQuery(true);

		$query->select('e.title, e.course_code, x.id as xref, x.dates, v.venue')
			->select('r.*, r.waitinglist, r.confirmed, r.confirmdate, r.submit_key, u.name, pg.name as pricegroup')
			->select('s.answer_id, s.id AS submitter_id, s.price, s.currency')
			->select('p.paid, p.status')
			->from('#__redevent_register AS r')
			->join('INNER', '#__redevent_event_venue_xref AS x ON r.xref = x.id')
			->join('INNER', '#__redevent_events AS e ON x.eventid = e.id')
			->join('INNER', '#__redevent_event_template AS t ON t.id =  e.template_id')
			->join('INNER', '#__rwf_forms AS fo ON fo.id = t.redform_id')
			->join('INNER', '#__rwf_submitters AS s ON r.sid = s.id')
			->join('INNER', '#__redevent_event_category_xref AS xcat ON xcat.event_id = e.id')
			->join('LEFT', '#__redevent_venues AS v ON v.id = x.venueid')
			->join('LEFT', '#__redevent_sessions_pricegroups AS spg ON spg.id = r.sessionpricegroup_id')
			->join('LEFT', '#__redevent_pricegroups AS pg ON pg.id = spg.pricegroup_id')
			->join('LEFT', '#__users AS u ON r.uid = u.id')
			->join(
				'LEFT',
				'(SELECT MAX(id) as id, submit_key FROM #__rwf_payment GROUP BY submit_key) AS latest_payment ON latest_payment.submit_key = s.submit_key'
			)
			->join('LEFT', '#__rwf_payment AS p ON p.id = latest_payment.id')
			->where('r.confirmed = 1')
			->where('r.cancelled = 0')
			->where('s.form_id = ' . (int) $form_id)
			->group('r.id')
			->order('e.title, x.dates');

		if ($events && count($events))
		{
			$query->where('e.id in (' . implode(',', $events) . ')');
		}

		if ($category_id)
		{
			$query->where('xcat.category_id = ' . (int) $category_id);
		}

		if ($venue_id)
		{
			$query->where('x.venueid = ' . (int) $venue_id);
		}

		switch ($state_filter)
		{
			case 0:
				$query->where('x.published = 1');
				break;

			case 1:
				$query->where('x.published = -1');
				break;

			case 2:
				$query->where('x.published <> 0');
				break;
		}

		switch ($filter_attending)
		{
			case 1:
				$query->where('r.waitinglist = 0');
				break;

			case 2:
				$query->where('r.waitinglist = 1');
				break;
		}

		$this->_db->setQuery($query);
		$submitters = $this->_db->loadObjectList();

		// Get answers
		$sids = array();

		if (count($submitters))
		{
			foreach ($submitters as $s)
			{
				$sids[] = $s->sid;
			}
		}
		else
		{
			return false;
		}

		$rfcore = RdfCore::getInstance();
		$answers = $rfcore->getAnswers($sids);

		// Add answers to registers
		foreach ($submitters as $k => $s)
		{
			$submitters[$k]->answers = $answers->getSubmissionBySid($s->sid);
		}

		return $submitters;
	}

	/**
	 * Return redform fields
	 *
	 * @param   int  $form_id  form id
	 *
	 * @return array
	 */
	public function getFields($form_id)
	{
		$rfcore = RdfCore::getInstance();

		return $rfcore->getFields($form_id);
	}

	/**
	 * Get events options filtered by category and venue
	 *
	 * @param   int  $categoryId  category id
	 * @param   int  $venueId     venue id
	 *
	 * @return mixed
	 */
	public function getEventsOptions($categoryId = 0, $venueId = 0)
	{
		$query = $this->_db->getQuery(true);

		$query->select('e.id, e.title')
			->from('#__redevent_events AS e')
			->join('LEFT', '#__redevent_event_category_xref AS xcat ON xcat.event_id = e.id')
			->join('LEFT', '#__redevent_event_venue_xref AS x ON x.eventid = e.id')
			->order('e.title ASC')
			->group('e.id');

		if ($categoryId)
		{
			$query->where('xcat.category_id = ' . $categoryId);
		}

		if ($venueId)
		{
			$query->where('x.venueid = ' . $venueId);
		}

		$this->_db->setQuery($query);

		return $this->_db->loadObjectList();
	}

	/**
	 * Stock method to auto-populate the model state.
	 *
	 * @return  void
	 */
	protected function populateState()
	{
		$filters = JFactory::getApplication()->input->get('jform', array(), 'array');

		if (isset($filters['parent']))
		{
			$this->setState('parent', (int) $filters['parent']);
		}
	}
}
