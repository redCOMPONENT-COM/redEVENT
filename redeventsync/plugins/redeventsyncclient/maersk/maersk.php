<?php
/**
 * @package     Redcomponent.redeventsync
 * @subpackage  plugins.redeventsyncclient
 * @copyright   Copyright (C) 2013 redCOMPONENT.com
 * @license     GNU General Public License version 2 or later
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$loader = JPATH_LIBRARIES . '/redeventsync/bootstrap.php';

if (!file_exists($loader))
{
	throw new Exception(JText::_('COM_redeventsync_LIB_INIT_FAILED'), 404);
}

include_once $loader;

// Bootstraps redEVENTSYNC
ResyncBootstrap::bootstrap();

// Register library prefix
JLoader::registerPrefix('Redevent', JPATH_LIBRARIES . '/redevent');
JLoader::registerPrefix('Rdf', JPATH_LIBRARIES . '/redform');

JLoader::registerPrefix('Plgresyncmaersk', __DIR__);
RLoader::registerPrefix('Redmember', JPATH_LIBRARIES . '/redmember');

require_once 'helper.php';
require_once 'client.php';

/**
 * Class plgRedeventsyncclientMaersk
 *
 * @package     Redcomponent.redeventsync
 * @subpackage  plugins.redeventsyncclient
 * @since       2.5
 */
class plgRedeventsyncclientMaersk extends JPlugin
{
	/**
	 * the plugin name, to check if it's indeed been called
	 *
	 * @var string
	 */
	protected $name = 'maersk';

	/**
	 * runtime options
	 *
	 * @var mixed object or array
	 */
	protected $options = null;

	/**
	 * Debug logger
	 *
	 * @var object
	 */
	protected $logger = null;

	/**
	 * Client object to post data to
	 *
	 * @var object
	 */
	protected $client = null;

	protected $dblogger = null;

	/**
	 * constructor
	 *
	 * @param   object  $subject  subject
	 * @param   array   $params   params
	 */
	public function __construct($subject, $params)
	{
		parent::__construct($subject, $params);
		$this->loadLanguage();
	}

	/**
	 * Handle data posted to sync engine
	 *
	 * @param   string  $name  plugin name
	 * @param   string  $data  the posted data
	 *
	 * @return boolean true on success
	 *
	 * @throws Exception
	 */
	public function onHandle($name, $data)
	{
		if (!$name == $this->name)
		{
			return true;
		}

		try
		{
			$this->handle($data);
		}
		catch (PlgresyncmaerskExceptionMismatchuser $e)
		{
			// Try to update/create user from Customer
			$res = $this->getCustomer($e->email, $e->venueCode, $e->firstname, $e->lastname);

			if (!$res)
			{
				throw $e;
			}

			// Try again !
			$this->handle($data);
		}

		return true;
	}

	/**
	 * Send a message through the plugin
	 *
	 * @param   string  $plugin     plugin name
	 * @param   string  $message    message to send
	 * @param   bool    &$response  true if message was successfully sent
	 *
	 * @return void
	 *
	 * @throws LogicException
	 */
	public function onSend($plugin, $message, &$response)
	{
		if (!strstr($plugin, $this->name))
		{
			return;
		}

		$client = $this->getClient();

		if (!$this->getType($message))
		{
			throw new LogicException('Undefined message type');
		}

		try
		{
			$res = $client->send($message);
			$this->dblog(REDEVENTSYNC_LOG_DIRECTION_OUTGOING, $this->getType($message), $this->getTransactionId($message), $message, 'sent', $res ? 'response: ' . $res : null);

			if ($this->getType($res))
			{
				$this->handle($res);
			}

			$response = true;
		}
		catch (Exception $e)
		{
			$this->dblog(REDEVENTSYNC_LOG_DIRECTION_OUTGOING, $this->getType($message), $this->getTransactionId($message), $message, 'error', $e->getMessage());
			$response = false;
		}
	}

	/**
	 * set database logger (for test unit...)
	 *
	 * @param   object  $logger  logger object, must implement log method
	 *
	 * @return void
	 */
	public function setDbLogger($logger)
	{
		$this->dblogger = $logger;
	}

	/**
	 * set client (for test unit...)
	 *
	 * @param   object  $client  client object
	 *
	 * @return void
	 */
	public function setClient($client)
	{
		$this->client = $client;
	}

