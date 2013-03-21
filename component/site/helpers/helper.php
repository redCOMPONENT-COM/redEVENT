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

defined('_JEXEC') or die('Restricted access');

/**
 *
 * Holds some usefull functions to keep the code a bit cleaner
 *
 * @package Joomla
 * @subpackage redEVENT
*/
class redEVENTHelper {

	/**
	 * Pulls settings from database and stores in an static object
	 *
	 * @return object
	 * @since 0.9
	 */
	public static function config()
	{
		return JComponentHelper::getParams('com_redevent');
		static $config;

		if (!$config)
		{
			require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_redevent'.DS.'tables'.DS.'redevent_settings.php');
			$config = JTable::getInstance('RedEvent_settings', '');
			$config->load(1);
			$config->params = JComponentHelper::getParams('com_redevent'); // redundant, but for legacy
		}
		return $config;
	}

	/**
	 * Performs dayly scheduled cleanups
	 *
	 * Currently it archives and removes outdated events
	 * and takes care of the recurrence of events
	 *
	 * @since 0.9
	 */
	function cleanup($forced = 0)
	{
		$db			= & JFactory::getDBO();

		$elsettings = & redEVENTHelper::config();
		$params = &JComponentHelper::getParams('com_redevent');

		$now 		= time();

		$query = ' SELECT lastupdate '
		. ' FROM #__redevent_settings ';
		$db->setQuery($query);
		$lastupdate = $db->loadResult();

		//last update later then 24h?
		//$difference = $now - $lastupdate;

		//if ( $difference > 86400 ) {

		//better: new day since last update?
		$nrdaysnow = floor($now / 86400);
		$nrdaysupdate = floor($lastupdate / 86400);

		if ( $nrdaysnow > $nrdaysupdate || $forced)
		{
			$nulldate = '0000-00-00';
			$limit_date = strftime('%Y-%m-%d', time() - $params->get('pastevents_delay', 3) * 3600 * 24);

			redEVENTHelper::generaterecurrences();

			// date filtering
			$where = array('x.dates IS NOT NULL');
			switch ($params->get('pastevents_reference_date', 'end'))
			{
				case 'start':
					$where[] = ' DATEDIFF('. $db->Quote($limit_date) .', x.dates) >= 0 ';
					break;
				case 'registration':
					$where[] = ' DATEDIFF('. $db->Quote($limit_date) .', (IF (x.registrationend <> '. $db->Quote($nulldate) .', x.registrationend, x.dates))) >= 0 ';
					break;
				case 'end':
					$where[] = ' DATEDIFF('. $db->Quote($limit_date) .', (IF (x.enddates <> '. $db->Quote($nulldate) .', x.enddates, x.dates))) >= 0 ';
					break;
			}
			$where_date = implode(' AND ', $where);

			//delete outdated events
			if ($params->get('pastevents_action', 0) == 1)
			{

				// lists event_id for which we are going to delete xrefs
				$query = ' SELECT x.eventid FROM #__redevent_event_venue_xref AS x ';
				$query .= ' WHERE '. $where_date;

				$db->SetQuery( $query );
				$event_ids = $db->loadResultArray();

				if (!count($event_ids)) {
					return true;
				}

				$query = ' DELETE x FROM #__redevent_event_venue_xref AS x '
				. ' WHERE '. $where_date;
				;
				$db->SetQuery( $query );
				if (!$db->Query()) {
					RedeventHelperLog::simpleLog('CLEANUP Error while deleting old xrefs: '. $db->getErrorMsg());
				}

				if ($params->get('pastevents_events_action', 1))
				{
					// now delete the events with no more xref
					$query = ' DELETE e FROM #__redevent_events AS e '
					. ' LEFT JOIN #__redevent_event_venue_xref AS x ON x.eventid = e.id '
					. ' WHERE x.id IS NULL '
					. '   AND e.id IN (' . implode(', ', $event_ids) . ')'
					;
					$db->SetQuery( $query );
					if (!$db->Query()) {
						RedeventHelperLog::simpleLog('CLEANUP Error while deleting old events with no more xrefs: '. $db->getErrorMsg());
					}
				}
			}

			//Set state archived of outdated events
			if ($params->get('pastevents_action', 0) == 2)
			{
				// lists xref_id and associated event_id for which we are going to be archived
				$query = ' SELECT x.id, x.eventid '
				. ' FROM #__redevent_event_venue_xref AS x '
				. ' WHERE '. $where_date
				. ' AND x.published = 1 '
				;
				$db->SetQuery( $query );
				$xrefs = $db->loadObjectList();

				if (empty($xrefs)) {
					return true;
				}

				// build list of xref and corresponding events
				$event_ids = array();
				$xref_ids  = array();
				foreach ($xrefs AS $xref)
				{
					$event_ids[] = $db->Quote($xref->eventid);
					$xref_ids[]  = $db->Quote($xref->id);
				}
				// filter duplicates
				$event_ids = array_unique($event_ids);

				// update xref to archive
				$query = ' UPDATE #__redevent_event_venue_xref AS x '
				. ' SET x.published = -1 '
				. ' WHERE x.id IN ('. implode(', ', $xref_ids) .')'
				;
				$db->SetQuery( $query );
				if (!$db->Query()) {
					RedeventHelperLog::simpleLog('CLEANUP Error while archiving old xrefs: '. $db->getErrorMsg());
				}

				if ($params->get('pastevents_events_action', 1))
				{
					// update events to archive (if no more published xref)
					$query = ' UPDATE #__redevent_events AS e '
					. ' LEFT JOIN #__redevent_event_venue_xref AS x ON x.eventid = e.id AND x.published <> -1 '
					. ' SET e.published = -1 '
					. ' WHERE x.id IS NULL '
					. '   AND e.id IN (' . implode(', ', $event_ids) . ')'
					;
					$db->SetQuery( $query );
					if (!$db->Query()) {
						RedeventHelperLog::simpleLog('CLEANUP Error while archiving events with only archived xrefs: '. $db->getErrorMsg());
					}
				}
			}

			//Set timestamp of last cleanup
			$query = 'UPDATE #__redevent_settings SET lastupdate = '.time().' WHERE id = 1';
			$db->SetQuery( $query );
			$db->Query();
		}
	}

