<?php
/**
 * @package    Redevent.Library
 * @copyright  Copyright (C) 2008 - 2020 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Holds some usefull functions to keep the code a bit cleaner
 *
 * @TODO: split in more specialized classes !
 *
 * @package  Redevent.Library
 * @since    2.5
 */
class RedeventHelper
{
	/**
	 * Pulls settings from database and stores in an static object
	 *
	 * @return object
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
	 * Performs daily scheduled cleanups
	 *
	 * Currently it archives and removes outdated events
	 * and takes care of the recurrence of events
	 *
	 * @param   int  $forced  force cleanup
	 *
	 * @return boolean
	 */
	public static function cleanup($forced = 0)
	{
		$db = JFactory::getDBO();

		$params = self::config();

		JPluginHelper::importPlugin('redevent');
		$dispatcher = JDispatcher::getInstance();

		$now = time();
		$cronfile = JPATH_COMPONENT . '/recron.txt';

		if (file_exists($cronfile))
		{
			$lastupdate = file_get_contents($cronfile);
		}
		else
		{
			$lastupdate = 0;
		}

		// Number of days since last update?
		$nrdaysnow = floor($now / 86400);
		$nrdaysupdate = floor($lastupdate / 86400);

		if ($nrdaysnow > $nrdaysupdate || $forced)
		{
			$limit_date = strftime('%Y-%m-%d', time() - $params->get('pastevents_delay', 3) * 3600 * 24);

			$recurrenceHelper = new RedeventRecurrenceHelper;
			$recurrenceHelper->generaterecurrences();

			// Date filtering
			$where = array('x.dates IS NOT NULL');

			switch ($params->get('pastevents_reference_date', 'end'))
			{
				case 'start':
					$where[] = ' DATEDIFF(' . $db->Quote($limit_date) . ', x.dates) >= 0 ';
					break;

				case 'registration':
					$where[] = ' DATEDIFF(' . $db->Quote($limit_date) . ', (IF (x.registrationend, x.registrationend, x.dates))) >= 0 ';
					break;

				case 'end':
					$where[] = ' DATEDIFF(' . $db->Quote($limit_date) . ', (IF (x.enddates, x.enddates, x.dates))) >= 0 ';
					break;
			}

			$where_date = implode(' AND ', $where);

			// Delete outdated events
			if ($params->get('pastevents_action', 0) == 1)
			{
				// Lists event_id for which we are going to delete xrefs
				$query = $db->getQuery(true)
					->select('x.eventid')
					->from('#__redevent_event_venue_xref')
					->where($where_date);

				$db->setQuery($query);
				$event_ids = $db->loadColumn();

				// If we deleted some sessions, check if we now have events without sesssion, and take actions accordingly
				if (count($event_ids))
				{
					$query = $db->getQuery(true)
						->delete('#__redevent_event_venue_xref')
						->where($where_date);

					$db->setQuery($query);

					if (!$db->execute())
					{
						RedeventHelperLog::simpleLog('CLEANUP Error while deleting old xrefs: ' . $db->getErrorMsg());
					}

					// Now delete the events with no more xref
					if ($params->get('pastevents_events_action', 1))
					{
						$model = RModel::getAdminInstance('events');

						if (!$model->delete($event_ids))
						{
							RedeventHelperLog::simpleLog('CLEANUP Error while deleting old events with no more xrefs: ' . $model->getError());
						}
					}
				}
			}

			// Set state archived of outdated events
			if ($params->get('pastevents_action', 0) == 2)
			{
				// Lists xref_id and associated event_id for which we are going to be archived
				$query = $db->getQuery(true)
					->select('x.id, x.eventid')
					->from('#__redevent_event_venue_xref AS x')
					->where($where_date)
					->where('x.published > -1');

				$db->setQuery($query);
				$xrefs = $db->loadObjectList();

				// If we deleted some sessions, check if we now have events without sesssion, and take actions accordingly
				if (!empty($xrefs))
				{
					// Build list of xref and corresponding events
					$event_ids = array();
					$xref_ids  = array();

					foreach ($xrefs AS $xref)
					{
						$event_ids[] = $db->Quote($xref->eventid);
						$xref_ids[]  = $db->Quote($xref->id);
					}

					// Filter duplicates
					$event_ids = array_unique($event_ids);

					// Update xref to archive
					$query = $db->getQuery(true)
						->update('#__redevent_event_venue_xref AS x')
						->set('x.published = -1')
						->where('x.id IN (' . implode(', ', $xref_ids) . ')');

					$db->setQuery($query);

					if (!$db->execute())
					{
						RedeventHelperLog::simpleLog('CLEANUP Error while archiving old xrefs: ' . $db->getErrorMsg());
					}

					$dispatcher->trigger('onEventCleanArchived', array($xrefs));

					if ($params->get('pastevents_events_action', 1))
					{
						// Update events to archive (if no more published xref)
						$query = $db->getQuery(true)
							->update('#__redevent_events AS e')
							->join('LEFT', '#__redevent_event_venue_xref AS x ON x.eventid = e.id AND x.published <> -1')
							->set('e.published = -1')
							->where('x.id IS NULL')
							->where('e.id IN (' . implode(', ', $event_ids) . ')');

						$db->setQuery($query);

						if (!$db->execute())
						{
							RedeventHelperLog::simpleLog('CLEANUP Error while archiving events with only archived xrefs: ' . $db->getErrorMsg());
						}
					}
				}
			}

			// Update recron file with latest update
			JFile::write($cronfile, $now);
		}

		return true;
	}

