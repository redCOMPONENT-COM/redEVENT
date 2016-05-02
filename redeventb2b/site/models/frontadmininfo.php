<?php
/**
 * @package    Redeventb2b.site
 * @copyright  Copyright (C) 2008 - 2016 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevents Component events list Model
 *
 * @package  Redevent
 * @since    2.5
 */
class Redeventb2bModelFrontadmininfo extends JModelLegacy
{
	/**
	 * @var int
	 */
	private $xref;

	/**
	 * @var string
	 */
	private $question;

	/**
	 * @var JUser
	 */
	private $user;

	/**
	 * @var object
	 */
	private $sessionDetails;

	/**
	 * Send question to session admin
	 *
	 * @param   int     $xref      session id
	 * @param   JUser   $user      user sending the question
	 * @param   string  $question  question
	 *
	 * @throws RuntimeException
	 *
	 * @return void
	 */
	public function sendNotification($xref, $user, $question)
	{
		$this->xref = $xref;
		$this->question = $question;
		$this->user = $user;

		$details = $this->getSessionDetails();

		$mailer = RdfHelper::getMailer();
		$mailer->IsHTML(true);
		$mailer->AddReplyTo(array($user->get('email'), $user->get('name')));

		$mailer->setSubject(
			JText::sprintf('COM_REDEVENT_FRONTEND_ADMIN_SESSION_INFO_FORM_SUBJECT',
				$details->event_title,
				$details->course_code . ($details->session_code ? '-' . $details->session_code : ''),
				$details->venue,
				RedeventHelperDate::formatdate($details->dates, false)
			)
		);

		$posterInfo = $this->getPosterInfo();
		$body = $posterInfo . $this->question;
		$mailer->setBody($body);

		$recipientsHelper = new RedeventHelperSessionadmins;
		$recipient = $recipientsHelper->getVenueContactEmail($this->xref);

		if (!JMailHelper::isEmailAddress($recipient))
		{
			throw new RuntimeException('Invalid venue admin contact email');
		}

		$mailer->addAddress($recipient);

		if (!$mailer->send())
		{
			throw new RuntimeException('COM_REDEVENT_FRONTEND_ADMIN_SESSION_INFO_FORM_SENDING_ERROR');
		}
	}

	/**
	 * Get session details
	 *
	 * @return object
	 */
	private function getSessionDetails()
	{
		if (!$this->sessionDetails)
		{
			JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_redevent/models');
			$model = $this->getInstance('Details', 'RedeventModel');
			$model->setXref($this->xref);
			$this->sessionDetails = $model->getDetails();
		}

		return $this->sessionDetails;
	}

	/**
	 * Get html admin info
	 *
	 * @return string
	 *
	 * @throws Exception
	 */
	private function getPosterInfo()
	{
		$user = JFactory::getUser();

		$data = RedmemberApi::getUser($user->get('id'));

		if (!$data)
		{
			throw new Exception('Missing redmember data for admin');
		}

		$text = JText::sprintf('COM_REDEVENT_FRONTEND_ADMIN_SESSION_INFO_POSTER_DETAILS',
			$data->name,
			$data->email,
			isset($data->rm_mobile) && $data->rm_mobile ? $data->rm_mobile : '-'
		);

		return $text;
	}
}