	/**
	 * adds xref repeats to the database.
	 *
	 * @return bool true on success
	 */
	function generaterecurrences($recurrence_id = null)
	{
		$db = & JFactory::getDBO();

		$nulldate = '0000-00-00';

		// generate until limit
		$params = & JComponentHelper::getParams('com_redevent');
		$limit = $params->get('recurrence_limit', 30);
		$limit_date_int = time() + $limit*3600*24;

		// get active recurrences
		$query = ' SELECT MAX(rp.xref_id) as xref_id, r.rrule, r.id as recurrence_id '
		. ' FROM #__redevent_repeats AS rp '
		. ' INNER JOIN #__redevent_recurrences AS r on r.id = rp.recurrence_id '
		. ' INNER JOIN #__redevent_event_venue_xref AS x on x.id = rp.xref_id ' // make sure there are still events associated...
		. ' WHERE r.ended = 0 '
		. '   AND x.dates > 0 '
		;
		if ($recurrence_id) {
			$query .= ' AND r.id = '. $db->Quote($recurrence_id);
		}
		$query .= ' GROUP BY rp.recurrence_id ';
		$db->setQuery($query);
		$recurrences = $db->loadObjectList();

		if (empty($recurrences)) {
			return true;
		}

		// get corresponding xrefs
		$rids = array();
		foreach ($recurrences as $r) {
			$rids[] = $r->xref_id;
		}
		$query = ' SELECT x.*, rp.count '
		. ' FROM #__redevent_event_venue_xref AS x '
		. ' INNER JOIN #__redevent_repeats AS rp ON rp.xref_id = x.id '
		. ' WHERE x.id IN ('. implode(",", $rids) .')'
		;
		$db->setQuery($query);
		$xrefs = $db->loadObjectList('id');

		// now, do the job...
		foreach ($recurrences as $r)
		{
			$next = RedeventHelperRecurrence::getnext($r->rrule, $xrefs[$r->xref_id]);
			while ($next)
			{
				if (strtotime($next->dates) > $limit_date_int) {
					break;
				}

				//record xref
				$object = & JTable::getInstance('RedEvent_eventvenuexref', '');
				$object->bind($next);
				if ($object->store())
				{
					// copy the roles
					$query = ' INSERT INTO #__redevent_sessions_roles (xref, role_id, user_id) '
					. ' SELECT '.$object->id.', role_id, user_id '
					. ' FROM #__redevent_sessions_roles '
					. ' WHERE xref = ' . $db->Quote($r->xref_id);
					$db->setQuery($query);
					if (!$db->query()) {
						RedeventHelperLog::simpleLog('recurrence copying roles error: '.$db->getErrorMsg());
					}

					// copy the prices
					$query = ' INSERT INTO #__redevent_sessions_pricegroups (xref, pricegroup_id, price) '
					. ' SELECT '.$object->id.', pricegroup_id, price '
					. ' FROM #__redevent_sessions_pricegroups '
					. ' WHERE xref = ' . $db->Quote($r->xref_id);
					$db->setQuery($query);
					if (!$db->query()) {
						RedeventHelperLog::simpleLog('recurrence copying prices error: '.$db->getErrorMsg());
					}

					// update repeats table
					$query = ' INSERT INTO #__redevent_repeats '
					. ' SET xref_id = '. $db->Quote($object->id)
					. '   , recurrence_id = '. $db->Quote($r->recurrence_id)
					. '   , count = '. $db->Quote($next->count)
					;
					$db->setQuery($query);
					if (!$db->query()) {
						RedeventHelperLog::simpleLog('saving repeat error: '.$db->getErrorMsg());
					}
					//           echo "added xref $object->id / count $next->count";
					//           echo '<br>';
				}
				else {
					RedeventHelperLog::simpleLog('saving recurrence xref error: '.$db->getErrorMsg());
				}
				$next = RedeventHelperRecurrence::getnext($r->rrule, $next);
			}
			if (!$next)
			{
				// no more events to generate, we can disable the rule
				$query = ' UPDATE #__redevent_recurrences SET ended = 1 WHERE id = '. $db->Quote($r->recurrence_id);
				$db->setQuery($query);
				$db->query();
			}
		}
		return true;
	}

	/**
	 * transforms <br /> and <br> back to \r\n
	 *
	 * @param string $string
	 * @return string
	 */
	function br2break($string)
	{
		return preg_replace("=<br(>|([\s/][^>]*)>)\r?\n?=i", "\r\n", $string);
	}

	/**
	 * returns formated event duration.
	 *
	 * @param $event object having properties dates, enddates, times, endtimes
	 */
	function getEventDuration($event)
	{
		if (!redEVENTHelper::isValidDate($event->dates)) {
			return '-';
		}

		// all day events if start or end time is null or 00:00:00
		if (empty($event->times) || $event->times == '00:00:00' || empty($event->endtimes) || $event->endtimes == '00:00:00')
		{
			if (empty($event->enddates) || $event->enddates == '0000-00-00' || $event->enddates == $event->dates) // same day
			{
				return '1' . ' ' . JText::_('COM_REDEVENT_Day');
			}
			else
			{
				$days = floor((strtotime($event->enddates) - strtotime($event->dates)) / (3600 * 24)) + 1;
				return $days . ' ' . JText::_('COM_REDEVENT_Days');
			}
		}
		else // there is start and end times
		{
			$start = strtotime($event->dates. ' ' . $event->times);
			if (empty($event->enddates) || $event->enddates == '0000-00-00' || $event->enddates == $event->dates) // same day, return hours and minutes
			{
				$end = strtotime($event->dates. ' ' . $event->endtimes);
				$duration = $end - $start;
				return floor($duration / 3600) . JText::_('COM_REDEVENT_LOC_H') . sprintf('%02d', floor(($duration % 3600) / 60));
			}
			else // not same day, display in days
			{
				$days = floor((strtotime($event->enddates) - strtotime($event->dates)) / (3600 * 24)) + 1;
				return $days . ' ' . JText::_('COM_REDEVENT_Days');
			}
		}
	}

