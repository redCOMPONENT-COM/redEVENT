<?php
/**
 * @package     Redevent.Library
 * @subpackage  Entity
 *
 * @copyright   Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Attendee entity.
 *
 * @since  1.0
 */
class RedeventEntityAttendee extends RedeventEntityBase
{
	/**
	 * redform answers
	 *
	 * @var RdfAnswers
	 */
	protected $answers;

	/**
	 * email from form submission
	 *
	 * @var string
	 */
	protected $email;

	/**
	 * @var RedeventTags
	 */
	private $replacer;

	/**
	 * @var RedeventEntitySession
	 */
	private $session;

	/**
	 * @var JUser
	 */
	private $user;

	/**
	 * Set attendee as confirmed
	 *
	 * @return bool true on success
	 *
	 * @since __deploy_version__
	 */
	public function confirm()
	{
		$attendee = new RedeventAttendee($this->id);

		return $attendee->confirm();
	}

	/**
	 * Get email
	 *
	 * @return string
	 */
	public function getEmail()
	{
		if (!$this->email)
		{
			$answers = $this->getAnswers();

			foreach ($answers->getSubmitterEmails() as $a)
			{
				if (JMailHelper::isEmailAddress($a))
				{
					$this->email = $a;

					return $this->email;
				}
			}

			// Still there... look for user ?
			if ($this->getUser())
			{
				$this->email = $this->getUser()->get('email');
			}
		}

		return $this->email;
	}

	/**
	 * Generate unique id from registration data
	 *
	 * @return string
	 *
	 * @throws Exception
	 */
	public function getRegistrationUniqueId()
	{
		$item = $this->getItem();

		return $this->getSession()->getEvent()->course_code . '-' . $item->xref . '-' . $item->id;
	}

	/**
	 * Return creator
	 *
	 * @return RedeventEntitySession
	 */
	public function getSession()
	{
		if (!$this->session)
		{
			$item = $this->getItem();

			if (!empty($item))
			{
				$this->session = RedeventEntitySession::load($item->xref);
			}
		}

		return $this->session;
	}

	/**
	 * Return joomla user
	 *
	 * @return JUser
	 */
	public function getUser()
	{
		if (!$this->user)
		{
			$item = $this->getItem();

			if (!empty($item))
			{
				$this->user = JFactory::getUser($item->uid);
			}
		}

		return $this->user;
	}

	/**
	 * Is attendee confirmed
	 *
	 * @return boolean
	 */
	public function isAttending()
	{
		$item = $this->getItem(true);

		return $this->isConfirmed() && !$item->waitinglist;
	}

	/**
	 * Is attendee confirmed
	 *
	 * @return boolean
	 */
	public function isConfirmed()
	{
		$item = $this->getItem(true);

		return $this->confirmed && !$item->cancelled;
	}

	/**
	 * Is attendee on waiting list
	 *
	 * @return boolean
	 */
	public function isWaiting()
	{
		$item = $this->getItem(true);

		return $this->isConfirmed() && $item->waitinglist;
	}

	/**
	 * Return array of RedeventEntityAttendee
	 *
	 * @param   string  $submit_key  submit key
	 *
	 * @return RedeventEntityAttendee[]
	 */
	public static function loadBySubmitKey($submit_key)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('r.*')
			->from('#__redevent_register AS r')
			->where('r.submit_key = ' . $db->q($submit_key));

		$db->setQuery($query);
		$res = $db->loadObjectList();

		if (!$res)
		{
			return false;
		}

		$attendees = array_map(
			function ($item)
			{
				$instance = self::getInstance($item->id);
				$instance->bind($item);

				return $instance;
			},
			$res
		);

		return $attendees;
	}

	/**
	 * Return RedeventEntityAttendee from submitter id
	 *
	 * @param   integer  $submitterId  submit key
	 *
	 * @return RedeventEntityAttendee
	 */
	public static function loadBySubmitterId($submitterId)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('r.*')
			->from('#__redevent_register AS r')
			->where('r.sid = ' . $db->q($submitterId));

		$db->setQuery($query);
		$res = $db->loadObject();

		if (!$res)
		{
			return false;
		}

		$instance = self::getInstance($res->id);
		$instance->bind($res);

		return $instance;
	}

	/**
	 * Replace tags in text
	 *
	 * @param   string  $text  text
	 *
	 * @return string
	 */
	public function replaceTags($text)
	{
		return $this->getReplacer()->replaceTags($text);
	}

	/**
	 * get redform answers for this attendee
	 *
	 * @return RdfAnswers
	 */
	public function getAnswers()
	{
		if (empty($this->answers))
		{
			$item = $this->getItem();

			$rfcore = RdfCore::getInstance();
			$sidsanswers = $rfcore->getAnswers(array($item->sid));
			$this->answers = $sidsanswers->getSubmissionBySid($item->sid);
		}

		return $this->answers;
	}

	/**
	 * Update attendee payment requests
	 *
	 * @return boolean
	 */
	public function updatePaymentRequests()
	{
		if (!$this->isValid())
		{
			return false;
		}

		$answers = $this->getAnswers();

		$model = new RdfCoreModelSubmissionprice;
		$model->setAnswers($answers);

		return $model->updatePrice();
	}

	/**
	 * Get replacer
	 *
	 * @return mixed|RedeventTags
	 */
	private function getReplacer()
	{
		if (!$this->replacer)
		{
			$item = $this->getItem();

			$tags = new RedeventTags;
			$tags->setXref($item->xref);
			$tags->addOptions(array('sids' => array($item->sid)));
			$tags->setSubmitkey($item->submit_key);

			$this->replacer = $tags;
		}

		return $this->replacer;
	}
}
