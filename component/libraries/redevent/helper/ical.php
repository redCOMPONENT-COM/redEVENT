<?php
/**
 * @package    Redevent.Library
 * @copyright  Copyright (C) 2008 - 2020 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Helper class for iCal
 *
 * @package  Redevent.Library
 * @since    3.1
 */
class RedeventHelperIcal
{
	/**
	 * @var vcalendar
	 */
	private $calendar;

	/**
	 * return initialized calendar tool class for ics export
	 *
	 * @param   string  $uniqueId  unique id for calendar
	 */
	public function __construct($uniqueId)
	{
		require_once JPATH_SITE . '/components/com_redevent/classes/iCalcreator.class.php';

		$app = JFactory::getApplication();
		$cachePath = JPATH_SITE . '/cache/com_redevent';

		$timezone_name = $app->getCfg('offset');

		// Initiate new CALENDAR
		$this->calendar = new vcalendar;

		if (!file_exists($cachePath))
		{
			jimport('joomla.filesystem.folder');
			JFolder::create($cachePath);
		}

		$this->calendar->setConfig('directory', $cachePath);
		$this->calendar->setProperty('unique_id', 'events@' . $app->getCfg('sitename'));
		$this->calendar->setProperty("calscale", "GREGORIAN");
		$this->calendar->setProperty('method', 'PUBLISH');

		if ($timezone_name)
		{
			$this->calendar->setProperty("X-WR-TIMEZONE", $timezone_name);
		}

		$this->calendar->setProperty('unique_id', $uniqueId);
	}

	/**
	 * Add session to ical
	 *
	 * @param   RedeventEntitySession  $session  session data
	 *
	 * @return boolean
	 */
	public function addSession($session)
	{
		$app = JFactory::getApplication();
		$params = $app->getParams('com_redevent');

		$timezone_name = $params->get('ical_timezone', 'Europe/London');

		// Get categories names
		$categories = array();

		foreach ($session->getEvent()->getCategories() as $c)
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

		$title = $session->getFullTitle();

		// Item description text
		$description = $title . '\\n';
		$description .= JText::_('COM_REDEVENT_CATEGORY') . ': ' . implode(', ', $categories) . '\\n';

		// Url link to event
		$link = JURI::base() . RedeventHelperRoute::getDetailsRoute($session->getEvent()->slug, $session->slug);
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
			$venue = $session->getVenue();

			$location[] = $venue->venue;

			if (!empty($venue->street))
			{
				$location[] = $venue->street;
			}

			if (!empty($venue->city))
			{
				$location[] = $venue->city;
			}

			if (!empty($venue->countryname))
			{
				$exp = explode(",", $venue->countryname);
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
		$e->setProperty('uid', 'event' . $session->eventid . '-' . $session->id . '@' . $app->getCfg('sitename'));

		$this->calendar->addComponent($e);

		return true;
	}

	/**
	 * Save to path
	 *
	 * @param   string  $path  path to save to
	 *
	 * @return boolean true on success
	 */
	public function write($path)
	{
		$dir = dirname($path);
		$file = basename($path);

		if ($this->calendar->saveCalendar($dir, $file))
		{
			return true;
		}

		return false;
	}
}
