<?php
/**
 * @version 1.0 $Id: image.class.php 298 2009-06-24 07:42:35Z julien $
 * @package Joomla
 * @subpackage redEVENT
 * @copyright redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license GNU/GPL, see LICENSE.php
 * redEVENT is based on EventList made by Christoph Lukes from schlu.net
 * redEVENT can be downloaded from www.redcomponent.com
 * redEVENT is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * redEVENT is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with redEVENT; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * attendee class - helper for managing attendees
 */
class REattendee extends JObject {

	protected $_username;

	protected $_fullname;

	protected $_email;

	protected $_id;

	protected $_db;

	/**
	 * data from db
	 * @var object
	 */
	protected $_data;

	/**
	 * redform answers
	 *
	 * @var array
	 */
	protected $_answers;

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

	public function __construct($id = null)
	{
		if ($id) {
			$this->setId($id);
		}

		$this->_db = Jfactory::getDbo();
	}

	public function setUsername($name)
	{
		$this->_username = $name;
	}

  public function getUsername()
  {
  	if (!$this->_username)
  	{
  		$answers = $this->getAnswers();

  		foreach ($answers as $a)
  		{
  			if ($a->fieldtype == 'username' && $a->answer) {
  				$this->_username = $a->answer;
  				return $this->_username;
  			}
  		}
  		// still there... look for user ?
  		if ($this->load()->uid) {
  			$this->_username = JFactory::getUser()->get('username');
  			return $this->_username;
			}
  	}
    return $this->_username;
  }

	public function setFullname($name)
	{
  	if (!$this->_fullname)
  	{
  		$answers = $this->getAnswers();

  		foreach ($answers as $a)
  		{
  			if ($a->fieldtype == 'fullname' && $a->answer) {
  				$this->_fullname = $a->answer;
  				return $this->_fullname;
  			}
  		}
  		// still there... look for user ?
  		if ($this->load()->uid) {
  			$this->_fullname = JFactory::getUser()->get('name');
  			return $this->_fullname;
			}
  	}
    $this->_fullname = $name;
	}

  public function getFullname()
  {
    return $this->_fullname;
  }

	public function setEmail($email)
	{
    $this->_email= $email;
	}

  public function getEmail()
  {
  	if (!$this->_email)
  	{
  		$answers = $this->getAnswers();

  		foreach ($answers as $a)
  		{
  			if ($a->fieldtype == 'email' && JMailHelper::isEmailAddress($a->answer)) {
  				$this->_email = $a->answer;
  				return $this->_email;
  			}
  		}
  		// still there... look for user ?
  		if ($this->load()->uid) {
  			$this->_email = JFactory::getUser()->get('email');
  			return $this->_email;
			}
  	}
    return $this->_email;
  }

  public function setId($id)
  {
    $this->_id = (int) $id;
  }

  public function getId()
  {
    return $this->_id;
  }

	/**
	 * loads data from the db
	 *
	 * @return object
	 */
	public function load()
	{
		if (empty($this->_data))
		{
			$query = ' SELECT r.* '
			       . ' FROM #__redevent_register AS r '
			       . ' WHERE r.id = ' . $this->_db->Quote($this->_id);
			$this->_db->setQuery($query);
			$res = $this->_db->loadObject();
			$this->_data = $res;
		}
		return $this->_data;
	}

	/**
	 * confirms attendee registration
	 *
	 * @return true on success
	 */
	public function confirm()
	{
		// first, changed status to confirmed
		$query = ' UPDATE #__redevent_register '
		       . ' SET confirmed = 1, confirmdate = ' .$this->_db->Quote(gmdate('Y-m-d H:i:s'))
		       . '   , paymentstart = ' .$this->_db->Quote(gmdate('Y-m-d H:i:s'))
		       . ' WHERE id = ' . $this->_id;
		$this->_db->setQuery($query);
		$res = $this->_db->query();

		if (!$res) {
			$this->setError(JText::_('COM_REDEVENT_REGISTRATION_FAILED_CONFIRM_REGISTRATION'));
			return false;
		}

		// now, handle waiting list
		$session = $this->getSessionDetails();
		if ($session->maxattendees == 0) { // no waiting list
			// send attending email
			$this->sendWaitinglistStatusEmail(0);
			$this->sendWLAdminNotification(0);
			return true;
		}

		$attendees = $this->getAttending();
		if (count($attendees) > $session->maxattendees)
		{
			// put this attendee on WL
			$this->toggleWaitingListStatus(1);
		}
		else
		{
			$this->addToAttending();

			// send attending email
			$this->sendWaitinglistStatusEmail(0);
			$this->sendWLAdminNotification(0);
		}
		return true;
	}