	/**
	 * returns formatted event duration.
	 *
	 * @param   object  $event  object having properties dates, enddates, times, endtimes
	 *
	 * @return string
	 *
	 * @deprecated
	 */
	public static function getEventDuration($event)
	{
		return RedeventHelperDate::getEventDuration($event);
	}

	/**
	 * returns indented event category options
	 *
	 * @param   boolean  $show_empty        show categories with no publish xref associated
	 * @param   boolean  $show_unpublished  show unpublished categories
	 * @param   boolean  $enabled           id of enabled categories
	 * @param   integer  $root              id of root category
	 *
	 * @return array
	 */
	public static function getEventsCatOptions($show_empty = true, $show_unpublished = false, $enabled = false, $root = null)
	{
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();

		$gids = JFactory::getUser()->getAuthorisedViewLevels();
		$gids = implode(',', $gids);

		if ($show_empty == false)
		{
			// Select categories with events first
			$query = $db->getQuery(true);
			$query->select('c.id')
				->from('#__redevent_categories AS c')
				->join('INNER', '#__redevent_categories AS child ON child.lft BETWEEN c.lft AND c.rgt')
				->join('INNER', '#__redevent_event_category_xref AS xcat ON xcat.category_id = child.id')
				->join('INNER', '#__redevent_event_venue_xref AS x ON x.eventid = xcat.event_id')
				->where('x.published = 1')
				->group('c.id');

			$db->setQuery($query);

			$notempty = $db->loadColumn();

			if (empty($notempty))
			{
				return array();
			}
		}

		$query = $db->getQuery(true);
		$query->select('c.id, c.name AS name, (COUNT(parent.name) - 1) AS depth')
			->from('#__redevent_categories AS c')
			->join('INNER', '#__redevent_categories AS parent ON c.lft BETWEEN parent.lft AND parent.rgt')
			->where('c.access IN (' . $gids . ')')
			->group('c.id')
			->order('c.ordering, c.lft');

		if ($show_empty == false)
		{
			$query->where('c.id IN (' . implode(', ', $notempty) . ')');
		}

		if (!$show_unpublished)
		{
			$query->where('c.published = 1');
		}

		if ($app->getLanguageFilter())
		{
			$query->where('(c.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ') OR c.language IS NULL)');
		}

		if ($root)
		{
			$rootCategory = RedeventEntityCategory::load($root);
			$query->where('(c.lft BETWEEN ' . $rootCategory->lft . ' AND ' . $rootCategory->rgt . ')');
		}

		$db->setQuery($query);

		$results = $db->loadObjectList();

		$options = array();

		foreach ((array) $results as $cat)
		{
			$options[] = JHTML::_(
				'select.option',
				$cat->id,
				str_repeat('&nbsp;', $cat->depth) . ' ' . $cat->name,
				'value', 'text', ($enabled ? !in_array($cat->id, $enabled) : false)
			);
		}