	/**
	 * returns indented event category options
	 *
	 * @param boolean show categories with no publish xref associated
	 * @param boolean show unpublished categories
	 * @param array   id of enabled categories
	 * @return array
	 */
	function getEventsCatOptions($show_empty = true, $show_unpublished = false, $enabled = false)
	{
		$db   = & JFactory::getDBO();

		if ($show_empty == false)
		{
			// select categories with events first
			$query = ' SELECT c.id '
			. ' FROM #__redevent_categories AS c '
			. ' INNER JOIN #__redevent_categories AS child ON child.lft BETWEEN c.lft AND c.rgt '
			. ' INNER JOIN #__redevent_event_category_xref AS xcat ON xcat.category_id = child.id '
			. ' INNER JOIN #__redevent_event_venue_xref AS x ON x.eventid = xcat.event_id '
			. ' WHERE x.published = 1 '
			. ' GROUP BY c.id '
			;
			$db->setQuery($query);

			$notempty = $db->loadResultArray();
			if (empty($notempty)) {
				return array();
			}
		}

		$query =  ' SELECT c.id, c.catname, (COUNT(parent.catname) - 1) AS depth '
		. ' FROM #__redevent_categories AS c '
		. ' INNER JOIN #__redevent_categories AS parent ON c.lft BETWEEN parent.lft AND parent.rgt '
		;

		$where = array();
		if ($show_empty == false)
		{
			$where[] = ' c.id IN (' . implode(', ', $notempty) . ')';
		}
		if (!$show_unpublished) {
			$where[] = ' c.published = 1 ';
		}
		if (count($where)) {
			$query .= ' WHERE ' . implode(' AND ', $where);
		}

		$query .= ' GROUP BY c.id ';
		$query .= ' ORDER BY c.ordering, c.lft ';

		$db->setQuery($query);

		$results = $db->loadObjectList();

		$options = array();
		foreach((array) $results as $cat)
		{
			$options[] = JHTML::_('select.option', $cat->id, str_repeat('&nbsp;', $cat->depth) . ' ' . $cat->catname, 'value', 'text', ($enabled ? !in_array($cat->id, $enabled) : false));
		}
		return $options;
	}

	/**
	 * returns indented venues category options
	 *
	 * @param boolean show venues categories with no published venue associated
	 * @param boolean show unpublished venues categories
	 * @return array
	 */
	function getVenuesCatOptions($show_empty = true, $show_unpublished = false)
	{
		$db   = JFactory::getDBO();

		$gids = JFactory::getUser()->getAuthorisedViewLevels();
		$gids = implode(',', $gids);

		if ($show_empty == false)
		{
			// select only categories with published venues
			$query = ' SELECT c.id '
			. ' FROM #__redevent_venues_categories AS c '
			. ' INNER JOIN #__redevent_venues_categories AS child ON child.lft BETWEEN c.lft AND c.rgt '
			. ' INNER JOIN #__redevent_venue_category_xref AS xcat ON xcat.category_id = child.id '
			. ' INNER JOIN #__redevent_venues AS v ON v.id = xcat.venue_id '
			. ' WHERE c.published = 1 '
			. '   AND c.access IN (' . $gids . ')'
			. ' GROUP BY c.id '
			;
			$db->setQuery($query);

			$cats = $db->loadResultArray();
			if (empty($cats)) {
				return array();
			}
		}
		else
		{
			// select only categories with published venues
			$query = ' SELECT c.id '
			. ' FROM #__redevent_venues_categories AS c '
			. ' WHERE c.published = 1 '
			. '   AND c.access IN (' . $gids . ')'
			. ' GROUP BY c.id '
			;
			$db->setQuery($query);

			$cats = $db->loadResultArray();
			if (empty($cats)) {
				return array();
			}
		}

		$query =  ' SELECT c.id, c.name, (COUNT(parent.id) - 1) AS depth '
		. ' FROM #__redevent_venues_categories AS c '
		. ' INNER JOIN #__redevent_venues_categories AS parent ON c.lft BETWEEN parent.lft AND parent.rgt '
		;

		$where = array();
		$where[] = ' c.id IN (' . implode(', ', $cats) . ')';

		if (!$show_unpublished) {
			$where[] = ' c.published = 1 ';
		}

		if (count($where)) {
			$query .= ' WHERE ' . implode(' AND ', $where);
		}

		$query .= ' GROUP BY c.id '
		. ' ORDER BY c.lft;'
		;
		$db->setQuery($query);

		$results = $db->loadObjectList();

		$options = array();

		foreach((array) $results as $cat)
		{
			$options[] = JHTML::_('select.option', $cat->id, str_repeat('&nbsp;', $cat->depth) . ' ' . $cat->name);
		}

		return $options;
	}

