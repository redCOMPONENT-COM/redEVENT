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
	 * @return mixed object user with redmember data or false if not found
	 *
	 * @throws Exception
	 */
	public static function getUser($email)
	{
		if (!$email || !JMailHelper::isEmailAddress($email))
		{
			throw new PlgresyncmaerskExceptionInvalidemail('Empty or invalid email');
		}

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('u.id');
		$query->from('#__users AS u');
		$query->where('u.email = ' . $db->quote($email));

		$db->setQuery($query);
		$user_id = $db->loadResult();

		if ($user_id)
		{
			$user = redmemberlib::getUserData($user_id);

			return $user;
		}

		return false;
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
		$query->select('p.date AS payment_date, p.gateway AS payment_gateway, p.status AS payment_status, p.data AS payment_data, p.paid');
		$query->from('#__redevent_register AS r');
		$query->join('INNER', '#__redevent_event_venue_xref AS x ON x.id = r.xref');
		$query->join('INNER', '#__redevent_events AS e ON e.id = x.eventid');
		$query->join('INNER', '#__redevent_venues AS v ON v.id = x.venueid');
		$query->join('LEFT', '#__rwf_payment AS p ON p.submit_key = r.submit_key AND p.paid = 1');
		$query->join('LEFT', '#__users AS u ON u.id = r.uid');
		$query->where('r.id = ' . $db->quote($attendee_id));

		$db->setQuery($query);
		$attendee = $db->loadObject();

		if ($attendee)
		{
			$attendee->redmember = redmemberlib::getUserData($attendee->uid);
		}

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

	/**
	 * Parse payment part of request.
	 *
	 * @param   SimpleXMLElement  $xml  xml object
	 *
	 * @return array
	 */
	public static function parsePayment(SimpleXMLElement $xml)
	{
		if (isset($xml->Payment))
		{
			$payment = array();

			if (isset($xml->Payment->Plateform))
			{
				$payment['plateform'] = (string) $xml->Payment->Plateform;
			}

			$payment['transactionId'] = (string) $xml->Payment->TransactionId;

			if (isset($xml->Payment->OrderId))
			{
				$payment['orderId'] = (string) $xml->Payment->OrderId;
			}

			if (isset($xml->Payment->CvrNo))
			{
				$payment['cvrNo'] = (string) $xml->Payment->CvrNo;
			}

			return $payment;
		}

		return null;
	}

	/**
	 * Records a payment
	 *
	 * @param   string  $submit_key  submit key
	 * @param   array   $payment     payment array
	 *
	 * @return true on success
	 */
	public static function recordPayment($submit_key, $payment)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->insert('#__rwf_payment');
		$query->set('submit_key = ' . $db->q($submit_key));
		$query->set('gateway = ' . $db->q('custom'));
		$query->set('status = ' . $db->q('Completed'));

		$data = array();

		foreach ($payment as $k => $v)
		{
			$data[] = $k . ':' . $v;
		}

		$query->set('data = ' . $db->q(implode("\n", $data)));

		$db->setQuery($query);

		return $db->execute();
	}
}
