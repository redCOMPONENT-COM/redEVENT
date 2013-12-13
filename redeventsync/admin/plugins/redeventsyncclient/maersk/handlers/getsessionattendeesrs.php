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
	 * process Attendee request
	 *
	 * @param   SimpleXMLElement  $xml  xml data for the object
	 *
	 * @return boolean
	 */
	protected function processAttendee(SimpleXMLElement $xml)
	{
		try
		{
			$parsed = $this->parseAttendeeXml($xml);

			$userid = RedeventsyncclientMaerskHelper::getUser($parsed->user_email);

			if (!$userid)
			{
				// We need an user, trigger a special Exception to force getting one
				throw new MissingUserException($parsed->user_email, $parsed->venue_code);
			}

			// Store the attendee
			$this->storeAttendee($parsed);
		}
		catch (InvalidEmailException $e)
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
		require_once JPATH_ADMINISTRATOR . '/components/com_redevent/tables/redevent_register.php';
		$row = JTable::getInstance('RedEvent_register', '');

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
		require_once JPATH_SITE . '/components/com_redform/redform.core.php';
		$rfcore = new RedFormCore;
		$rfcore->setFormId($session_details->redform_id);

		$data = array();

		$token = JSession::getFormToken();
		$data[$token] = 1;
		$data['form_id'] = $session_details->redform_id;

		if ($row->submit_key)
		{
			$data['submit_key'] = $row->submit_key;
		}

		// Get user
		if (!$attendee->id)
		{
			$row->uid = $this->getUser($attendee->user_email);

			if (!$row->uid)
			{
				throw new Exception('No user associated to attendee');
			}
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

		$row->bind($attendee);
		$row->xref = $session_details->session_id;
		$row->sid = $sid;
		$row->submit_key = $result->submit_key;

		// Now save !
		if (!($row->check() && $row->store()))
		{
			throw new Exception($row->getError());
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

		if (isset($xml->UserEmail))
		{
			$object->user_email    = (string) $xml->UserEmail;
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

		return $object;
	}
}
