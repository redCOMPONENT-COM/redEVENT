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
 * Class plgRedformIbcglobasesync
 *
 * @package     Redcomponent
 * @subpackage  ibc
 * @since    2.5
 */
class plgRedformIbcglobase extends JPlugin
{
	private $client;

	private $ws_username;

	private $ws_password;

	private $listId;

	private $mapping;

	private $redFormCore;

	private $answers;

	private $form;

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
	 * Handles onAfterRedformSavedSubmission
	 *
	 * @param   array  $result  result of submission
	 *
	 * @return bool
	 */
	public function onAfterRedformSavedSubmission($result)
	{
		if (!$this->init())
		{
			return true;
		}

		try
		{
			$sid = $result->posts[0]['sid'];

			$rfcore = $this->getRedFormCore();
			$formId = $rfcore->getSidForm($sid);
			$this->form = $rfcore->getForm($formId);

			$answers = $this->getAnswers($sid);

			return $this->saveProfile($answers);
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
	 * @param   array   $answers         submitter answers
	 *
	 * @throws Exception
	 *
	 * @return true on success
	 */
	protected function saveProfile($answers)
	{
		$input = JFactory::getApplication()->input;
		$client = $this->getClient();
		$specials = $this->getSpecialFields();
		$profileFields = $this->getListProfileFields();

		$xmlFields = array($specials->Formularnavn->name => array($this->form->formname));

		if ($val = $input->get('uddannelse', array(), 'array'))
		{
			$xmlFields[$specials->Uddannelse->name] = $val;
		}

		if ($val = $input->get('nyhedsbrev', array(), 'array'))
		{
			$xmlFields[$specials->Nyhedsbrev->name] = $val;
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

		// Is there already a profile for the email, merge special fields (Uddannelse & Nyhedsbrev)
		if ($exists = $client->FindProfiles($this->ws_username, $this->ws_password, $this->listId, 'email', $email))
		{
			$previousValues = $client->GetProfileInformation($this->ws_username, $this->ws_password, $exists[0]->guid);

			foreach ($previousValues->fields as $field)
			{
				if ($field->name == $specials->Nyhedsbrev->name || $field->name == $specials->Uddannelse->name)
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
			$this->redFormCore = new RedFormCore;
		}

		return $this->redFormCore;
	}

	private function getUddannelseFieldId()
	{
		$uddannelseFieldId = (int) $this->params->get('uddannelseFieldId');

		if (!$uddannelseFieldId)
		{
			throw new Exception('ibcglobase plugin: missing uddannelse field id');
		}

		return $uddannelseFieldId;
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

				case 'Uddannelse':
					$res->Uddannelse = $f;
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
