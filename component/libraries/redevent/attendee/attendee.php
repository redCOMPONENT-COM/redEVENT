<?php
/**
 * @package    Redevent.Library
 * @copyright  Copyright (C) 2008 - 2020 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * attendee class - helper for managing attendees
 *
 * @package  Redevent.Library
 * @since    2.5
 *
 * @TODO: convert to an entity
 */
class RedeventAttendee extends JObject
{
	protected $username;

	protected $fullname;

	protected $email;

	protected $id;

	protected $db;

	/**
	 * data from db
	 * @var object
	 */
	protected $data;

	/**
	 * redform answers
	 *
	 * @var array
	 */
	protected $answers;

	/**
	 * events data, caching for when several attendees are called
	 * @var array
	 */
	static protected $sessions = array();

	/**
	 * array of 'attending' registrations for events sessions data
	 * @var array
	 */
	static protected $attending = array();

	/**
	 * @var \RedeventTags
	 *
	 * @since 3.2.4
	 */
	protected $tagHelper;

	/**
	 * Constructor
	 *
	 * @param   int  $id  attendee id
	 */
	public function __construct($id = null)
	{
		if ($id)
		{
			$this->setId($id);
		}

		$this->db = JFactory::getDbo();
	}

	/**
	 * Returns a property of the object or the default value if the property is not set.
	 *
	 * @param   string  $property  The name of the property.
	 * @param   mixed   $default   The default value.
	 *
	 * @return  mixed    The value of the property.
	 */
	public function get($property, $default = null)
	{
		$data = $this->load();

		if (isset($data->$property))
		{
			return $data->$property;
		}

		return parent::get($property, $default);
	}

	/**
	 * Set username
	 *
	 * @param   string  $name  username
	 *
	 * @return void
	 */
	public function setUsername($name)
	{
		$this->username = $name;
	}

	/**
	 * Get username
	 *
	 * @return string
	 */
	public function getUsername()
	{
		if (!$this->username)
		{
			$answers = $this->getAnswers();

			if ($username = $answers->getUsername())
			{
				$this->username = $username;
			}
			// Still there... look for user ?
			elseif ($this->load()->uid)
			{
				$this->username = JFactory::getUser()->get('username');
			}
		}

		return $this->username;
	}

	/**
	 * Get user id for this attendee
	 *
	 * @return boolean
	 */
	public function getUserId()
	{
		if ($this->load()->uid)
		{
			return $this->load()->uid;
		}

		return false;
	}

	/**
	 * Set Fullname
	 *
	 * @param   string  $name  Fullname
	 *
	 * @return void
	 */
	public function setFullname($name)
	{
		$this->fullname = $name;
	}

	/**
	 * Get a field value
	 *
	 * @param   int    $field_id  field id
	 * @param   mixed  $default   default value
	 *
	 * @return string
	 */
	public function getFieldValue($field_id, $default = null)
	{
		$answers = $this->getAnswers();

		return $answers->getFieldAnswer($field_id) ?: $default;
	}

	/**
	 * Get full name
	 *
	 * @return string
	 */
	public function getFullname()
	{
		if (!$this->fullname)
		{
			$answers = $this->getAnswers();

			if ($name = $answers->getFullname())
			{
				$this->fullname = $name;
			}
			// Still there... look for user ?
			elseif ($this->load()->uid)
			{
				$this->fullname = JFactory::getUser()->get('name');
			}
		}

		return $this->fullname;
	}

	/**
	 * Set email
	 *
	 * @param   string  $email  email
	 *
	 * @return void
	 */
	public function setEmail($email)
	{
		$this->email = $email;
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
			if ($this->load()->uid)
			{
				$this->email = JFactory::getUser()->get('email');

				return $this->email;
			}
		}

