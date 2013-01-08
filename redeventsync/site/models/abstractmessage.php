<?php
/**
 * @package		redcomponent.redeventsync
 * @subpackage	com_redeventsync
 * @copyright	Copyright (C) 2013 redCOMPONENT.com
 * @license		GNU General Public License version 2 or later
 */

defined('_JEXEC') or die();

require_once JPATH_ADMINISTRATOR . '/components/com_redeventsync/defines.php';

/**
 * redEVENT sync Abstractmessage Model
 *
 * @package  RED.redeventsync
 * @since    2.5
 */
class RedeventsyncModelAbstractmessage extends FOFModel
{
	/**
	 * @var SimpleXMLElement response message
	 */
	protected $response;

	/**
	 * handle nodes from xml
	 *
	 * @param   string  $xml  the dom node
	 *
	 * @return string xml
	 *
	 * @throws Exception
	 */
	public function handle($xml_post)
	{
		$this->initResponse();

		$xml = new SimpleXMLElement($xml_post);

		foreach ($xml->children() as $node)
		{
			try
			{
				if (!method_exists($this, 'process' . $node->getName()))
				{
					throw new Exception('handle error - Unknown node: ' . $node->getName());
				}

				$this->{'process' . $node->getName()}($node);
			}
			catch (Exception $e)
			{
				echo $e->getMessage();
				JFactory::getApplication()->close();
			}
		}
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
			return $this->response->asXML();
		}
		else
		{
			return true;
		}
	}

	/**
	 * Init response message if applicable
	 *
	 * @return void
	 */
	protected function initResponse()
	{

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
	 * @param   int               $transactionid  transaction id
	 * @param   SimpleXMLElement  $xml            xml message
	 * @param   string            $status         status
	 *
	 * @return void
	 */
	protected function log($direction, $transactionid, SimpleXMLElement $xml, $status)
	{
		$log = FOFTable::getAnInstance('logs', 'RedeventsyncTable');
		$log->direction = $direction;
		$log->transactionid = $transactionid;
		$log->type = $xml->getName();
		$log->message = $xml->asXML();
		$log->status = $status;
		$log->date = JFactory::getDate()->toSql(true);
		$log->store();
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
	 * @throws Exception
	 */
	public function validate($xml, $schema)
	{
		// Enable user error handling
		$prev = libxml_use_internal_errors(true);

		$dom = new DOMDocument();
		$dom->loadXML($xml);

		// Validate
		if (! $dom->schemaValidate(JPATH_SITE . '/components/com_redeventsync/schemas/' . $schema . '.xsd'))
		{
			$error = "Invalid xml data !\n";

			foreach (libxml_get_errors() as $e)
			{
				$error .= $e->message . "\n";
			}

			throw new Exception($error);

			libxml_clear_errors();
			JFactory::getApplication()->close();
		}

		libxml_clear_errors();
		libxml_use_internal_errors($prev);

		return true;
	}
}