  /**
   * toggles waiting list status
   *
   * @param int $waiting 0 for attending, 1 for waiting
   * @return true on success
   */
	public function toggleWaitingListStatus($waiting = null)
	{
		$data = $this->load();

		if (is_null($waiting)) {
	  	$waiting = $data->waitinglist ? 0 : 1;
		}

		$query = ' UPDATE #__redevent_register AS r '
		       . ' SET r.waitinglist = '.$waiting
		       . '   , r.paymentstart = NOW() '
		       . ' WHERE id = ' . $this->_db->Quote($this->_id);
		$this->_db->setQuery($query);

		if (!$this->_db->query()) {
			$this->setError(JText::_('COM_REDEVENT_FAILED_UPDATING_WAITINGLIST_STATUS'));
			return false;
		}
		try
		{
			$this->sendWaitinglistStatusEmail($waiting);
			$this->sendWLAdminNotification($waiting);
		}
		catch (Exception $e) {
			$this->setError($e->getMessage());
			return false;
		}
		return true;
	}

	/**
	 * send waiting list status emails
	 *
	 * @param int $rid register id
	 * @param int $waiting status: 0 for attending, 1 for waiting
	 * @return boolean true on success
	 */
	public function sendWaitinglistStatusEmail($waiting = 0)
	{
		$data = $this->load();
		$session = $this->getSessionDetails();

		$sid = $data->sid;

		if (empty($this->taghelper)) {
			$this->taghelper = new redEVENT_tags();
			$this->taghelper->setXref($data->xref);
		}

		if ($waiting == 0)
		{
			if ($session->notify_off_list_subject)
			{
				$subject = $session->notify_off_list_subject;
				$body    = $session->notify_off_list_body;
			}
			else if ($session->notify_subject)
			{
				$subject = $session->notify_subject;
				$body    = $session->notify_body;
			}
			else
			{
				$subject = JText::_('COM_REDEVENT_WL_DEFAULT_NOTIFY_OFF_SUBJECT');
				$body    = JText::_('COM_REDEVENT_WL_DEFAULT_NOTIFY_OFF_BODY');
			}
			$body    = $this->taghelper->ReplaceTags($body);
			$subject = $this->taghelper->ReplaceTags($subject);
		}
		else
		{
			if ($session->notify_on_list_body)
			{
				$subject = $session->notify_on_list_subject;
				$body    = $session->notify_on_list_body;
			}
			else
			{
				$subject = JText::_('COM_REDEVENT_WL_DEFAULT_NOTIFY_ON_SUBJECT');
				$body    = JText::_('COM_REDEVENT_WL_DEFAULT_NOTIFY_ON_BODY');
			}
			$body    = $this->taghelper->ReplaceTags($body);
			$subject = $this->taghelper->ReplaceTags($subject);
		}

		if (empty($subject)) {
			// not sending !
			throw new Exception(JText::_('COM_REDEVENT_WL_NOTIFICATION_MISSING_SUBJECT'));
			return false;
		}

		// update image paths in body
		$body = REOutput::ImgRelAbs($body);

		$mailer = JFactory::getMailer();

		$rfcore = new RedFormCore();
		$emails = $rfcore->getSidContactEmails($sid);
		foreach ($emails as $email)
		{
			/* Add the email address */
			$mailer->AddAddress($email['email'], $email['fullname']);
		}

		/* Mail submitter */
		$htmlmsg = '<html><head><title></title></title></head><body>'.$body.'</body></html>';
		$mailer->setBody($htmlmsg);
		$mailer->setSubject($subject);
		$mailer->IsHTML(true);

		/* Send the mail */
		if (!$mailer->Send()) {
			RedeventHelperLog::simpleLog(JText::_('COM_REDEVENT_REGISTRATION_FAILED_SENDING_WAITING_LIST_STATUS_EMAIL'));
			throw new Exception(JText::_('COM_REDEVENT_REGISTRATION_FAILED_SENDING_WAITING_LIST_STATUS_EMAIL'));
			return false;
		}

		return true;
	}

