<?php
/**
 * @package     Redcomponent.redeventsync
 * @subpackage  com_redeventsync
 * @copyright   Copyright (C) 2013 redCOMPONENT.com
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die();

require_once 'abstractmessage.php';

/**
 * redEVENT sync Sessionsrq Model
 *
 * @package  RED.redeventsync
 * @since    2.5
 */
class RedeventsyncModelSessionsrq extends RedeventsyncModelAbstractmessage
{
	/**
	 * process CreateAttendeeRQ request
	 *
	 * @param   SimpleXMLElement  $xml  xml data for the object
	 *
	 * @return boolean
	 */
	protected function processCreateSessionRQ(SimpleXMLElement $xml)
	{
		require_once JPATH_ADMINISTRATOR . '/components/com_redevent/tables/redevent_eventvenuexref.php';

		$transaction_id = (int) $xml->TransactionId;

		try
		{
			$object = $this->parseSessionXml($xml);
			$row = JTable::getInstance('RedEvent_eventvenuexref', '');

			if (!$row->bind($object))
			{
				throw new Exception($row->getError());
			}

			if (!($row->check() && $row->store()))
			{
				throw new Exception($row->getError());
			}

			// Log
			$this->log(REDEVENTSYNC_LOG_DIRECTION_INCOMING, $transaction_id,
				$xml, 'ok');

			// Response
			$response = new SimpleXMLElement('<SessionRS/>');
			$response->addChild('TransactionId', $transaction_id);
			$response->addChild('Success', '');
			$this->addResponse($response);

			// Log
			$this->log(REDEVENTSYNC_LOG_DIRECTION_OUTGOING, $transaction_id,
				$response, 'ok');
		}
		catch (Exception $e)
		{
			// Log
			$this->log(REDEVENTSYNC_LOG_DIRECTION_INCOMING, $transaction_id,
				$xml, 'error');

			$response = new SimpleXMLElement('<SessionRS/>');
			$response->addChild('TransactionId', $transaction_id);

			$errors = new SimpleXMLElement('<Errors/>');
			$errors->addChild('Error', $e->getMessage());
			$this->appendElement($response, $errors);

			$this->addResponse($response);

			// Log
			$this->log(REDEVENTSYNC_LOG_DIRECTION_OUTGOING, $transaction_id,
				$response, 'error');

			return false;
		}

		return true;
	}

	/**
	 * process CreateAttendeeRQ request
	 *
	 * @param   SimpleXMLElement  $xml  xml data for the object
	 *
	 * @return boolean
	 */
	protected function processModifySessionRQ(SimpleXMLElement $xml)
	{
		require_once JPATH_ADMINISTRATOR . '/components/com_redevent/tables/redevent_eventvenuexref.php';

		$transaction_id = (int) $xml->TransactionId;

		try
		{
			$object = $this->parseSessionXml($xml);
			$row = JTable::getInstance('RedEvent_eventvenuexref', '');

			if (!$object->id)
			{
				throw new Exception('session not found');
			}

			if (!$row->bind($object))
			{
				throw new Exception($row->getError());
			}

			if (!($row->check() && $row->store()))
			{
				throw new Exception($row->getError());
			}

			// Log
			$this->log(REDEVENTSYNC_LOG_DIRECTION_INCOMING, $transaction_id,
				$xml, 'ok');
		}
		catch (Exception $e)
		{
			$response = new SimpleXMLElement('<SessionRS/>');
			$response->addChild('TransactionId', $transaction_id);

			$errors = new SimpleXMLElement('<Errors/>');
			$errors->addChild('Error', $e->getMessage());
			$this->appendElement($response, $errors);

			$this->addResponse($response);

			// Log
			$this->log(REDEVENTSYNC_LOG_DIRECTION_OUTGOING, $transaction_id,
				$response, 'error');

			return false;
		}

		$response = new SimpleXMLElement('<SessionRS/>');
		$response->addChild('TransactionId', $transaction_id);
		$response->addChild('Success', '');

		$this->addResponse($response);

		// Log
		$this->log(REDEVENTSYNC_LOG_DIRECTION_OUTGOING, $transaction_id,
			$response, 'ok');

		return true;
	}

