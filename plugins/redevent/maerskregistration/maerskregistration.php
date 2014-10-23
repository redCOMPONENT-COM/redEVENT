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

require_once JPATH_SITE . '/components/com_redmember/lib/redmemberlib.php';

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
	 * @return mixed
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

			$rfcore = new RedformCore;
			$answers = $rfcore->getSidsFieldsAnswers(array($registration->sid));
			$this->answers = $answers[$registration->sid];
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

		foreach ($answers as $a)
		{
			if (in_array($a->id, $fieldIds))
			{
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);

				$query->update('#__redevent_register');
				$query->set('ponumber = ' . $db->quote($a->answer));
				$query->where('id = ' . $this->registrationId);

				$db->setQuery($query);
				$res = $db->execute();

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

		foreach ($answers as $a)
		{
			if (in_array($a->id, $fieldIds))
			{
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);

				$query->update('#__redevent_register');
				$query->set('comments = ' . $db->quote($a->answer));
				$query->where('id = ' . $this->registrationId);

				$db->setQuery($query);
				$res = $db->execute();

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

	public function onB2BRegistrationNotifyAdmins($registrationId)
	{
		$app = JFactory::getApplication();
		$params = $app->getParams('com_redevent');

		$attendee = new RedeventAttendee($registrationId);

		$recipients = $attendee->getAdminEmails();

		if (!count($recipients))
		{
			return true;
		}

		$subject = $params->get('registration_notification_subject');

		$attendeeInfo = RedmemberLib::getUserData($attendee->getUserId());

		$booker = JFactory::getUser();
		$bookerInfo = RedmemberLib::getUserData($booker->get('id'));

		$body = '<HTML><HEAD>
			<STYLE TYPE="text/css">
			<!--
			  table.formanswers , table.formanswers td, table.formanswers th
				{
				    border-color: darkgrey;
				    border-style: solid;
				    text-align:left;
				}
				table.formanswers
				{
				    border-width: 0 0 1px 1px;
				    border-spacing: 0;
				    border-collapse: collapse;
				    padding: 5px;
				}
				table.formanswers td, table.formanswers th
				{
				    margin: 0;
				    padding: 4px;
				    border-width: 1px 1px 0 0;
				}
			-->
			</STYLE>
			</head>
			<BODY bgcolor="#FFFFFF">';

			$body .= '<h2>' . JText::_('PLG_REDEVENT_MAERSKREGISTRATION_B2B_ADMIN_NOTIFICATION_DELEGATE_HEADER') . '</h2>';
			$body .= '<ul>';
			$body .= '<li>' . JText::_('PLG_REDEVENT_MAERSKREGISTRATION_B2B_ADMIN_NOTIFICATION_DELEGATE_LABEL_FIRST_NAME') .': ' . $attendeeInfo->rm_firstname . '</li>';
			$body .= '<li>' . JText::_('PLG_REDEVENT_MAERSKREGISTRATION_B2B_ADMIN_NOTIFICATION_DELEGATE_LABEL_LAST_NAME') .': ' . $attendeeInfo->rm_lastname . '</li>';
			$body .= '<li>' . JText::_('PLG_REDEVENT_MAERSKREGISTRATION_B2B_ADMIN_NOTIFICATION_DELEGATE_LABEL_EMAIL') .': ' . $attendeeInfo->email . '</li>';
			$body .= '<li>' . JText::_('PLG_REDEVENT_MAERSKREGISTRATION_B2B_ADMIN_NOTIFICATION_DELEGATE_LABEL_BIRTHDAY') .': ' . $attendeeInfo->rm_birthday . '</li>';
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

			$body .= '<h2>' . JText::_('PLG_REDEVENT_MAERSKREGISTRATION_B2B_ADMIN_NOTIFICATION_COMPANY_HEADER') . '</h2>';
			$body .= '<ul>';
			$body .= '<li>' . JText::_('PLG_REDEVENT_MAERSKREGISTRATION_B2B_ADMIN_NOTIFICATION_COMPANY_LABEL_COMPANY_NAME') .': ' . $attendeeInfo->organization_name . '</li>';
			$body .= '<li>' . JText::_('PLG_REDEVENT_MAERSKREGISTRATION_B2B_ADMIN_NOTIFICATION_COMPANY_LABEL_ADDRESS1') .': ' . $attendeeInfo->organization_address1 . '</li>';
			$body .= '<li>' . JText::_('PLG_REDEVENT_MAERSKREGISTRATION_B2B_ADMIN_NOTIFICATION_COMPANY_LABEL_ADDRESS2') .': ' . $attendeeInfo->organization_address2 . '</li>';
			$body .= '<li>' . JText::_('PLG_REDEVENT_MAERSKREGISTRATION_B2B_ADMIN_NOTIFICATION_COMPANY_LABEL_ADDRESS3') .': ' . $attendeeInfo->organization_address3 . '</li>';
			$body .= '<li>' . JText::_('PLG_REDEVENT_MAERSKREGISTRATION_B2B_ADMIN_NOTIFICATION_COMPANY_LABEL_ZIP') .': ' . $attendeeInfo->organization_zip . '</li>';
			$body .= '<li>' . JText::_('PLG_REDEVENT_MAERSKREGISTRATION_B2B_ADMIN_NOTIFICATION_COMPANY_LABEL_COUNTRY') .': ' . $attendeeInfo->organization_country . '</li>';
			$body .= '<li>' . JText::_('PLG_REDEVENT_MAERSKREGISTRATION_B2B_ADMIN_NOTIFICATION_COMPANY_LABEL_COMPANY_PHONE') .': ' . $attendeeInfo->organization_phone . '</li>';
			$body .= '<li>' . JText::_('PLG_REDEVENT_MAERSKREGISTRATION_B2B_ADMIN_NOTIFICATION_COMPANY_LABEL_VAT') .': ' . $attendeeInfo->organization_vat . '</li>';
			$body .= '<li>' . JText::_('PLG_REDEVENT_MAERSKREGISTRATION_B2B_ADMIN_NOTIFICATION_COMPANY_LABEL_NOTE') .': ' . $attendeeInfo->organization_note . '</li>';
			$body .= '</ul>';

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
		$mailer = JFactory::getMailer();
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

		$data = RedmemberLib::getUserData($user->get('id'));

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
}
