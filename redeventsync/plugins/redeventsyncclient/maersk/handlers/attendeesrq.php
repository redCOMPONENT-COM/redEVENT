<?php
/**
 * @package		redcomponent.redeventsync
 * @subpackage	com_redeventsync
 * @copyright	Copyright (C) 2013 redCOMPONENT.com
 * @license		GNU General Public License version 2 or later
 */

defined('_JEXEC') or die();

require_once 'abstractmessage.php';

/**
 * redEVENT sync Attendeesrq Handler
 *
 * @package  RED.redeventsync
 * @since    2.5
 */
class RedeventsyncHandlerAttendeesrq extends RedeventsyncHandlerAbstractmessage
{
	/**
	 * send createattendeeRQ
	 *
	 * @param   int  $attendee_id  attendee id
	 *
	 * @return bool
	 */
	public function sendCreateAttendeeRQ($attendee_id)
	{
		$xml = new SimpleXMLElement('<AttendeesRQ xmlns="http://www.redcomponent.com/redevent"/>');

		$message = new SimpleXMLElement('<CreateAttendeeRQ/>');

		$this->addAttendeeXml($message, $attendee_id);

		$this->appendElement($xml, $message);

		$this->validate($xml->asXML(), 'AttendeesRQ');

		$this->enqueue($xml->asXML());

		$this->log(REDEVENTSYNC_LOG_DIRECTION_OUTGOING, (int) $message->TransactionId, $xml, 'queued');

		return true;
	}

	/**
	 * send ModifyattendeeRQ
	 *
	 * @param   int  $attendee_id  attendee id
	 *
	 * @return bool
	 */
	public function sendModifyAttendeeRQ($attendee_id)
	{
		$xml = new SimpleXMLElement('<AttendeesRQ xmlns="http://www.redcomponent.com/redevent"/>');

		$message = new SimpleXMLElement('<ModifyAttendeeRQ/>');

		$this->addAttendeeXml($message, $attendee_id);

		$this->appendElement($xml, $message);

		$this->validate($xml->asXML(), 'AttendeesRQ');

		$this->enqueue($xml->asXML());

		$this->log(REDEVENTSYNC_LOG_DIRECTION_OUTGOING, (int) $message->TransactionId, $xml, 'queued');

		return true;
	}

	/**
	 * send DeleteattendeeRQ
	 *
	 * @param   int  $attendee_id  attendee id
	 *
	 * @return bool
	 */
	public function sendDeleteAttendeeRQ($attendee_id)
	{
		$xml = new SimpleXMLElement('<AttendeesRQ xmlns="http://www.redcomponent.com/redevent"/>');

		$attendee = RedeventsyncclientMaerskHelper::getAttendee($attendee_id);

		$this->validateAddAttendeeFields($attendee);

		$message = new SimpleXMLElement('<DeleteAttendeeRQ/>');
		$message->addChild('TransactionId', $this->getNextTransactionId());
		$message->addChild('SessionCode',   $attendee->session_code);
		$message->addChild('VenueCode',     $attendee->venue_code);
		$message->addChild('UserEmail',     $attendee->email);
		$message->addChild('Firstname',     $attendee->redmember->rm_firstname);
		$message->addChild('Lastname',      $attendee->redmember->rm_lastname);

		$this->appendElement($xml, $message);

		$this->validate($xml->asXML(), 'AttendeesRQ');

		$this->enqueue($xml->asXML());

		$this->log(REDEVENTSYNC_LOG_DIRECTION_OUTGOING, (int) $message->TransactionId, $xml, 'queued');

		return true;
	}