		return $options;
	}

	/**
	 * returns indented venues category options
	 *
	 * @param   boolean  $show_empty        show venues categories with no published venue associated
	 * @param   boolean  $show_unpublished  show unpublished venues categories
	 *
	 * @return array
	 */
	public static function getVenuesCatOptions($show_empty = true, $show_unpublished = false)
	{
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();

		$gids = array_unique(JFactory::getUser()->getAuthorisedViewLevels());
		$gids = implode(',', $gids);

		$query = $db->getQuery(true)
			->select('c.id')
			->from('#__redevent_venues_categories AS c')
			->where('c.published = 1')
			->where('c.access IN (' . $gids . ')')
			->group('c.id');

		if ($show_empty == false)
		{
			// Select only categories with published venues
			$query->join('INNER', '#__redevent_venues_categories AS child ON child.lft BETWEEN c.lft AND c.rgt')
				->join('INNER', '#__redevent_venue_category_xref AS xcat ON xcat.category_id = child.id')
				->join('INNER', '#__redevent_venues AS v ON v.id = xcat.venue_id');
		}

		$db->setQuery($query);
		$cats = $db->loadColumn();

		if (empty($cats))
		{
			return array();
		}

		$query = $db->getQuery(true)
			->select('c.id, c.name, (COUNT(parent.id) - 1) AS depth')
			->from('#__redevent_venues_categories AS c')
			->join('INNER', '#__redevent_venues_categories AS parent ON c.lft BETWEEN parent.lft AND parent.rgt')
			->where('c.id IN (' . implode(', ', $cats) . ')')
			->group('c.id')
			->order('c.lft');

		if (!$show_unpublished)
		{
			$query->where('c.published = 1');
		}

		if ($app->getLanguageFilter())
		{
			$query->where('(c.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ') OR c.language IS NULL)');
		}

		$db->setQuery($query);

		$results = $db->loadObjectList();

		$options = array();

		foreach ((array) $results as $cat)
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
	 * @param   int  $xref_id  session id
	 * @param   int  $user_id  user id
	 *
	 * @return object (canregister, status)
	 */
	public static function canRegister($xref_id, $user_id = null)
	{
		$user_id = $user_id ?: JFactory::getUser()->id;
		$helper = new RedeventRegistrationCanregister($xref_id);

		return $helper->canRegister($user_id);
	}

	/**
	 * Check if the user can unregister to the specified xref.
	 *
	 * @param   int  $xref_id  session id
	 * @param   int  $user_id  user id
	 *
	 * @return boolean
	 */
	public static function canUnregister($xref_id, $user_id = null)
	{
		$db = JFactory::getDBO();
		$user = JFactory::getUser($user_id);

		// If user is not logged, he can't unregister
		if (!$user->get('id'))
		{
			return false;
		}

		$query = $db->getQuery(true);

		$query->select('x.allday, x.dates, x.times, x.enddates, x.endtimes, x.registrationend, e.unregistra')
			->from('#__redevent_event_venue_xref AS x')
			->join('INNER', '#__redevent_events AS e ON x.eventid = e.id')
			->where('x.id = ' . $db->Quote($xref_id));

		$db->setQuery($query);
		$session = $db->loadObject();

		// Check if unregistration is allowed
		if (!$session->unregistra)
		{
			return false;
		}

		if (!empty($session->registrationend) && $session->registrationend != '0000-00-00 00:00:00')
		{
			if (strtotime($session->registrationend) < time())
			{
				// REGISTRATION IS OVER
				return false;
			}
		}
		elseif (RedeventHelperDate::isValidDate($session->dates) && strtotime($session->dates . ' ' . $session->times) < time())
		{
			// It's separated from previous case so that it is not checked if a registration end was set
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
	 * @param   object  $session  session data
	 *
	 * @return string
	 */
	public static function getRemainingPlaces($session)
	{
		// Only display for events were registrations still open
		if (!$session->registra)
		{
			return '-';
		}

		if ((RedeventHelperDate::isValidDate($session->registrationend) && strtotime($session->registrationend) < time())
			|| strtotime($session->dates . ' ' . $session->times) < time())
		{
			return '-';
		}

		// If there is no limit...
		if (!$session->maxattendees)
		{
			return '-';
		}

		return $session->maxattendees - $session->registered;
	}

	/**
	 * returns true if the session is over.
	 * object in parameters must include properties
	 *
	 * @param   object  $session    event data
	 * @param   bool    $day_check  daycheck: if true, events are over only the next day, otherwise, use time too.
	 *
	 * @return boolean
	 *
	 * @deprecated
	 */
	public static function isOver($session, $day_check = true)
	{
		return RedeventHelperDate::isOver($session, $day_check);
	}

	/**
	 * return true is a date is valid (not null, or 0000-00...)
	 *
	 * @param   string  $date  date string from db
	 *
	 * @return boolean
	 */
	public static function isValidDate($date)
	{
		if (is_null($date))
		{
			return false;
		}

		if ($date == '0000-00-00' || $date == '0000-00-00 00:00:00')
		{
			return false;
		}

		if (!strtotime($date))
		{
			return false;
		}

		return true;
	}

	/**
	 * return true is a date is valid (not null, or 0000-00...)
	 *
	 * @param   string  $time  time string from db
	 *
	 * @return boolean
	 */
	public static function isValidTime($time)
	{
		if (is_null($time))
		{
			return false;
		}

		return preg_match('/[0-2]*[0-9]:[0-5][0-9](:[0-5][0-9])*/', $time);
	}

	/**
	 * return session code from object
	 *
	 * @param   object  $session  must contain xref, course_code
	 *
	 * @return string
	 */
	public static function getSessioncode($session)
	{
		return $session->course_code . '-' . $session->xref;
	}

	/**
	 * returns mime of a file
	 *
	 * @param   string  $filename  file path
	 *
	 * @return string mime
	 */
	public static function getMime($filename)
	{
		$finfo = finfo_open(FILEINFO_MIME);
		$mimetype = finfo_file($finfo, $filename);
		finfo_close($finfo);

		return $mimetype;
	}

	/**
	 * returns mime type of a file
	 *
	 * @param   string  $filename  file path
	 *
	 * @return string mime type
	 */
	public static function getMimeType($filename)
	{
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$mimetype = finfo_file($finfo, $filename);
		finfo_close($finfo);

		return $mimetype;
	}

	/**
	 * return initialized calendar tool class for ics export
	 *
	 * @return object
	 */
	public static function getCalendarTool()
	{
		require_once JPATH_SITE . '/components/com_redevent/classes/iCalcreator.class.php';

		$mainframe = JFactory::getApplication();
		$cachePath = JPATH_SITE . '/cache/com_redevent';

		$timezone_name = $mainframe->getCfg('offset');

		// Initiate new CALENDAR
		$vcal = new vcalendar;

		if (!file_exists($cachePath))
		{
			jimport('joomla.filesystem.folder');
			JFolder::create($cachePath);
		}

		$vcal->setConfig('directory', $cachePath);
		$vcal->setProperty('unique_id', 'events@' . $mainframe->getCfg('sitename'));
		$vcal->setProperty("calscale", "GREGORIAN");
		$vcal->setProperty('method', 'PUBLISH');

		if ($timezone_name)
		{
			$vcal->setProperty("X-WR-TIMEZONE", $timezone_name);
		}

		return $vcal;
	}

	/**
	 * Add event to ical
	 *
	 * @param   vcalendar  $calendartool  calendar object
	 * @param   object     $session       session data
	 *
	 * @return boolean
	 */
	public static function icalAddEvent(&$calendartool, $session)
	{
		require_once JPATH_SITE . '/components/com_redevent/classes/iCalcreator.class.php';

		$mainframe = JFactory::getApplication();
		$params = $mainframe->getParams('com_redevent');

		$timezone_name = $params->get('ical_timezone', 'Europe/London');

		// Get categories names
		$categories = array();

		foreach ($session->categories as $c)
		{
			$categories[] = $c->name;
		}

		if (!RedeventHelperDate::isValidDate($session->dates))
		{
			// No start date...
			return false;
		}

		// Make end date same as start date if not set
		if (!RedeventHelperDate::isValidDate($session->enddates))
		{
			$session->enddates = $session->dates;
		}

		// Start
		if (!preg_match('/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/', $session->dates, $start_date))
		{
			throw new RuntimeException(JText::_('COM_REDEVENT_ICAL_EXPORT_WRONG_STARTDATE_FORMAT'));
		}

		$date = array('year' => (int) $start_date[1], 'month' => (int) $start_date[2], 'day' => (int) $start_date[3]);

		// All day event if start time is not set
		if ($session->allday)
		{
			// All day !
			$dateparam = array('VALUE' => 'DATE');

			// For ical all day events, dtend must be send to the next day
			$session->enddates = strftime('%Y-%m-%d', strtotime($session->enddates . ' +1 day'));

			if (!preg_match('/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/', $session->enddates, $end_date))
			{
				throw new RuntimeException(JText::_('COM_REDEVENT_ICAL_EXPORT_WRONG_ENDDATE_FORMAT'));
			}

			$date_end = array('year' => $end_date[1], 'month' => $end_date[2], 'day' => $end_date[3]);
			$dateendparam = array('VALUE' => 'DATE');
		}
		else
		{
			// Not all day events, there is a start time
			if (!preg_match('/([0-9]{2}):([0-9]{2}):([0-9]{2})/', $session->times, $start_time))
			{
				throw new RuntimeException(JText::_('COM_REDEVENT_ICAL_EXPORT_WRONG_STARTTIME_FORMAT'));
			}

			$date['hour'] = $start_time[1];
			$date['min']  = $start_time[2];
			$date['sec']  = $start_time[3];
			$dateparam = array('VALUE' => 'DATE-TIME');

			if (!$params->get('ical_no_timezone', 0))
			{
				$dateparam['TZID'] = $timezone_name;
			}

			if (!$session->endtimes || $session->endtimes == '00:00:00')
			{
				$session->endtimes = $session->times;
			}

			// If same day but end time < start time, change end date to +1 day
			if ($session->enddates == $session->dates
				&& strtotime($session->dates . ' ' . $session->endtimes) < strtotime($session->dates . ' ' . $session->times))
			{
				$session->enddates = strftime('%Y-%m-%d', strtotime($session->enddates . ' +1 day'));
			}

			if (!preg_match('/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/', $session->enddates, $end_date))
			{
				throw new RuntimeException(JText::_('COM_REDEVENT_ICAL_EXPORT_WRONG_ENDDATE_FORMAT'));
			}

			$date_end = array('year' => $end_date[1], 'month' => $end_date[2], 'day' => $end_date[3]);

			if (!preg_match('/([0-9]{2}):([0-9]{2}):([0-9]{2})/', $session->endtimes, $end_time))
			{
				throw new RuntimeException(JText::_('COM_REDEVENT_ICAL_EXPORT_WRONG_STARTTIME_FORMAT'));
			}

			$date_end['hour'] = $end_time[1];
			$date_end['min']  = $end_time[2];
			$date_end['sec']  = $end_time[3];
			$dateendparam = array('VALUE' => 'DATE-TIME');

			if (!$params->get('ical_no_timezone', 0))
			{
				$dateendparam['TZID'] = $timezone_name;
			}
		}

		$title = static::getSessionFullTitle($session);

		// Item description text
		$description = $title . '\\n';
		$description .= JText::_('COM_REDEVENT_CATEGORY') . ': ' . implode(', ', $categories) . '\\n';

		// Url link to event
		$link = JURI::base() . RedeventHelperRoute::getDetailsRoute($session->slug, $session->xref);
		$link = JRoute::_($link);
		$description .= JText::_('COM_REDEVENT_ICS_LINK') . ': ' . $link . '\\n';

		if (!empty($session->icaldetails))
		{
			$description .= $session->icaldetails;
		}

		// Location
		$location = array();

		if (isset($session->icalvenue) && !empty($session->icalvenue))
		{
			$location[] = $session->icalvenue;
		}
		else
		{
			$location[] = $session->venue;

			if (isset($session->street) && !empty($session->street))
			{
				$location[] = $session->street;
			}

			if (isset($session->city) && !empty($session->city))
			{
				$location[] = $session->city;
			}

			if (isset($session->countryname) && !empty($session->countryname))
			{
				$exp = explode(",", $session->countryname);
				$location[] = $exp[0];
			}
		}

		$location = implode(",", $location);

		// Initiate a new EVENT
		$e = new vevent;
		$e->setProperty('summary', $title);
		$e->setProperty('categories', implode(', ', $categories));
		$e->setProperty('dtstart', $date, $dateparam);

		if (count($date_end))
		{
			$e->setProperty('dtend', $date_end, $dateendparam);
		}

		$e->setProperty('description', $description);
		$e->setProperty('location', $location);
		$e->setProperty('url', $link);
		$e->setProperty('uid', 'event' . $session->id . '-' . $session->xref . '@' . $mainframe->getCfg('sitename'));
		$calendartool->addComponent($e);

		return true;
	}

	/**
	 * Displays a calendar control field
	 *
	 * @param   string  $value    The date value
	 * @param   string  $name     The name of the text field
	 * @param   string  $id       The id of the text field
	 * @param   string  $format   The date format
	 * @param   string  $onClose  on close code
	 * @param   array   $attribs  Additional html attributes
	 *
	 * @return string
	 */
	public static function calendar($value, $name, $id, $format = '%Y-%m-%d', $onClose = null, $attribs = null)
	{
		// Load the calendar behavior
		JHTML::_('behavior.calendar');

		if (is_array($attribs))
		{
			$attribs = JArrayHelper::toString($attribs);
		}

		$document = JFactory::getDocument();
		$document->addScriptDeclaration(
			'window.addEvent(\'domready\', function() {Calendar.setup({
			inputField     :    "' . $id . '",     // id of the input field
			ifFormat       :    "' . $format . '",      // format of the input field
			button         :    "' . $id . '_img",  // trigger for the calendar (button ID)
			align          :    "Tl",           // alignment (defaults to "Bl")
			onClose        :    ' . ($onClose ? $onClose : 'null') . ',
			singleClick    :    true
		});});'
		);

		return '<input type="text" name="' . $name . '" id="' . $id . '" value="'
			. htmlspecialchars($value, ENT_COMPAT, 'UTF-8') . '" ' . $attribs . ' />' .
			'<img class="calendar" src="' . JURI::root(true) . '/templates/system/images/calendar.png" alt="calendar" id="' . $id . '_img" />';
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
	 * @param   object  $pricegroup  the pricegroups object (price, currency, form_currency)
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
	 * @param   array   $fields     fields to write
	 * @param   string  $delimiter  delimiter
	 * @param   string  $enclosure  enclosure
	 *
	 * @return string csv line
	 */
	public static function writecsvrow($fields, $delimiter = ',', $enclosure = '"')
	{
		$params = static::config();

		$delimiterEsc = preg_quote($delimiter, '/');
		$enclosureEsc = preg_quote($enclosure, '/');

		$output = array();

		foreach ($fields as $field)
		{
			if ($params->get('csv_export_strip_linebreaks', 0))
			{
				$field = str_replace(array("\r\n"), "", $field);
				$field = str_replace(array("\n"), "", $field);
			}

			$output[] = preg_match("/(?:${delimiterEsc}|${enclosureEsc}|\s)/", $field) ? (
				$enclosure . str_replace($enclosure, $enclosure . $enclosure, $field) . $enclosure
				) : $field;
		}

		return join($delimiter, $output) . "\n";
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
					'enddate',
					'session_code',
					'registration',
			);
		}

		if ($customs)
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('CONCAT("custom", f.id)')
				->from('#__redevent_fields AS f')
				->where('f.published = 1');

			$db->setQuery($query);

			if ($res = $db->loadColumn())
			{
				$allowed = array_merge($allowed, $res);
			}
		}

		return array_intersect($columns, $allowed);
	}

	/**
	 * returns submit_key associated to attendee id
	 *
	 * @param   int  $attendee_id  attendee id
	 *
	 * @return string key
	 */
	public static function getAttendeeSubmitKey($attendee_id)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('submit_key')
			->from('#__redevent_register')
			->where('id = ' . $db->Quote($attendee_id));

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
		$settings = static::config();

		if (!$settings->get('registration_expiration', 0))
		{
			// Nothing to do
			return true;
		}

		// Get expired registrations
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('r.id as attendee_id, r.xref, r.uregdate')
			->from('#__redevent_register AS r')
			->join('INNER', '#__rwf_submitters AS s ON r.sid = s.id')
			->join('LEFT', '#__rwf_payment_request AS pr ON pr.id = s.id AND pr.paid > 0')
			->where('DATEDIFF(NOW(), r.paymentstart) >= ' . $settings->get('registration_expiration', 0))
			->where('s.price > 0')
			->where('r.confirmed = 1')
			->where('r.cancelled = 0')
			->where('r.waitinglist = 0')
			->where('pr.id IS NULL')
			->group('r.id');

		$db->setQuery($query);
		$res = $db->loadObjectList();

		if (!$res || !count($res))
		{
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

		// Change registrations as cancelled
		$query = $db->getQuery(true)
			->update('#__redevent_register AS r')
			->set('r.cancelled = 1')
			->where('r.id IN (' . implode(', ', $exp_ids) . ')');

		$db->setQuery($query);

		if (!$db->execute())
		{
			echo JText::_('COM_REDEVENT_CLEANUP_ERROR_CANCELLING_EXPIRED_REGISTRATION');

			return false;
		}

		// Then update waiting list of corresponding sessions
		require_once JPATH_BASE . '/administrator/components/com_redevent/models/waitinglist.php';

		foreach ($xrefs as $xref)
		{
			$model = JModel::getInstance('waitinglist', 'RedeventModel');
			$model->setXrefId($xref);
			$model->updateWaitingList();
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
	public static function ajaxSortColumn($title, $order, $direction = 'asc', $selected = '', $task = null, $new_direction = 'asc')
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

		$html = '<a href="#" ordercol="' . $order . '" orderdir="' . $direction . '" class="ajaxsortcolumn hasTooltip" title="'
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
		$config = static::config();

		$event_title = isset($object->event_title) ? $object->event_title : $object->title;

		if ($config->get('disable_frontend_session_title', 0))
		{
			return $event_title;
		}

		if (isset($object->session_title) && $object->session_title)
		{
			return $event_title . ' - ' . $object->session_title;
		}
		else
		{
			return $event_title;
		}
	}

	/**
	 * Generate unique id from registration data
	 *
	 * @param   object  $data  data
	 *
	 * @return string
	 *
	 * @throws Exception
	 */
	public static function getRegistrationUniqueId($data)
	{
		if (!is_object($data)
			|| !isset($data->course_code)
			|| !isset($data->xref)
			|| !isset($data->attendee_id))
		{
			throw new InvalidArgumentException('Cannot generate registration unique id from data');
		}

		return $data->course_code . '-' . $data->xref . '-' . $data->attendee_id;
	}

	/**
	 * Return Roles types
	 *
	 * @return mixed
	 */
	public static function getRolesOptions()
	{
		$db = JFactory::getDbo();

		$query = $db->getQuery(true);

		$query->select('id AS value, name AS text')
			->from('#__redevent_roles')
			->order('ordering ASC');

		$db->setQuery($query);
		$res = $db->loadObjectList();

		return $res;
	}

	/**
	 * Return price groups names
	 *
	 * @return mixed
	 */
	public static function getPricegroupsOptions()
	{
		$db = JFactory::getDbo();

		$query = $db->getQuery(true);

		$query->select('id AS value, name AS text')
			->from('#__redevent_pricegroups')
			->order('ordering ASC');

		$db->setQuery($query);
		$res = $db->loadObjectList();

		return $res;
	}

	/**
	 * returns all custom fields for event object
	 *
	 * @param   int  $published  filter by published state
	 *
	 * @return array
	 */
	public static function getEventCustomFields($published = 1)
	{
		static $fields;

		if (empty($fields))
		{
			$fields = self::getCustomFields('redevent.event', $published);
		}

		return $fields;
	}

	/**
	 * returns all custom fields for session object
	 *
	 * @param   int  $published  filter by published state
	 *
	 * @return array
	 */
	public static function getSessionCustomFields($published = 1)
	{
		static $fields;

		if (empty($fields))
		{
			$fields = self::getCustomFields('redevent.xref', $published);
		}

		return $fields;
	}

	/**
	 * Get custom fields
	 *
	 * @param   string  $object_key  filter fields by type of object
	 * @param   int     $published   filter by published state
	 *
	 * @return RedeventAbstractCustomfield
	 */
	protected static function getCustomFields($object_key = null, $published = 1)
	{
		if ($object_key && !in_array($object_key, array('redevent.event', 'redevent.xref')))
		{
			throw new RuntimeException('Unknown Custom field object key');
		}

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('f.*');
		$query->from('#__redevent_fields AS f');

		$query->where('f.published = ' . (int) $published);

		if ($object_key)
		{
			$query->where('f.object_key = ' . $db->Quote($object_key));
		}

		$query->order('f.ordering ASC');

		$db->setQuery($query);

		if (!$rows = $db->loadObjectList())
		{
			return array();
		}

		$fields = array_map(
			function ($row)
			{
				$field = RedeventFactoryCustomfield::getField($row->type);
				$field->bind($row);

				return $field;
			},
			$rows
		);

		return $fields;
	}
}
