<?php
/**
 * @package    Redevent.Library
 * @copyright  Copyright (C) 2008 - 2014 redCOMPONENT.com. All rights reserved.
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
	 * @return bool
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
	 * @return int
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

		$app = JFactory::getApplication();
		$data = $this->load();
		$session = $this->getSessionDetails();

		$sid = $data->sid;

		$rfcore = RdfCore::getInstance();
		$emails = $rfcore->getSidContactEmails($sid);

		$valid_emails = false;

		foreach ($emails as $e)
		{
			if (JMailHelper::isEmailAddress($e['email']))
			{
				$valid_emails = true;
				break;
			}
		}

		// Stop if no valid emails
		if (!$valid_emails)
		{
			return true;
		}

		if (empty($this->taghelper))
		{
			$this->taghelper = new RedeventTags;
			$this->taghelper->setXref($data->xref);
			$this->taghelper->setSubmitkey($data->submit_key);
		}

		if ($waiting == 0)
		{
			if ($session->notify_off_list_subject)
			{
				$subject = $session->notify_off_list_subject;
				$body = $session->notify_off_list_body;
			}
			elseif ($session->notify_subject)
			{
				$subject = $session->notify_subject;
				$body = $session->notify_body;
			}
			else
			{
				$subject = JText::_('COM_REDEVENT_WL_DEFAULT_NOTIFY_OFF_SUBJECT');
				$body = JText::_('COM_REDEVENT_WL_DEFAULT_NOTIFY_OFF_BODY');
			}

			$body = $this->taghelper->replaceTags($body);
			$subject = $this->taghelper->replaceTags($subject);
		}
		else
		{
			if ($session->notify_on_list_body)
			{
				$subject = $session->notify_on_list_subject;
				$body = $session->notify_on_list_body;
			}
			else
			{
				$subject = JText::_('COM_REDEVENT_WL_DEFAULT_NOTIFY_ON_SUBJECT');
				$body = JText::_('COM_REDEVENT_WL_DEFAULT_NOTIFY_ON_BODY');
			}

			$body = $this->taghelper->replaceTags($body);
			$subject = $this->taghelper->replaceTags($subject);
		}

		if (empty($subject))
		{
			// Not sending !
			throw new Exception(JText::_('COM_REDEVENT_WL_NOTIFICATION_MISSING_SUBJECT'));
		}

		// Update image paths in body
		$body = RedeventHelperOutput::ImgRelAbs($body);

		$mailer = JFactory::getMailer();

		$sender = array($app->getCfg('mailfrom'), $app->getCfg('sitename'));
		$mailer->setSender($sender);
		$mailer->addReplyTo($sender);

		foreach ($emails as $email)
		{
			/* Add the email address */
			$mailer->AddAddress($email['email'], $email['fullname']);
		}

		/* Mail submitter */
		$htmlmsg = '<html><head><title></title></title></head><body>' . $body . '</body></html>';
		$mailer->MsgHTML($htmlmsg);
		$mailer->setSubject($subject);

		/* Send the mail */
		if (!$mailer->Send())
		{
			RedeventHelperLog::simpleLog(JText::_('COM_REDEVENT_REGISTRATION_FAILED_SENDING_WAITING_LIST_STATUS_EMAIL'));
			throw new Exception(JText::_('COM_REDEVENT_REGISTRATION_FAILED_SENDING_WAITING_LIST_STATUS_EMAIL'));

			return false;
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
		$data = $this->load();

		$tags = new RedeventTags;
		$tags->setSubmitkey($data->submit_key);
		$tags->setXref($this->getXref());
		$tags->addOptions(array('sids' => array($data->sid)));

		$text = $tags->replaceTags($text);

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
	 * @return object
	 */
	protected function getSessionDetails()
	{
		$xref = $this->getXref();

		if (!isset(self::$sessions[$xref]))
		{
			$query = 'SELECT a.id AS did, x.id AS xref, a.title, a.datdescription, a.meta_keywords, a.meta_description, a.datimage, '
					. ' a.registra, a.unregistra, a.activate, a.notify, a.redform_id as form_id, '
					. ' a.notify_confirm_body, a.notify_confirm_subject, a.notify_subject, a.notify_body, '
					. ' a.notify_off_list_subject, a.notify_off_list_body, a.notify_on_list_subject, a.notify_on_list_body, '
					. ' x.*, a.created_by, a.redform_id, x.maxwaitinglist, x.maxattendees, a.juser, a.show_names, a.showfields, '
					. ' a.submission_type_email, a.submission_type_external, a.submission_type_phone,'
					. ' v.venue, v.email as venue_email,'
					. ' u.name AS creator_name, u.email AS creator_email, '
					. ' a.confirmation_message, a.review_message, '
					. " IF (x.course_credit = 0, '', x.course_credit) AS course_credit, a.course_code, a.submission_types, c.name AS catname, c.published, c.access,"
					. ' CASE WHEN CHAR_LENGTH(x.title) THEN CONCAT_WS(\' - \', a.title, x.title) ELSE a.title END as full_title, '
					. ' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug, '
					. ' CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as categoryslug '
					. ' FROM #__redevent_events AS a'
					. ' LEFT JOIN #__redevent_event_venue_xref AS x ON x.eventid = a.id'
					. ' LEFT JOIN #__redevent_venues AS v ON x.venueid = v.id'
					. ' LEFT JOIN #__redevent_event_category_xref AS xcat ON xcat.event_id = a.id'
					. ' LEFT JOIN #__redevent_categories AS c ON c.id = xcat.category_id'
					. ' LEFT JOIN #__users AS u ON a.created_by = u.id '
					. ' WHERE x.id = ' . $xref;
			$this->db->setQuery($query);
			self::$sessions[$xref] = $this->db->loadObject();
		}

		return self::$sessions[$xref];
	}

	/**
	 * return attendee event session xref
	 *
	 * @return int
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
		$eventsettings = $this->getSessionDetails();

		/**
		 * Send a submission mail to the attendee and/or contact person
		 * This will only work if the contact person has an e-mail address
		 **/
		if (isset($eventsettings->notify) && $eventsettings->notify)
		{
			$params = JComponentHelper::getParams('com_redevent');

			$subject = $eventsettings->notify_subject;
			$body = '<html><head><title></title></title></head><body>';
			$body .= $eventsettings->notify_body;
			$body .= '</body></html>';

			/* Load the mailer */
			$mailer = $this->prepareEmail($subject, $body);
			$emails = $this->getContactEmails();

			foreach ($emails as $email)
			{
				/* Add the email address */
				$mailer->AddAddress($email['email'], $email['fullname']);
			}

			if ($params->get('registration_notification_attach_ics', 0))
			{
				$ics = $this->getIcs();
				$mailer->addAttachment($ics);
			}

			/* send */
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
	 * @return bool
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

		/* Load the mailer */
		$mailer = $this->prepareEmail($subject, $body);

		if ($this->getEmail() && $params->get('allow_email_aliasing', 1))
		{
			$sender = array($this->getEmail(), $this->getFullname());
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

		/* Load the mailer */
		$mailer = JFactory::getMailer();
		$mailer->isHTML(true);
		$mailer->From = $mainframe->getCfg('mailfrom');
		$mailer->FromName = $mainframe->getCfg('sitename');
		$mailer->AddReplyTo(array($mainframe->getCfg('mailfrom'), $mainframe->getCfg('sitename')));

		$tags = new RedeventTags;
		$tags->setXref($this->getXref());
		$tags->addOptions(array('sids' => array($this->load()->sid)));
		$tags->setSubmitkey($this->load()->submit_key);

		/* build activation link */
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

		// Convert urls
		$htmlmsg = RedeventHelperOutput::ImgRelAbs($htmlmsg);
		$mailer->setBody($htmlmsg);

		$subject = $tags->replaceTags($subject);
		$mailer->setSubject($subject);

		return $mailer;
	}

	/**
	 * return ics file for session
	 *
	 * @return bool|string
	 */
	private function getIcs()
	{
		$app = JFactory::getApplication();

		// Get data from the model
		$row = $this->getSessionDetails();

		// Initiate new CALENDAR
		$vcal = RedeventHelper::getCalendarTool();
		$vcal->setProperty('unique_id', 'session' . $row->xref . '@' . $app->getCfg('sitename'));
		$vcal->setConfig("filename", "event" . $row->xref . ".ics");

		RedeventHelper::icalAddEvent($vcal, $row);

		if ($vcal->saveCalendar($app->getCfg('tmp_path'), "event" . $row->xref . ".ics"))
		{
			return $app->getCfg('tmp_path') . "/event" . $row->xref . ".ics";
		}
		else
		{
			return false;
		}
	}
}
