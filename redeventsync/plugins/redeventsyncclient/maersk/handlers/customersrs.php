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
 * redEVENT sync Customersrs Handler
 *
 * @package  RED.redeventsync
 * @since    2.5
 */
class RedeventsyncHandlerCustomersrs extends RedeventsyncHandlerAbstractmessage
{
	/**
	 * process CreateAttendeeRQ request
	 *
	 * @param   SimpleXMLElement  $xml  xml data for the object
	 *
	 * @return boolean
	 */
	protected function processCustomerRS(SimpleXMLElement $xml)
	{
		require_once JPATH_SITE . '/components/com_redmember/lib/redmemberlib.php';

		$transaction_id = (int) $xml->TransactionId;

		if (isset($xml->Errors))
		{
			// Log
			$this->log(
				REDEVENTSYNC_LOG_DIRECTION_INCOMING, $transaction_id,
				$xml, 'error');

			return true;
		}

		$customer = $xml->Success;

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
		$data['id'] = $user_id;
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
				REDEVENTSYNC_LOG_DIRECTION_INCOMING, $transaction_id,
				$xml, 'ok');
		}
		catch (Exception $e)
		{
			$this->log(
				REDEVENTSYNC_LOG_DIRECTION_INCOMING, $transaction_id,
				$xml, 'failed', $e->getMessage()
			);

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
}
