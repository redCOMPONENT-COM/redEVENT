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
 * redEVENT sync Customersrq Handler
 *
 * @package  RED.redeventsync
 * @since    2.5
 */
class RedeventsyncHandlerCustomersrq extends RedeventsyncHandlerAbstractmessage
{
	/**
	 * process CreateAttendeeRQ request
	 *
	 * @param   SimpleXMLElement  $xml  xml data for the object
	 *
	 * @return boolean
	 */
	protected function processCustomerRQ(SimpleXMLElement $xml)
	{
		$transaction_id = (int) $xml->TransactionId;

		try
		{
			$email = (string) $xml->Emailaddress;

			// Find user
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('id');
			$query->from('#__users');
			$query->where('email = ' . $db->quote($email));

			$db->setQuery($query);
			$user_id = $db->loadResult();

			if (!$user_id)
			{
				throw new Exception('User not found');
			}

			$user = redmemberlib::getUserData($user_id);

			// Log
			$this->log(
				REDEVENTSYNC_LOG_DIRECTION_INCOMING, $transaction_id,
				$xml, 'ok');
		}
		catch (Exception $e)
		{
			// Log
			$this->log(
				REDEVENTSYNC_LOG_DIRECTION_INCOMING, $transaction_id,
				$xml, 'error', $e->getMessage()
			);

			$response = new SimpleXMLElement('<CustomerRS/>');
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

		// Generate xml from user data
		$response = new SimpleXMLElement('<CustomerRS/>');
		$response->addChild('TransactionId', $transaction_id);

		$success = new SimpleXMLElement('<Success/>');

		if ($user->rm_customerid)
		{
			$success->addChild('CustomerID',      $user->rm_customerid);
		}

		$companyAddress = array();

		if ($user->organization_address1)
		{
			$companyAddress[] = $user->organization_address1;
		}

		if ($user->organization_address2)
		{
			$companyAddress[] = $user->organization_address1;
		}

		if ($user->organization_address3)
		{
			$companyAddress[] = $user->organization_address3;
		}

		$success->addChild('Firstname',    $user->rm_firstname);
		$success->addChild('Lastname',     $user->rm_lastname);
		$success->addChild('Address1',     $user->rm_address1);
		$success->addChild('Address2',     $user->rm_address2);
		$success->addChild('Address3',     $user->rm_address3);
		$success->addChild('City',         $user->rm_city);
		$success->addChild('Zipcode',      $user->rm_zipcode);
		$success->addChild('Countrycode',  $user->rm_countrycode);
		$success->addChild('Emailaddress', $user->email);
		$success->addChild('Nationality', $user->rm_nationality);
		$success->addChild('TitleRank', $user->titlerank);

		if ($dob = $this->parent->convertDateToPicasso($user->rm_birthday))
		{
			$success->addChild('Birthdate', $dob);
		}

		$success->addChild('Phonenumber',  $user->rm_phone);
		$success->addChild('Mobilephonenumber', $user->rm_mobile);
		$success->addChild('CompanyCvrNr',      $user->organization_vat);
		$success->addChild('CompanyName',      $user->organization_name);
		$success->addChild('CompanyZip',      $user->organization_zip);
		$success->addChild('CompanyAddress',      implode(', ', $companyAddress));
		$success->addChild('CompanyPhone',      $user->organization_phone);

		$this->appendElement($response, $success);

		// Log
		$this->log(
			REDEVENTSYNC_LOG_DIRECTION_OUTGOING, $transaction_id,
			$response, 'ok');

		$this->addResponse($response);

		return true;
	}

	/**
	 * Init response message if applicable
	 *
	 * @return void
	 */
	protected function initResponse()
	{
		$this->response = new SimpleXMLElement('<CustomersRS xmlns="http://www.redcomponent.com/redevent"/>');
	}
}
