<?php
/**
 * @package     Redcomponent.redeventsync
 * @subpackage  plugins.redeventsyncclient
 * @copyright	Copyright (C) 2013 redCOMPONENT.com
 * @license		GNU General Public License version 2 or later
 */

defined('_JEXEC') or die();

require_once JPATH_ADMINISTRATOR . '/components/com_redeventsync/defines.php';

/**
 * redEVENT sync Abstractmessage
 *
 * @package     Redcomponent.redeventsync
 * @subpackage  plugins.redeventsyncclient
 *
 * @since    2.5
 */
class RedeventsyncHandlerAbstractmessage
{
	/**
	 * @var SimpleXMLElement response message
	 */
	protected $response;

	protected $messages;

	/**
	 * the parent calling this handler
	 *
	 * @var object
	 */
	protected $parent;

	/**
	 * Constructor
	 *
	 * @param   object  $parent  the calling object
	 */
	public function __construct($parent)
	{
		$this->parent = $parent;
	}

	/**
	 * Handle nodes from xml
	 *
	 * @param   string  $xml_post  the data to parse
	 *
	 * @return  boolean true on success
	 *
	 * @throws Exception
	 */
	public function handle($xml_post)
	{
		$this->initResponse();

		$xml = new SimpleXMLElement($xml_post);

		foreach ($xml->children() as $node)
		{
			if (!method_exists($this, 'process' . $node->getName()))
			{
				throw new Exception('handle error - Unknown node: ' . $node->getName());
			}

			$this->{'process' . $node->getName()}($node);
		}

		return true;
	}

	/**
	 * returns the response message, if applicable, false otherwise
	 *
	 * @return string
	 */
	public function getResponseMessage()
	{
		if ($this->response)
		{
			$this->validate($this->response->asXML());

			return $this->response->asXML();
		}
		else
		{
			return false;
		}
	}

	/**
	 * returns the response message type, if applicable, false otherwise
	 *
	 * @return string
	 */
	public function getResponseMessageType()
	{
		if ($this->response)
		{
			return $this->response->getName();
		}
		else
		{
			return false;
		}
	}

	/**
	 * Init response message if applicable
	 *
	 * @return void
	 */
	protected function initResponse()
	{
		return;
	}

	/**
	 * adds a response to response message
	 *
	 * @param   DOMElement  $node  the message part to add
	 *
	 * @return bool
	 */
	protected function addResponse($node)
	{
		$this->appendElement($this->response, $node);

		return true;
	}

	/**
	 * Appends a simplexmlelement to another
	 *
	 * @param   SimpleXMLElement  $to    the xml element to append to
	 * @param   SimpleXMLElement  $from  the xml element to add
	 *
	 * @return void
	 */
	protected function appendElement(SimpleXMLElement $to, SimpleXMLElement $from)
	{
		$toDom = dom_import_simplexml($to);
		$fromDom = dom_import_simplexml($from);
		$toDom->appendChild($toDom->ownerDocument->importNode($fromDom, true));
	}

	/**
	 * log transaction
	 *
	 * @param   int               $direction      up or down
	 * @param   string            $transactionid  transaction id
	 * @param   SimpleXMLElement  $xml            xml message
	 * @param   string            $status         status
	 * @param   string            $debug          debug info
	 *
	 * @return void
	 */
	protected function log($direction, $transactionid, SimpleXMLElement $xml, $status, $debug = null)
	{
		$this->parent->dblog($direction, $xml->getName(), $transactionid, $xml->asXML(), $status, $debug);
	}

	/**
	 * log message for display
	 *
	 * @param   string  $message  message
	 *
	 * @return void
	 */
	protected function enqueueMessage($message)
	{
		if (is_null($this->messages))
		{
			$this->messages = array();
		}

		$this->messages[] = $message;
	}

	/**
	 * Get messages for debug
	 *
	 * @return array messages
	 */
	public function getMessages()
	{
		return $this->messages;
	}

	/**
	 * return next transaction id
	 *
	 * @return int
	 */
	protected function getNextTransactionId()
	{
		return time();
	}

	/**
	 * write tmp xml file
	 *
	 * @param   SimpleXMLElement  $xml  xml data to write
	 *
	 * @return true on success
	 *
	 * @throws Exception
	 */
	protected function writeFile(SimpleXMLElement $xml)
	{
		$tmp_path = JFactory::getApplication()->getCfg('tmp_path');
		$filename = $xml->getName() . time() . '.xml';

		if (!file_put_contents($tmp_path . '/' . $filename, $xml->asXML()))
		{
			throw new Exception('error writing xml file');
		}

		return true;
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
	public function validate($xml, $schema = null)
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
		if (! $dom->schemaValidate(dirname(dirname(__FILE__)) . '/schemas/' . $schema . '.xsd'))
		{
			$error = "Invalid xml data !\n";

			foreach (libxml_get_errors() as $e)
			{
				$error .= 'line ' . $e->line . ': ' . $e->message . "\n";
			}

			$this->parent->dblog(
				REDEVENTSYNC_LOG_DIRECTION_OUTGOING,
				'',
				0,
				$xml,
				'error',
				'Parsing error: ' . $error . "\n"
			);

			libxml_clear_errors();
			throw new Exception($error);
		}

		libxml_clear_errors();
		libxml_use_internal_errors($prev);

		return true;
	}

	/**
	 * Sends xml message through client
	 *
	 * @param   string  $message  message to send
	 *
	 * @return response
	 */
	protected function send($message)
	{
		$resp = $this->parent->getClient()->send($message);

		if ($resp)
		{
			$this->parent->onHandle('maersk', $resp);
		}

		return true;
	}

	/**
	 * Enqueue message for asynchronuous messaging
	 *
	 * @param   string  $message  xml message to enqueue
	 *
	 * @return void
	 */
	protected function enqueue($message)
	{
		ResyncHelperQueue::add($message, 'maersk');
	}
}