	/**
	 * process CreateAttendeeRQ request
	 *
	 * @param   SimpleXMLElement  $xml     xml data for the object
	 * @param   boolean           $create  create attendee if not found ?
	 *
	 * @return boolean
	 *
	 * @throws PlgresyncmaerskExceptionMissinguser
	 * @throws PlgresyncmaerskExceptionMismatchuser
	 */
	protected function processAttendeeRQXml(SimpleXMLElement $xml, $create = true)
	{
		$transaction_id = (int) $xml->TransactionId;

		try
		{
			// Register table
			$row = RTable::getAdminInstance('Attendee', array(), 'com_redevent');

			// Create attendee from the xml info
			$attendee = $this->parseAttendeeXml($xml);

			// Try to find attendee
			$existing = RedeventsyncclientMaerskHelper::findAttendee($attendee->user_email, $attendee->session_code, $attendee->venue_code);

			if ($existing)
			{
				$row->bind(get_object_vars($existing));
			}
			else
			{
				if (!isset($attendee->waitinglist))
				{
					$attendee->waitinglist = 0;
				}

				$row->origin = "picasso";
			}

			// Make sure we have an user !
			$rmUser = RedeventsyncclientMaerskHelper::getUser($attendee->user_email);

			if (!$rmUser)
			{
				// We need an user, trigger a special Exception to force getting one
				throw new PlgresyncmaerskExceptionMissinguser($attendee->user_email, $attendee->venue_code);
			}
			elseif ($attendee->firstname != $rmUser->rm_firstname || $attendee->lastname != $rmUser->rm_lastname)
			{
				// Just try to update it
				$this->parent->getCustomer($attendee->user_email, $attendee->venue_code, $rmUser->rm_firstname, $rmUser->rm_lastname);
			}

			// We will first add to redform submitters, then to corresponding redform form,
			// and then to register table
			$session_details = RedeventsyncclientMaerskHelper::getSessionDetails($attendee->session_code, $attendee->venue_code);

			// Post to redform
			$rfcore = new RdfCore;
			$rfcore->setFormId($session_details->redform_id);

			$data = array();

			$token = JSession::getFormToken();
			$data[$token] = 1;
			$data['form_id'] = $session_details->redform_id;

			if ($row->submit_key)
			{
				$data['submit_key'] = $row->submit_key;
			}

			if ($row->sid)
			{
				$data['sid'] = $row->sid;
			}

			if (isset($attendee->answers))
			{
				foreach ($attendee->answers as $a)
				{
					$field = "field" . $a->id;
					$data[$field] = $a->value;
				}

				$result = $rfcore->saveAnswers('redevent', null, $data);
			}
			else
			{
				// Use quickSubmit method
				$result = $rfcore->quickSubmit($rmUser->id, 'redevent', $data);
			}

			if (!$result)
			{
				throw new Exception($rfcore->getError());
			}

			$sid = $result->posts[0]['sid'];

			$row->bind(get_object_vars($attendee));
			$row->xref = $session_details->session_id;
			$row->sid = $sid;
			$row->submit_key = $result->submit_key;
			$row->uid = $rmUser->joomla_user_id;

			// Now save !
			if (!($row->check() && $row->store()))
			{
				throw new Exception($row->getError());
			}

			// Save payment if there is one
			if ($attendee->payment)
			{
				RedeventsyncclientMaerskHelper::recordPayment($result->submit_key, $attendee->payment);
			}

			// Log
			$this->log(
				REDEVENTSYNC_LOG_DIRECTION_INCOMING, $transaction_id,
				$xml, 'ok');

			// Response
			$response = new SimpleXMLElement('<AttendeeRS/>');
			$response->addChild('TransactionId', $transaction_id);
			$response->addChild('Success', '');
			$this->addResponse($response);

			// Log
			$this->log(
				REDEVENTSYNC_LOG_DIRECTION_OUTGOING, $transaction_id,
				$response, 'ok');
		}
		catch (PlgresyncmaerskExceptionMismatchuser $e)
		{
			// Bubble !
			throw $e;
		}
		catch (Exception $e)
		{
			// Log
			$this->log(
				REDEVENTSYNC_LOG_DIRECTION_INCOMING, $transaction_id,
				$xml, 'error', $e->getMessage());

			$response = new SimpleXMLElement('<AttendeeRS/>');
			$response->addChild('TransactionId', $transaction_id);

			$errors = new SimpleXMLElement('<Errors/>');
			$errors->addChild('Error', $e->getMessage());
			$this->appendElement($response, $errors);

			$this->addResponse($response);

			// Log
			$this->log(
				REDEVENTSYNC_LOG_DIRECTION_OUTGOING, $transaction_id,
				$response, 'error response');

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
	protected function processCreateAttendeeRQ(SimpleXMLElement $xml)
	{
		return $this->processAttendeeRQXml($xml, true);
	}

	/**
	 * process CreateAttendeeRQ request
	 *
	 * @param   SimpleXMLElement  $xml  xml data for the object
	 *
	 * @return boolean
	 */
	protected function processModifyAttendeeRQ(SimpleXMLElement $xml)
	{
		$this->create = false;

		return $this->processAttendeeRQXml($xml, false);
	}

	/**
	 * process DeleteAttendeeRQ request
	 *
	 * @param   SimpleXMLElement  $xml  xml data for the object
	 *
	 * @return boolean
	 */
	protected function processDeleteAttendeeRQ(SimpleXMLElement $xml)
	{
		$transaction_id = (int) $xml->TransactionId;

		try
		{
			$attendee_id = (int) $xml->AttendeeId;

			// Get attendee details
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('e.redform_id');
			$query->from('#__redevent_register AS r');
			$query->join('INNER', '#__redevent_event_venue_xref AS x ON x.id = r.xref');
			$query->join('INNER', '#__redevent_events AS e ON e.id = x.eventid');
			$query->where('r.id = ' . $db->quote($attendee_id));

			$db->setQuery($query);
			$attendee = $db->loadObject();

			if (!$attendee)
			{
				throw new Exception('Attendee not found');
			}

			$query = ' DELETE s, f, r '
				. ' FROM #__redevent_register AS r '
				. ' LEFT JOIN #__rwf_submitters AS s ON r.sid = s.id '
				. ' LEFT JOIN #__rwf_forms_' . $attendee->redform_id . ' AS f ON f.id = s.answer_id '
				. ' WHERE r.id = ' . $attendee_id;
			$db->setQuery($query);

			if (!$db->query())
			{
				throw new Exception($db->getError());
			}

			// Log
			$this->log(
				REDEVENTSYNC_LOG_DIRECTION_INCOMING, $transaction_id,
				$xml, 'ok');

			// Response
			$response = new SimpleXMLElement('<AttendeeRS/>');
			$response->addChild('TransactionId', $transaction_id);
			$response->addChild('Success', '');
			$this->addResponse($response);

			// Log
			$this->log(
				REDEVENTSYNC_LOG_DIRECTION_OUTGOING, $transaction_id,
				$response, 'ok');
		}
		catch (Exception $e)
		{
			// Log
			$this->log(
				REDEVENTSYNC_LOG_DIRECTION_INCOMING, $transaction_id,
				$xml, 'error', $e->getMessage());

			$response = new SimpleXMLElement('<AttendeeRS/>');
			$response->addChild('TransactionId', $transaction_id);

			$errors = new SimpleXMLElement('<Errors/>');
			$errors->addChild('Error', $e->getMessage());
			$this->appendElement($response, $errors);

			$this->addResponse($response);

			// Log
			$this->log(
				REDEVENTSYNC_LOG_DIRECTION_OUTGOING, $transaction_id,
				$response, 'error response');

			return false;
		}
	}

	/**
	 * Init response message if applicable
	 *
	 * @return void
	 */
	protected function initResponse()
	{
		$this->response = new SimpleXMLElement('<AttendeesRS xmlns="http://www.redcomponent.com/redevent"/>');
	}

	/**
	 * parses xml for create and modify attendee
	 *
	 * @param   SimpleXMLElement  $xml  xml from request
	 *
	 * @return mixed
	 */
	protected function parseAttendeeXml(SimpleXMLElement $xml)
	{
		$object = new stdClass;

		$object->session_code   = (string) $xml->SessionCode;

		$object->venue_code   = (string) $xml->VenueCode;

		$object->user_email    = (string) $xml->UserEmail;

		if (isset($xml->Firstname))
		{
			$object->firstname    = (string) $xml->Firstname;
		}

		if (isset($xml->Lastname))
		{
			$object->lastname    = (string) $xml->Lastname;
		}

		if (isset($xml->PoNumber))
		{
			$object->ponumber    = (string) $xml->PoNumber;
		}

		if (isset($xml->Comments))
		{
			$object->comments    = (string) $xml->Comments;
		}

		if (isset($xml->Status))
		{
			$object->status    = (int) $xml->Status;
		}

		if (isset($xml->Cancelled))
		{
			$object->cancelled    = (int) $xml->Cancelled;
		}

		if (isset($xml->PriceGroupId))
		{
			$object->pricegroup_id    = (int) $xml->PriceGroupId;
		}

		if (isset($xml->waitinglist))
		{
			$object->waitinglist    = (int) $xml->waitinglist;
		}

		if (isset($xml->Confirmed))
		{
			$object->confirmed    = (int) $xml->Confirmed;
		}

		if (isset($xml->ConfirmDate))
		{
			$date = JDate::getInstance((string) $xml->ConfirmDate);
			$object->confirmdate    = $date->toSql();
		}

		if (isset($xml->PaymentStart))
		{
			$date = JDate::getInstance((string) $xml->PaymentStart);
			$object->paymentstart    = $date->toSql();
		}

		if (isset($xml->RegistrationDate))
		{
			$date = JDate::getInstance((string) $xml->RegistrationDate);
			$object->uregdate    = $date->toSql();
		}

		if (isset($xml->IP))
		{
			$object->ip      = (string) $xml->IP;
		}

		if (isset($xml->Answers))
		{
			$answers = array();

			foreach ($xml->Answers->children() as $a)
			{
				$answer = new stdClass;
				$answer->id = (int) $a->attributes()->FieldId;
				$answer->name = (string) $a->attributes()->FieldName;
				$answer->type = (string) $a->attributes()->FieldType;
				$answer->value = (string) $a->attributes()->FieldValue;
				$answers[] = $answer;
			}
			$object->answers = $answers;
		}
		else
		{
			$object->answers = null;
		}

		$object->payment = RedeventsyncclientMaerskHelper::parsePayment($xml);

		return $object;
	}

	/**
	 * return simplexmlelement for attendee
	 *
	 * @param   SimpleXMLElement  $message      root element
	 * @param   int               $attendee_id  attendee id
	 *
	 * @return SimpleXMLElement
	 */
	protected function addAttendeeXml(SimpleXMLElement $message, $attendee_id)
	{
		$message->addChild('TransactionId', $this->getNextTransactionId());

		$attendee = RedeventsyncclientMaerskHelper::getAttendee($attendee_id);

		$this->validateAddAttendeeFields($attendee);

		$message->addChild('SessionCode',   $attendee->session_code);
		$message->addChild('VenueCode',     $attendee->venue_code);
		$message->addChild('UserEmail',     $attendee->email);
		$message->addChild('Firstname',     $attendee->redmember->rm_firstname);
		$message->addChild('Lastname',      $attendee->redmember->rm_lastname);
		$message->addChild('PoNumber',      $attendee->ponumber);
		$message->addChild('Comments',      $attendee->comments);
		$message->addChild('Status',        (int) $attendee->status);
		$message->addChild('Cancelled',     $attendee->cancelled);
		$message->addChild('PriceGroupId',  $attendee->sessionpricegroup_id);
		$message->addChild('WaitingList',   $attendee->waitinglist);
		$message->addChild('Confirmed',     $attendee->confirmed);

		if ($attendee->confirmdate)
		{
			$date = JDate::getInstance($attendee->confirmdate);
			$message->addChild('ConfirmDate',   str_replace(' ', 'T', $date->toSql()));
		}

		if ($attendee->paymentstart)
		{
			$date = JDate::getInstance($attendee->paymentstart);
			$message->addChild('PaymentStart',   str_replace(' ', 'T', $date->toSql()));
		}

		if ($attendee->uregdate)
		{
			$date = JDate::getInstance($attendee->uregdate);
			$message->addChild('RegistrationDate',   str_replace(' ', 'T', $date->toSql()));
		}

		$message->addChild('IP',            $attendee->id);

		$answers = new SimpleXMLElement('<Answers/>');

		// Redform data
		$rfcore = new RdfCore;
		$rf_fields = $rfcore->getSidAnswers($attendee->sid);

		foreach ($rf_fields->getFields() as $f)
		{
			$a = new SimpleXMLElement('<Answer/>');
			$a->addAttribute('FieldId',    $f->id);
			$a->addAttribute('FieldName',  $f->field);
			$a->addAttribute('FieldType',  $f->fieldtype);
			$a->addAttribute('FieldValue', $f->getValueAsString(','));

			$this->appendElement($answers, $a);
		}

		$this->appendElement($message, $answers);

		// Payment
		if ($attendee->paid)
		{
			// Extract transaction id
			if (preg_match('/tid:([^\n]+)/', $attendee->payment_data, $matches))
			{
				$transactionId = $matches[1];
			}
			elseif (preg_match('/transactionId:([^\n]+)/', $attendee->payment_data, $matches))
			{
				$transactionId = $matches[1];
			}
			else
			{
				$transactionId = 'not found';
			}

			$a = new SimpleXMLElement('<Payment/>');
			$a->addChild('Plateform', $attendee->payment_gateway);
			$a->addChild('TransactionId', $transactionId);

			$this->appendElement($message, $a);
		}

		return $message;
	}

	/**
	 * Validate AddAttendee required fields
	 *
	 * @param   object  $attendee  attendee object
	 *
	 * @return bool
	 *
	 * @throws PlgresyncmaerskExceptionInvalidattendee
	 */
	protected function validateAddAttendeeFields($attendee)
	{
		if (!$attendee->session_code)
		{
			throw new PlgresyncmaerskExceptionInvalidattendee('Missing Session Code', 'Missing Session Code', print_r($attendee, true));
		}

		if (!$attendee->venue_code)
		{
			throw new PlgresyncmaerskExceptionInvalidattendee('Missing Venue Code', 'Missing Venue Code', print_r($attendee, true));
		}

		if (!$attendee->email)
		{
			throw new PlgresyncmaerskExceptionInvalidattendee('Missing Attendee email', 'Missing Attendee email', print_r($attendee, true));
		}

		return true;
	}
}
