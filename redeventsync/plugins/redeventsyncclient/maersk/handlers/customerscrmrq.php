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
 * redEVENT sync CustomerCRMRS Handler
 *
 * @package  RED.redeventsync
 * @since    2.5
 */
class RedeventsyncHandlerCustomerscrmrq extends RedeventsyncHandlerAbstractmessage
{
	protected $transactionId;

	/**
	 * process CreateCustomerCRMRQ request
	 *
	 * @param   SimpleXMLElement  $customer  xml data for the object
	 *
	 * @return boolean
	 */
	protected function processCreateCustomerCRMRQ(SimpleXMLElement $customer)
	{
		if (!class_exists('RedmemberLib'))
		{
			require_once JPATH_SITE . '/components/com_redmember/lib/redmemberlib.php';
		}

		$data = array();

		$data['email'] = (string) $customer->Emailaddress;

		// Find user
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('id');
		$query->from('#__users');
		$query->where('email = ' . $db->quote($data['email']));

		$db->setQuery($query);
		$user_id = $db->loadResult();

		// Fields should match the actual fields db_name from maersk redmember
		$data['id'] = (int) $user_id;
		$data['rm_customerid'] = (string) $customer->CustomerID;
		$data['rm_firstname'] = (string) $customer->Firstname;
		$data['rm_lastname'] = (string) $customer->Lastname;
		$data['rm_address1'] = (string) $customer->Address1;
		$data['rm_address2'] = (string) $customer->Address2;
		$data['rm_address3'] = (string) $customer->Address3;
		$data['rm_city'] = (string) $customer->City;
		$data['rm_zipcode'] = (string) $customer->Zipcode;
		$data['rm_countrycode'] = (string) $customer->Countrycode;
		$data['rm_nationality'] = (string) $customer->Nationality;
		$data['rm_titlerank'] = (string) $customer->TitleRank;
		$data['rm_birthdate'] = (string) $customer->Birthdate;
		$data['rm_phone'] = (string) $customer->Phonenumber;
		$data['rm_mobile'] = (string) $customer->Mobilephonenumber;
		$data['rm_companycvr'] = (string) $customer->CompanyCvrNr;
		$data['rm_company'] = (string) $customer->CompanyName;
		$data['rm_companyzip'] = (string) $customer->CompanyZip;
		$data['rm_companyaddress'] = (string) $customer->CompanyAddress;
		$data['rm_companyphone'] = (string) $customer->CompanyPhone;
		$data['rm_companysegmentpos'] = (string) $customer->CompanySegmentPos;
		$data['username'] = trim((string) $customer->Firstname) . trim((string) $customer->Lastname);
		$data['name'] = trim((string) $customer->Firstname) . ' ' . trim((string) $customer->Lastname);
		$data['email'] = (string) $customer->Emailaddress;

		try
		{
			redmemberlib::saveUser(false, $data, false, array('no_check' => 1));

			// Log
			$this->log(
				REDEVENTSYNC_LOG_DIRECTION_INCOMING, $this->transactionId,
				$customer, 'ok');

			// Response
			$this->sendSuccess($this->transactionId);
		}
		catch (Exception $e)
		{
			$this->log(
				REDEVENTSYNC_LOG_DIRECTION_INCOMING, $this->transactionId,
				$customer, 'failed', $e->getMessage()
			);

			$this->sendError($this->transactionId, $e->getMessage());

			return false;
		}

		return true;
	}

	/**
	 * process TransactionId
	 *
	 * @param   SimpleXMLElement  $xml  xml data for the object
	 *
	 * @return boolean
	 */
	protected function processTransactionId(SimpleXMLElement $xml)
	{
		$this->transactionId = strip_tags($xml->asXML());
	}

	/**
	 * process ModifyCustomerCRMRQ request
	 *
	 * @param   SimpleXMLElement  $xml  xml data for the object
	 *
	 * @return boolean
	 */
	protected function processModifyCustomerCRMRQ(SimpleXMLElement $xml)
	{
		return $this->processCreateCustomerCRMRQ($xml);
	}

	/**
	 * Init response message if applicable
	 *
	 * @return void
	 */
	protected function initResponse()
	{
		$this->response = new SimpleXMLElement('<CustomersCRMRS xmlns="http://www.redcomponent.com/redevent"/>');
	}

	/**
	 * build success message
	 *
	 * @param   string  $transactionId  transaction id
	 *
	 * @return void
	 */
	protected function sendSuccess($transactionId)
	{
		$message = new SimpleXMLElement('<CustomersCRMRS xmlns="http://www.redcomponent.com/redevent"/>');
		$message->addChild('TransactionId', $transactionId);
		$message->addChild('Success', '');

		$this->response = $message;
	}

	/**
	 * build error message
	 *
	 * @param   string  $transactionId  transaction id
	 * @param   string  $error          error message
	 *
	 * @return void
	 */
	protected function sendError($transactionId, $error)
	{
		$message = new SimpleXMLElement('<CustomersCRMRS xmlns="http://www.redcomponent.com/redevent"/>');
		$message->addChild('TransactionId', $transactionId);

		$errors = new SimpleXMLElement('<Errors/>');
		$errors->addChild('Error', $error);
		$this->appendElement($message, $errors);

		$this->response = $message;
	}
}