	/**
	 * process DeleteSession request
	 *
	 * @param   SimpleXMLElement  $xml  xml data for the object
	 *
	 * @return boolean
	 */
	protected function processDeleteSessionRQ(SimpleXMLElement $xml)
	{
		require_once JPATH_ADMINISTRATOR . '/components/com_redevent/models/session.php';

		$transaction_id = (int) $xml->TransactionId;

		try
		{
			$code = (string) $xml->SessionCode;
			$id = $this->getSessionId($code);

			if (!$id)
			{
				throw new Exception('session not found');
			}

			$row = JModel::getInstance('Session', 'RedeventModel');

			if (!$row->removexref($id))
			{
				throw new Exception($row->getError());
			}

			// Log
			$this->log(REDEVENTSYNC_LOG_DIRECTION_INCOMING, $transaction_id,
				$xml, 'ok');
		}
		catch (Exception $e)
		{
			$response = new SimpleXMLElement('<SessionRS/>');
			$response->addChild('TransactionId', $transaction_id);

			$errors = new SimpleXMLElement('<Errors/>');
			$errors->addChild('Error', $e->getMessage());
			$this->appendElement($response, $errors);

			$this->addResponse($response);

			// Log
			$this->log(REDEVENTSYNC_LOG_DIRECTION_OUTGOING, $transaction_id,
				$response, 'error');

			return false;
		}

		$response = new SimpleXMLElement('<SessionRS/>');
		$response->addChild('TransactionId', $transaction_id);
		$response->addChild('Success', '');

		$this->addResponse($response);

		// Log
		$this->log(REDEVENTSYNC_LOG_DIRECTION_OUTGOING, $transaction_id,
			$response, 'ok');

		return true;
	}

	/**
	 * generete the CreateSessionrq message
	 *
	 * @param   int  $session_id  session id
	 *
	 * @return void
	 */
	public function sendCreateSessionRq($session_id)
	{
		$xml = new SimpleXMLElement('<SessionsRQ xmlns="http://www.redcomponent.com/redevent"/>');

		$message = new SimpleXMLElement('<CreateSessionRQ/>');

		$this->addSessionXml($message, $session_id);

		$this->appendElement($xml, $message);

		$this->validate($xml->asXML(), 'SessionsRQ');

		$this->writeFile($xml);

		$this->log(REDEVENTSYNC_LOG_DIRECTION_OUTGOING, (int) $message->TransactionId, $xml, 'ok');
	}

	/**
	 * generete the ModifySessionrq message
	 *
	 * @param   int  $session_id  session id
	 *
	 * @return void
	 */
	public function sendModifySessionRq($session_id)
	{
		$xml = new SimpleXMLElement('<SessionsRQ xmlns="http://www.redcomponent.com/redevent"/>');

		$message = new SimpleXMLElement('<ModifySessionRQ/>');

		$this->addSessionXml($message, $session_id);

		$this->appendElement($xml, $message);

		$this->validate($xml->asXML(), 'SessionsRQ');

		$this->writeFile($xml);

		$this->log(REDEVENTSYNC_LOG_DIRECTION_OUTGOING, (int) $message->TransactionId, $xml, 'ok');
	}

	/**
	 * generate the DeleteSessionRq message
	 *
	 * @param   string  $session_code  session code
	 *
	 * @return void
	 */
	public function sendDeleteSessionRq($session_code)
	{
		$xml = new SimpleXMLElement('<SessionsRQ xmlns="http://www.redcomponent.com/redevent"/>');

		$message = new SimpleXMLElement('<DeleteSessionRQ/>');
		$message->addChild('TransactionId', $this->getNextTransactionId());
		$message->addChild('SessionCode', $session_code);

		$this->appendElement($xml, $message);

		$this->writeFile($xml);

		$this->log(REDEVENTSYNC_LOG_DIRECTION_OUTGOING, (int) $message->TransactionId, $xml, 'ok');
	}

	/**
	 * Init response message if applicable
	 *
	 * @return void
	 */
	protected function initResponse()
	{
		$this->response = new SimpleXMLElement('<SessionsRS/>');
	}

	/**
	 * returns id of session associated to code
	 *
	 * @param   string  $code  session code
	 *
	 * @return int
	 * @throws Exception
	 */
	protected function getSessionId($code)
	{
		$code = trim($code);

		if (empty($code))
		{
			throw new Exception('Session code is required');
		}

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('id');
		$query->from('#__redevent_event_venue_xref');
		$query->where('session_code = ' . $db->quote($code));

		$db->setQuery($query);
		$res = $db->loadResult();

		return $res;
	}

	/**
	 * returns id of event associated to code
	 *
	 * @param   string  $code  event code
	 *
	 * @return int
	 * @throws Exception
	 */
	protected function getEventId($code)
	{
		$code = trim($code);

		if (empty($code))
		{
			throw new Exception('Course code is required');
		}

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('id');
		$query->from('#__redevent_events');
		$query->where('course_code = ' . $db->quote($code));

		$db->setQuery($query);
		$res = $db->loadResult();

		if (!$res)
		{
			throw new Exception('Unkown Course code: ' . $code);
		}

		return $res;
	}

