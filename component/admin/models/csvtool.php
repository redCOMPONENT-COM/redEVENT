<?php
/**
 * @version 1.0 $Id: group.php 298 2009-06-24 07:42:35Z julien $
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

//no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

/**
 * redEvent Component csvtool Model
 *
 * @package Joomla
 * @subpackage redEvent
 * @since		2.0
 */
class RedEventModelcsvtool extends JModel
{

	/**
	 * Return forms asssigned to redevents as options
	 *
	 * @return array
	 */
	function getFormOptions()
	{
		$query = ' SELECT DISTINCT f.id AS value, f.formname AS text '
		       . ' FROM #__redevent_events AS e '
		       . ' INNER JOIN #__rwf_forms AS f ON f.id = e.redform_id '
		       . ' ORDER BY f.formname ASC '
		       ;
		$this->_db->setQuery($query);
		$res = $this->_db->loadObjectList();
		return $res;
	}

	/**
	 * get events as options according to filters
	 *
	 * @param int category_id
	 * @param int venue_id
	 * @return array
	 */
	function getEventOptions($category_id = 0, $venue_id = 0)
	{
		$query = ' SELECT e.id, e.title '
		       . ' FROM #__redevent_events AS e '
		       . ' INNER JOIN #__redevent_event_category_xref AS xcat ON xcat.event_id = e.id '
		       . ' INNER JOIN #__redevent_event_venue_xref AS x ON x.eventid = e.id '
		       ;
		$where = array();
		$where[] = ' e.published > -1 ';
		$where[] = ' x.published > -1 ';

		if ($category_id)	{
			$where[] = ' xcat.category_id = '.$category_id;
		}
		if ($venue_id)	{
			$where[] = ' x.venueid = '.$venue_id;
		}

		$query .= ' WHERE '.implode(' AND ', $where);

		$query .= ' GROUP BY e.id ';
		$query .= ' ORDER BY e.title ';

		$this->_db->setQuery($query);
		$res = $this->_db->loadObjectList();

		return $res;
	}

	function getFields($form_id)
	{
		$rfcore = new RedformCore();
		return $rfcore->getFields($form_id);
	}


/**
	 * Method to get the registered users
	 *
	 * @access	public
	 * @return	object
	 * @since	0.9
	 * @todo Complete CB integration
	 */
	function getRegisters($form_id, $events = null, $category_id = 0, $venue_id = 0, $state_filter = null, $filter_attending = null, $attendees_confirmed_filter = null)
	{
		$query = $this->_db->getQuery(true)
			->select('r.*, r.id as attendee_id, u.username, u.name, e.id AS eventid, u.email')
			->select('s.answer_id, r.waitinglist, r.confirmdate, r.confirmed, s.id AS submitter_id, s.price, s.currency')
			->select('pg.name as pricegroup, fo.activatepayment, p.paid, p.status')
			->select('e.course_code, e.title, x.dates, x.times, v.venue, x.maxattendees')
			->select('auth.username AS creator')
			->from('#__redevent_register AS r')
			->join('INNER', '#__redevent_event_venue_xref AS x ON r.xref = x.id')
			->join('INNER', '#__redevent_events AS e ON x.eventid = e.id')
			->join('INNER', '#__redevent_event_category_xref AS xcat ON xcat.event_id = e.id')
			->join('INNER', '#__rwf_submitters AS s ON r.sid = s.id')
			->join('INNER', '#__rwf_forms AS fo ON fo.id = s.form_id')
			->join('LEFT', '#__redevent_venues AS v ON x.venueid = v.id')
			->join('LEFT', '#__redevent_sessions_pricegroups AS spg ON spg.id = r.sessionpricegroup_id')
			->join('LEFT', '#__redevent_pricegroups AS pg ON pg.id = spg.pricegroup_id')
			->join('LEFT', '#__users AS u ON r.uid = u.id')
			->join('LEFT', '#__users AS auth ON auth.id = e.created_by')
			->join('LEFT', '(SELECT MAX(id) as id, submit_key FROM #__rwf_payment GROUP BY submit_key) AS latest_payment ON latest_payment.submit_key = s.submit_key')
			->join('LEFT', '#__rwf_payment AS p ON p.id = latest_payment.id')
			->where('r.cancelled = 0')
			->where('s.form_id = ' . $form_id)
			->group('r.id')
			->order('e.title, x.dates');

		if ($events && count($events))
		{
			$query->where('e.id in ('.implode(',', $events).')');
		}

		if ($category_id)
		{
			$query->where('xcat.category_id = ' . $category_id);
		}

		if ($venue_id)
		{
			$query->where('x.venueid = ' . $venue_id);
		}

		if (is_numeric($state_filter))
		{
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
		}

		if (is_numeric($filter_attending))
		{
			switch ($filter_attending)
			{
				case 1:
					$query->where('r.waitinglist = 0');
					break;
				case 2:
					$query->where('r.waitinglist = 1');
					break;
			}
		}

		if (is_numeric($attendees_confirmed_filter))
		{
			switch ($attendees_confirmed_filter)
			{
				case 1:
					$query->where('r.confirmed = 1');
					break;
				case 2:
					$query->where('r.confirmed = 0');
					break;
			}
		}

		$this->_db->setQuery($query);
		$submitters = $this->_db->loadObjectList();

		// Get answers
		$sids = JArrayHelper::getColumn($submitters, 'sid');
		$rfcore = new RedformCore();
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
}