	function getCustomField($type)
	{
		require_once (JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_redevent'.DS.'customfield'.DS.'includes.php');

		if (class_exists('TCustomfield'.ucfirst($type))) {
			$class = 'TCustomfield'.ucfirst($type);
			return new $class();
		}

		switch ($type)
		{
			case 'select_multiple':
				return new TCustomfieldSelectmultiple();
				break;

			default:
				return new TCustomfieldTextbox();
				break;
		}
	}

	function renderFieldValue($field)
	{
		switch ($field->type)
		{
			case 'select_multiple':
			case 'checkbox':
				return str_replace("\n", "<br/>", $field->value);
			case 'textarea':
				return str_replace("\n", "<br/>", htmlspecialchars($field->value));
			case 'date':
				return strftime(($field->options ? $field->options : '%Y-%m-%d'), strtotime($field->value));
			case 'wysiwyg':
				return $field->value;
			case 'textbox':
			default:
				return htmlspecialchars($field->value);
		}
	}

	/**
	 * Check if the user can register to the specified xref.
	 *
	 * Returns an object with properties canregister and status
	 *
	 * @param $xref_id
	 * @param $user_id
	 * @return object (canregister, status)
	 */
	function canRegister($xref_id, $user_id = null)
	{
		if (!file_exists(JPATH_SITE.DS.'components'.DS.'com_redform'.DS.'redform.core.php')) {
			JError::raiseWarning(0,JText::_('COM_REDEVENT_REGISTRATION_NOT_ALLOWED_REDFORMCORE_NOT_FOUND'));
			$result->canregister = 0;
			$result->status = JText::_('COM_REDEVENT_REGISTRATION_NOT_ALLOWED_REDFORMCORE_NOT_FOUND');
			return $result;
		}

		$app =& JFactory::getApplication();
		$db = & JFactory::getDBO();
		$user = & JFactory::getUser($user_id);
		$result = new stdclass();
		$result->canregister = 1;

		$acl = UserAcl::getInstance();
		if ($acl->canManageAttendees($xref_id)) {
			return $result;
		}

		$query = ' SELECT x.dates, x.times, x.enddates, x.endtimes, x.maxattendees, x.maxwaitinglist, x.registrationend, e.registra, e.max_multi_signup '
		. ' FROM #__redevent_event_venue_xref AS x '
		. ' INNER JOIN #__redevent_events AS e ON x.eventid = e.id '
		. ' WHERE x.id='. $db->Quote($xref_id)
		;
		$db->setQuery($query);
		$event = & $db->loadObject();

		// we need to take into account the server offset into account for the registration dates
		$now = JFactory::getDate();
		$now->setOffset($app->getCfg('offset'));
		$now_unix = $now->toUnix('true');

		// first, let's check the thing that don't need database queries
		if (!$event->registra)
		{
			$result->canregister = 0;
			$result->status = JText::_('COM_REDEVENT_NO_REGISTRATION_FOR_THIS_EVENT');
			$result->error = 'noregistration';
			return $result;
		}
		else if (redEVENTHelper::isValidDate($event->registrationend))
		{
			if ( strtotime($event->registrationend) < $now_unix )
			{
				$result->canregister = 0;
				$result->status = JText::_('COM_REDEVENT_REGISTRATION_IS_OVER');
				$result->error = 'isover';
				return $result;
			}
		}
		else if (redEVENTHelper::isValidDate($event->dates) && strtotime($event->dates .' '. $event->times) < $now_unix)
		{
			// it's separated from previous case so that it is not checked if a registration end was set
			$result->canregister = 0;
			$result->status = JText::_('COM_REDEVENT_REGISTRATION_IS_OVER');
			$result->error = 'isover';
			return $result;
		}

		// now check the max registrations and waiting list
		if ($event->maxattendees)
		{
			// get places taken
			$q = "SELECT waitinglist, COUNT(id) AS total
			FROM #__redevent_register
			WHERE xref = ". $db->Quote($xref_id)."
			AND confirmed = 1
			AND cancelled = 0
			GROUP BY waitinglist";
			$db->setQuery($q);
			$res = $db->loadObjectList('waitinglist');
			$event->registered = (isset($res[0]) ? $res[0]->total : 0) ;
			$event->waiting = (isset($res[1]) ? $res[1]->total : 0) ;

			if ($event->maxattendees <= $event->registered && $event->maxwaitinglist <= $event->waiting)
			{
				$result->canregister = 0;
				$result->status = JText::_('COM_REDEVENT_EVENT_FULL');
				$result->error = 'isfull';
				return $result;
			}
		}

		// check if the user has pending unconfirm registration for the session
		if ($user->get('id'))
		{
			$q = "SELECT COUNT(r.id) AS total
			FROM #__redevent_register AS r
			WHERE r.xref = ". $db->Quote($xref_id) ."
			AND r.confirmed = 0
			AND r.uid = ". $db->Quote($user->get('id'))
			;
			$db->setQuery($q);
			$res = $db->loadResult();
			if ($res)
			{
				$result->canregister = 0;
				$result->status = JTEXT::_('COM_REDEVENT_REGISTRATION_NOT_ALLOWED_PENDING_UNCONFIRM_REGISTRATION');
				$result->error = 'haspending';
				return $result;
			}
		}


		// then the max registration per user
		if ($user->get('id'))
		{
			$q = "SELECT COUNT(r.id) AS total
			FROM #__redevent_register AS r
			WHERE r.xref = ". $db->Quote($xref_id) ."
			AND r.confirmed = 1
			AND r.cancelled = 0
			AND r.uid = ". $db->Quote($user->get('id')) ."
			";

			// if there is a submit key set, it means we are reviewing, so we need to discard this submit_key from the count.
			if (JRequest::getVar('submit_key')) {
				$q .= '  AND r.submit_key <> '. $db->Quote(JRequest::getVar('submit_key', ''));
			}
			$db->setQuery($q);
			$event->userregistered = $db->loadResult();

			// in case this is a review, user has already registered... but not finished yet.
			if ($event->userregistered && JRequest::getVar('event_task') == 'review') {
				$event->userregistered--;
			}
		}
		else
		{
			$event->userregistered = 0;
		}

		if ($event->userregistered >= ($event->max_multi_signup ? $event->max_multi_signup : 1) )
		{
			$result->canregister = 0;
			$result->status = JText::_('COM_REDEVENT_USER_MAX_REGISTRATION_REACHED');
			$result->error = 'usermax';
			return $result;
		}

		return $result;
	}

	function canUnregister($xref_id, $user_id = null)
	{
		$db = & JFactory::getDBO();
		$user = & JFactory::getUser($user_id);

		// if user is not logged, he can't unregister
		if (!$user->get('id')) {
			return false;
		}

		$query = ' SELECT x.dates, x.times, x.enddates, x.endtimes, x.registrationend, e.unregistra '
		. ' FROM #__redevent_event_venue_xref AS x '
		. ' INNER JOIN #__redevent_events AS e ON x.eventid = e.id '
		. ' WHERE x.id='. $db->Quote($xref_id)
		;
		$db->setQuery($query);
		$event = & $db->loadObject();

		// check if unregistration is allowed
		if (!$event->unregistra) {
			return false;
		}

		if (!empty($event->registrationend) && $event->registrationend != '0000-00-00 00:00:00')
		{
			if ( strtotime($event->registrationend) < time() )
			{
				// REGISTRATION IS OVER
				return false;
			}
		}
		else if (redEVENTHelper::isValidDate($event->dates) && strtotime($event->dates .' '. $event->times) < time())
		{
			// it's separated from previous case so that it is not checked if a registration end was set
			// REGISTRATION IS OVER
			return false;
		}

		return true;
	}

	/**
	 * this function is used to return the number of places left in event lists
	 *
	 * it requires the input object to have the properties registra, registrationend, dates, times, maxattendees, registered
	 *
	 * @param object xref
	 * @return string
	 */
	function getRemainingPlaces($xref)
	{
		// only display for events were registrations still open
		if (!$xref->registra) {
			return '-';
		}
		if (    (redEVENTHelper::isValidDate($xref->registrationend) && strtotime($xref->registrationend) < time())
		|| strtotime($xref->dates . ' ' . $xref->times) < time() )
		{
			return '-';
		}

		// if there is no limit...
		if (!$xref->maxattendees)
		{
			return '-';
		}
		return $xref->maxattendees - $xref->registered;
	}

	/**
	 * returns true if the event is over.
	 * object in parameters must include properties
	 *
	 * @param object $event
	 * @param boolean daycheck: if true, events are over only the next day, otherwise, use time too.
	 */
	function isOver($event, $day_check = true)
	{
		if (! (property_exists($event, 'dates') && property_exists($event, 'times')
		&& property_exists($event, 'enddates') && property_exists($event, 'endtimes') ) ) {
			throw new Exception('Missing object properties');
		}
		if (!redEVENTHelper::isValidDate($event->dates)) { // open dates
			return false;
		}

		$cmp = $day_check ? strtotime('today') : now();

		if (redEVENTHelper::isValidDate($event->enddates.' '.$event->endtimes)) {
			return strtotime($event->enddates.' '.$event->endtimes) < $cmp;
		}
		else {
			return strtotime($event->dates.' '.$event->times) < $cmp;
		}
	}

	/**
	 * returns array of timezones indexed by offset
	 *
	 * @return array
	 */
	function getTimeZones()
	{
		$timezones = array(
				'-12'=>'Pacific/Kwajalein',
				'-11'=>'Pacific/Samoa',
				'-10'=>'Pacific/Honolulu',
				'-9'=>'America/Juneau',
				'-8'=>'America/Los_Angeles',
				'-7'=>'America/Denver',
				'-6'=>'America/Mexico_City',
				'-5'=>'America/New_York',
				'-4'=>'America/Caracas',
				'-3.5'=>'America/St_Johns',
				'-3'=>'America/Argentina/Buenos_Aires',
				'-2'=>'Atlantic/Azores',// no cities here so just picking an hour ahead
				'-1'=>'Atlantic/Azores',
				'0'=>'Europe/London',
				'1'=>'Europe/Paris',
				'2'=>'Europe/Helsinki',
				'3'=>'Europe/Moscow',
				'3.5'=>'Asia/Tehran',
				'4'=>'Asia/Baku',
				'4.5'=>'Asia/Kabul',
				'5'=>'Asia/Karachi',
				'5.5'=>'Asia/Calcutta',
				'6'=>'Asia/Colombo',
				'7'=>'Asia/Bangkok',
				'8'=>'Asia/Singapore',
				'9'=>'Asia/Tokyo',
				'9.5'=>'Australia/Darwin',
				'10'=>'Pacific/Guam',
				'11'=>'Asia/Magadan',
				'12'=>'Asia/Kamchatka'
		);
		return $timezones;
	}

	/**
	 * returns timezone name from offset
	 * @param string $offset
	 * @return string
	 */
	function getTimeZone($offset)
	{
		$tz = self::getTimeZones();
		if (isset($tz[$offset])) {
			return $tz[$offset];
		}
		return false;
	}

	/**
	 * return true is a date is valid (not null, or 0000-00...)
	 *
	 * @param string $date
	 * @return boolean
	 */
	function isValidDate($date)
	{
		if (is_null($date)) {
			return false;
		}
		if ($date == '0000-00-00' || $date == '0000-00-00 00:00:00') {
			return false;
		}
		if (!strtotime($date)) {
			return false;
		}
		return true;
	}

	/**
	 * return session code from object
	 * @param object $session must contain xref, course_code
	 * @return string
	 */
	function getSessioncode($session)
	{
		return $session->course_code.'-'.$session->xref;
	}

	/**
	 * Build the select list for access level
	 *
	 * @TODO: adapt for 1.7 acl
	 */
	function getAccesslevelOptions()
	{
		$db =& JFactory::getDBO();

		$query = 'SELECT id AS value, title AS text'
		. ' FROM #__usergroups'
		. ' ORDER BY id'
		;
		$db->setQuery( $query );
		$groups = $db->loadObjectList();

		return $groups;
	}

	/**
	 * returns mime type of a file
	 *
	 * @param string file path
	 * @return string mime type
	 */
	function getMimeType($filename)
	{
		if (function_exists('finfo_open')) {
			$finfo = finfo_open(FILEINFO_MIME);
			$mimetype = finfo_file($finfo, $filename);
			finfo_close($finfo);
			return $mimetype;
		}
		else if (function_exists('mime_content_type') && 0)
		{
			return mime_content_type($filename);
		}
		else
		{
			$mime_types = array(

					'txt' => 'text/plain',
					'htm' => 'text/html',
					'html' => 'text/html',
					'php' => 'text/html',
					'css' => 'text/css',
					'js' => 'application/javascript',
					'json' => 'application/json',
					'xml' => 'application/xml',
					'swf' => 'application/x-shockwave-flash',
					'flv' => 'video/x-flv',

					// images
					'png' => 'image/png',
					'jpe' => 'image/jpeg',
					'jpeg' => 'image/jpeg',
					'jpg' => 'image/jpeg',
					'gif' => 'image/gif',
					'bmp' => 'image/bmp',
					'ico' => 'image/vnd.microsoft.icon',
					'tiff' => 'image/tiff',
					'tif' => 'image/tiff',
					'svg' => 'image/svg+xml',
					'svgz' => 'image/svg+xml',

					// archives
					'zip' => 'application/zip',
					'rar' => 'application/x-rar-compressed',
					'exe' => 'application/x-msdownload',
					'msi' => 'application/x-msdownload',
					'cab' => 'application/vnd.ms-cab-compressed',

					// audio/video
					'mp3' => 'audio/mpeg',
					'qt' => 'video/quicktime',
					'mov' => 'video/quicktime',

					// adobe
					'pdf' => 'application/pdf',
					'psd' => 'image/vnd.adobe.photoshop',
					'ai' => 'application/postscript',
					'eps' => 'application/postscript',
					'ps' => 'application/postscript',

					// ms office
					'doc' => 'application/msword',
					'rtf' => 'application/rtf',
					'xls' => 'application/vnd.ms-excel',
					'ppt' => 'application/vnd.ms-powerpoint',

					// open office
					'odt' => 'application/vnd.oasis.opendocument.text',
					'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
			);

			$ext = strtolower(array_pop(explode('.',$filename)));
			if (array_key_exists($ext, $mime_types)) {
				return $mime_types[$ext];
			}
			else {
				return 'application/octet-stream';
			}
		}
	}


	/**
	 * return initialized calendar tool class for ics export
	 *
	 * @return object
	 */
	function getCalendarTool()
	{
		require_once JPATH_SITE.DS.'components'.DS.'com_redevent'.DS.'classes'.DS.'iCalcreator.class.php';
		$mainframe = &JFactory::getApplication();

		$offset = (float) $mainframe->getCfg('offset');
		$timezone_name = self::getTimeZone($offset);

		$vcal = new vcalendar();                          // initiate new CALENDAR
		if (!file_exists(JPATH_SITE.DS.'cache'.DS.'com_redevent')) {
			jimport('joomla.filesystem.folder');
			JFolder::create(JPATH_SITE.DS.'cache'.DS.'com_redevent');
		}
		$vcal->setConfig('directory', JPATH_SITE.DS.'cache'.DS.'com_redevent');
		$vcal->setProperty('unique_id', 'events@'.$mainframe->getCfg('sitename'));
		$vcal->setProperty( "calscale", "GREGORIAN" );
		$vcal->setProperty( 'method', 'PUBLISH' );
		if ($timezone_name) {
			$vcal->setProperty( "X-WR-TIMEZONE", $timezone_name );
		}
		return $vcal;
	}

	function icalAddEvent(&$calendartool, $event)
	{
		require_once JPATH_SITE.DS.'components'.DS.'com_redevent'.DS.'classes'.DS.'iCalcreator.class.php';
		$mainframe = &JFactory::getApplication();
		$params = $mainframe->getParams('com_redevent');

		$offset = $params->get('ical_timezone', 1);
		$timezone_name = self::getTimeZone($offset);

		// get categories names
		$categories = array();
		foreach ($event->categories as $c) {
			$categories[] = $c->catname;
		}

		if (!$event->dates || $event->dates == '0000-00-00') {
			// no start date...
			return false;
		}
		// make end date same as start date if not set
		if (!$event->enddates || $event->enddates == '0000-00-00') {
			$event->enddates = $event->dates;
		}

		// start
		if (!preg_match('/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/',$event->dates, $start_date)) {
			JError::raiseError(0, JText::_('COM_REDEVENT_ICAL_EXPORT_WRONG_STARTDATE_FORMAT'));
		}
		$date = array('year' => (int) $start_date[1], 'month' => (int) $start_date[2], 'day' => (int) $start_date[3]);

		// all day event if start time is not set
		if ( !$event->times || $event->times == '00:00:00' ) // all day !
		{
			$dateparam = array('VALUE' => 'DATE');

			// for ical all day events, dtend must be send to the next day
			$event->enddates = strftime('%Y-%m-%d', strtotime($event->enddates.' +1 day'));

			if (!preg_match('/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/',$event->enddates, $end_date)) {
				JError::raiseError(0, JText::_('COM_REDEVENT_ICAL_EXPORT_WRONG_ENDDATE_FORMAT'));
			}
			$date_end = array('year' => $end_date[1], 'month' => $end_date[2], 'day' => $end_date[3]);
			$dateendparam = array('VALUE' => 'DATE');
		}
		else // not all day events, there is a start time
		{
			if (!preg_match('/([0-9]{2}):([0-9]{2}):([0-9]{2})/',$event->times, $start_time)) {
				JError::raiseError(0, JText::_('COM_REDEVENT_ICAL_EXPORT_WRONG_STARTTIME_FORMAT'));
			}
			$date['hour'] = $start_time[1];
			$date['min']  = $start_time[2];
			$date['sec']  = $start_time[3];
			$dateparam = array('VALUE' => 'DATE-TIME');
			if (!$params->get('ical_no_timezone', 0)) {
				$dateparam['TZID'] = $timezone_name;
			}

			if ( !$event->endtimes || $event->endtimes == '00:00:00' )
			{
				$event->endtimes = $event->times;
			}

			// if same day but end time < start time, change end date to +1 day
			if ($event->enddates == $event->dates && strtotime($event->dates.' '.$event->endtimes) < strtotime($event->dates.' '.$event->times)) {
				$event->enddates = strftime('%Y-%m-%d', strtotime($event->enddates.' +1 day'));
			}

			if (!preg_match('/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/',$event->enddates, $end_date)) {
				JError::raiseError(0, JText::_('COM_REDEVENT_ICAL_EXPORT_WRONG_ENDDATE_FORMAT'));
			}
			$date_end = array('year' => $end_date[1], 'month' => $end_date[2], 'day' => $end_date[3]);

			if (!preg_match('/([0-9]{2}):([0-9]{2}):([0-9]{2})/',$event->endtimes, $end_time)) {
				JError::raiseError(0, JText::_('COM_REDEVENT_ICAL_EXPORT_WRONG_STARTTIME_FORMAT'));
			}
			$date_end['hour'] = $end_time[1];
			$date_end['min']  = $end_time[2];
			$date_end['sec']  = $end_time[3];
			$dateendparam = array('VALUE' => 'DATE-TIME');
			if (!$params->get('ical_no_timezone', 0)) {
				$dateendparam['TZID'] = $timezone_name;
			}
		}
		$title = (isset($event->full_title) ? $event->full_title : $event->title);
		// item description text
		$description = $title.'\\n';
		$description .= JText::_('COM_REDEVENT_CATEGORY' ).': '.implode(', ', $categories).'\\n';
		//		if (isset($event->summary) && $event->summary) {
		//			$description .= $event->summary.'\\n';
		//		}

		// url link to event
		$link = JURI::base().RedeventHelperRoute::getDetailsRoute($event->slug, $event->xref);
		$link = JRoute::_( $link );
		$description .= JText::_( 'COM_REDEVENT_ICS_LINK' ).': '.$link.'\\n';
		if (!empty($event->icaldetails)) {
			$description .= $event->icaldetails;
		}

		// location
		$location = array();
		if (isset($event->icalvenue) && !empty($event->icalvenue)) {
			$location[] = $event->icalvenue;
		}
		else {
			$location[] = $event->venue;
			if (isset($event->street) && !empty($event->street)) {
				$location[] = $event->street;
			}
			if (isset($event->city) && !empty($event->city)) {
				$location[] = $event->city;
			}
			if (isset($event->countryname) && !empty($event->countryname)) {
				$exp = explode(",",$event->countryname);
				$location[] = $exp[0];
			}
		}
		$location = implode(",", $location);

		$e = new vevent();              // initiate a new EVENT
		$e->setProperty( 'summary', $title );           // title
		$e->setProperty( 'categories', implode(', ', $categories) );           // categorize
		$e->setProperty( 'dtstart', $date, $dateparam );
		if (count($date_end)) {
			$e->setProperty( 'dtend', $date_end, $dateendparam );
		}
		$e->setProperty( 'description', $description );    // describe the event
		$e->setProperty( 'location', $location ); // locate the event
		$e->setProperty( 'url', $link );
		$e->setProperty( 'uid', 'event'.$event->id.'-'.$event->xref.'@'.$mainframe->getCfg('sitename') );
		$calendartool->addComponent( $e );                    // add component to calendar
		return true;
	}

	/**
	 * Displays a calendar control field
	 *
	 * @param string  The date value
	 * @param string  The name of the text field
	 * @param string  The id of the text field
	 * @param string  The date format
	 * @param array Additional html attributes
	 */
	function calendar($value, $name, $id, $format = '%Y-%m-%d', $onClose = null, $attribs = null)
	{
		JHTML::_('behavior.calendar'); //load the calendar behavior

		if (is_array($attribs)) {
			$attribs = JArrayHelper::toString( $attribs );
		}
		$document =& JFactory::getDocument();
		$document->addScriptDeclaration('window.addEvent(\'domready\', function() {Calendar.setup({
		inputField     :    "'.$id.'",     // id of the input field
		ifFormat       :    "'.$format.'",      // format of the input field
		button         :    "'.$id.'_img",  // trigger for the calendar (button ID)
		align          :    "Tl",           // alignment (defaults to "Bl")
		onClose        :    '.($onClose ? $onClose : 'null').',
		singleClick    :    true
	});});');

		return '<input type="text" name="'.$name.'" id="'.$id.'" value="'.htmlspecialchars($value, ENT_COMPAT, 'UTF-8').'" '.$attribs.' />'.
		'<img class="calendar" src="'.JURI::root(true).'/templates/system/images/calendar.png" alt="calendar" id="'.$id.'_img" />';
	}

	/**
	 * generates the html for price group selection for redform
	 * @TODO doesn't work with multiple forms !!!
	 *
	 * @param array session pricegroups objects
	 * @param int selected pricegroup id
	 * @return string html
	 */
	function getRfPricesSelect($sessionpricegroups, $selected = null)
	{
		$layout = JComponentHelper::getParams('com_redevent')->get('price_select_layout', 'select');
		$html = array();
		if ($layout == 'radio')
		{
			$html[] = '<fieldset class="price-select">';
			foreach ((array)$sessionpricegroups as $i => $p)
			{
				$selected = $selected == null ? $p->pricegroup_id : $selected; // force at least one radio to be selected
				$html[] = '<input type="radio" name="pricegroup_id" value="'.$p->pricegroup_id.'" price="'.$p->price.'"'
				. 'id="pricegroup_id'.$i.'"'
				. ($p->pricegroup_id == $selected ? ' checked="checked"' : '')
				. '/>';

				$html[] = '<label for="pricegroup_id' . $i . '">'
				. $p->price.' ('.$p->name.')' . '</label>';
			}

			$html[] = '</fieldset>';
		}
		else
		{
			$html[] = '<select name="pricegroup_id">';
			foreach ((array)$sessionpricegroups as $p)
			{
				$html[] = '<option value="'.$p->pricegroup_id.'" price="'.$p->price.'"'.($p->pricegroup_id == $selected ? ' selected="selected"' : '').'>'.$p->price.' ('.$p->name.')'.'</option>';
			}
			$html[] = '</select>';
		}
		return implode($html);
	}

	/**
	 * writes a csv row
	 *
	 * @param array $fields
	 * @param string $delimiter
	 * @param string $enclosure
	 * @return string csv line
	 */
	function writecsvrow($fields, $delimiter = ',', $enclosure = '"')
	{
		$params = &JComponentHelper::getParams('com_redevent');

		$delimiter_esc = preg_quote($delimiter, '/');
		$enclosure_esc = preg_quote($enclosure, '/');

		$output = array();
		foreach ($fields as $field)
		{
			if ($params->get('csv_export_strip_linebreaks', 0)) {
				$field = str_replace(array("\r\n"), "", $field);
				$field = str_replace(array("\n"), "", $field);
			}
			$output[] = preg_match("/(?:${delimiter_esc}|${enclosure_esc}|\s)/", $field) ? (
			$enclosure . str_replace($enclosure, $enclosure . $enclosure, $field) . $enclosure
			) : $field;
}

return join($delimiter, $output) . "\n";
}

