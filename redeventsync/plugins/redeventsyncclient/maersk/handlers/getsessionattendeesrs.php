<?php
/**
 * @package     redcomponent.redeventsync
 * @subpackage  com_redeventsync
 * @copyright   Copyright (C) 2013 redCOMPONENT.com
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die();

require_once 'abstractmessage.php';

/**
 * redEVENT sync Attendeesrq Handler
 *
 * @package  RED.redeventsync
 * @since    2.5
 */
class RedeventsyncHandlerGetSessionAttendeesrs extends RedeventsyncHandlerAbstractmessage
{
	/**
	 * @var int
	 */
	protected $transactionId = 0;

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

		foreach ($xml->children() as $node)
		{
			switch ($node->getName())
			{
				case 'TransactionId':
					$this->transactionId = (int) $xml->TransactionId;

					$this->log(REDEVENTSYNC_LOG_DIRECTION_INCOMING, $this->transactionId, $xml, '');
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
	 * process Attendee request
	 *
	 * @param   SimpleXMLElement  $xml  xml data for the object
	 *
	 * @return boolean
	 *
	 * @throws PlgresyncmaerskExceptionMissinguser
	 * @throws PlgresyncmaerskExceptionMismatchuser
	 */
	protected function processAttendee(SimpleXMLElement $xml)
	{
		try
		{
			$parsed = $this->parseAttendeeXml($xml);

			$rmUser = RedeventsyncclientMaerskHelper::getUser($parsed->user_email);

			if (!$rmUser->id)
			{
				// We need an user, trigger a special Exception to force getting one
				throw new PlgresyncmaerskExceptionMissinguser($parsed->user_email, $parsed->venue_code);
			}
			elseif ($parsed->firstname != $rmUser->rm_firstname || $parsed->lastname != $rmUser->rm_lastname)
			{
				throw new PlgresyncmaerskExceptionMismatchuser($parsed->user_email, $parsed->venue_code, $rmUser->rm_firstname, $rmUser->rm_lastname);
			}

			// Store the attendee
			$this->storeAttendee($parsed);
		}
		catch (PlgresyncmaerskExceptionInvalidemail $e)
		{
			$this->enqueueMessage(sprintf('Error handling attendee for session %s: %s', $parsed->session_code, $e->getMessage()));
		}

		return true;
	}

	/**
	 * save attendee from parsed data
	 *
	 * @param   object  $attendee  attendee data
	 *
	 * @throws Exception
	 *
	 * @return boolean
	 */
	protected function storeAttendee($attendee)
	{
		// Register table
		$row = RTable::getAdminInstance('Register', array(), 'com_redevent');

		if ($attendee->id)
		{
			$row->load($attendee->id);

			if (!$row->id)
			{
				throw new Exception('Attendee id not found');
			}
		}

		// We will first add to redform submitters, then to corresponding redform form,
		// and then to register table
		$session_details = RedeventsyncclientMaerskHelper::getSessionDetails($attendee->session_code, $attendee->venue_code);

		// Post to redform
		$rfcore = new RdfCore;
		$rfcore->setFormId($session_details->getEvent()->getEventTemplate()->redform_id);

		$data = array();

		$token = JSession::getFormToken();
		$data[$token] = 1;
		$data['form_id'] = $session_details->getEvent()->getEventTemplate()->redform_id;

		if ($row->submit_key)
		{
			$data['submit_key'] = $row->submit_key;
		}

		// Get user
		if (!$attendee->id)
		{
			$rmUser = RedeventsyncclientMaerskHelper::getUser($attendee->user_email);

			if (!$rmUser->joomla_user_id)
			{
				throw new Exception('No user associated to attendee');
			}

			$row->uid = $rmUser->joomla_user_id;
		}

		if ($attendee->answers)
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
			// Use quickbook method
			$result = $rfcore->quickSubmit($row->uid, 'redevent');
		}

		if (!$result)
		{
			throw new Exception($rfcore->getError());
		}

		$sid = $result->posts[0]['sid'];

		$row->bind(get_object_vars($attendee));
		$row->xref = $session_details->id;
		$row->sid = $sid;
		$row->submit_key = $result->submit_key;

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

		$this->enqueueMessage(sprintf('Registered %s to session %s', $attendee->user_email,  $attendee->session_code));

		return true;
	}

	/**
	 * parses xml for create and modify attendee
	 *
	 * @param   SimpleXMLElement  $xml  xml from request
	 *
	 * @return mixed
	 */
	public function parseAttendeeXml(SimpleXMLElement $xml)
	{
		$object = new stdClass;

		if (isset($xml->AttendeeId))
		{
			$object->id    = (int) $xml->AttendeeId;
		}

		$object->session_code   = (string) $xml->SessionCode;

		$object->venue_code   = (string) $xml->VenueCode;

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

		if (isset($xml->UserEmail))
		{
			$object->user_email    = (string) $xml->UserEmail;
		}

		if (isset($xml->Firstname))
		{
			$object->firstname    = (string) $xml->Firstname;
		}

		if (isset($xml->Lastname))
		{
			$object->lastname    = (string) $xml->Lastname;
		}

		if (isset($xml->Cancelled))
		{
			$object->cancelled    = (int) $xml->Cancelled;
		}

		if (isset($xml->PriceGroupId))
		{
			$object->sessionpricegroup_id    = (int) $xml->PriceGroupId;
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
			$object->confirmdate    = (int) $xml->ConfirmDate;
		}

		if (isset($xml->PaymentStart))
		{
			$object->paymentstart      = (int) $xml->PaymentStart;
		}

		if (isset($xml->RegistrationDate))
		{
			$object->uregdate      = (string) $xml->RegistrationDate;
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

		$object->payment = RedeventsyncclientMaerskHelper::parsePayment($xml);

		return $object;
	}
}
