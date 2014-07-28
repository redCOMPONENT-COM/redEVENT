<?php
/**
 * @package    Redeventsync.lib
 * @copyright  Copyright (C) 2013 redCOMPONENT.com
 * @license    GNU General Public License version 2 or later
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Class RedeventsyncQueueMessage
 *
 * @package  Redeventsync.lib
 * @since    2.5
 */
class ResyncQueueMessage
{
	/**
	 * @var SimpleXMLElement
	 */
	private $xml;

	/**
	 * constructor
	 *
	 * @param   string  $xml  xml message
	 */
	public function __construct($xml)
	{
		$this->xml = new SimpleXMLElement($xml);
	}

	/**
	 * Get message type
	 *
	 * @return string
	 */
	public function getType()
	{
		return $this->xml->getName();
	}

	/**
	 * Return first found transaction id
	 *
	 * @return int|string
	 */
	public function getTransactionId()
	{
		$this->xml->registerXPathNamespace('re', 'http://www.redcomponent.com/redevent');
		$result = $this->xml->xpath('//re:TransactionId');

		if ($result)
		{
			return (int) $result[0];
		}

		return '0';
	}
}