/**
 * returns html for user selector
 *
 * @param string $field_name
 * @param int $selected
 * @return string
 */
function getUserSelector($field_name, $selected)
{
	$app = &JFactory::getApplication();
	$document = &JFactory::getDocument();
	$db = &JFactory::getDBO();
	$user = &JFactory::getUser($selected);

	JHTML::_('behavior.mootools');
	$document->addScript(JURI::base().'components/com_redevent/assets/js/selectuser.js');
	//		echo '<pre>';print_r(JURI::base().'components/com_redevent/assets/selectuser.js'); echo '</pre>';exit;

	$link = 'index.php?option=com_redevent&amp;task=selectuser&amp;tmpl=component&field='.$field_name;

	$field  = '<input type="text" readonly="readonly" name="'.$field_name.'_name" id="'.$field_name.'_name" value="'.$user->get('username').'"/>';
	$field .= '<input type="hidden" name="'.$field_name.'" id="'.$field_name.'" value="'.$user->get('id').'"/>';
	$field .= "<a class=\"modal\" class=\"re-selectuserbutton\" title=\"".JText::_('COM_REDEVENT_SELECT_USER')."\" href=\"$link\" rel=\"{handler: 'iframe', size: {x: 650, y: 500}}\">".JText::_('COM_REDEVENT_SELECT_USER')."</a>\n";

	return $field;
}

