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
class plgRedeventIbcquickbook extends JPlugin
{
	private $mapping;
	private $rfcore;
	private $employmentStatusFieldIds;

	private $xref;
	private $sid;
	private $answers;

	private $sessionFormId;
	private $sessionFormFields;
	private $sessionDetails;

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
	 * Handle before registration
	 *
	 * @param   int  $attendee_id  attendee id
	 *
	 * @return bool
	 */
	public function onBeforeRegistration($xref, &$redformResponse, &$notification)
	{
		$this->xref = $xref;
		$this->setSidFromResponse($redformResponse);

		$status = $this->getEmploymentStatus();

		if ($status == 'unemployed' || $status == 'other')
		{
			$this->triggerGlobase();
			$this->triggerMailflow($status, $redformResponse);

			return $this->notifyAndStop($notification);
		}

		// We need to match response to real registration form, if not the same as submitted form
		$redformResponse = $this->newRedFormResponse($redformResponse);

		return true;
	}

	private function setSidFromResponse($redformResponse)
	{
		if (!isset($redformResponse->posts[0]['sid']))
		{
			throw new Exception('Invalid redform response');
		}

		$this->sid = $redformResponse->posts[0]['sid'];

		return $this->sid;
	}

	private function getSid()
	{
		if (!$this->sid)
		{
			throw new Exception('Sid not initialized');
		}

		return $this->sid;
	}

	private function getAnswers()
	{
		if (!$this->answers)
		{
			$rfcore = $this->getRedFormCore();

			$sid = $this->getSid();
			$answers = $rfcore->getSidsFieldsAnswers(array($sid));

			if (!isset($answers[$sid]))
			{
				throw new Exception('Invalid sid');
			}

			$this->answers = $answers[$sid];
		}

		return $this->answers;
	}

	private function getEmploymentStatus()
	{
		foreach ($this->getAnswers() as $field)
		{
			if ($this->isEmployementStatusField($field))
			{
				return $field->answer;
			}
		}

		return false;
	}

	private function isEmployementStatusField($field)
	{
		if (!$this->employmentStatusFieldIds)
		{
			$ids = explode(",", $this->params->get('employedFieldMapping'));
			JArrayHelper::toInteger($ids);
			$this->employmentStatusFieldIds = $ids;
		}

		return in_array($field->id, $this->employmentStatusFieldIds);
	}

	private function triggerMailflow($status)
	{
		$path = JPATH_SITE . '/cli/newsletter/classes/mailflow.php';;

		if (!file_exists($path))
		{
			return;
		}

		$mailflowId = $this->getMailflowId($status);
		$email = $this->getSubmissionEmail();

		include_once $path;
		$mailflow = new Mailflow($mailflowId, $email, $this->xref, false);
		$mailflow->start();
	}

	private function getMailFlowId($status)
	{
		$session = $this->getSessionDetails();

		switch ($status)
		{
			case 'unemployed':
				$customId = (int) $this->params->get('unemployedMailflowFieldId');
				break;

			case 'other':
				$customId = (int) $this->params->get('otherMailflowFieldId');
				break;
		}

		if (!$customId)
		{
			throw new Exception('Missing mailflow id for status ' . $status);
		}

		$mailflowId = $session->{'custom' . $customId};

		return $mailflowId;
	}

	private function getSubmissionEmail()
	{
		foreach ($this->getAnswers() as $field)
		{
			if ($field->fieldtype == 'email')
			{
				return $field->answer;
			}
		}
	}

	private function getSessionDetails()
	{
		if (!$this->sessionDetails)
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('e.*, x.dates');
			$query->from('#__redevent_events AS e');
			$query->join('INNER', '#__redevent_event_venue_xref AS x ON x.eventid = e.id');
			$query->where('x.id = ' . $this->xref);

			$db->setQuery($query);
			$this->sessionDetails = $db->loadObject();
		}

		return $this->sessionDetails;
	}

	private function triggerGlobase()
	{
		JPluginHelper::importPlugin('redevent', 'ibcglobase');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onGlobaseAddProfile', array($this->xref, $this->getAnswers()));
	}

	private function notifyAndStop(&$notification)
	{
		$notification = $this->params->get('notification');

		return true;
	}

	/**
	 * Replace data from module redform submission to actual registration form response
	 *
	 * @return void
	 */
	private function newRedFormResponse($redformResponse)
	{
		$xrefFormId = $this->getXrefFormId();
		$submittedFormId = JFactory::getApplication()->input->getInt('form_id', 0);

		// Nothing to do if same form
		if ($xrefFormId == $submittedFormId)
		{
			return $redformResponse;
		}

		// Prepare data for redformcore
		$data = array(
			'form_id' => $xrefFormId,
			'curform' => 1,
			'submit_key' => false,
			JSession::getFormToken() => 1
		);

		// Map fields to data, if match is found
		foreach ($this->getAnswers() as $field)
		{
			$data = $this->map($data, $field);
		}

		// Get new response
		return $this->getRedFormCore()->saveAnswers('redevent', null, $data);
	}

	private function map($data, $field)
	{
		if (!$mapped = $this->getFieldsMappedTo($field->id))
		{
			return $data;
		}

		foreach ($this->getSessionFormFields() as $registrationField)
		{
			if (in_array($registrationField->id, $mapped))
			{
				$data['field' . $registrationField->id] = $field->answer;
				break;
			}
		}

		return $data;
	}

	private function getSessionFormFields()
	{
		if (!$this->sessionFormFields)
		{
			$formId = $this->getXrefFormId();
			$this->sessionFormFields = $this->getRedFormCore()->getFields($formId);
		}

		return $this->sessionFormFields;
	}

	private function getFieldsMappedTo($fieldId)
	{
		$mapping = $this->getMapping();

		if (!isset($mapping[$fieldId]))
		{
			return false;
		}

		return $mapping[$fieldId];
	}

	private function getMapping()
	{
		if (!$this->mapping)
		{
			$lines = explode("\n", $this->params->get('redformMapping'));

			$mapping = array();

			foreach ($lines as $l)
			{
				if (strpos($l, '#') !== 0 && strpos($l, ',') > 0)
				{
					$parts = explode(",", $l);
					$moduleField = (int) $parts[0];
					$registrationField = (int) $parts[1];

					if (!isset($mapping[$moduleField]))
					{
						$mapping[$moduleField] = array();
					}

					$mapping[$moduleField][] = $registrationField;
				}
			}

			$this->mapping = $mapping;
		}

		return $this->mapping;
	}

	private function getXrefFormId()
	{
		if (!$this->sessionFormId)
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('e.redform_id');
			$query->from('#__redevent_event_venue_xref AS x');
			$query->join('INNER', '#__redevent_events AS e ON e.id = x.eventid');
			$query->where('x.id = ' . $this->xref);

			$db->setQuery($query);
			$this->sessionFormId = $db->loadResult();
		}

		return $this->sessionFormId;
	}

	private function getRedFormCore()
	{
		if (!$this->rfcore)
		{
			require_once JPATH_SITE . '/components/com_redform/redform.core.php';

			$this->rfcore = new RedFormCore;
		}

		return $this->rfcore;
	}
}
