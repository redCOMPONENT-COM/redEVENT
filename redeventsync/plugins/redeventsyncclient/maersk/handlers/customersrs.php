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

		if ($user_id = $db->loadResult())
		{
			$rmUser = RedmemberApi::getUser($user_id);
		}
		else
		{
			$rmUser = RedmemberApi::getUser();
		}

		// Fields should match the actual fields db_name from maersk redmember
		if ($user_id)
		{
			$data['id'] = $rmUser->id;
			$data['joomla_user_id'] = $user_id;
		}

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
		}

		$data['username'] = $data['email'];

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
