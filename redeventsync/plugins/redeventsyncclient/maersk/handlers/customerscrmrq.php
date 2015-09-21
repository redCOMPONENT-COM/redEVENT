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
	 * send CreateCustomerCRMRQ
	 *
	 * @param   int   $userId  user id
	 * @param   bool  $isNew   true if new user
	 *
	 * @return bool
	 */
	public function sendCustomersCRMRQ($userId, $isNew)
	{
		$xml = new SimpleXMLElement('<CustomersCRMRQ xmlns="http://www.redcomponent.com/redevent"/>');
		$xml->addChild('TransactionId', $this->getNextTransactionId());

		if ($isNew)
		{
			$message = new SimpleXMLElement('<CreateCustomerCRMRQ/>');
		}
		else
		{
			$message = new SimpleXMLElement('<ModifyCustomerCRMRQ/>');
		}

		$this->addUserXml($message, $userId);

		$this->appendElement($xml, $message);

		$this->validate($xml->asXML(), 'CustomersCRMRQ');

		$this->enqueue($xml->asXML());

		$this->log(REDEVENTSYNC_LOG_DIRECTION_OUTGOING, (int) $message->TransactionId, $xml, 'queued');

		return true;
	}

	/**
	 * process CreateCustomerCRMRQ request
	 *
	 * @param   SimpleXMLElement  $customer  xml data for the object
	 *
	 * @return boolean
	 */
	protected function processCreateCustomerCRMRQ(SimpleXMLElement $customer)
	{
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

		$rmUser = RedmemberApi::getUser($user_id);

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
		$data['title_rank'] = (string) $customer->TitleRank;

		if ($dob = $this->parent->convertDateFromPicasso((string) $customer->Birthdate))
		{
			$data['rm_birthday'] = $dob;
		}

		$data['rm_phone'] = (string) $customer->Phonenumber;
		$data['rm_mobile'] = (string) $customer->Mobilephonenumber;

		if (!$user_id)
		{
			$data['name'] = trim((string) $customer->Firstname) . ' ' . trim((string) $customer->Lastname);
			$data['email'] = (string) $customer->Emailaddress;
			$data['username'] = $data['email'];
		}

		$companyData = array(
			'name' => (string) $customer->CompanyName,
			'organization_vat' => (string) $customer->CompanyCvrNr,
			'organization_zip' => (string) $customer->CompanyZip,
			'organization_address1' => (string) $customer->CompanyAddress,
			'organization_phone' => (string) $customer->CompanyPhone
		);

		$orgId = $this->getCompanyId($companyData);

		if ($orgId)
		{
			$currentUserOrganizations = $rmUser->getOrganizations();

			if (!in_array($orgId, $currentUserOrganizations))
			{
				$currentUserOrganizations[$orgId] = array('organization_id' => $orgId, 'level' => 1);
			}

			$rmUser->setOrganizations($currentUserOrganizations);
		}

		try
		{
			$rmUser->save($data, false);

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
	 * Get company id
	 *
	 * @param   array  $data  data
	 *
	 * @return int|mixed
	 */
	private function getCompanyId($data)
	{
		if (!isset($data['name']) || !$data['name'])
		{
			return 0;
		}

		if ($id = $this->findCompanyIdByName($data['name']))
		{
			return $id;
		}

		return RedeventsyncclientMaerskHelper::createCompany($data);
	}

	/**
	 * find company id
	 *
	 * @param   string  $name  name
	 *
	 * @return int
	 */
	private function findCompanyIdByName($name)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('id')
			->from('#__redmember_organization')
			->where('name = ' . $db->quote($name));

		$db->setQuery($query);
		$res = $db->loadResult();

		return $res;
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

	/**
	 * Returns unique username
	 *
	 * @param   string  $username  username
	 *
	 * @return string
	 */
	private function getUniqueUsername($username)
	{
		$res = str_replace("'", "", $username);
		$i = 1;

		while (JUserHelper::getUserId($res))
		{
			$res = $username . '_' . ($i++);
		}

		return $res;
	}

	/**
	 * Build user data in xml
	 *
	 * @param   SimpleXMLElement  $message  message to add info to
	 * @param   int               $userId   user id
	 *
	 * @return mixed
	 */
	private function addUserXml($message, $userId)
	{
		$rmUser = RedmemberApi::getUser($userId);

		$companyAddress = array();

		if ($rmUser->organization_address1)
		{
			$companyAddress[] = $rmUser->organization_address1;
		}

		if ($rmUser->organization_address2)
		{
			$companyAddress[] = $rmUser->organization_address1;
		}

		if ($rmUser->organization_address3)
		{
			$companyAddress[] = $rmUser->organization_address3;
		}

		$message->addChild('VenueCode', '');

		if ($rmUser->rm_customerid)
		{
			$message->addChild('CustomerID',      $rmUser->rm_customerid);
		}

		$message->addChild('Firstname',    $rmUser->rm_firstname);
		$message->addChild('Lastname',     $rmUser->rm_lastname);
		$message->addChild('Address1',     $rmUser->rm_address1);
		$message->addChild('Address2',     $rmUser->rm_address2);
		$message->addChild('Address3',     $rmUser->rm_address3);
		$message->addChild('City',         $rmUser->rm_city);
		$message->addChild('Zipcode',      $rmUser->rm_zipcode);
		$message->addChild('Countrycode',  $rmUser->rm_countrycode);
		$message->addChild('Emailaddress', $rmUser->email);
		$message->addChild('Nationality', $rmUser->rm_nationality);
		$message->addChild('TitleRank', $rmUser->title_rank);

		if ($dob = $this->parent->convertDateToPicasso($rmUser->rm_birthday))
		{
			$message->addChild('Birthdate', $dob);
		}

		$message->addChild('Phonenumber',  $rmUser->rm_phone);
		$message->addChild('Mobilephonenumber', $rmUser->rm_mobile);
		$message->addChild('CompanyCvrNr',      $rmUser->organization_vat);
		$message->addChild('CompanyName',      $rmUser->organization);
		$message->addChild('CompanyZip',      $rmUser->organization_zip);
		$message->addChild('CompanyAddress',      implode(', ', $companyAddress));
		$message->addChild('CompanyPhone',      $rmUser->organization_phone);

		return $message;
	}
}