/**
 * return the valid columns for frontend display
 *
 * @param array $columns
 * @return array $columns
 */
function validateColumns($columns)
{
	$db = &JFactory::getDBO();

	$columns = array_map('strtolower', $columns);
	$columns = array_map('trim', $columns);

	$allowed = array('date',
			'title',
			'venue',
			'state',
			'city',
			'category',
			'picture',
			'registrationend',
			'places',
			'placesleft',
			'price',
			'credits',
			'country',
			'countryflag',
	);

	$query = 'SELECT CONCAT("custom", f.id) FROM #__redevent_fields AS f WHERE f.published = 1';
	$db->setQuery($query);
	if ($res = $db->loadResultArray()) {
		$allowed = array_merge($allowed, $res);
	}

	return array_intersect($columns, $allowed);
}

/**
 * returns submit_key associated to attendee id
 *
 * @param int $attendee_id
 * @return string key
 */
function getAttendeeSubmitKey($attendee_id)
{
	$db = &JFactory::getDBO();

	$query = ' SELECT submit_key '
	. ' FROM #__redevent_register '
	. ' WHERE id = ' . $db->Quote($attendee_id);
	$db->setQuery($query);
	$res = $db->loadResult();
	return $res;
}

/**
 * returns sid associated to attendee id
 *
 * @param int $attendee_id
 * @return int sid
 */
