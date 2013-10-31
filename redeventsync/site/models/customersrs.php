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
 * redEVENT sync Customersrs Model
 *
 * @package  RED.redeventsync
 * @since    2.5
 */
class RedeventsyncModelCustomersrs extends RedeventsyncModelAbstractmessage
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
			$this->log(REDEVENTSYNC_LOG_DIRECTION_INCOMING, $transaction_id,
				$xml, 'error');
			return true;
		}

		$data = array();

		$data['email'] = (string) $xml->Emailaddress;

		// Find user
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('id');
		$query->from('#__users');
		$query->where('email = ' . $db->quote($data['email']));

		$db->setQuery($query);
		$user_id = $db->loadResult();

		if (!$user_id)
		{
			throw new Exception('User not found');
		}

		$data['id'] = $user_id;
		$data['rm_field12'] = (string) $xml->Firstname;
		$data['rm_field13'] = (string) $xml->Lastname;
		$data['rm_field14'] = (string) $xml->Address1;
		$data['rm_field1'] = (string) $xml->Address2;
		$data['rm_field2'] = (string) $xml->Address3;
		$data['rm_field3'] = (string) $xml->City;
		$data['rm_field4'] = (string) $xml->Zipcode;
		$data['rm_field5'] = (string) $xml->Countrycode;
		$data['rm_field6'] = (string) $xml->Phonenumber;
		$data['rm_field7'] = (string) $xml->Mobilephonenumber;
		$data['rm_field8'] = (string) $xml->Company;
		$data['rm_field9'] = (string) $xml->PointOfSales;
		$data['rm_field10'] = (string) $xml->Salesman;
		$data['rm_field11'] = (string) $xml->Description;

		try
		{
			redmemberlib::saveUser(false, $data);
		}
		catch (Exception $e)
		{
			echo $e->getMessage();
			return false;
		}

		return true;
	}

	/**
	 * Init response message if applicable
	 *
	 * @return void
	 */
	protected function initResponse()
	{

	}
}
