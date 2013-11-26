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
 * redEVENT sync Sessionsrq Handler
 *
 * @package  RED.redeventsync
 * @since    2.5
 */
class RedeventsyncHandlerGetsessionsrs extends RedeventsyncHandlerAbstractmessage
{
	/**
	 * Handle nodes from xml
	 *
	 * @param   string  $xml_post  the data to parse
	 *
	 * @return  boolean true on success
	 *
	 * @throws Exception
	 */
	public function handle($xml_post)
	{
		$this->initResponse();

		$xml = new SimpleXMLElement($xml_post);

		$transactionId = 0;

		foreach ($xml->children() as $node)
		{
			switch ($node->getName())
			{
				case 'TransactionId':
					$transactionId = (int) $xml->TransactionId;
					break;

				default:
					if (!method_exists($this, 'process' . $node->getName()))
					{
						throw new Exception('handle error - Unknown node: ' . $node->getName());
					}

					$this->{'process' . $node->getName()}($node);
			}
		}

		return true;
	}

	/**
	 * process Session request
	 *
	 * @param   SimpleXMLElement  $xml  xml data for the object
	 *
	 * @return boolean
	 */
	protected function processSession(SimpleXMLElement $xml)
	{
		require_once JPATH_ADMINISTRATOR . '/components/com_redevent/tables/redevent_eventvenuexref.php';

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
			$this->log(
				REDEVENTSYNC_LOG_DIRECTION_INCOMING, 0,
				$xml, 'ok');
		}
		catch (Exception $e)
		{
			// Log
			$this->log(
				REDEVENTSYNC_LOG_DIRECTION_INCOMING, 0,
				$xml, 'error');
			$this->enqueueMessage($e->getMessage());

			return false;
		}

		if ($object->id)
		{
			$this->enqueueMessage('Successfully updated session ' . $object->session_code);
		}
		else
		{
			$this->enqueueMessage('Successfully added session' . $object->session_code);
		}

		return true;
	}

	/**
	 * returns id of session associated to code
	 *
	 * @param   string  $code  session code
	 *
	 * @return int
	 *
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
	 *
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
	 *
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

			foreach ($el->Role->children() as $price)
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
}