	/**
	 * send waiting list status change notification to event admins
	 *
	 * @param int $waiting 0 for attending, 1 for waiting
	 * @return boolean true on success
	 */
	public function sendWLAdminNotification($waiting = 0)
	{
		$params = JComponentHelper::getParams('com_redevent');
		if (!$params->get('wl_notify_admin', 0)) { // never notify admins
			return true;
		}
		else if ($params->get('wl_notify_admin', 0) == 1 && $waiting == 1) { // only for people begin added to attending
			return true;
		}
		else if ($params->get('wl_notify_admin', 0) == 2 && $waiting == 0) { // only for people being added to waiting list
			return true;
		}

		$app    = &JFactory::getApplication();
		$tags   = new redEVENT_tags();
		$tags->setXref($this->getXref());
		$tags->addOptions(array('sids' => array($this->load()->sid)));
		$event = $this->getSessionDetails();
		// recipients
		$recipients = $this->getAdminEmails();

		if (!count($recipients)) {
			return true;
		}

		$mailer = & JFactory::getMailer();
		$sender = array($app->getCfg('mailfrom'), $app->getCfg('sitename'));
		$mailer->setSender($sender);
		$mailer->addReplyTo($sender);

		foreach ($recipients as $r)
		{
			$mailer->addAddress($r['email'], $r['name']);
		}

		$subject = $tags->ReplaceTags($waiting ? $params->get('wl_notify_admin_waiting_subject') : $params->get('wl_notify_admin_attending_subject'));
		$body    = $tags->ReplaceTags($waiting ? $params->get('wl_notify_admin_waiting_body') : $params->get('wl_notify_admin_attending_body'));
		$body    = REOutput::ImgRelAbs($body);

		$mailer->setSubject($subject);
		$mailer->MsgHTML($body);
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
	protected function getAdminEmails()
	{
		$params = JComponentHelper::getParams('com_redevent');
  	$event  = $this->getSessionDetails();

		$recipients = array();
		// default recipients
		$default = $params->get('registration_default_recipients');
		if (!empty($default))
		{
			if (strstr($default, ';')) {
				$addresses = explode(";", $default);
			}
			else {
				$addresses = explode(",", $default);
			}
			foreach ($addresses as $a)
			{
				$a = trim($a);
				if (JMailHelper::isEmailAddress($a)) {
					$recipients[] = array('email' => $a, 'name' => '');
				}
			}
		}

		// creator
		if ($params->get('registration_notify_creator', 1)) {
			if (JMailHelper::isEmailAddress($event->creator_email)) {
				$recipients[] = array('email' => $event->creator_email, 'name' => $event->creator_name);
			}
		}

		// group recipients
		$gprecipients = $this->getXrefRegistrationRecipients();
		foreach ($gprecipients AS $r)
		{
			if (JMailHelper::isEmailAddress($r->email)) {
				$recipients[] =  array('email' => $r->email, 'name' => $r->name);
			}
		}

		// redform recipients
		$rfrecipients = $this->getRFRecipients();
		foreach ((array) $rfrecipients as $r)
		{
			if (JMailHelper::isEmailAddress($r)) {
				$recipients[] =  array('email' => $r, 'name' => '');
			}
		}
		return $recipients;
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
	 * return array
	 */
	protected function getAnswers()
	{
		if (empty($this->_answers))
		{
			$rfcore  = new redformcore();
			$this->_answers = $rfcore->getSidsFieldsAnswers($this->load()->sid);
		}
		return $this->_answers;
	}

	/**
	 * returns attendee event session info
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
			. " IF (x.course_credit = 0, '', x.course_credit) AS course_credit, a.course_code, a.submission_types, c.catname, c.published, c.access,"
			. ' CASE WHEN CHAR_LENGTH(x.title) THEN CONCAT_WS(\' - \', a.title, x.title) ELSE a.title END as full_title, '
			. ' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug, '
			. ' CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as categoryslug '
			. ' FROM #__redevent_events AS a'
			. ' LEFT JOIN #__redevent_event_venue_xref AS x ON x.eventid = a.id'
			. ' LEFT JOIN #__redevent_venues AS v ON x.venueid = v.id'
			. ' LEFT JOIN #__redevent_event_category_xref AS xcat ON xcat.event_id = a.id'
			. ' LEFT JOIN #__redevent_categories AS c ON c.id = xcat.category_id'
			. ' LEFT JOIN #__users AS u ON a.created_by = u.id '
			. ' WHERE x.id = '.$xref
			;
			$this->_db->setQuery($query);
			self::$sessions[$xref] = $this->_db->loadObject();
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

		foreach ($fields as $f)
		{
			$property = 'field_'.$f->id;
			if ($f->fieldtype == 'fileupload')
			{
				foreach ($answers as $a)
				{
					$path = $a->answer;
					if (!empty($path) && file_exists($path)) {
						$files[] = $path;
					}
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
			. '   AND r.waitinglist = 0 '
			;
			$this->_db->setQuery($query);
			self::$attending[$this->getXref()] = $this->_db->loadResultArray();
		}
		return self::$attending[$this->getXref()];
	}

	/**
	 * add id to the list of attending attendees
	 *
	 */
	protected function addToAttending()
	{
		self::$attending[$this->getXref()][] = $this->_id;
	}

	/**
	 * returns registration recipients from groups acl
	 *
	 * @return array
	 */
	protected function getXrefRegistrationRecipients()
	{
		$event = $this->getSessionDetails();
		$usersIds = UserAcl::getXrefRegistrationRecipients($event->xref);

		if (!$usersIds)
		{
			return false;
		}

		$db      = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('u.name, u.email');
		$query->from('#__users AS u');
		$query->where('u.id IN (' . implode(",", $usersIds) . ')');

		$db->setQuery($query);
		$xref_group_recipients = $db->loadObjectList();

		return $xref_group_recipients;
	}

	/**
	* Send e-mail confirmations
	*/
	public function sendNotificationEmail()
	{
		$mainframe = JFactory::getApplication();
		$eventsettings = $this->getSessionDetails();

		/**
		 * Send a submission mail to the attendee and/or contact person
		 * This will only work if the contact person has an e-mail address
		 **/
		if (isset($eventsettings->notify) && $eventsettings->notify)
		{
			/* Load the mailer */
			$mailer = JFactory::getMailer();
			$mailer->isHTML(true);
			$mailer->From = $mainframe->getCfg('mailfrom');
			$mailer->FromName = $mainframe->getCfg('sitename');
			$mailer->AddReplyTo(array($mainframe->getCfg('mailfrom'), $mainframe->getCfg('sitename')));

			$tags = new redEVENT_tags();
			$tags->setXref($this->getXref());
			$tags->addOptions(array('sids' => array($this->load()->sid)));

			$rfcore = new RedFormCore();
			$emails = $rfcore->getSidContactEmails($this->load()->sid);

			/* build activation link */
			// TODO: use the route helper !
			$url = JRoute::_( JURI::root().'index.php?option=com_redevent&controller=registration&task=activate'
			. '&confirmid='.str_replace(".", "_", $this->_data->uip)
			.              'x'.$this->_data->xref
			.              'x'.$this->_data->uid
			.              'x'.$this->_data->id
			.              'x'.$this->_data->submit_key );
			$activatelink = '<a href="'.$url.'">'.JText::_('COM_REDEVENT_Activate').'</a>';
			$cancellink = JRoute::_(JURI::root().'index.php?option=com_redevent&task=cancelreg'
			.'&rid='.$this->_data->id.'&xref='.$this->_data->xref.'&submit_key='.$this->_data->submit_key);

			/* Mail attendee */
			$htmlmsg = '<html><head><title></title></title></head><body>';
			$htmlmsg .= $eventsettings->notify_body;
			$htmlmsg .= '</body></html>';

			$htmlmsg = $tags->ReplaceTags($htmlmsg);
			$htmlmsg = str_replace('[activatelink]', $activatelink, $htmlmsg);
			$htmlmsg = str_replace('[cancellink]', $cancellink, $htmlmsg);
			$htmlmsg = str_replace('[fullname]', $this->getFullname(), $htmlmsg);

			// convert urls
			$htmlmsg = REOutput::ImgRelAbs($htmlmsg);

			$mailer->setBody($htmlmsg);
			$subject = $tags->ReplaceTags($eventsettings->notify_subject);
			$mailer->setSubject($subject);

			foreach ($emails as $email)
			{
				/* Add the email address */
				$mailer->AddAddress($email['email'], $email['fullname']);
			}

			/* send */
			if (!$mailer->Send()) {
				RedeventHelperLog::simpleLog('Error sending notify message to submitted attendants');
				return false;
			}
		}
		return true;
	}

	function notifyManagers($unreg = false)
	{
		jimport('joomla.mail.helper');
		$app    = &JFactory::getApplication();
		$params = $app->getParams('com_redevent');
		$tags   = new redEVENT_tags();
		$tags->setXref($this->getXref());
		$tags->setSubmitkey($this->load()->submit_key);
		$tags->addOptions(array('sids' => array($this->load()->sid)));

		$event = $this->getSessionDetails();

		$recipients = $this->getAdminEmails();
        if(!empty($event->venue_email))
        {
            $recipients[] = array('email' => $event->venue_email, 'name' => $event->venue);
        }
		if (!count($recipients)) {
			return true;
		}

		$mailer = & JFactory::getMailer();
		if ($this->getEmail() && $params->get('allow_email_aliasing', 1)) {
			$sender = array($this->getEmail(), $this->getFullname());
		}
		else { // default to site settings
			$sender = array($app->getCfg('mailfrom'), $app->getCfg('sitename'));
		}
		$mailer->setSender($sender);
		$mailer->addReplyTo($sender);

		foreach ($recipients as $r)
		{
			$mailer->addAddress($r['email'], $r['name']);
		}

		$mail = '<HTML><HEAD>
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
			'.$tags->ReplaceTags($unreg ? $params->get('unregistration_notification_body') : $params->get('registration_notification_body')).'
			</body>
			</html>';

		// convert urls
		$mail = REOutput::ImgRelAbs($mail);

		if (!$unreg && $params->get('registration_notification_attach_rfuploads', 1))
		{
			// files submitted through redform
			$files = $this->getRFFiles();
			$filessize = 0;
			foreach ($files as $f)
			{
				$filessize += filesize($f);
			}

			if ($filessize < $params->get('registration_notification_attach_rfuploads_maxsize', 1500) * 1000)
			{
				foreach ($files as $f) {
					$mailer->addAttachment($f);
				}
			}
		}

		$mailer->setSubject($tags->ReplaceTags($unreg ? $params->get('unregistration_notification_subject') : $params->get('registration_notification_subject')));
		$mailer->MsgHTML($mail);
		if (!$mailer->send())
		{
			RedeventHelperLog::simplelog(JText::_('COM_REDEVENT_ERROR_REGISTRATION_MANAGERS_NOTIFICATION_FAILED'));
			$this->setError(JText::_('COM_REDEVENT_ERROR_REGISTRATION_MANAGERS_NOTIFICATION_FAILED'));
			return false;
		}
		return true;
	}

}
