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
		return $this->saveProfile($attendee_id);
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
		return $this->saveProfile($attendee_id);
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
	 * Save profile to globase
	 *
	 * @param   int $attendeeId attendee id
	 *
	 * @return true on success
	 */
	protected function saveProfile($attendeeId)
	{
		require_once JPATH_SITE . '/components/com_redform/redform.core.php';

		$this->ws_username = $this->params->get('ws_username');
		$this->ws_password = $this->params->get('ws_password');
		$this->listId      = (int) $this->params->get('listId');

		$details = $this->getAttendeeDetails($attendeeId);

		if (!$this->listId)
		{
			return true;
		}

		$rfcore = new RedFormCore;
		$answers = $rfcore->getSidsFieldsAnswers(array($details->sid));

		if ($answers)
		{
			$answers = reset($answers);
		}

		$client = $this->getClient();

		$profileFields = $client->GetListFields($this->ws_username, $this->ws_password, $this->listId);

		$xmlFields = array('customfield_15' => array($details->formname));

		if ($details->uddannelse)
		{
			$parts = explode("\n", $details->uddannelse);
			$xmlFields['customfield_18'] = $parts;
		}

		if ($details->nyhedsbrev)
		{
			$parts = explode("\n", $details->nyhedsbrev);
			$xmlFields['customfield_19'] = $parts;
		}

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

					$xmlFields[$pf->name][] = $a->answer;
					break;
				}
			}
		}

		if (!isset($xmlFields['email']) || !reset($xmlFields['email']))
		{
			throw new Exception('email is required');
		}

		$email = reset($xmlFields['email']);

		// Is there already a profile for the email, merge special fields (Uddannelse & Nyhedsbrev)
		if ($exists = $client->FindProfiles($this->ws_username, $this->ws_password, $this->listId, 'email', $email))
		{
			$previousValues = $client->GetProfileInformation($this->ws_username, $this->ws_password, $exists[0]->guid);

			foreach ($previousValues->fields as $field)
			{
				if ($field->name == 'customfield_18' || $field->name == 'customfield_19')
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
		$app = JFactory::getApplication();

		$uddannelseFieldId = (int) $this->params->get('uddannelseFieldId');

		if (!$uddannelseFieldId)
		{
			$app->enqueueMessage('ibcglobase plugin: missing uddannelse field id', 'error');

			return false;
		}

		$nyhedsbrevFieldId = (int) $this->params->get('nyhedsbrevFieldId', 'error');

		if (!$nyhedsbrevFieldId)
		{
			$app->enqueueMessage('ibcglobase plugin: missing nyhedsbrev field id');

			return false;
		}

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('r.*');
		$query->select('e.title');
		$query->select('f.formname');

		// Custom fields for integration
		$query->select(array(
			'e.custom' . $uddannelseFieldId . ' AS uddannelse',
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
