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

		if (!$sid = $this->getSid($redformResponse))
		{
			return false;
		}

		$answers = $this->getAnswers($sid);
		$status = $this->getEmploymentStatus($answers);

		if ($status == 'unemployed')
		{
			$this->triggerUnemployedMailflowAndGlobase($redformResponse);

			return $this->notifyAndStop($notification);
		}
		elseif ($status == 'senior')
		{
			$this->triggerSeniorMailflowAndGlobase($redformResponse);

			return $this->notifyAndStop($notification);
		}
		elseif ($status == 'employed') // In that case, we need to match response to real registration form
		{
			$redformResponse = $this->updateRedFormResponse($redformResponse);
		}

		return true;
	}

	private function getSid($redformResponse)
	{
		if (!isset($redformResponse->posts[0]['sid']))
		{
			return false;
		}

		return $redformResponse->posts[0]['sid'];
	}

	private function getAnswers($sid)
	{
		$rfcore = $this->getRedFormCore();
		$answers = $rfcore->getSidsFieldsAnswers(array($sid));

		if (!isset($answers[$sid]))
		{
			throw new Exception('Invalid sid');
		}

		return $answers[$sid];
	}

	private function getEmploymentStatus($answers)
	{
		foreach ($answers as $field)
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

	private function triggerUnemployedMailflowAndGlobase($redformResponse)
	{
		throw new Exception('triggerUnemployedMailflowAndGlobase not implemented');
	}

	private function triggerSeniorMailflowAndGlobase($redformResponse)
	{
		throw new Exception('triggerSeniorMailflowAndGlobase not implemented');
	}

	private function notifyAndStop(&$notification)
	{
		$notification = $this->params->get('notification');

		return true;
	}

	private function updateRedformResponse($redformResponse)
	{

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
