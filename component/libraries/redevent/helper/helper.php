<?php
/**
 * @package    Redevent.Library
 * @copyright  Copyright (C) 2008 - 2014 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 *
 * Holds some usefull functions to keep the code a bit cleaner
 *
 * @package  Redevent.Library
 * @since    2.5
*/
class redEVENTHelper
{
	/**
	 * Pulls settings from database and stores in an static object
	 *
	 * @return object
	 * @since 0.9
	 */
	public static function config()
	{
		$params = JComponentHelper::getParams('com_redevent');

		// See if there are any plugins that wish to alter the configuration (client specific demands !)
		JPluginHelper::importPlugin('redevent_config');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onGetRedeventConfig', array(&$params));

		return $params;
	}

	/**
	 * Performs dayly scheduled cleanups
	 *
	 * Currently it archives and removes outdated events
	 * and takes care of the recurrence of events
	 *
	 * @since 0.9
	 */
	public static function cleanup($forced = 0)
	{
		$db			= JFactory::getDBO();

		$elsettings = RedeventHelper::config();
		$params = JComponentHelper::getParams('com_redevent');

		$now 		= time();
		$cronfile   = JPATH_COMPONENT . '/recron.txt';

		if (file_exists($cronfile))
		{
			$lastupdate = file_get_contents($cronfile);
		}
		else
		{
			$lastupdate = 0;
		}

		//last update later then 24h?
		//$difference = $now - $lastupdate;

		//if ( $difference > 86400 ) {

		//better: new day since last update?
		$nrdaysnow = floor($now / 86400);
		$nrdaysupdate = floor($lastupdate / 86400);

		if ($nrdaysnow > $nrdaysupdate || $forced)
		{
			$nulldate = '0000-00-00';
			$limit_date = strftime('%Y-%m-%d', time() - $params->get('pastevents_delay', 3) * 3600 * 24);

			RedeventHelper::generaterecurrences();

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

				// If we deleted some sessions, check if we now have events without sesssion, and take actions accordingly
				if (count($event_ids))
				{
					$query = ' DELETE x FROM #__redevent_event_venue_xref AS x '
					. ' WHERE '. $where_date;
					;
					$db->SetQuery( $query );
					if (!$db->Query()) {
						RedeventHelperLog::simpleLog('CLEANUP Error while deleting old xrefs: '. $db->getErrorMsg());
					}

					// now delete the events with no more xref
					if ($params->get('pastevents_events_action', 1))
					{
						require_once(JPATH_COMPONENT_ADMINISTRATOR . '/models/events.php');
						$model = JModel::getInstance('events', 'redeventmodel');
						if (!$model->delete($event_ids))
						{
							RedeventHelperLog::simpleLog('CLEANUP Error while deleting old events with no more xrefs: '. $model->getError());
						}
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

				// If we deleted some sessions, check if we now have events without sesssion, and take actions accordingly
				if (!empty($xrefs))
				{
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
			}

			// update recron file with latest update
			JFile::write($cronfile, $now);
		}
	}

	/**
	 * adds xref repeats to the database.
	 *
	 * @return bool true on success
	 */
	public static function generaterecurrences($recurrence_id = null)
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
					$query = ' INSERT INTO #__redevent_sessions_pricegroups (xref, pricegroup_id, price, currency) '
					. ' SELECT '.$object->id.', pricegroup_id, price, currency '
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
	public static function br2break($string)
	{
		return preg_replace("=<br(>|([\s/][^>]*)>)\r?\n?=i", "\r\n", $string);
	}

	/**
	 * returns formated event duration.
	 *
	 * @param $event object having properties dates, enddates, times, endtimes
	 */
	public static function getEventDuration($event)
	{
		if (!RedeventHelper::isValidDate($event->dates)) {
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
	public static function getEventsCatOptions($show_empty = true, $show_unpublished = false, $enabled = false)
	{
		$app = JFactory::getApplication();
		$db  = JFactory::getDBO();

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

		if ($app->getLanguageFilter())
		{
			$where[] = '(c.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ') OR c.language IS NULL)';
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
	public static function getVenuesCatOptions($show_empty = true, $show_unpublished = false)
	{
		$app = JFactory::getApplication();
		$db  = JFactory::getDBO();

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

		if ($app->getLanguageFilter())
		{
			$where[] = '(c.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ') OR c.language IS NULL)';
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

	/**
	 * Check if the user can register to the specified xref.
	 *
	 * Returns an object with properties canregister and status
	 *
	 * @param $xref_id
	 * @param $user_id
	 * @return object (canregister, status)
	 */
	public static function canRegister($xref_id, $user_id = null)
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

		$acl = RedeventUserAcl::getInstance();
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
		else if (RedeventHelper::isValidDate($event->registrationend))
		{
			if ( strtotime($event->registrationend) < $now_unix )
			{
				$result->canregister = 0;
				$result->status = JText::_('COM_REDEVENT_REGISTRATION_IS_OVER');
				$result->error = 'isover';
				return $result;
			}
		}
		else if (RedeventHelper::isValidDate($event->dates) && strtotime($event->dates .' '. $event->times) < $now_unix)
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

	public static function canUnregister($xref_id, $user_id = null)
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
		else if (RedeventHelper::isValidDate($event->dates) && strtotime($event->dates .' '. $event->times) < time())
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
	public static function getRemainingPlaces($xref)
	{
		// only display for events were registrations still open
		if (!$xref->registra) {
			return '-';
		}
		if (    (RedeventHelper::isValidDate($xref->registrationend) && strtotime($xref->registrationend) < time())
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
	public static function isOver($event, $day_check = true)
	{
		if (! (property_exists($event, 'dates') && property_exists($event, 'times')
		&& property_exists($event, 'enddates') && property_exists($event, 'endtimes') ) ) {
			throw new Exception('Missing object properties');
		}
		if (!RedeventHelper::isValidDate($event->dates)) { // open dates
			return false;
		}

		$cmp = $day_check ? strtotime('today') : now();

		if (RedeventHelper::isValidDate($event->enddates.' '.$event->endtimes)) {
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
	public static function getTimeZones()
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
	public static function getTimeZone($offset)
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
	public static function isValidDate($date)
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
	public static function getSessioncode($session)
	{
		return $session->course_code.'-'.$session->xref;
	}

	/**
	 * Build the select list for access level
	 *
	 * @TODO: adapt for 1.7 acl
	 */
	public static function getAccesslevelOptions()
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
	public static function getMimeType($filename)
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
	public static function getCalendarTool()
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

	public static function icalAddEvent(&$calendartool, $event)
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
		$title = RedeventHelper::getSessionFullTitle($event);
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
	public static function calendar($value, $name, $id, $format = '%Y-%m-%d', $onClose = null, $attribs = null)
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
	 * Generates the html for price group selection for redform
	 *
	 * @TODO doesn't work with multiple forms !!!
	 *
	 * @param   array  $sessionpricegroups  session pricegroups objects
	 * @param   int    $selected            selected session_pricegroup id
	 *
	 * @return string html
	 */
	public static function getRfPricesSelect($sessionpricegroups, $selected = null)
	{
		$layout = JComponentHelper::getParams('com_redevent')->get('price_select_layout', 'select');
		$html = array();

		JHtml::_('behavior.mootools');
		$document = JFactory::getDocument();
		$document->addScript(JURI::root() . 'media/com_redevent/js/updateformprice.js');

		if ($layout == 'radio')
		{
			$html[] = '<fieldset class="price-select">';

			foreach ((array) $sessionpricegroups as $i => $p)
			{
				$selected = $selected == null ? $p->id : $selected; // force at least one radio to be selected
				$html[] = '<input type="radio" name="sessionpricegroup_id" value="' . $p->id . '" price="' . $p->price . '"'
				. ' currency="' . $p->currency . '"'
				. ' id="sessionpricegroup_id' . $i . '"'
				. ($p->id == $selected ? ' checked="checked"' : '')
				. ' class="updateCurrency"'
				. '/>';

				$html[] = '<label for="sessionpricegroup_id' . $i . '">'
					. $p->currency . ' ' . $p->price . ' (' . $p->name . ')' . '</label>';
			}

			$html[] = '</fieldset>';
		}
		else
		{
			$html[] = '<select name="sessionpricegroup_id" class="updateCurrency">';

			foreach ((array) $sessionpricegroups as $p)
			{
				$price = self::convertPrice($p->price, $p->currency, $p->form_currency);

				$html[] = '<option value="' . $p->id . '"
					price="'  . $p->price . '"'
					. ' currency="' . $p->currency . '"'
					. ($p->id == $selected ? ' selected="selected"' : '') . '>'
					. $p->currency . ' ' . $p->price . ' (' . $p->name . ')'
					. '</option>';
			}

			$html[] = '</select>';
		}

		return implode($html);
	}

	/**
	 * Convert a price between 2 currencies
	 *
	 * @param   float   $amount        the amount to convert
	 * @param   string  $currencyFrom  the currency code to convert from
	 * @param   string  $currencyTo    the currency code to convert to
	 *
	 * @return float converted price
	 */
	public static function convertPrice($amount, $currencyFrom, $currencyTo)
	{
		if ($currencyFrom == $currencyTo || !$amount)
		{
			return $amount;
		}

		JPluginHelper::importPlugin('currencyconverter');
		$dispatcher = JDispatcher::getInstance();

		$price = false;
		$dispatcher->trigger('onCurrencyConvert', array($amount, $currencyFrom, $currencyTo, &$price));

		return $price;
	}

	/**
	 * Get the price associated to a session price group in form currency
	 *
	 * @param   object   $pricegroup   the pricegroups object (price, currency, form_currency)
	 *
	 * @return float converted price
	 */
	public static function getFormCurrencyPrice($pricegroup)
	{
		return self::convertPrice($pricegroup->price, $pricegroup->currency, $pricegroup->form_currency);
	}

	/**
	 * writes a csv row
	 *
	 * @param array $fields
	 * @param string $delimiter
	 * @param string $enclosure
	 * @return string csv line
	 */
	public static function writecsvrow($fields, $delimiter = ',', $enclosure = '"')
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
	public static function getUserSelector($field_name, $selected)
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
	 * @param   array    $columns  columns to filter
	 * @param   array    $allowed  allowed static columns, overrides default list
	 * @param   boolean  $customs  allow custom fields columns
	 *
	 * @return array $columns
	 */
	public static function validateColumns($columns, $allowed = null, $customs = true)
	{
		$db = &JFactory::getDBO();

		$columns = array_map('strtolower', $columns);
		$columns = array_map('trim', $columns);

		if (!$allowed)
		{
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
		}

		if ($customs)
		{
			$query = 'SELECT CONCAT("custom", f.id) FROM #__redevent_fields AS f WHERE f.published = 1';
			$db->setQuery($query);

			if ($res = $db->loadResultArray())
			{
				$allowed = array_merge($allowed, $res);
			}
		}

		return array_intersect($columns, $allowed);
	}

	/**
	 * returns submit_key associated to attendee id
	 *
	 * @param int $attendee_id
	 * @return string key
	 */
	public static function getAttendeeSubmitKey($attendee_id)
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
	public static function getAttendeeSid($attendee_id)
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
	public static function registrationexpiration()
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

	/**
	 * Method to sort a column in a grid
	 *
	 * @param   string  $title          The link title
	 * @param   string  $order          The order field for the column
	 * @param   string  $direction      The current direction
	 * @param   string  $selected       The selected ordering
	 * @param   string  $task           An optional task override
	 * @param   string  $new_direction  An optional direction for the new column
	 *
	 * @return  string
	 *
	 * @since   11.1
	 */
	public static function ajaxSortColumn($title, $order, $direction = 'asc', $selected = 0, $task = null, $new_direction = 'asc')
	{
		$direction = strtolower($direction);
		$index = intval($direction == 'desc');

		if ($order != $selected)
		{
			$direction = $new_direction;
		}
		else
		{
			$direction = ($direction == 'desc') ? 'asc' : 'desc';
		}

		$html = '<a href="#" ordercol="' . $order . '" orderdir="' . $direction . '" class="ajaxsortcolumn" title="'
		. JText::_('JGLOBAL_CLICK_TO_SORT_THIS_COLUMN') . '">';
		$html .= JText::_($title);

		if ($order == $selected)
		{
			$iclass = array('icon-chevron-up', 'icon-chevron-down');
			$html .= ' <i class="' . $iclass[$index] . '"></i>';
		}

		$html .= '</a>';

		return $html;
	}


	/**
	 * get attendee status icon
	 *
	 * @param   int  $status  current status value
	 *
	 * @return string
	 */
	public static function getStatusIcon($status)
	{
		$status = (int) $status;
		switch ($status)
		{
			case 1:
				$src = 'status_passed.png';
				$tip = JText::_('COM_REDEVENT_ATTENDEE_STATUS_PASSED');
				break;

			case 2:
				$src = 'status_failed.png';
				$tip = JText::_('COM_REDEVENT_ATTENDEE_STATUS_FAILED');
				break;

			case 3:
				$src = 'status_dnp.png';
				$tip = JText::_('COM_REDEVENT_ATTENDEE_STATUS_DNP');
				break;

			case 0:
			default:
				$src = 'status_na.png';
				$tip = JText::_('COM_REDEVENT_ATTENDEE_STATUS_NA');
				break;
		}

		$options = array('class' => 'hasTip statusicon');
		$options['title'] = JText::_('COM_REDEVENT_STATUS_ICON_TITLE');
		$options['rel'] = $tip;
		$options['current'] = $status;
		$img = JHtml::image('media/com_redevent/images/' . $src, $src, $options);

		return $img;
	}

	/**
	 * returns full title for session (event + session title if exists)
	 *
	 * @param   object  $object  object containing title/event_title and session_title
	 *
	 * @return string
	 */
	public static function getSessionFullTitle($object)
	{
		$event_title = isset($object->event_title) ? $object->event_title : $object->title;

		if (isset($object->session_title) && $object->session_title)
		{
			return $event_title . ' - ' . $object->session_title;
		}
		else
		{
			return $event_title;
		}

	}
}
