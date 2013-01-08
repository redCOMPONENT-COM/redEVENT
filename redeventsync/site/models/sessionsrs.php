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
 * redEVENT sync Sessionsrs Model
 *
 * @package  RED.redeventsync
 * @since    2.5
 */
class RedeventsyncModelSessionsrs extends RedeventsyncModelAbstractmessage
{
	/**
	 * process CreateAttendeeRQ request
	 *
	 * @param   SimpleXMLElement  $xml  xml data for the object
	 *
	 * @return boolean
	 */
	protected function processSessionRS(SimpleXMLElement $xml)
	{
		$transaction_id = (int) $xml->TransactionId;

		if (isset($xml->Success))
		{
			// Log
			$this->log(REDEVENTSYNC_LOG_DIRECTION_INCOMING, $transaction_id,
				$xml, 'ok');
		}
		else
		{
			// Log
			$this->log(REDEVENTSYNC_LOG_DIRECTION_INCOMING, $transaction_id,
				$xml, 'error');
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