		return $this->email;
	}

	/**
	 * Set id
	 *
	 * @param   int  $id  attendee id
	 *
	 * @return void
	 */
	public function setId($id)
	{
		$this->id = (int) $id;
	}

	/**
	 * get id
	 *
	 * @return integer
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * loads data from the db
	 *
	 * @return object
	 */
	public function load()
	{
		if (empty($this->data))
		{
			$query = ' SELECT r.* '
				. ' FROM #__redevent_register AS r '
				. ' WHERE r.id = ' . $this->db->Quote($this->id);
			$this->db->setQuery($query);
			$res = $this->db->loadObject();
			$this->data = $res;
		}

		return $this->data;
	}

	/**
	 * confirms attendee registration
	 *
	 * @return true on success
	 */
	public function confirm()
	{
		$current = $this->load();

		if ($current->confirmed)
		{
			return true;
		}

		// First, changed status to confirmed
		$query = ' UPDATE #__redevent_register '
			. ' SET confirmed = 1, confirmdate = ' . $this->db->Quote(gmdate('Y-m-d H:i:s'))
			. '   , paymentstart = ' . $this->db->Quote(gmdate('Y-m-d H:i:s'))
			. ' WHERE id = ' . $this->id;
		$this->db->setQuery($query);
		$res = $this->db->query();

		if (!$res)
		{
			RedeventHelperLog::simpleLog(JText::_('COM_REDEVENT_REGISTRATION_FAILED_CONFIRM_REGISTRATION'));
			$this->setError(JText::_('COM_REDEVENT_REGISTRATION_FAILED_CONFIRM_REGISTRATION'));

			return false;
		}

		// Now, handle waiting list
		$session = $this->getSessionDetails();

		if ($session->maxattendees == 0)
		{
			// No waiting list
			// Send attending email
			$this->sendWaitinglistStatusEmail(0);
			$this->sendWLAdminNotification(0);
		}
		else
		{
			$attendees = $this->getAttending();

			if (count($attendees) > $session->maxattendees)
			{
				// Put this attendee on WL
				$this->toggleWaitingListStatus(1);
			}
			else
			{
				$this->addToAttending();

				// Send attending email
				$this->sendWaitinglistStatusEmail(0);
				$this->sendWLAdminNotification(0);
			}
		}

		// Notify
		JPluginHelper::importPlugin('redevent');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onAttendeeConfirmed', array($this->id));

		return true;
	}

	/**
	 * toggles waiting list status
	 *
	 * @param   int  $waiting  0 for attending, 1 for waiting
	 *
	 * @return true on success
	 */
	public function toggleWaitingListStatus($waiting = null)
	{
		$data = $this->load();

		if (is_null($waiting))
		{
			$waiting = $data->waitinglist ? 0 : 1;
		}

		if ($data->waitinglist == $waiting)
		{
			// Nothing to do
			return true;
		}

		$query = $this->db->getQuery(true);

		$query->update('#__redevent_register')
			->set('waitinglist = ' . $waiting)
			->set('paymentstart = NOW()')
			->where('id = ' . $this->db->Quote($this->id));

		$this->db->setQuery($query);

		if (!$this->db->execute())
		{
			$this->setError(JText::_('COM_REDEVENT_FAILED_UPDATING_WAITINGLIST_STATUS'));

			return false;
		}

		try
		{
			JPluginHelper::importPlugin('redevent');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('onAttendeeWaitingStatusChanged', array($this->id, $waiting));

			$this->sendWaitinglistStatusEmail($waiting);
			$this->sendWLAdminNotification($waiting);
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		return true;
	}

	/**
	 * send waiting list status emails
	 *
	 * @param   int  $waiting  status: 0 for attending, 1 for waiting
	 *
	 * @return boolean true on success
	 *
	 * @throws Exception
	 */
	public function sendWaitinglistStatusEmail($waiting = 0)
	{
		$config = RedeventHelper::config();

		if ($config->get('disable_waitinglist_status_email', 0))
		{
			return true;
		}

		$data = $this->load();
		$session = $this->getSessionDetails();

		// Disable sending of waiting list status emails for past or unpublished sessions
		if (!$session->isUpcoming() || $session->published != 1 || $session->getEvent()->published != 1)
		{
			return true;
		}

		$template = $session->getEvent()->getEventtemplate();

		$sid = $data->sid;

		$rfcore = RdfCore::getInstance();
		$emails = $rfcore->getSidContactEmails($sid);

		$validEmails = false;

		foreach ($emails as $e)
		{
			if (JMailHelper::isEmailAddress($e['email']))
			{
				$validEmails = true;
				break;
			}
		}

		// Stop if no valid emails
		if (!$validEmails)
		{
			return true;
		}

		if ($waiting == 0)
		{
			if ($template->notify_off_list_subject)
			{
				$subject = $template->notify_off_list_subject;
			}
			elseif ($template->notify_subject)
			{
				$subject = $template->notify_subject;
			}
			else
			{
				$subject = JText::_('COM_REDEVENT_WL_DEFAULT_NOTIFY_OFF_SUBJECT');
			}

			if ($template->notify_off_list_body)
			{
				$body = $template->notify_off_list_body;
			}
			elseif ($template->notify_body)
			{
				$body = $template->notify_body;
			}
			else
			{
				$body = JText::_('COM_REDEVENT_WL_DEFAULT_NOTIFY_OFF_BODY');
			}
		}
		else
		{
			if ($template->notify_on_list_subject)
			{
				$subject = $template->notify_on_list_subject;
			}
			else
			{
				$subject = JText::_('COM_REDEVENT_WL_DEFAULT_NOTIFY_ON_SUBJECT');
			}

			if ($template->notify_on_list_body)
			{
				$body = $template->notify_on_list_body;
			}
			else
			{
				$body = JText::_('COM_REDEVENT_WL_DEFAULT_NOTIFY_ON_BODY');
			}
		}

		$mailer = $this->prepareEmail($subject, $body);

		foreach ($emails as $email)
		{
			// Add the email address
			$mailer->AddAddress($email['email'], $email['fullname']);
		}

		// Send the mail
		if (!$mailer->Send())
		{
			RedeventHelperLog::simpleLog(JText::_('COM_REDEVENT_REGISTRATION_FAILED_SENDING_WAITING_LIST_STATUS_EMAIL'));
			throw new Exception(JText::_('COM_REDEVENT_REGISTRATION_FAILED_SENDING_WAITING_LIST_STATUS_EMAIL'));
		}

		return true;
	}

	/**
	 * send waiting list status change notification to event admins
	 *
	 * @param   int  $waiting  0 for attending, 1 for waiting
	 *
	 * @return boolean true on success
	 */
	public function sendWLAdminNotification($waiting = 0)
	{
		$params = JComponentHelper::getParams('com_redevent');

		if (!$params->get('wl_notify_admin', 0))
		{
			// Never notify admins
			return true;
		}
		elseif ($params->get('wl_notify_admin', 0) == 1 && $waiting == 1)
		{
			// Only for people begin added to attending
			return true;
		}
		elseif ($params->get('wl_notify_admin', 0) == 2 && $waiting == 0)
		{
			// Only for people being added to waiting list
			return true;
		}

		// Recipients
		$recipients = $this->getAdminEmails();

		if (!count($recipients))
		{
			return true;
		}

		$subject = $waiting
			? $params->get('wl_notify_admin_waiting_subject')
			: $params->get('wl_notify_admin_attending_subject');
		$body = $waiting
			? $params->get('wl_notify_admin_waiting_body')
			: $params->get('wl_notify_admin_attending_body');

		$mailer = $this->prepareEmail($subject, $body);

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
	 * return email for the registration admins
	 *
	 * @return array
	 */
	public function getAdminEmails()
	{
		$helper = new RedeventHelperSessionadmins;
		$adminEmails = $helper->getAdminEmails($this->getXref());

		if ($rfRecipients = $this->getRFRecipients())
		{
			foreach ((array) $rfRecipients as $r)
			{
				if (JMailHelper::isEmailAddress($r))
				{
					$adminEmails[] = array('email' => $r, 'name' => '');
				}
			}
		}

		// Custom recipients
		$customrecipients = array();

		JPluginHelper::importPlugin('redevent');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onGetRegistrationAdminEmails', array($this->id, &$customrecipients));

		foreach ((array) $customrecipients as $r)
		{
			if (JMailHelper::isEmailAddress($r['email']))
			{
				$adminEmails[] = array('email' => $r['email'], 'name' => $r['name']);
			}
		}

		return $adminEmails;
	}

	/**
	 * Replace text tags
	 *
	 * @param   string  $text  text to replace
	 *
	 * @return string
	 */
	public function replaceTags($text)
	{
		if (empty($this->tagHelper))
		{
			$data = $this->load();

			$this->tagHelper = new RedeventTags;
			$this->tagHelper->setSubmitkey($data->submit_key);
			$this->tagHelper->setXref($this->getXref());
			$this->tagHelper->addOptions(array('sids' => array($data->sid)));
			$this->tagHelper->addOptions(array('attendeeIds' => array($data->id)));
		}

		$text = $this->tagHelper->replaceTags($text);

		return $text;
	}

	/**
	 * return selected redform recipients emails if any
	 *
	 * @return string
	 */
	protected function getRFRecipients()
	{
		$answers = $this->getAnswers();

		$emails = array();

		foreach ($answers as $f)
		{
			if ($f->fieldtype == 'recipients' && $f->answer)
			{
				$email = explode('~~~', $f->answer);
				$emails = array_merge($emails, $email);
			}
		}

		return count($emails) ? $emails : false;
	}

	/**
	 * get redform answers for this attendee
	 *
	 * @return RdfAnswers
	 */
	protected function getAnswers()
	{
		if (empty($this->answers))
		{
			$rfcore = RdfCore::getInstance();
			$sid = $this->load()->sid;
			$sidsanswers = $rfcore->getAnswers(array($sid));
			$this->answers = $sidsanswers->getSubmissionBySid($sid);
		}

		return $this->answers;
	}

	/**
	 * returns attendee event session info
	 *
	 * @return RedeventEntitySession
	 */
	protected function getSessionDetails()
	{
		$xref = $this->getXref();

		if (!isset(self::$sessions[$xref]))
		{
			self::$sessions[$xref] = RedeventEntitySession::load($xref);
		}

		return self::$sessions[$xref];
	}

	/**
	 * return attendee event session xref
	 *
	 * @return integer
	 */
	public function getXref()
	{
		return $this->load()->xref;
	}

	/**
	 * return redform submitted files path if any
	 *
	 * @return array
	 */
	protected function getRFFiles()
	{
		$files = array();
		$answers = $this->getAnswers();

		foreach ($answers as $f)
		{
			if ($f->fieldtype == 'fileupload')
			{
				$path = $f->answer;

				if (!empty($path) && file_exists($path))
				{
					$files[] = $path;
				}
			}
		}

		return $files;
	}

	/**
	 * returns array of ids of currently attending (confirmed, not on wl, not cancelled) register_id
	 *
	 * @return array;
	 */
	protected function getAttending()
	{
		if (!isset(self::$attending[$this->getXref()]))
		{
			$query = ' SELECT r.id '
				. ' FROM #__redevent_register AS r '
				. ' WHERE r.xref = ' . $this->getXref()
				. '   AND r.confirmed = 1 '
				. '   AND r.cancelled = 0 '
				. '   AND r.waitinglist = 0 ';
			$this->db->setQuery($query);
			self::$attending[$this->getXref()] = $this->db->loadColumn();
		}

		return self::$attending[$this->getXref()];
	}

	/**
	 * add id to the list of attending attendees
	 *
	 * @return void
	 */
	protected function addToAttending()
	{
		self::$attending[$this->getXref()][] = $this->id;
	}

	/**
	 * Get attendee contact emails
	 *
	 * @return array
	 */
	public function getContactEmails()
	{
		$rfcore = RdfCore::getInstance();

		return $rfcore->getSidContactEmails($this->load()->sid);
	}

	/**
	 * Send e-mail confirmations
	 *
	 * @return boolean
	 */
	public function sendNotificationEmail()
	{
		$eventsettings = $this->getSessionDetails()->getEvent()->getEventtemplate();

		/**
		 * Send a submission mail to the attendee and/or contact person
		 * This will only work if the contact person has an e-mail address
		 */
		if (!empty($eventsettings->notify))
		{
			$params = JComponentHelper::getParams('com_redevent');

			$subject = $eventsettings->notify_subject;
			$body = '<html><head><title></title></title></head><body>';
			$body .= $eventsettings->notify_body;
			$body .= '</body></html>';

			// Load the mailer
			$mailer = $this->prepareEmail($subject, $body);
			$emails = $this->getContactEmails();

			foreach ($emails as $email)
			{
				// Add the email address
				$mailer->AddAddress($email['email'], $email['fullname']);
			}

			if ($params->get('registration_notification_attach_ics', 0))
			{
				$ics = $this->getIcs();
				$mailer->addAttachment($ics);
			}

			// Send
			if (!$mailer->Send())
			{
				RedeventHelperLog::simpleLog('Error sending notify message to submitted attendants');

				return false;
			}
		}

		return true;
	}

	/**
	 * Notify managers
	 *
	 * @param   bool  $unreg  is this unregistration ?
	 *
	 * @return boolean
	 */
	public function notifyManagers($unreg = false)
	{
		$app = JFactory::getApplication();
		$params = $app->getParams('com_redevent');

		$recipients = $this->getAdminEmails();

		if (!count($recipients))
		{
			return true;
		}

		$subject = $unreg ?
			$params->get('unregistration_notification_subject')
			: $params->get('registration_notification_subject');

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
			<BODY bgcolor="#FFFFFF">
			' . ($unreg ? $params->get('unregistration_notification_body') : $params->get('registration_notification_body')) . '
			</body>
			</html>';

		// Load the mailer
		$mailer = $this->prepareEmail($subject, $body);
		$sender = array($app->getCfg('mailfrom'), $app->getCfg('sitename'));

		$mailer->setSender($sender);
		$mailer->ClearReplyTos();

		if ($this->getEmail())
		{
			$mailer->addReplyTo($this->getEmail(), $this->getFullname());
		}

		foreach ($recipients as $r)
		{
			$mailer->addAddress($r['email'], $r['name']);
		}

		if (!$unreg && $params->get('registration_notification_attach_rfuploads', 1))
		{
			// Files submitted through redform
			$files = $this->getRFFiles();
			$filessize = 0;

			foreach ($files as $f)
			{
				$filessize += filesize($f);
			}

			if ($filessize < $params->get('registration_notification_attach_rfuploads_maxsize', 1500) * 1000)
			{
				foreach ($files as $f)
				{
					$mailer->addAttachment($f);
				}
			}
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
	 * Prepare email, with tag replacements done
	 *
	 * @param   string  $subject  email subject
	 * @param   string  $body     email body
	 *
	 * @return JMail
	 */
	public function prepareEmail($subject, $body)
	{
		jimport('joomla.mail.helper');

		$mainframe = JFactory::getApplication();

		// Load the mailer
		$mailer = RdfHelper::getMailer();
		$mailer->isHTML(true);
		$mailer->From = $mainframe->getCfg('mailfrom');
		$mailer->FromName = $mainframe->getCfg('sitename');
		$mailer->AddReplyTo(array($mainframe->getCfg('mailfrom'), $mainframe->getCfg('sitename')));

		$tags = new RedeventTags;
		$tags->setXref($this->getXref());
		$tags->addOptions(array('sids' => array($this->load()->sid)));
		$tags->setSubmitkey($this->load()->submit_key);

		// Build activation link
		// TODO: use the route helper !
		$url = JRoute::_(
			JURI::root()
			. 'index.php?option=com_redevent&task=registration.activate'
			. '&confirmid=' . str_replace(".", "_", $this->data->uip)
			. 'x' . $this->data->xref
			. 'x' . $this->data->uid
			. 'x' . $this->data->id
			. 'x' . $this->data->submit_key
		);
		$activatelink = '<a href="' . $url . '">' . JText::_('COM_REDEVENT_Activate') . '</a>';

		$cancellinkurl = JRoute::_(
			JURI::root()
			. 'index.php?option=com_redevent&task=registration.emailcancelregistration'
			. '&rid=' . $this->data->id
			. '&xref=' . $this->data->xref
			. '&submit_key=' . $this->data->submit_key
		);
		$cancellink = '<a href="' . $cancellinkurl . '">' . JText::_('COM_REDEVENT_CANCEL') . '</a>';

		$htmlmsg = $tags->replaceTags($body);
		$htmlmsg = str_replace('[activatelink]', $activatelink, $htmlmsg);
		$htmlmsg = str_replace('[cancellink]', $cancellink, $htmlmsg);
		$htmlmsg = str_replace('[fullname]', $this->getFullname(), $htmlmsg);

		$subject = $tags->replaceTags($subject);
		$mailer->setSubject($subject);

		// Convert urls
		$htmlmsg = RedeventHelperOutput::ImgRelAbs($htmlmsg);
		$htmlmsg = RdfHelper::wrapMailHtmlBody($htmlmsg, $subject);
		$mailer->setBody($htmlmsg);

		return $mailer;
	}

	/**
	 * return ics file for session
	 *
	 * @return boolean|string
	 */
	private function getIcs()
	{
		$app = JFactory::getApplication();

		// Get data from the model
		$session = $this->getSessionDetails();

		// Initiate new CALENDAR
		$helper = new RedeventHelperIcal('session' . $session->id . '@' . $app->getCfg('sitename'));
		$helper->addSession($session);

		$path = $app->getCfg('tmp_path') . "/event" . $session->id . ".ics";

		return $helper->write($path) ? $path : false;
	}
}
