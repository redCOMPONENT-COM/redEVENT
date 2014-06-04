<?php
/**
 * @package     Redcomponent
 * @subpackage  ibc
 * @copyright   Copyright (C) 2013 redCOMPONENT.com
 * @license     GNU General Public License version 2 or later
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Import library dependencies
jimport('joomla.plugin.plugin');

/**
 * Class plgRedeventRedeventsync
 *
 * @package     Redcomponent
 * @subpackage  ibc
 * @since    2.5
 */
class plgRedeventIbcglobase extends JPlugin
{
	private $client;

	private $ws_username;

	private $ws_password;

	private $listId;

	private $mapping;

	private $redFormCore;

	private $answers;

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
	 * handles attendee created
	 *
	 * @param   int  $attendee_id  attendee id
	 *
	 * @return bool
	 */
	public function onAttendeeCreated($attendee_id)
	{
		return $this->saveAttendeeProfile($attendee_id);
	}

	/**
	 * handles attendee modified
	 *
	 * @param   int  $attendee_id  attendee id
	 *
	 * @return bool
	 */
	public function onAttendeeModified($attendee_id)
	{
		return $this->saveAttendeeProfile($attendee_id);
	}

	/**
	 * handles attendee cancelled
	 *
	 * @param   int  $attendee_id  attendee id
	 *
	 * @return bool
	 */
	public function onAttendeeCancelled($attendee_id)
	{
		return true;
	}

	/**
	 * handles attendee deleted
	 *
	 * @param   int  $attendee_id  attendee id
	 *
	 * @return bool
	 */
	public function onAttendeeDeleted($attendee_id)
	{
		return true;
	}

	/**
	 * Handles adding with session id and answers
	 *
	 * @param   int    $xref     session id
	 * @param   array  $answers  answers from redFORM
	 *
	 * @return bool
	 */
	public function onGlobaseAddProfile($xref, $answers)
	{
		if (!$this->init())
		{
			return true;
		}

		try
		{
			$sessionDetails = $this->getSessionDetails($xref);

			return $this->saveProfile($sessionDetails, $answers);
		}
		catch (Exception $e)
		{
			$app = JFactory::getApplication();
			$app->enqueueMessage($e->getMessage(), 'error');

			return false;
		}
	}

	/**
	 * Save profile to globase
	 *
	 * @param   int  $attendeeId  attendee id
	 *
	 * @return true on success
	 */
	protected function saveAttendeeProfile($attendeeId)
	{
		if (!$this->init())
		{
			return true;
		}

		try
		{
			$sessionDetails = $this->getAttendeeDetails($attendeeId);
			$answers = $this->getAnswers($sessionDetails->sid);

			return $this->saveProfile($sessionDetails, $answers);
		}
		catch (Exception $e)
		{
			$app = JFactory::getApplication();
			$app->enqueueMessage($e->getMessage(), 'error');

			return false;
		}
	}

	/**
	 * init and return true if there is a list configured
	 *
	 * @return bool
	 */
	private function init()
	{
		$this->ws_username = $this->params->get('ws_username');
		$this->ws_password = $this->params->get('ws_password');
		$this->listId      = (int) $this->params->get('listId');

		return $this->listId ? true : false;
	}

	/**
	 * Save profile to globase
	 *
	 * @param   object  $sessionDetails  session details
	 * @param   array   $answers         submitter answers
	 *
	 * @throws Exception
	 *
	 * @return true on success
	 */
	protected function saveProfile($sessionDetails, $answers)
	{
		$client = $this->getClient();
		$specials = $this->getSpecialFields();
		$profileFields = $this->getListProfileFields();

		$xmlFields = array($specials->Formularnavn->name => array($sessionDetails->formname));

		if ($sessionDetails->nyhedsbrev)
		{
			$parts = explode("\n", $sessionDetails->nyhedsbrev);
			$xmlFields[$specials->Nyhedsbrev->name] = $parts;
		}

		$isMapped = false;

		foreach ($profileFields as $pf)
		{
			foreach ($answers as $a)
			{
				if ($this->getGlobaseMapping($a->id) == $pf->name)
				{
					if (!isset($xmlFields[$pf->name]))
					{
						$xmlFields[$pf->name] = array();
					}

					$isMapped = true;
					$xmlFields[$pf->name][] = $a->answer;
					break;
				}
			}
		}

		if (!$isMapped)
		{
			throw new Exception('Globase: no map fields found for this form');
		}

		if (!isset($xmlFields['email']) || !reset($xmlFields['email']))
		{
			throw new Exception('Globase: email is required');
		}

		$email = reset($xmlFields['email']);

		// Is there already a profile for the email, merge special fields (Nyhedsbrev)
		if ($exists = $client->FindProfiles($this->ws_username, $this->ws_password, $this->listId, 'email', $email))
		{
			$previousValues = $client->GetProfileInformation($this->ws_username, $this->ws_password, $exists[0]->guid);

			foreach ($previousValues->fields as $field)
			{
				if ($field->name == $specials->Nyhedsbrev->name)
				{
					$xmlFields[$field->name][] = $field->value;
				}
			}
		}

		$xml = '';

		foreach ($xmlFields as $name => $values)
		{
			$values = array_unique($values);

			foreach ($values as $v)
			{
				$xml .= '<' . $name  . '>' . $v . '</' . $name  . '>';
			}
		}

		// Do the update
		$resp = $client->SaveProfileV2(
			$this->ws_username, $this->ws_password, $this->listId, $xml, 'email'
		);

		return true;
	}