	/**
	 * Handle data posted to sync engine
	 *
	 * @param   string  $data  the posted data
	 *
	 * @return boolean true on success
	 *
	 * @throws Exception
	 */
	protected function handle($data)
	{
		// Enable user error handling
		$prev = libxml_use_internal_errors(true);

		$xml = new DOMDocument;

		if (!$xml->loadXml($data))
		{
			$errors = array();

			foreach (libxml_get_errors() as $error)
			{
				$errors[] = $error->message;
			}

			libxml_clear_errors();
			$this->dblog(
				REDEVENTSYNC_LOG_DIRECTION_INCOMING,
				'',
				0,
				$data,
				'Parsing error',
				'Parsing error: ' . implode("\n", $errors) . "\n"
			);
			throw new Exception('Parsing error: ' . implode("\n", $errors));
		}

		// Log the whole message
		$this->dblog(
			REDEVENTSYNC_LOG_DIRECTION_INCOMING,
			$xml->firstChild->nodeName,
			$this->getTransactionId($data),
			$data,
			'received'
		);

		$xml->preserveWhiteSpace = false;

		$type = $xml->firstChild->nodeName;

		// Check if it's a supported type
		if (!$this->isSupportedSchema($type))
		{
			$this->dblog(
				REDEVENTSYNC_LOG_DIRECTION_INCOMING,
				'',
				0,
				$data,
				'error',
				'Parsing error: Unsupported schema ' . $type
			);
			throw new Exception('Parsing error: Unsupported schema ' . $type);
		}

		// Validate
		if (! $xml->schemaValidate(dirname(__FILE__) . '/schemas/' . $type . '.xsd'))
		{
			$errors = array("Invalid xml data !");

			$errors[] = $xml->saveXML();

			foreach (libxml_get_errors() as $error)
			{
				$errors[] = 'line ' . $error->line . ': ' . $error->message;
			}

			libxml_clear_errors();
			$this->dblog(
				REDEVENTSYNC_LOG_DIRECTION_INCOMING,
				'',
				0,
				$data,
				'error',
				'Parsing error: ' . implode("\n", $errors)
			);
			throw new Exception('Parsing error: ' . implode("\n", $errors));
		}

		// Process !
		$handler = $this->getHandler($type);
		$handler->handle($data);

		// Display response to request
		if ($msg = $handler->getResponseMessage())
		{
			$this->dblog(
				REDEVENTSYNC_LOG_DIRECTION_OUTGOING,
				$this->getType($msg),
				$this->getTransactionId($msg),
				$msg,
				'see message'
			);

			echo $msg;
		}

		// Add to log
		if ($this->logger && $log = $handler->getMessages())
		{
			foreach ($log as $l)
			{
				$this->logger->write($l);
			}
		}

		libxml_use_internal_errors($prev);

		return true;
	}

	/**
	 * Performs a sync with the client
	 *
	 * @param   string  $name     plugin name
	 * @param   object  &$log     log object
	 * @param   array   $options  options for sync (dates from/to, etc...)
	 *
	 * @return boolean true on success
	 */
	public function onSync($name, &$log, $options = null)
	{
		$this->setOptions($options);

		$client = $this->getClient();

		$this->logger = $log;

		switch ($this->getOption('object'))
		{
			case 'attendees':
				return $this->syncAttendees();
				break;

			case 'sessions':
				return $this->syncSessions();
				break;
		}

		return true;
	}

	/**
	 * return client to post data to
	 *
	 * @return RedeventsyncClientMaersk
	 */
	public function getClient()
	{
		if (!$this->client)
		{
			$client_url = $this->params->get('client_url', RedeventsyncClientMaersk::TEST_URL);
			$this->client = RedeventsyncClientMaersk::getInstance($client_url, array('timeout' => $this->params->get('timeout', 20)));
		}

		return $this->client;
	}

	/**
	 * Set Options
	 *
	 * @param   mixed  $options  object or array
	 *
	 * @return bool true on success
	 *
	 * @throws Exception
	 */
	protected function setOptions($options)
	{
		if (!$options)
		{
			$this->options = array();
		}

		if (!(is_object($options) || is_array($options)))
		{
			throw new Exception('Wrong options type');
		}

		if (is_object($options))
		{
			$options = get_object_vars($options);
		}

		$this->options = $options;

		return true;
	}

	/**
	 * return option value
	 *
	 * @param   string  $name     option name
	 * @param   string  $default  option default value
	 *
	 * @return string
	 */
	protected function getOption($name, $default = '')
	{
		return isset($this->options[$name]) ? $this->options[$name] : $default;
	}

	/**
	 * Sync sessions
	 *
	 * @return bool
	 */
	protected function syncSessions()
	{
		$client = $this->getClient();

		if ($venue = $this->getOption('VenueCode'))
		{
			$this->logger->write('Syncing ' . $venue);
			$resp = $client->getSessions(time(), $this->getOption('from'), $this->getOption('to'), $venue);

			if (!$resp)
			{
				return false;
			}

			$this->handle($resp);
		}
		else
		{
			$codes = $this->getVenuesCodes();

			foreach ($codes as $venue)
			{
				$this->logger->write('Syncing ' . $venue);
				$resp = $client->getSessions(time(), $this->getOption('from'), $this->getOption('to'), $venue);

				if (!$resp)
				{
					return false;
				}

				$this->handle($resp);
			}
		}

		return true;
	}