	/**
	 * returns id of venue associated to code
	 *
	 * @param   string  $code  venue code
	 *
	 * @return int
	 * @throws Exception
	 */
	protected function getVenueId($code)
	{
		$code = trim($code);

		if (empty($code))
		{
			throw new Exception('Venue code is required');
		}

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('id');
		$query->from('#__redevent_venues');
		$query->where('venue_code = ' . $db->quote($code));

		$db->setQuery($query);
		$res = $db->loadResult();

		if (!$res)
		{
			throw new Exception('Unkown Venue code: ' . $code);
		}

		return $res;
	}

	/**
	 * parse xml and returns object for binding
	 *
	 * @param   SimpleXMLElement  $xml  xml
	 *
	 * @return stdclass
	 */
	protected function parseSessionXml(SimpleXMLElement $xml)
	{
		// Create object from the xml info
		$object = new stdclass;

		if (isset($xml->SessionDetails))
		{
			$el = $xml->SessionDetails;

			$object->session_code = (string) $el->SessionCode;

			if (isset($el->NewSessionCode))
			{
				$object->new_session_code = (string) $el->NewSessionCode;
			}

			$object->course_code  = (string) $el->CourseCode;
			$object->venue_code   = (string) $el->VenueCode;

			if (isset($el->Title))
			{
				$object->title   = (string) $el->Title;
			}

			if (isset($el->Alias))
			{
				$object->alias   = (string) $el->Alias;
			}

			if (isset($el->Date))
			{
				$object->dates   = (string) $el->Date;
			}

			if (isset($el->Time))
			{
				$object->times   = (string) $el->Time;
			}

			if (isset($el->EndDate))
			{
				$object->enddates   = (string) $el->EndDate;
			}

			if (isset($el->EndTime))
			{
				$object->endtimes   = (string) $el->EndTime;
			}

			if (isset($el->EndOfRegistration))
			{
				if ((string) $el->EndOfRegistration)
				{
					$parts = explode("T", (string) $el->EndOfRegistration);
				}

				$object->registrationend   = $parts[0] . ' ' . $parts[1];
			}

			if (isset($el->Note))
			{
				$object->note   = (string) $el->Note;
			}

			$object->published   = (string) $el->Published;

			if (isset($el->Featured))
			{
				$object->featured   = (string) $el->Featured;
			}

			if (isset($el->CustomFields))
			{
				foreach ($el->CustomFields->children() as $custom)
				{
					$fieldid = 'custom' . (int) $custom->CustomFieldId;
					$object->{$fieldid} = (string) $custom->CustomFieldValue;
				}
			}

			if (isset($el->Details))
			{
				$object->details   = (string) $el->Details;
			}
		}

		if (isset($xml->SessionRegistration))
		{
			$el = $xml->SessionRegistration;

			if (isset($el->MaximumAttendees))
			{
				$object->maxattendees   = (int) $el->MaximumAttendees;
			}

			if (isset($el->MaximumPlacesWaitingList))
			{
				$object->maxwaitinglist   = (int) $el->MaximumPlacesWaitingList;
			}

			if (isset($el->EventCredit))
			{
				$object->course_credit   = (int) $el->EventCredit;
			}

			if (isset($el->EventPrice))
			{
				$prices = array();

				foreach ($el->EventPrice->children() as $price)
				{
					$p = new stdClass;
					$p->pricegroup_id = (int) $price->PriceGroupId;
					$p->price         = (float) $price->PriceGroupPrice;
					$prices[] = $p;
				}

				$object->prices = $prices;
			}
		}

		if (isset($xml->SessionRoles))
		{
			$roles = array();

			foreach ($el->EventPrice->children() as $price)
			{
				$p = new stdClass;
				$p->RoleId    = (int) $price->RoleId;
				$p->RoleName  = (string) $price->RoleName;
				$p->RoleUser  = (string) $price->RoleUser;
				$roles[] = $p;
			}

			$xml->roles = $roles;
		}

		if (isset($xml->SessionIcal))
		{
			if (isset($xml->SessionIcal->IcalDescription))
			{
				$object->icaldetails   = (string) $xml->SessionIcal->IcalDescription;
			}

			if (isset($xml->SessionIcal->IcalLocation))
			{
				$object->icalvenue   = (string) $xml->SessionIcal->IcalLocation;
			}
		}

		$object->id = $this->getSessionId($object->session_code);
		$object->eventid = $this->getEventId($object->course_code);
		$object->venueid = $this->getVenueId($object->venue_code);

		return $object;
	}

