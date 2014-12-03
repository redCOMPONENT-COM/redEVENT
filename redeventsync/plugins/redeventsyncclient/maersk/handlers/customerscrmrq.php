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

		if (!$user_id)
		{
			$data['name'] = trim((string) $customer->Firstname) . ' ' . trim((string) $customer->Lastname);
			$data['email'] = (string) $customer->Emailaddress;
			$data['username'] = trim((string) $customer->Firstname) . trim((string) $customer->Lastname);
			$data['username'] = $this->getUniqueUsername($data['username']);
		}

		$companyData = array(
			'organization_name' => (string) $customer->CompanyName,
			'vat' => (string) $customer->CompanyCvrNr,
			'zip' => (string) $customer->CompanyZip,
			'address1' => (string) $customer->CompanyAddress,
			'phone' => (string) $customer->CompanyPhone
		);

		$orgId = $this->getCompanyId($companyData);

		$options = array('no_check' => 1);

		if ($orgId)
		{
			$options['assign_organization'] = $orgId;
		}

		try
		{
			redmemberlib::saveUser(false, $data, false, $options);

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
		if (!isset($data['organization_name']) || !$data['organization_name'])
		{
			return 0;
		}

		if ($id = $this->findCompanyIdByName($data['organization_name']))
		{
			return $id;
		}

		return $this->createCompany($data);
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

		$query->select('organization_id')
			->from('#__redmember_organization')
			->where('organization_name = ' . $db->quote($name));

		$db->setQuery($query);
		$res = $db->loadResult();

		return $res;
	}

	/**
	 * Create company from data
	 *
	 * @param   array  $data  data
	 *
	 * @return int|mixed
	 *
	 * @throws Exception
	 */
	private function createCompany($data)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->insert('#__redmember_organization')
			->set('organization_name = ' . $db->quote($data['organization_name']))
			->set('address1 = ' . $db->quote($data['address1']))
			->set('zip = ' . $db->quote($data['zip']))
			->set('phone = ' . $db->quote($data['phone']))
			->set('vat = ' . $db->quote($data['vat']))
			->set('published = 1');

		$db->setQuery($query);

		if (!$db->execute())
		{
			throw new Exception($db->getErrorMsg());
		}

		return $db->insertid();
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
		$user = redmemberlib::getUserData($userId);

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

		$message->addChild('VenueCode', '');

		if ($user->rm_customerid)
		{
			$message->addChild('CustomerID',      $user->rm_customerid);
		}

		$message->addChild('Firstname',    $user->rm_firstname);
		$message->addChild('Lastname',     $user->rm_lastname);
		$message->addChild('Address1',     $user->rm_address1);
		$message->addChild('Address2',     $user->rm_address2);
		$message->addChild('Address3',     $user->rm_address3);
		$message->addChild('City',         $user->rm_city);
		$message->addChild('Zipcode',      $user->rm_zipcode);
		$message->addChild('Countrycode',  $user->rm_countrycode);
		$message->addChild('Emailaddress', $user->email);
		$message->addChild('Nationality', $user->rm_nationality);
		$message->addChild('TitleRank', $user->titlerank);
		$message->addChild('Birthdate', $user->birthdate);
		$message->addChild('Phonenumber',  $user->rm_phone);
		$message->addChild('Mobilephonenumber', $user->rm_mobile);
		$message->addChild('CompanyCvrNr',      $user->organization_vat);
		$message->addChild('CompanyName',      $user->organization_name);
		$message->addChild('CompanyZip',      $user->organization_zip);
		$message->addChild('CompanyAddress',      implode(', ', $companyAddress));
		$message->addChild('CompanyPhone',      $user->organization_phone);

		return $message;
	}
}