	/**
	 * Get all venues codes
	 *
	 * @return array
	 */
	protected function getVenuesCodes()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('v.venue_code');
		$query->from('#__redevent_venues AS v');
		$query->where('CHAR_LENGTH(v.venue_code) > 0');

		$db->setQuery($query);
		$res = $db->loadColumn();

		return $res;
	}

	/**
	 * Sync attendees
	 *
	 * @return bool
	 */
	protected function syncAttendees()
	{
		$client = $this->getClient();

		// Get sessions
		$sessions = $this->getSessions($this->getOption('from'), $this->getOption('to'));

		foreach ($sessions as $s)
		{
			// Get attendees
			$resp = $client->getSessionAttendees(time(), $s->session_code, $s->venue_code);

			try
			{
				$this->handle($resp);
			}
			catch (PlgresyncmaerskExceptionMismatchuser $e)
			{
				// Try to update/create user from Customer
				$res = $this->getCustomer($e->email, $e->venueCode, $e->firstname, $e->lastname);

				if (!$res)
				{
					throw $e;
				}

				// Try again the attendee now
				$this->handle($resp);
			}
		}
	}

	/**
	 * Get new user with CustomerRQ
	 *
	 * @param   string  $email      email
	 * @param   string  $venueCode  venue code
	 * @param   string  $firstname  first name
	 * @param   string  $lastname   lastname
	 *
	 * @return boolean true on success
	 */
	public function getCustomer($email, $venueCode, $firstname = null, $lastname = null)
	{
		$client = $this->getClient();
		$response = $client->getCustomer(time(), $email, $venueCode, $firstname, $lastname);

		return $this->handle($response);
	}

	/**
	 * return sessions between two dates
	 *
	 * @param   string  $from  from date
	 * @param   string  $to    to date
	 *
	 * @return array
	 */
	protected function getSessions($from, $to)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$fromDate = JDate::getInstance($from);
		$toDate = JDate::getInstance($to);

		$query->select('x.*, v.venue_code');
		$query->from('#__redevent_event_venue_xref AS x');
		$query->join('INNER', '#__redevent_venues AS v ON v.id = x.venueid');
		$query->where('x.dates BETWEEN ' . $db->quote($fromDate->toSql()) . ' AND ' . $db->quote($toDate->toSql()));
		$query->where('CHAR_LENGTH(x.session_code) > 0');
		$query->where('CHAR_LENGTH(v.venue_code) > 0');

		$db->setQuery($query);
		$sessions = $db->loadObjectList();

		return $sessions;
	}

	/**
	 * returns root node name
	 *
	 * @param   string  $xml_string  xml data as string
	 *
	 * @return string
	 */
	protected function getType($xml_string)
	{
		$doc = new SimpleXMLElement($xml_string);
		return $doc->getName();
	}

	/**
	 * handles session save to generate Create/Modify SessionRQ
	 *
	 * @param   int   $session_id  session id
	 * @param   bool  $isNew       is new session
	 *
	 * @return bool
	 */
	public function onHandleAfterSessionSave($session_id, $isNew = false)
	{
		// Not supported by biztalk
		return true;

		$model = $this->getHandler('Sessionsrq');

		if ($isNew)
		{
			$model->sendCreateSessionRq($session_id);
		}
		else
		{
			$model->sendModifySessionRq($session_id);
		}

		return true;
	}

	/**
	 * handles session delete to generate DeleteSessionRQ
	 *
	 * @param   string  $session_code  session code
	 *
	 * @return bool
	 */
	public function onHandleAfterSessionDelete($session_code)
	{
		// Not supported by biztalk
		return true;

		$model = $this->getHandler('Sessionsrq');
		$model->sendDeleteSessionRQ($session_code);

		return true;
	}

	/**
	 * handles attendee creation, only once confirmed
	 *
	 * @param   int  $attendee_id  attendee id
	 *
	 * @return bool
	 */
	public function onHandleAttendeeConfirmed($attendee_id)
	{
		$model = $this->getHandler('Attendeesrq');
		$model->sendCreateAttendeeRQ($attendee_id);

		return true;
	}

	/**
	 * handles attendee modification
	 *
	 * @param   int  $attendee_id  attendee id
	 *
	 * @return bool
	 */
	public function onHandleAttendeeModified($attendee_id)
	{
		$model = $this->getHandler('Attendeesrq');
		$model->sendModifyAttendeeRQ($attendee_id);

		return true;
	}

	/**
	 * handles attendee cancellation
	 *
	 * @param   int  $attendee_id  attendee id
	 *
	 * @return bool
	 */
	public function onHandleAttendeeCancelled($attendee_id)
	{
		return $this->onHandleAttendeeModified($attendee_id);
	}

	/**
	 * handles attendee deletion
	 *
	 * @param   int  $attendee_id  attendee id
	 *
	 * @return bool
	 */
	public function onHandleAttendeeDeleted($attendee_id)
	{
		// Disabling this as techotel doesn't handle it
		return true;
		$model = $this->getHandler('Attendeesrq');
		$model->sendDeleteAttendeeRQ($attendee_id);

		return true;
	}

	/**
	 * handles attendee paid
	 *
	 * @param   int  $attendee_id  attendee id
	 *
	 * @return bool
	 */
	public function onHandleAttendeePaid($attendee_id)
	{
		return $this->onHandleAttendeeModified($attendee_id);
	}

	/**
	 * handle user saved
	 *
	 * @param   int   $userId  user id
	 * @param   bool  $isNew   true if new user
	 *
	 * @return bool
	 */
	public function onHandleUserSaved($userId, $isNew)
	{
		// Not supported by picasso, so disable it
//		$model = $this->getHandler('Customerscrmrq');
//		$model->sendCustomersCRMRQ($userId, $isNew);

		return true;
	}

	/**
	 * Returns the handler
	 *
	 * @param   string  $type  handler name
	 *
	 * @return RedeventsyncHandlerAbstractmessage
	 */
	protected function getHandler($type)
	{
		// Require class
		require_once dirname(__FILE__) . '/handlers/' . strtolower($type) . '.php';
		$class = 'RedeventsyncHandler' . $type;
		$handler = new $class($this);

		return $handler;
	}

	/**
	 * Check if a schema is supported
	 *
	 * @param   string  $schema  schema (tag) name
	 *
	 * @return bool
	 */
	private function isSupportedSchema($schema)
	{
		$supported = $this->getSupportedSchema();

		return in_array($schema, $supported);
	}

	/**
	 * return supported schemas
	 *
	 * @return array
	 */
	private function getSupportedSchema()
	{
		jimport('joomla.filesystem.folder');
		$files = JFolder::files(dirname(__FILE__) . '/schemas/', '.xsd');

		foreach ($files as &$file)
		{
			$file = substr($file, 0, -4);
		}

		return $files;
	}

	/**
	 * log transaction
	 *
	 * @param   int     $direction      up or down
	 * @param   string  $type           message type
	 * @param   string  $transactionid  transaction id
	 * @param   string  $message        xml message
	 * @param   string  $status         status
	 * @param   string  $debug          debug info
	 *
	 * @return void
	 */
	public function dblog($direction, $type, $transactionid, $message, $status, $debug = null)
	{
		if ($this->dblogger)
		{
			$this->dblogger->log($direction, $type, $transactionid, $message, $status, $debug);
		}
		else
		{
			ResyncHelperMessagelog::log($direction, $type, $transactionid, $message, $status, $debug);
		}
	}

	/**
	 * Return first found transaction id
	 *
	 * @param   string  $xml  xml
	 *
	 * @return int|string
	 */
	public function getTransactionId($xml)
	{
		$simpleXml = new SimpleXMLElement($xml);
		$simpleXml->registerXPathNamespace('re', 'http://www.redcomponent.com/redevent');
		$result = $simpleXml->xpath('//re:TransactionId');

		if ($result)
		{
			$all = array();

			foreach ($result as $tid)
			{
				$all[] = (int) $tid;
			}

			return implode(', ', $all);
		}

		return '0';
	}

	/**
	 * Convert date format from dd-mm-yyyy to ddmmyy
	 *
	 * @param   string  $redMemberdate  date
	 *
	 * @return string
	 */
	public function convertDateToPicasso($redMemberdate)
	{
		if (preg_match("/^([0-9]{1,2})-([0-9]{1,2})-([0-9]{4})$/", $redMemberdate, $matches))
		{
			return sprintf("%02d%02d%s", $matches[1], $matches[2], substr($matches[3], 2));
		}
		elseif (preg_match("/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})$/", $redMemberdate, $matches))
		{
			return sprintf("%02d%02d%s", $matches[3], $matches[2], substr($matches[1], 2));
		}

		return false;
	}

	/**
	 * Convert date format from ddmmyy to dd-mm-yyyy
	 *
	 * @param   string  $date  date
	 *
	 * @return string
	 */
	public function convertDateFromPicasso($date)
	{
		if (preg_match("/^[0-9]{6}$/", $date))
		{
			$year = substr($date, 4, 2);
			$year = ($year < 20) ? '20' . $year : '19' . $year;

			return substr($date, 0, 2) . '-' . substr($date, 2, 2) . '-' . $year;
		}

		return false;
	}
}