	/**
	 * Return answers for sid
	 *
	 * @param   int  $sid  sid
	 *
	 * @return mixed
	 */
	private function getAnswers($sid)
	{
		if (!isset($this->answers[$sid]))
		{
			$answers = $this->getRedFormCore()->getSidsFieldsAnswers(array($sid));

			if ($answers)
			{
				$answers = reset($answers);
			}

			if (!$this->answers)
			{
				$this->answers = array();
			}

			$this->answers[$sid] = $answers;
		}

		return $this->answers[$sid];
	}

	private function getRedFormCore()
	{
		if (!$this->redFormCore)
		{
			require_once JPATH_SITE . '/components/com_redform/redform.core.php';
			$this->redFormCore = new RedFormCore;
		}

		return $this->redFormCore;
	}

	private function getNyhedsbrevFieldId()
	{
		$nyhedsbrevFieldId = (int) $this->params->get('nyhedsbrevFieldId', 'error');

		if (!$nyhedsbrevFieldId)
		{
			throw new Exception('ibcglobase plugin: missing nyhedsbrev field id');
		}

		return $nyhedsbrevFieldId;
	}

	/**
	 * Get session details
	 *
	 * @param   int  $xref  session id
	 *
	 * @return bool|mixed
	 */
	private function getSessionDetails($xref)
	{
		$nyhedsbrevFieldId = $this->getNyhedsbrevFieldId();

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('e.title');
		$query->select('f.formname');

		// Custom fields for integration
		$query->select(array(
			'e.custom' . $nyhedsbrevFieldId . ' AS nyhedsbrev'
		));

		$query->from('#__redevent_event_venue_xref AS x');
		$query->join('INNER', '#__redevent_events AS e ON e.id = x.eventid');
		$query->join('INNER', '#__rwf_forms AS f ON f.id = e.redform_id');
		$query->where('x.id = ' . $xref);

		$db->setQuery($query);
		$res = $db->loadObject();

		return $res;
	}

	/**
	 * Get attendees details
	 *
	 * @param   int  $attendeeId  attendee id
	 *
	 * @return bool|mixed
	 */
	protected function getAttendeeDetails($attendeeId)
	{
		$nyhedsbrevFieldId = $this->getNyhedsbrevFieldId();

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('r.*');
		$query->select('e.title');
		$query->select('f.formname');

		// Custom fields for integration
		$query->select(array(
			'e.custom' . $nyhedsbrevFieldId . ' AS nyhedsbrev'
		));

		$query->from('#__redevent_register AS r');
		$query->join('INNER', '#__redevent_event_venue_xref AS x ON x.id = r.xref');
		$query->join('INNER', '#__redevent_events AS e ON e.id = x.eventid');
		$query->join('INNER', '#__rwf_forms AS f ON f.id = e.redform_id');
		$query->where('r.id = ' . $attendeeId);

		$db->setQuery($query);
		$res = $db->loadObject();

		return $res;
	}

	/**
	 * return special fields
	 *
	 * @return object
	 */
	protected function getSpecialFields()
	{
		$client = $this->getClient();

		$fields = $client->GetListFields($this->ws_username, $this->ws_password, $this->listId);

		$res = new stdclass;

		foreach ($fields as $f)
		{
			switch ($f->nicename)
			{
				case 'Formularnavn':
					$res->Formularnavn = $f;
					break;

				case 'Nyhedsbrev':
					$res->Nyhedsbrev = $f;
					break;
			}
		}

		return $res;
	}

	/**
	 * Return plfile fields for the list
	 *
	 * @return mixed
	 */
	protected function getListProfileFields()
	{
		$client = $this->getClient();

		return $client->GetListFields($this->ws_username, $this->ws_password, $this->listId);
	}

	/**
	 * Return soap client
	 *
	 * @return object|SoapClient
	 */
	protected function getClient()
	{
		if (!$this->client)
		{
			$this->client = new SoapClient("http://ws.globase.com/v2/service.php?class=globaseSOAP&wsdl");
		}

		return $this->client;
	}

	/**
	 * Returns the globase field name mapped to a redFORM field, if defined
	 *
	 * @param   int  $redformFieldId  redFORM field id
	 *
	 * @return bool|string the globase field name, or false if not mapped
	 *
	 * @throws Exception
	 */
	protected function getGlobaseMapping($redformFieldId)
	{
		if (!$this->mapping)
		{
			$result = array();
			$mapping = $this->params->get('redformMapping');

			if (!strstr($mapping, ';'))
			{
				throw new Exception('invalid mapping');
			}

			$lines = explode("\n", $mapping);

			foreach ($lines as $l)
			{
				if ((!(strpos($l, '#') === 0)) && strstr($l, ';'))
				{
					list($fid, $fname) = explode(";", $l);
					$fid = (int) $fid;
					$fname = trim($fname);

					if ($fid)
					{
						$result[$fid] = $fname;
					}
				}
			}

			$this->mapping = $result;
		}

		return isset($this->mapping[$redformFieldId]) ? $this->mapping[$redformFieldId] : false;
	}

}
