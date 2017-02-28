<?php
/**
 * @package     Redevent.Plugin
 * @subpackage  Redevent.Maerskregistration
 *
 * @copyright   Copyright (C) 2013 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */


defined('JPATH_BASE') or die;

// Import library dependencies
jimport('joomla.plugin.plugin');

JLoader::registerPrefix('Redmemer', JPATH_LIBRARIES . '/redmember');

/**
 * Specific parameters for redEVENT.
 *
 * @package     Redevent.Plugin
 * @subpackage  Redevent.Maerskregistration
 * @since       2.5
 */
class plgRedeventMaerskregistration extends JPlugin
{
	protected $registrationId;

	/**
	 * @var RdfAnswers
	 */
	protected $answers;

	/**
	 * Constructor
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $config    An optional associative array of configuration settings.
	 *                             Recognized key values include 'name', 'group', 'params', 'language'
	 *                             (this list is not meant to be comprehensive).
	 *
	 * @since   11.1
	 */
	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}

	/**
	 * intercepts onAttendeeCreated
	 *
	 * @param   int  $rid  registration id
	 *
	 * @return true on success
	 */
	public function onAttendeeCreated($rid)
	{
		$this->registrationId = $rid;
		$this->updatePoNumber();
		$this->updateComments();

		return true;
	}

	/**
	 * Get answers for current registration id
	 *
	 * @return RdfAnswers
	 */
	protected function getAnswers()
	{
		if (!$this->answers)
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('id, sid');
			$query->from('#__redevent_register');
			$query->where('id = ' . $this->registrationId);

			$db->setQuery($query);
			$registration = $db->loadObject();

			$rfcore = new RdfCore();
			$answers = $rfcore->getSidAnswers($registration->sid);
			$this->answers = $answers;
		}

		return $this->answers;
	}

	/**
	 * Update PO number from B2C registration
	 *
	 * @return bool
	 */
	protected function updatePoNumber()
	{
		// Get ids for ponumber
		$text = $this->params->get('ponumberFieldIds');
		$fieldIds = $this->cleanIds($text);
		$answers = $this->getAnswers();

		foreach ($answers->getFields() as $a)
		{
			if (in_array($a->field_id, $fieldIds))
			{
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);

				$query->update('#__redevent_register');
				$query->set('ponumber = ' . $db->quote($a->value));
				$query->where('id = ' . $this->registrationId);

				$db->setQuery($query);
				$db->execute();

				return true;
			}
		}

		return true;
	}

	/**
	 * Update comments from B2C registration
	 *
	 * @return bool
	 */
	protected function updateComments()
	{
		// Get ids for ponumber
		$text = $this->params->get('commentsFieldIds');
		$fieldIds = $this->cleanIds($text);
		$answers = $this->getAnswers();

		foreach ($answers->getFields() as $a)
		{
			if (in_array($a->field_id, $fieldIds))
			{
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);

				$query->update('#__redevent_register');
				$query->set('comments = ' . $db->quote($a->value));
				$query->where('id = ' . $this->registrationId);

				$db->setQuery($query);
				$db->execute();

				return true;
			}
		}

		return true;
	}

	/**
	 * Return clean array of ids
	 *
	 * @param   string  $text  comma separated list of ids
	 *
	 * @return array
	 */
	protected function cleanIds($text)
	{
		$ids = array();

		if (!$text)
		{
			return $ids;
		}

		$lines = explode(",", $text);
		$lines = array_map('trim', $lines);

		foreach ($lines as $l)
		{
			if (strlen($l))
			{
				$ids[] = (int) $l;
			}
		}

		return $ids;
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
		$input = JFactory::getApplication()->input;

		// See if the comments were modified
		if (!strstr('updatecomments', $input->get('task')))
		{
			return true;
		}

		return $this->emailCommentUpdated($attendee_id);
	}

	/**
	 * Send managers notification for b2b registration
	 *
	 * @param   int  $registrationId  registration id
	 *
	 * @return bool
	 */
	public function onB2BRegistrationNotifyAdmins($registrationId)
	{
		return $this->sendManagersNotification($registrationId, false);
	}

	/**
	 * Send managers notification for b2b cancellation
	 *
	 * @param   int  $registrationId  registration id
	 *
	 * @return bool
	 */
	public function onB2BCancellationNotifyAdmins($registrationId)
	{
		return $this->sendManagersNotification($registrationId, true);
	}

	/**
	 * Remap b2b posted data for redmember user save
	 *
	 * @param   array  &$data  data
	 *
	 * @return void;
	 */
	public function onRemapB2bUserData(&$data)
	{
		$data['name'] = (!empty($data['rm_firstname']) ? $data['rm_firstname'] . ' ' : '') . $data['rm_lastname'];
	}

	/**
	 * Handle the notification sending
	 *
	 * @param   int   $registrationId  registration id
	 * @param   bool  $cancellation    is this a cancellation ?
	 *
	 * @return bool
	 */
	private function sendManagersNotification($registrationId, $cancellation = false)
	{
		$app = JFactory::getApplication();
		$params = $app->getParams('com_redevent');

		$attendee = new RedeventAttendee($registrationId);

		$recipients = $attendee->getAdminEmails();

		if (!count($recipients))
		{
			return true;
		}

		if ($cancellation)
		{
			$subject = $params->get('unregistration_notification_subject');
		}
		else
		{
			$subject = $params->get('registration_notification_subject');
		}

		if (strtolower($attendee->get('origin')) == 'b2b')
		{
			$subject = $this->params->get('b2b_notifications_prefix') . $subject;
		}

		$attendeeInfo = RedmemberApi::getUser($attendee->getUserId());

		$booker = JFactory::getUser();
		$bookerInfo = RedmemberApi::getUser($booker->get('id'));

		$body = '<HTML><HEAD>
			<STYLE TYPE="text/css">
			<!--
			-->
			</STYLE>
			</head>
			<BODY bgcolor="#FFFFFF">';

		if ($cancellation)
		{
			$body .= '<p>' . JText::_('PLG_REDEVENT_MAERSKREGISTRATION_B2B_ADMIN_UNREGISTRATION_NOTIFICATION_BODY_INTRO') . '</p>';
		}
		else
		{
			$body .= '<p>' . JText::_('PLG_REDEVENT_MAERSKREGISTRATION_B2B_ADMIN_REGISTRATION_NOTIFICATION_BODY_INTRO') . '</p>';
		}

		$body .= '<h2>' . JText::_('PLG_REDEVENT_MAERSKREGISTRATION_B2B_ADMIN_NOTIFICATION_DELEGATE_HEADER') . '</h2>';
		$body .= '<ul>';
		$body .= '<li>' . JText::_('PLG_REDEVENT_MAERSKREGISTRATION_B2B_ADMIN_NOTIFICATION_DELEGATE_LABEL_FIRST_NAME') .': ' . $attendeeInfo->rm_firstname . '</li>';
		$body .= '<li>' . JText::_('PLG_REDEVENT_MAERSKREGISTRATION_B2B_ADMIN_NOTIFICATION_DELEGATE_LABEL_LAST_NAME') .': ' . $attendeeInfo->rm_lastname . '</li>';
		$body .= '<li>' . JText::_('PLG_REDEVENT_MAERSKREGISTRATION_B2B_ADMIN_NOTIFICATION_DELEGATE_LABEL_EMAIL') .': ' . $attendeeInfo->email . '</li>';
		$body .= '<li>' . JText::_('PLG_REDEVENT_MAERSKREGISTRATION_B2B_ADMIN_NOTIFICATION_DELEGATE_LABEL_BIRTHDAY') .': ' . $attendeeInfo->rm_birthday . '</li>';
		$body .= '<li>' . JText::_('PLG_REDEVENT_MAERSKREGISTRATION_B2B_ADMIN_NOTIFICATION_DELEGATE_LABEL_TITLE') .': ' . $attendeeInfo->title_rank . '</li>';
		$body .= '<li>' . JText::_('PLG_REDEVENT_MAERSKREGISTRATION_B2B_ADMIN_NOTIFICATION_DELEGATE_LABEL_NOTE') .': ' . $attendeeInfo->rm_note . '</li>';
		$body .= '</ul>';

		$body .= '<h2>' . JText::_('PLG_REDEVENT_MAERSKREGISTRATION_B2B_ADMIN_NOTIFICATION_BOOKER_HEADER') . '</h2>';
		$body .= '<ul>';
		$body .= '<li>' . JText::_('PLG_REDEVENT_MAERSKREGISTRATION_B2B_ADMIN_NOTIFICATION_DELEGATE_LABEL_FIRST_NAME') .': ' . $bookerInfo->rm_firstname . '</li>';
		$body .= '<li>' . JText::_('PLG_REDEVENT_MAERSKREGISTRATION_B2B_ADMIN_NOTIFICATION_DELEGATE_LABEL_LAST_NAME') .': ' . $bookerInfo->rm_lastname . '</li>';
		$body .= '<li>' . JText::_('PLG_REDEVENT_MAERSKREGISTRATION_B2B_ADMIN_NOTIFICATION_DELEGATE_LABEL_MOBILE') .': ' . $bookerInfo->rm_mobile . '</li>';
		$body .= '<li>' . JText::_('PLG_REDEVENT_MAERSKREGISTRATION_B2B_ADMIN_NOTIFICATION_DELEGATE_LABEL_EMAIL') .': ' . $bookerInfo->email . '</li>';
		$body .= '<li>' . JText::_('PLG_REDEVENT_MAERSKREGISTRATION_B2B_ADMIN_NOTIFICATION_DELEGATE_LABEL_CERTIFICATE_EMAIL') .': ' . $bookerInfo->rm_certificate_email . '</li>';
		$body .= '<li>' . JText::_('PLG_REDEVENT_MAERSKREGISTRATION_B2B_ADMIN_NOTIFICATION_DELEGATE_LABEL_INVOICE_EMAIL') .': ' . $bookerInfo->rm_invoice_email . '</li>';
		$body .= '<li>' . JText::_('PLG_REDEVENT_MAERSKREGISTRATION_B2B_ADMIN_NOTIFICATION_DELEGATE_LABEL_INVOICE_CONTACT') .': ' . $bookerInfo->rm_invoice_contact . '</li>';
		$body .= '</ul>';

		$company = null;

		if ($organizationName = $attendee->getFieldValue(4))
		{
			if ($orgId = $this->getOrganizationIdFromName($organizationName))
			{
				$company = RedmemberApi::getOrganization($orgId);
			}
		}
		elseif ($companies = $attendeeInfo->getOrganizations())
		{
			$companyUser = reset($companies);
			$company = RedmemberApi::getOrganization($companyUser['organization_id']);
		}

		if ($company)
		{
			$body .= '<h2>' . JText::_('PLG_REDEVENT_MAERSKREGISTRATION_B2B_ADMIN_NOTIFICATION_COMPANY_HEADER') . '</h2>';
			$body .= '<ul>';
			$body .= '<li>' . JText::_('PLG_REDEVENT_MAERSKREGISTRATION_B2B_ADMIN_NOTIFICATION_COMPANY_LABEL_COMPANY_NAME') .': ' . $company->name . '</li>';
			$body .= '<li>' . JText::_('PLG_REDEVENT_MAERSKREGISTRATION_B2B_ADMIN_NOTIFICATION_COMPANY_LABEL_ADDRESS1') .': ' . $company->organization_address1 . '</li>';
			$body .= '<li>' . JText::_('PLG_REDEVENT_MAERSKREGISTRATION_B2B_ADMIN_NOTIFICATION_COMPANY_LABEL_ADDRESS2') .': ' . $company->organization_address2 . '</li>';
			$body .= '<li>' . JText::_('PLG_REDEVENT_MAERSKREGISTRATION_B2B_ADMIN_NOTIFICATION_COMPANY_LABEL_ADDRESS3') .': ' . $company->organization_address3 . '</li>';
			$body .= '<li>' . JText::_('PLG_REDEVENT_MAERSKREGISTRATION_B2B_ADMIN_NOTIFICATION_COMPANY_LABEL_ZIP') .': ' . $company->organization_zip . '</li>';
			$body .= '<li>' . JText::_('PLG_REDEVENT_MAERSKREGISTRATION_B2B_ADMIN_NOTIFICATION_COMPANY_LABEL_CITY') .': ' . $company->organization_city . '</li>';
			$body .= '<li>' . JText::_('PLG_REDEVENT_MAERSKREGISTRATION_B2B_ADMIN_NOTIFICATION_COMPANY_LABEL_COUNTRY') .': ' . $company->organization_country . '</li>';
			$body .= '<li>' . JText::_('PLG_REDEVENT_MAERSKREGISTRATION_B2B_ADMIN_NOTIFICATION_COMPANY_LABEL_COMPANY_PHONE') .': ' . $company->organization_phone . '</li>';
			$body .= '<li>' . JText::_('PLG_REDEVENT_MAERSKREGISTRATION_B2B_ADMIN_NOTIFICATION_COMPANY_LABEL_VAT') .': ' . $company->organization_vat . '</li>';
			$body .= '<li>' . JText::_('PLG_REDEVENT_MAERSKREGISTRATION_B2B_ADMIN_NOTIFICATION_COMPANY_LABEL_NOTE') .': ' . $company->organization_note . '</li>';
			$body .= '</ul>';
		}

		$body .= '</body>
			</html>';

		/* Load the mailer */
		$mailer = $attendee->prepareEmail($subject, $body);

		if ($attendee->getEmail() && $params->get('allow_email_aliasing', 1))
		{
			$sender = array($attendee->getEmail(), $attendee->getFullname());
		}
		else
		{
			// Default to site settings
			$sender = array($app->getCfg('mailfrom'), $app->getCfg('sitename'));
		}

		$mailer->setSender($sender);
		$mailer->ClearReplyTos();
		$mailer->addReplyTo($sender);

		foreach ($recipients as $r)
		{
			$mailer->addAddress($r['email'], $r['name']);
		}

		if (!$mailer->send())
		{
			RedeventHelperLog::simplelog(JText::_('COM_REDEVENT_ERROR_REGISTRATION_MANAGERS_NOTIFICATION_FAILED'));
			$this->setError(JText::_('COM_REDEVENT_ERROR_REGISTRATION_MANAGERS_NOTIFICATION_FAILED'));

			return false;
		}

		return true;
	}

	/**
	 * Send comment updated notification
	 *
	 * @param   int  $attendee_id  attendee id
	 *
	 * @return bool
	 */
	private function emailCommentUpdated($attendee_id)
	{
		$app = JFactory::getApplication();
		$mailer = RdfHelper::getMailer();
		$mailer->IsHTML(true);

		if (!$recipient = $this->getVenueContactAdminEmail($attendee_id))
		{
			return true;
		}

		$attendee = new RedeventAttendee($attendee_id);

		$sender = array($app->getCfg('mailfrom'), $app->getCfg('sitename'));
		$mailer->setSender($sender);
		$mailer->addReplyTo($sender);
		$mailer->addAddress($recipient);

		$subject = $attendee->replaceTags(JText::_('PLG_REDEVENT_MAERSKREGISTRATION_COMMENT_UPDATED_NOTIFICATION_EMAIL_SUBJECT'));

		$comment = $app->input->get('value', '', 'string');
		$body = $attendee->replaceTags(JText::_('PLG_REDEVENT_MAERSKREGISTRATION_COMMENT_UPDATED_NOTIFICATION_EMAIL_BODY'));
		$body = str_replace('[comment]', $comment, $body);

		$body .= $this->getAdminInfo();

		$mailer->setSubject($subject);
		$mailer->setBody($body);

		if (!$mailer->send())
		{
			RedeventHelperLog::simplelog(JText::_('PLG_REDEVENT_MAERSKREGISTRATION_COMMENT_UPDATED_NOTIFICATION_EMAIL_FAILED'));

			return false;
		}
	}

	/**
	 * Get html admin info
	 *
	 * @return string
	 *
	 * @throws Exception
	 */
	private function getAdminInfo()
	{
		$user = JFactory::getUser();

		$data = RedmemberApi::getUser($user->get('id'));

		if (!$data)
		{
			throw new Exception('Missing redmember data for admin');
		}

		$text = JText::sprintf('PLG_REDEVENT_MAERSKREGISTRATION_COMMENT_UPDATED_NOTIFICATION_EMAIL_BODY_ADMININFO',
			$data->name,
			$data->email,
			isset($data->rm_mobile) && $data->rm_mobile ? $data->rm_mobile : '-'
		);

		return $text;
	}

	/**
	 * Get admin email for comment notification email
	 *
	 * @param   int  $attendee_id  attendee id
	 *
	 * @return mixed
	 */
	private function getVenueContactAdminEmail($attendee_id)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('CASE WHEN CHAR_LENGTH(v.contactAdminEmail) THEN v.contactAdminEmail ELSE v.email END AS adminEmail');
		$query->from('#__redevent_register AS r');
		$query->join('INNER', '#__redevent_event_venue_xref AS x ON x.id = r.xref');
		$query->join('INNER', '#__redevent_events AS e ON e.id = x.eventid');
		$query->join('INNER', '#__redevent_venues AS v ON v.id = x.venueid');
		$query->where('r.id = ' . $db->quote($attendee_id));

		$db->setQuery($query);
		$data = $db->loadResult();

		return $data;
	}

	private function getOrganizationIdFromName($name)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('id')
			->from('#__redmember_organization')
			->where('name = ' . $db->quote($name));

		$db->setQuery($query);

		return $db->loadResult();
	}
}
