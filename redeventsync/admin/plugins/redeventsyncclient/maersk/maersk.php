<?php
/**
 * @package     Redcomponent.redeventsync
 * @subpackage  plugins.redeventsyncclient
 * @copyright   Copyright (C) 2013 redCOMPONENT.com
 * @license     GNU General Public License version 2 or later
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// Import library dependencies
jimport('joomla.plugin.plugin');

// Register library prefix
JLoader::registerPrefix('Redeventsync', JPATH_LIBRARIES . '/redeventsync');

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
		catch (MissingUserException $e)
		{
			// Try to create user from Customer
			$res = $this->getCustomer($e->email, $e->venueCode);

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
			RedeventsyncHelperMessagelog::log(
				REDEVENTSYNC_LOG_DIRECTION_INCOMING,
				'',
				0,
				$data,
				'error',
				'Parsing error: ' . implode("\n", $errors) . "\n"
			);
			throw new Exception('Parsing error: ' . implode("\n", $errors));
		}

		$xml->preserveWhiteSpace = false;

		$type = $xml->firstChild->nodeName;

		$supported = array(
			'AttendeesRQ', 'AttendeesRS',
			'CustomersRQ', 'CustomersRS',
			'GetSessionAttendeesRQ', 'GetSessionAttendeesRS',
			'GetSessionsRQ', 'GetSessionsRS',
			'SessionsRQ', 'SessionsRS',
		);

		// Check if it's a supported type
		if (! in_array($type, $supported))
		{
			RedeventsyncHelperMessagelog::log(
				REDEVENTSYNC_LOG_DIRECTION_INCOMING,
				'',
				0,
				$data,
				'error',
				'Parsing error: Unsupported schema ' . $type
			);
			throw new Exception('Parsing error: Unsupported schema ' . $type);
		}
		else
		{
			$handler = $this->getHandler($type);
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
			RedeventsyncHelperMessagelog::log(
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
		$handler->handle($data);

		// Display response to request
		if ($msg = $handler->getResponseMessage())
		{
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
			$this->client = RedeventsyncClientMaersk::getInstance(RedeventsyncClientMaersk::TEST_URL);
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
			$this->handle($resp);
		}
		else
		{
			$codes = $this->getVenuesCodes();

			foreach ($codes as $venue)
			{
				$this->logger->write('Syncing ' . $venue);
				$resp = $client->getSessions(time(), $this->getOption('from'), $this->getOption('to'), $venue);
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
			catch (MissingUserException $e)
			{
				// Try to create user from Customer
				$res = $this->getCustomer($e->email, $e->venueCode);

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
	 *
	 * @return int the id of the new user
	 */
	protected function getCustomer($email, $venueCode)
	{
		$client = $this->getClient();
		$response = $client->getCustomer(time(), $email, $venueCode);

		$this->handle($response);

		return true;
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
	 * handles attendee creation
	 *
	 * @param   int  $attendee_id  attendee id
	 *
	 * @return bool
	 */
	public function onHandleAttendeeCreated($attendee_id)
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
	 * handles attendee deletion
	 *
	 * @param   int  $attendee_id  attendee id
	 *
	 * @return bool
	 */
	public function onHandleAttendeeDeleted($attendee_id)
	{
		$model = $this->getHandler('Attendeesrq');
		$model->sendDeleteAttendeeRQ($attendee_id);

		return true;
	}

	/**
	 * Returns the handler
	 *
	 * @param   string  $type  handler name
	 *
	 * @return object
	 */
	protected function getHandler($type)
	{
		// Require class
		require_once dirname(__FILE__) . '/handlers/' . strtolower($type) . '.php';
		$class = 'RedeventsyncHandler' . $type;
		$handler = new $class($this);

		return $handler;
	}
}

class MissingUserException extends Exception
{
	public $email;
	public $venueCode;

	// Redefine the exception to include
	public function __construct($email, $venueCode)
	{
		$this->email = $email;
		$this->venueCode = $venueCode;

		// make sure everything is assigned properly
		parent::__construct('No user found for email ' . $email . ' at venue ' . $venueCode);
	}
}

class InvalidEmailException extends Exception {}
