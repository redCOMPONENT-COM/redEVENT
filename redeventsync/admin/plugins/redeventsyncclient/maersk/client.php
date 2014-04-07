<?php
/**
 * @package     redcomponent.redeventsync
 * @subpackage  com_redeventsync
 * @copyright   Copyright (C) 2013 redCOMPONENT.com
 * @license	    GNU General Public License version 2 or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

class RedeventsyncClientMaersk
{
	const LIVE_URL = 'http://ota.test.techosting.dk:8080/RedWeb/BTSHTTPReceive.dll';
	const TEST_URL = 'http://ota.test.techosting.dk:8080/RedWeb/BTSHTTPReceive.dll';

	/**
	 * The url.
	 *
	 * @var  string
	 */
	protected $url;

	/**
	 * An array of instances.
	 *
	 * @var  array
	 */
	protected static $instance = array();

	/**
	 * timeout in seconds
	 *
	 * @var int
	 */
	protected $timeout = 20;

	/**
	 * Constructor.
	 *
	 * @param   string  $url      The wsdl url
	 * @param   array   $options  The options for the soap client
	 */
	private function __construct($url = null, array $options = array())
	{
		if (!$url)
		{
			// Loopback to our test log
			$url = JURI::root() . '/index.php?option=com_redeventsync&controller=request&task=test';
		}

		if (isset($options['timeout']) && (int) $options['timeout'])
		{
			$this->timeout = (int) $options['timeout'];
		}

		$this->url = $url;
	}

	/**
	 * Get an instance or create it.
	 *
	 * @param   string  $url      The wsdl url
	 * @param   array   $options  The options for the soap client
	 *
	 * @return  client
	 */
	public static function getInstance($url, array $options = array())
	{
		$hash = md5($url . serialize($options));

		if (!isset(self::$instance[$hash]))
		{
			self::$instance[$hash] = new static($url, $options);
		}

		return self::$instance[$hash];
	}

	/**
	 * Get the Sessions list.
	 *
	 * @param   string  $transaction_id  transaction id
	 * @param   string  $from            from date
	 * @param   string  $to              to date
	 * @param   string  $venueCode       venue code
	 *
	 * @return  SimpleXMLElement|boolean  The result or FALSE.
	 */
	public function getSessions($transaction_id, $from, $to, $venueCode = null)
	{
		$xml = new SimpleXMLElement('<GetSessionsRQ xmlns="http://www.redcomponent.com/redevent"></GetSessionsRQ>');
		$xml->addChild('TransactionId', $transaction_id);
		$xml->addChild('FromDate', $from);
		$xml->addChild('ToDate', $to);

		if ($venueCode)
		{
			$xml->addChild('VenueCode', $venueCode);
		}

		$this->validate($xml->asXML(), 'GetSessionsRQ');

		RESyncHelperMessagelog::log(REDEVENTSYNC_LOG_DIRECTION_OUTGOING, 'GetSessionsRQ', $transaction_id, $xml->asXML(), 'sending');

		$resp = $this->send($xml->asXML());

		return $resp;
	}

	/**
	 * Get the attendees list.
	 *
	 * @param   string  $transaction_id  transaction id
	 * @param   string  $sessionCode     session code
	 * @param   string  $venueCode       venue code
	 *
	 * @return  SimpleXMLElement|boolean  The result or FALSE.
	 */
	public function getSessionAttendees($transaction_id, $sessionCode, $venueCode)
	{
		$xml = new SimpleXMLElement('<GetSessionAttendeesRQ xmlns="http://www.redcomponent.com/redevent" />');
		$xml->addChild('TransactionId', $transaction_id);
		$xml->addChild('SessionCode',   $sessionCode);
		$xml->addChild('VenueCode',     $venueCode);

		$this->validate($xml->asXML(), 'GetSessionAttendeesRQ');

		RESyncHelperMessagelog::log(REDEVENTSYNC_LOG_DIRECTION_OUTGOING, 'GetSessionAttendeesRQ', $transaction_id, $xml->asXML(), 'sending');

		$resp = $this->send($xml->asXML());

		return $resp;
	}

	/**
	 * Get the customer details.
	 *
	 * @param   string  $transaction_id  transaction id
	 * @param   string  $email           user email
	 * @param   string  $venueCode       venue code
	 * @param   string  $firstname       first name
	 * @param   string  $lastname        lastname
	 *
	 * @return  SimpleXMLElement|boolean  The result or FALSE.
	 */
	public function getCustomer($transaction_id, $email, $venueCode, $firstname = null, $lastname = null)
	{
		$xml = new SimpleXMLElement('<CustomersRQ xmlns="http://www.redcomponent.com/redevent" />');
		$req = new SimpleXMLElement('<CustomerRQ/>');
		$req->addChild('TransactionId',    $transaction_id);
		$req->addChild('Emailaddress',     $email);

		if ($firstname || $lastname)
		{
			$req->addChild('CurrentFirstname', $firstname);
			$req->addChild('CurrentLastname',  $lastname);
		}

		$req->addChild('VenueCode',        $venueCode);

		$this->appendElement($xml, $req);

		$this->validate($xml->asXML(), 'CustomersRQ');

		RESyncHelperMessagelog::log(REDEVENTSYNC_LOG_DIRECTION_OUTGOING, 'CustomersRQ', $transaction_id, $xml->asXML(), 'sending');

		$resp = $this->send($xml->asXML());

		return $resp;
	}

	/**
	 * send xml data to server
	 *
	 * @param   string  $xml  xml
	 *
	 * @return mixed xml response from server
	 *
	 * @throws RuntimeException
	 */
	public function send($xml)
	{
		$ch = curl_init($this->url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FAILONERROR, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);

		if (!$ch_result = curl_exec($ch))
		{
			$debug = 'Biztalk error for request: ';
			$debug .= $xml;

			if (strstr(curl_error($ch), 'Operation timed out'))
			{
				$status = 'Biztalk server timeout';
			}
			else
			{
				$status = 'error';
			}

			throw new RESyncException(curl_error($ch), $status, $debug);
		}

		curl_close($ch);

		return $ch_result;
	}

	/**
	 * Appends a simplexmlelement to another
	 *
	 * @param   SimpleXMLElement  $parent  the xml element to append to
	 * @param   SimpleXMLElement  $child   the xml element to add
	 *
	 * @return void
	 */
	protected function appendElement(SimpleXMLElement $parent, SimpleXMLElement $child)
	{
		$toDom = dom_import_simplexml($parent);
		$fromDom = dom_import_simplexml($child);
		$toDom->appendChild($toDom->ownerDocument->importNode($fromDom, true));
	}

	/**
	 * validate xml
	 *
	 * @param   string  $xml     xml string
	 * @param   string  $schema  message name
	 *
	 * @return bool
	 *
	 * @throws Exception
	 */
	protected function validate($xml, $schema = null)
	{
		// Enable user error handling
		$prev = libxml_use_internal_errors(true);

		$dom = new DOMDocument;
		$dom->loadXML($xml);

		if (!$schema)
		{
			$schema = $dom->firstChild->nodeName;
		}

		// Validate
		if (! $dom->schemaValidate(dirname(__FILE__) . '/schemas/' . $schema . '.xsd'))
		{
			$error = "Invalid xml data !\n";

			foreach (libxml_get_errors() as $e)
			{
				$error .= $e->message . "\n";
			}

			libxml_clear_errors();
			throw new Exception($error);
		}

		libxml_clear_errors();
		libxml_use_internal_errors($prev);

		return true;
	}
}