	/**
	 * return xml code for session
	 *
	 * @param   SimpleXMLElement  $message     message root
	 * @param   int               $session_id  session id
	 *
	 * @return SimpleXmlElement
	 */
	protected function addSessionXml(SimpleXMLElement $message, $session_id)
	{
		$message->addChild('TransactionId', $this->getNextTransactionId());

		// Session details
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('x.*');
		$query->select('e.course_code');
		$query->select('v.venue_code');
		$query->from('#__redevent_event_venue_xref AS x');
		$query->join('INNER', '#__redevent_events AS e ON e.id = x.eventid');
		$query->join('INNER', '#__redevent_venues AS v ON v.id = x.venueid');
		$query->where('x.id = ' . $session_id);

		$db->setQuery($query);
		$session = $db->loadObject();

		$sessiondetails = new SimpleXMLElement('<SessionDetails/>');
		$sessiondetails->addChild('SessionCode', $session->session_code);
		$sessiondetails->addChild('CourseCode', $session->course_code);
		$sessiondetails->addChild('VenueCode', $session->venue_code);
//		$sessiondetails->addChild('Group', $session->session_code);
		$sessiondetails->addChild('Title', $session->title);
		$sessiondetails->addChild('Alias', $session->alias);

		if ($session->dates && $session->dates != '0000-00-00')
		{
			$sessiondetails->addChild('Date', $session->dates);
		}

		if ($session->times && $session->times != '00:00:00')
		{
			$sessiondetails->addChild('Time', $session->times);
		}

		if ($session->enddates && $session->enddates != '0000-00-00')
		{
			$sessiondetails->addChild('EndDate', $session->enddates);
		}

		if ($session->endtimes && $session->endtimes != '00:00:00')
		{
			$sessiondetails->addChild('EndTime', $session->endtimes);
		}

		if ($session->registrationend && $session->registrationend != '0000-00-00 00:00:00')
		{
			$sessiondetails->addChild('EndOfRegistration', str_replace(' ', 'T', $session->registrationend));
		}

		$sessiondetails->addChild('Note', $session->note);
		$sessiondetails->addChild('ExternalRegistrationUrl', $session->external_registration_url);
		$sessiondetails->addChild('Published', $session->published);
		$sessiondetails->addChild('Featured', $session->featured);

		$custom_fields = new SimpleXMLElement('<CustomFields/>');
		$custom = $this->getCustomFields();

		foreach ($custom as $c)
		{
			$field = new SimpleXMLElement('<CustomField/>');
			$field->addChild('CustomFieldId', $c->id);
			$field->addChild('CustomFieldType', $c->type);
			$dbname = 'custom' . $c->id;
			$field->addChild('CustomFieldValue', $session->{$dbname});
			$this->appendElement($custom_fields, $field);
		}

		$this->appendElement($sessiondetails, $custom_fields);

		$sessiondetails->addChild('Details', $session->details);

		$this->appendElement($message, $sessiondetails);

		$reg = new SimpleXMLElement('<SessionRegistration/>');
		$reg->addChild('MaximumAttendees', $session->maxattendees);
		$reg->addChild('MaximumPlacesWaitingList', $session->maxwaitinglist);

		if ($pricegroups = $this->getPricegroups($session_id))
		{
			$xml_prices = new SimpleXMLElement('<EventPrice/>');

			foreach ($pricegroups as $pg)
			{
				$xml_pg = new SimpleXMLElement('<PriceGroup/>');
				$xml_pg->addChild('PriceGroupId', $pg->id);
				$xml_pg->addChild('PriceGroupName', $pg->name);
				$xml_pg->addChild('PriceGroupPrice', $pg->price);

				$this->appendElement($xml_prices, $xml_pg);
			}

			$this->appendElement($reg, $xml_prices);
		}

		$this->appendElement($message, $reg);


		$ical = new SimpleXMLElement('<SessionIcal/>');
		$ical->addChild('IcalDescription', $session->icaldetails);
		$ical->addChild('IcalLocation', $session->icalvenue);

		$this->appendElement($message, $ical);

		return $message;
	}

	/**
	 * returns sessions custom fields
	 *
	 * @return mixed
	 */
	protected function getCustomFields()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('f.*');
		$query->from('#__redevent_fields AS f');
		$query->where('object_key = ' . $db->quote('redevent.xref'));

		$db->setQuery($query);
		$res = $db->loadObjectList();

		return $res;
	}

	/**
	 * returns pricegroups for session
	 *
	 * @param   int  $session_id  session id
	 *
	 * @return array
	 */
	protected function getPricegroups($session_id)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('xpg.price');
		$query->select('pg.id, pg.name');
		$query->from('#__redevent_sessions_pricegroups AS xpg');
		$query->join('INNER', '#__redevent_pricegroups AS pg ON xpg.pricegroup_id = pg.id');
		$query->where('xpg.xref = ' . $session_id);

		$db->setQuery($query);
		$res = $db->loadObjectList();

		return $res;
	}
}
