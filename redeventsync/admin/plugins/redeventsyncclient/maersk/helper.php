<?php
/**
 * @package     Redcomponent.redeventsync
 * @subpackage  plugins.redeventsyncclient
 * @copyright   Copyright (C) 2013 redCOMPONENT.com
 * @license     GNU General Public License version 2 or later
 */

class RedeventsyncclientMaerskHelper
{
	/**
	 * Get user associated to email
	 *
	 * @param   string  $email  user email
	 *
	 * @return mixed object user id and firstname/lastname or false if not found
	 *
	 * @throws Exception
	 */
	public static function getUser($email)
	{
		if (!$email || !JMailHelper::isEmailAddress($email))
		{
			throw new InvalidEmailException('Empty or invalid email');
		}

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('u.id');
		$query->select('rm.rm_firstname, rm.rm_lastname');
		$query->from('#__users AS u');
		$query->join('LEFT', '#__redmember_users AS rm ON rm.user_id = u.id');
		$query->where('u.email = ' . $db->quote($email));

		$db->setQuery($query);
		$user = $db->loadObject();

		return $user;
	}

	/**
	 * return session details
	 *
	 * @param   string  $session_code  session code
	 * @param   string  $venue_code    venue code
	 *
	 * @return object
	 *
	 * @throws Exception
	 */
	public static function getSessionDetails($session_code, $venue_code)
	{
		if (!$session_code)
		{
			throw new Exception('Session code is required');
		}

		if (!$venue_code)
		{
			throw new Exception('Venue code is required');
		}

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('x.id AS session_id');
		$query->select('e.redform_id');
		$query->from('#__redevent_event_venue_xref AS x');
		$query->join('INNER', '#__redevent_events AS e ON e.id = x.eventid');
		$query->join('INNER', '#__redevent_venues AS v on v.id = x.venueid');
		$query->where('x.session_code = ' . $db->quote($session_code));
		$query->where('v.venue_code = ' . $db->quote($venue_code));

		$db->setQuery($query);
		$res = $db->loadObject();

		if (!$res)
		{
			throw new Exception(sprintf('Session not found (%s @ %s)', $session_code, $venue_code));
		}

		return $res;
	}

	/**
	 * returns attendee info
	 *
	 * @param   int  $attendee_id  attendee id
	 *
	 * @return object
	 */
	public static function getAttendee($attendee_id)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('r.*');
		$query->select('x.session_code');
		$query->select('v.venue_code');
		$query->select('e.redform_id');
		$query->select('u.email');
		$query->from('#__redevent_register AS r');
		$query->join('INNER', '#__redevent_event_venue_xref AS x on x.id = r.xref');
		$query->join('INNER', '#__redevent_events AS e on e.id = x.eventid');
		$query->join('INNER', '#__redevent_venues AS v on v.id = x.venueid');
		$query->join('LEFT', '#__users AS u on u.id = r.uid');
		$query->where('r.id = ' . $db->quote($attendee_id));

		$db->setQuery($query);
		$attendee = $db->loadObject();

		return $attendee;
	}

	/**
	 * Tries to find an attendee by email and session code
	 *
	 * @param   string  $email         user email
	 * @param   string  $session_code  session code
	 * @param   string  $venue_code    venue code
	 *
	 * @return object
	 */
	public static function findAttendee($email, $session_code, $venue_code)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('r.*');
		$query->from('#__redevent_register AS r');
		$query->join('INNER', '#__users AS u on u.id = r.uid');
		$query->join('INNER', '#__redevent_event_venue_xref AS x on x.id = r.xref');
		$query->join('INNER', '#__redevent_venues AS v on v.id = x.venueid');
		$query->where('u.email = ' . $db->quote($email));
		$query->where('x.session_code = ' . $db->quote($session_code));
		$query->where('v.venue_code = ' . $db->quote($venue_code));
		$query->where('r.cancelled = 0');

		$db->setQuery($query);
		$res = $db->loadObject();

		return $res;
	}
}