function getAttendeeSid($attendee_id)
{
	$db = &JFactory::getDBO();

	$query = ' SELECT sid '
	. ' FROM #__redevent_register '
	. ' WHERE id = ' . $db->Quote($attendee_id);
	$db->setQuery($query);
	$res = $db->loadResult();
	return $res;
}


/**
 * Check registration expiration delay, and cleans up registrations accordingly
 *
 * @return boolean true on success
 */
function registrationexpiration()
{
	$settings = JComponentHelper::getParams('com_redevent');
	if (!$settings->get('registration_expiration', 0)) {
		// nothing to do
		return true;
	}

	$db = &JFactory::getDBO();

	// get expired registrations
	$query = ' SELECT r.id as attendee_id, r.xref, r.uregdate '
	. ' FROM #__redevent_register AS r '
	. ' INNER JOIN #__rwf_submitters AS s ON r.sid = s.id '
	. ' LEFT JOIN #__rwf_payment AS p ON p.submit_key = s.submit_key AND p.paid > 0 '
	. ' WHERE DATEDIFF(NOW(), r.paymentstart) >= '. $settings->get('registration_expiration', 0)
	. '   AND s.price > 0 '
	. '   AND r.confirmed = 1 AND r.cancelled = 0 AND r.waitinglist = 0 '
	. '   AND p.id IS NULL '
	. ' GROUP BY r.id '
	// 		. ' ORDER BY r.uregdate DESC '
	;
	$db->setQuery($query);
	$res = $db->loadObjectList();

	if (!$res || !count($res)) {
		return true;
	}

	$xrefs = array();
	$exp_ids = array();
	foreach ($res as $exp)
	{
		$xrefs[] = $exp->xref;
		$exp_ids[] = $exp->attendee_id;
	}
	$xrefs = array_unique($xrefs);

	// change registrations as cancelled
	$query = ' UPDATE #__redevent_register AS r '
	. '   SET r.cancelled = 1 '
	. ' WHERE r.id IN ('.implode(', ', $exp_ids).')'
	;
	$db->setQuery( $query );

	if (!$db->query()) {
		echo JText::_('COM_REDEVENT_CLEANUP_ERROR_CANCELLING_EXPIRED_REGISTRATION');
		return false;
	}

	// then update waiting list of corresponding sessions
	require_once(JPATH_BASE.DS.'administrator'.DS.'components'.DS.'com_redevent'.DS.'models'.DS.'waitinglist.php');
	foreach ($xrefs as $xref)
	{
		$model = JModel::getInstance('waitinglist', 'RedeventModel');
		$model->setXrefId($xref);
		$model->UpdateWaitingList();
	}

	return true;
}
}
