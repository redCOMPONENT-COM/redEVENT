<?php
/**
 * @package    Redevent.Site
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * redEvent Component registration Model
 *
 * @package  Redevent.Site
 * @since    2.0
 */
class RedeventModelRegistration extends RModel
{
	/**
	 * event session id
	 * @var int
	 */
	protected $xref = 0;

	/**
	 * data
	 * @var object
	 */
	protected $xrefdata = null;

	/**
	 * registration submit_key
	 * @var string
	 */
	protected $submit_key;

	/**
	 * caching redform fields for this submit_key
	 * @var array
	 */
	protected $rf_fields;

	/**
	 * caching registration answers from redform
	 * @var array
	 */
	protected $rf_answers;

	/**
	 * @var RedeventEntitySessionpricegroup
	 */
	protected $prices = null;

	/**
	 * @var string
	 */
	protected $origin = '';

	/**
	 * Constructor
	 *
	 * @param   int    $xref    session id
	 * @param   array  $config  config array
	 */
	public function __construct($xref = 0, $config = array())
	{
		parent::__construct($config);

		if ($xref)
		{
			$this->setXref($xref);
		}
		else
		{
			$this->setXref(JFactory::getApplication()->input->getInt('xref', 0));
		}
	}

	/**
	 * Set session id
	 *
	 * @param   int  $xrefId  session id
	 *
	 * @return void
	 */
	public function setXref($xrefId)
	{
		$this->xref = (int) $xrefId;
	}

	/**
	 * Set submit key
	 *
	 * @param   string  $submitKey  submit key
	 *
	 * @return void
	 */
	public function setSubmitKey($submitKey)
	{
		if ($submitKey && $this->submit_key != $submitKey)
		{
			$this->submit_key = $submitKey;
			$this->rf_answers = null;
			$this->rf_fields  = null;
		}
	}

	/**
	 * Setter
	 *
	 * @param   string  $origin  registration origin (b2c, backend, b2b, 3rd party, etc...)
	 *
	 * @return RedEventModelRegistration
	 */
	public function setOrigin($origin)
	{
		$this->origin = $origin;

		return $this;
	}

	/**
	 * create a new attendee
	 *
	 * @param   object  $user                  performing the registration
	 * @param   int     $sid                   associated redform submitter id
	 * @param   string  $submitKey             associated redform submit key
	 * @param   int     $sessionpricegroup_id  pricegroup id for registration
	 *
	 * @return boolean|object attendee row or false if failed
	 */
	public function register($user, $sid, $submitKey, $sessionpricegroup_id)
	{
		$config  = RedeventHelper::config();
		$session = $this->getSessionDetails();

		if (!$sid)
		{
			$this->setError(JText::_('COM_REDEVENT_REGISTRATION_UPDATE_XREF_REQUIRED'));

			return false;
		}

		$obj = RTable::getAdminInstance('Attendee', array(), 'com_redevent');
		$obj->load(array('sid' => $sid));

		$isNew = $obj->id == 0;

		if ($isNew)
		{
			$obj->origin = $this->origin;
		}

		$obj->sid        = $sid;
		$obj->xref       = $this->xref;
		$obj->sessionpricegroup_id = $sessionpricegroup_id;
		$obj->submit_key = $submitKey;
		$obj->uid        = $user ? $user->id : 0;
		$obj->uregdate 	 = gmdate('Y-m-d H:i:s');
		$obj->uip        = $config->get('storeip', '1') ? getenv('REMOTE_ADDR') : 'DISABLED';

		if (!$obj->check())
		{
			$this->setError($obj->getError());

			return false;
		}

		if (!$obj->store())
		{
			$this->setError($obj->getError());

			return false;
		}

		$submitter        = RdfEntitySubmitter::load($sid);
		$confirmOnPayment = redEventHelper::config()->get('payBeforeConfirm', 0);

		if (!$obj->confirmed
			&& $session->activate == 0 // No activation
			&& (!$confirmOnPayment || $submitter->isPaid()))
		{
			$doConfirm = true;

			JPluginHelper::importPlugin('redevent');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('onBeforeAutoConfirm', array($obj->id, &$doConfirm));

			if ($doConfirm)
			{
				$this->confirm($obj->id);
			}
		}

		return $obj;
	}

	/**
	 * Check confirm state from activation link parameters
	 *
	 * @param   string  $submit_key   submit key
	 * @param   int     $register_id  registration id
	 * @param   int     $uid          user id
	 * @param   int     $xref         xref
	 *
	 * @return object
	 */
	public function getRegistrationFromActivationLink($submit_key, $register_id, $uid, $xref)
	{
		// Check the db if this entry exists
		$query = $this->_db->getQuery(true)
			->select('r.confirmed')
			->from('#__redevent_register AS r')
			->where('r.uid = ' . $this->_db->Quote($uid))
			->where('r.submit_key = ' . $this->_db->Quote($submit_key))
			->where('r.xref = ' . $this->_db->Quote($xref))
			->where('r.id = ' . $this->_db->Quote($register_id));

		$this->_db->setQuery($query);

		return $this->_db->loadObject();
	}

	/**
	 * to update a registration
	 *
	 * @param   int     $sid                   associated redform submitter id
	 * @param   string  $submit_key            associated redform submit key
	 * @param   int     $sessionpricegroup_id  session pricegroup id
	 *
	 * @return boolean|object attendee row or false if failed
	 */
	public function update($sid, $submit_key, $sessionpricegroup_id)
	{
		if (!$sid)
		{
			$this->setError(JText::_('COM_REDEVENT_REGISTRATION_UPDATE_XREF_REQUIRED'));

			return false;
		}

		$obj = RTable::getAdminInstance('Attendee');
		$obj->load(array('sid' => $sid));
		$obj->sid = $sid;
		$obj->sessionpricegroup_id = $sessionpricegroup_id;
		$obj->submit_key = $submit_key;

		if (!$obj->check())
		{
			$this->setError($obj->getError());

			return false;
		}

		if (!$obj->store())
		{
			$this->setError($obj->getError());

			return false;
		}

		return $obj;
	}

	/**
	 * confirm a registration
	 *
	 * @param   int  $rid  register id
	 *
	 * @return boolean true on success
	 */
	public function confirm($rid)
	{
		$attendee = new RedeventAttendee($rid);

		// First, changed status to confirmed
		if (!$attendee->confirm())
		{
			$this->setError($attendee->getError());

			return false;
		}

		return true;
	}

	/**
	 * Get session details
	 *
	 * @return boolean|mixed|object
	 */
	public function getSessionDetails()
	{
		if (empty($this->xrefdata))
		{
			if (empty($this->xref))
			{
				$this->setError(JText::_('COM_REDEVENT_missing_xref_for_session'));

				return false;
			}

			$query = $this->_db->getQuery(true)
				->select('a.id AS did, x.id AS xref, a.title as event_name, a.datdescription, t.meta_keywords, t.meta_description, a.datimage')
				->select('a.registra, a.unregistra, t.activate, t.notify, t.redform_id as form_id')
				->select('t.enable_activation_confirmation, t.notify_confirm_body, t.notify_confirm_subject, t.notify_subject, t.notify_body')
				->select('t.notify_off_list_subject, t.notify_off_list_body, t.notify_on_list_subject, t.notify_on_list_body')
				->select('x.*, x.title as session_name, a.created_by, t.redform_id, x.maxwaitinglist, x.maxattendees, t.juser, t.show_names')
				->select('t.submission_type_email, t.submission_type_external, t.submission_type_phone, t.showfields')
				->select('v.venue')
				->select('u.name AS creator_name, u.email AS creator_email')
				->select('t.confirmation_message, t.review_message')
				->select('IF (x.course_credit = 0, "", x.course_credit) AS course_credit')
				->select('a.course_code, t.submission_types, c.name AS catname, c.published, c.access')
				->select('CASE WHEN CHAR_LENGTH(x.title) THEN CONCAT_WS(\' - \', a.title, x.title) ELSE a.title END as full_title')
				->select('CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug')
				->select('CASE WHEN CHAR_LENGTH(x.alias) THEN CONCAT_WS(\':\', x.id, x.alias) ELSE x.id END as xslug')
				->select('CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as categoryslug')
				->from('#__redevent_events AS a')
				->innerJoin('#__redevent_event_template AS t ON t.id = a.template_id')
				->join('LEFT', '#__redevent_event_venue_xref AS x ON x.eventid = a.id')
				->join('LEFT', '#__redevent_venues AS v ON x.venueid = v.id')
				->join('LEFT', '#__redevent_event_category_xref AS xcat ON xcat.event_id = a.id')
				->join('LEFT', '#__redevent_categories AS c ON c.id = xcat.category_id')
				->join('LEFT', '#__users AS u ON a.created_by = u.id')
				->where('x.id = ' . $this->xref);

			$this->_db->setQuery($query);
			$this->xrefdata = $this->_db->loadObject();

			if ($this->xrefdata)
			{
				$this->xrefdata = $this->_getEventCategories($this->xrefdata);
			}
		}

		return $this->xrefdata;
	}

	/**
	 * adds categories property to event row
	 *
	 * @param   object  $row  event
	 *
	 * @return object
	 */
	protected function _getEventCategories($row)
	{
		$query = $this->_db->getQuery(true)
			->select('c.id, c.name, c.access')
			->select('CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as slug')
			->from('#__redevent_categories as c')
			->join('INNER', '#__redevent_event_category_xref as x ON x.category_id = c.id')
			->where('c.published = 1')
			->where('x.event_id = ' . $this->_db->Quote($row->did))
			->order('c.ordering');

		$this->_db->setQuery($query);
		$row->categories = $this->_db->loadObjectList();

		return $row;
	}

	/**
	 * Send e-mail confirmations
	 *
	 * @param   string  $submit_key  submit key
	 *
	 * @return boolean true on success
	 */
	public function sendNotificationEmail($submit_key)
	{
		// Load database connection
		$db = JFactory::getDBO();

		// Get registration settings
		$query = $this->_db->getQuery(true)
			->select('r.id')
			->from('#__redevent_register AS r')
			->where('submit_key = ' . $db->Quote($submit_key));

		$this->_db->setQuery($query);
		$registrations = $db->loadColumn();

		if (!$registrations || !count($registrations))
		{
			JError::raiseError(0, JText::sprintf('COM_REDEVENT_notification_registration_not_found_for_key_s', $submit_key));

			return false;
		}

		foreach ($registrations as $rid)
		{
			$attendee = new RedeventAttendee($rid);

			if (!$attendee->sendNotificationEmail())
			{
				$this->setError($attendee->getError());

				return false;
			}
		}

		return true;
	}

	/**
	 * Notify managers
	 *
	 * @param   string  $submit_key  submit key
	 * @param   bool    $unreg       is this an unregistration ?
	 * @param   int     $reg_id      registration id
	 *
	 * @return boolean
	 */
	public function notifyManagers($submit_key, $unreg = false, $reg_id = 0)
	{
		if ($reg_id)
		{
			$registrations = array($reg_id);
		}
		else
		{
			// Get registration settings
			$query = $this->_db->getQuery(true)
				->select('r.id')
				->from('#__redevent_register AS r')
				->where('submit_key = ' . $this->_db->Quote($submit_key));

			$this->_db->setQuery($query);
			$registrations = $this->_db->loadColumn();

			if (!$registrations || !count($registrations))
			{
				JError::raiseError(0, JText::sprintf('COM_REDEVENT_notification_registration_not_found_for_key_s', $submit_key));

				return false;
			}
		}

		foreach ($registrations as $rid)
		{
			$attendee = new RedeventAttendee($rid);

			if (!$attendee->notifyManagers($unreg))
			{
				$this->setError($attendee->getError());

				return false;
			}
		}

		return true;
	}

	/**
	 * Get registration details
	 *
	 * @param   int  $submitter_id  submitter id
	 *
	 * @return boolean|mixed
	 */
	public function getRegistration($submitter_id)
	{
		$query = $this->_db->getQuery(true)
			->select('s.*, r.uid, r.xref, r.sessionpricegroup_id, e.unregistra')
			->from('#__rwf_submitters AS s')
			->join('INNER', '#__redevent_register AS r ON r.sid = s.id')
			->join('INNER', '#__redevent_event_venue_xref AS x ON x.id = r.xref')
			->join('INNER', '#__redevent_events AS e ON x.eventid = e.id')
			->where('s.id = ' . $this->_db->Quote($submitter_id));

		$this->_db->setQuery($query);
		$registration = $this->_db->loadObject();

		if (!$registration)
		{
			$this->setError(JText::_('COM_REDEVENT_REGISTRATION_NOT_VALID'));

			return false;
		}

		$query = $this->_db->getQuery(true)
			->select('*')
			->from('#__rwf_forms_' . $registration->form_id)
			->where('id = ' . $registration->answer_id);

		$this->_db->setQuery($query);
		$registration->answers = $this->_db->loadObject();

		return $registration;
	}

	/**
	 * Get session pricegroup according to sessionpricegroup_id
	 *
	 * @param   int  $sessionpricegroup_id  session pricegroup id
	 *
	 * @return RedeventEntitySessionpricegroup session price group
	 */
	public function getRegistrationPrice($sessionpricegroup_id)
	{
		$pricegoups = $this->getPricegroups();

		if (!count($pricegoups))
		{
			return 0;
		}

		foreach ($pricegoups as $p)
		{
			if ($p->id == $sessionpricegroup_id)
			{
				return $p;
			}
		}

		// Session pricegroup not found... not good at all !
		$this->setError(JText::_('COM_REDEVENT_Pricegroup_not_found'));

		return false;
	}

	/**
	 * Get current session price groups
	 *
	 * @return RedeventEntitySessionpricegroup[]
	 */
	public function getPricegroups()
	{
		if (!$this->prices)
		{
			$session = RedeventEntitySession::load($this->xref);
			$this->prices = $session->getActivePricegroups('true');
		}

		return $this->prices;
	}

	/**
	 * Cancel a registration
	 *
	 * @param   int  $register_id  registration ird
	 *
	 * @return boolean true on success
	 */
	public function cancelregistration($register_id)
	{
		$user = JFactory::getUser();
		$userid = $user->get('id');

		$acl = RedeventUserAcl::getInstance();

		if ($userid < 1)
		{
			throw new RuntimeException(JText::_('COM_REDEVENT_ALERTNOTAUTH'), 403);
		}

		// First, check if the user is allowed to unregister from this
		// He must be the one that submitted the form, plus the unregistration must be allowed
		$query = $this->_db->getQuery(true)
			->select('s.*, r.uid, r.xref, r.cancelled, e.unregistra, x.dates, x.times, x.registrationend')
			->from('#__rwf_submitters AS s')
			->join('INNER', '#__redevent_register AS r ON r.sid = s.id')
			->join('INNER', '#__redevent_event_venue_xref AS x ON x.id = r.xref')
			->join('INNER', '#__redevent_events AS e ON x.eventid = e.id')
			->where('r.id = ' . $this->_db->Quote($register_id));

		$this->_db->setQuery($query);
		$submitterinfo = $this->_db->loadObject();

		// Or be allowed to manage attendees
		$manager = $acl->canManageAttendees($submitterinfo->xref);

		if (!RedeventHelper::canUnregister($submitterinfo->xref) && !$manager)
		{
			throw new RuntimeException(JText::_('COM_REDEVENT_UNREGISTRATION_NOT_ALLOWED'));
		}

		if (($submitterinfo->uid <> $userid || $submitterinfo->unregistra == 0) && !$manager)
		{
			throw new RuntimeException(JText::_('COM_REDEVENT_UNREGISTRATION_NOT_ALLOWED'));
		}

		// Now that we made sure, we can set as cancelled
		$query = $this->_db->getQuery(true)
			->update('#__redevent_register')
			->set('cancelled = 1')
			->where('id = ' . $this->_db->Quote($register_id));

		$this->_db->setQuery($query);

		if (!$this->_db->execute())
		{
			throw new RuntimeException(JText::_('COM_REDEVENT_ERROR_CANNOT_DELETE_REGISTRATION'));
		}

		// Turn submission in redFORM
		$helper = new RdfPaymentTurnsubmission($submitterinfo->sid);
		$helper->turn();

		return true;
	}

	/**
	 * create user from posted data
	 *
	 * @param   int  $sid  redform submission id
	 *
	 * @return object|false created user
	 */
	public function createUser($sid)
	{
		if (class_exists('RedmemberApi'))
		{
			return $this->createRedmemberUser($sid);
		}
		else
		{
			return $this->createJoomlaUser($sid);
		}
	}

	/**
	 * Create redMEMBER user
	 *
	 * @param   int  $sid  submitter id
	 *
	 * @return boolean|JUser
	 */
	protected function createRedmemberUser($sid)
	{
		$rfcore = RdfCore::getInstance();

		$answers = $rfcore->getAnswers(array($sid));
		$answers = $answers->getSubmissionBySid($sid);

		$data = array();
		$data['name'] = $answers->getFullname();
		$data['username'] = $answers->getUsername();
		$emails = $answers->getSubmitterEmails();

		if ($emails)
		{
			$data['email'] = reset($emails);
		}

		foreach ($answers->getFields() as $field)
		{
			if ($field->redmember_field)
			{
				$data[$field->redmember_field] = $field->getValue();
			}
		}

		if (!isset($data['email']) || !$data['email'])
		{
			RedeventError::raiseWarning('', JText::_('COM_REDEVENT_NEED_MISSING_EMAIL_TO_CREATE_USER'));

			return false;
		}

		if ($uid = $this->_getUserIdFromEmail($data['email']))
		{
			return JFactory::getUser($uid);
		}

		if (!isset($data['username']) || !$data['username'])
		{
			$data['username'] = $data['email'];
		}

		if (!isset($data['name']) || !$data['name'])
		{
			// Using rm_firstname and rm_lastname if exists
			if (isset($data['rm_firstname']) && $data['rm_firstname']
				&& isset($data['rm_lastname']) && $data['rm_lastname'])
			{
				$data['name'] = trim($data['rm_firstname'] . ' ' . $data['rm_lastname']);
			}
			else
			{
				$data['name'] = $data['username'];
			}
		}

		if (!isset($data['rm_firstname']) && !isset($data['rm_lastname']) && $data['name'])
		{
			$data['name'] = trim($data['name']);
			$parts = explode(' ', $data['name']);

			if (count($parts) > 1)
			{
				$data['rm_lastname'] = array_pop($parts);
				$data['rm_firstname'] = implode(' ', $parts);
			}
			else
			{
				$data['rm_lastname'] = $data['name'];
				$data['rm_firstname'] = '';
			}
		}

		$password = JUserHelper::genRandomPassword();
		$data['password'] = md5($password);

		$rmUser = RedmemberApi::getUser();

		// Organization
		if (!empty($data['organization']))
		{
			$organization = RedmemberApi::getOrganization(array('name' => $data['organization']));
			$orgData = $data;
			$orgData['name'] = $data['organization'];

			if (!$organization->id)
			{
				$organization->save($orgData, false);
			}

			$rmUser->setOrganizations(array(array('organization_id' => $organization->id, 'level' => 1)));
		}

		$rmUser->save($data);

		// Send email using juser controller
		$this->_sendUserCreatedMail($rmUser->user, $data['password']);

		return $rmUser->user;
	}

	/**
	 * inspired from com_user controller function
	 *
	 * @param   object  $user      user object
	 * @param   string  $password  user password
	 *
	 * @return void
	 *
	 * @TODO: refactor
	 */
	protected function _sendUserCreatedMail($user, $password)
	{
		$lang = JFactory::getLanguage();
		$lang->load('com_user');

		$mainframe = JFactory::getApplication();

		$db = $this->_db;

		$name = $user->get('name');
		$email = $user->get('email');
		$username = $user->get('username');

		$usersConfig = JComponentHelper::getParams('com_users');
		$sitename = $mainframe->getCfg('sitename');
		$mailfrom = $mainframe->getCfg('mailfrom');
		$fromname = $mainframe->getCfg('fromname');
		$siteURL = JURI::base();

		$subject = JText::sprintf('COM_REDEVENT_CREATED_ACCOUNT_EMAIL_SUBJECT', $name, $sitename);
		$subject = html_entity_decode($subject, ENT_QUOTES);

		$message = JText::_('COM_REDEVENT_INFORM_USERNAME');
		$message = str_replace('[fullname]', $name, $message);
		$message = str_replace('[username]', $username, $message);
		$message = str_replace('[password]', $password, $message);

		$message = html_entity_decode($message, ENT_QUOTES);

		$mail = RdfHelper::getMailer();
		$mail->sendMail($mailfrom, $fromname, $email, $subject, $message);

		// Send notification to all administrators
		$subject2 = JText::sprintf('COM_REDEVENT_CREATED_ACCOUNT_EMAIL_SUBJECT', $name, $sitename);
		$subject2 = html_entity_decode($subject2, ENT_QUOTES);

		// Get all users that are set to receive system emails
		$query = $db->getQuery(true)
			->select('name, email')
			->from('#__users')
			->where('sendEmail = 1');

		$db->setQuery($query);
		$rows = $db->loadObjectList();

		foreach ($rows as $row)
		{
			$emailBody = JText::sprintf(
				'COM_REDEVENT_CREATED_ACCOUNT_EMAIL_BODY',
				$name,
				$sitename
			);
			$message2 = html_entity_decode($emailBody, ENT_QUOTES);
			$mail->sendMail($mailfrom, $fromname, $row->email, $subject2, $message2);
		}
	}

	/**
	 * Create a Joomla user from form data
	 *
	 * @param   int  $sid  submitter id
	 *
	 * @return boolean|JUser
	 *
	 * @throws Exception
	 */
	protected function createJoomlaUser($sid)
	{
		jimport('joomla.user.helper');

		$rfcore = RdfCore::getInstance();
		$answers = $rfcore->getSidContactEmails($sid);

		if (!$answers)
		{
			throw new Exception(JText::_('COM_REDEVENT_NO_ANSWERS_FOUND_FOR_SID') . ' ' . $sid);
		}

		$details = current($answers);

		if (!$details['email'])
		{
			RedeventError::raiseWarning('', JText::_('COM_REDEVENT_NEED_MISSING_EMAIL_TO_CREATE_USER'));

			return false;
		}

		if ($uid = $this->_getUserIdFromEmail($details['email']))
		{
			return JFactory::getUser($uid);
		}

		if (!$details['username'] && !$details['fullname'])
		{
			$username = 'redeventuser' . $sid;
			$details['fullname'] = $username;
		}
		else
		{
			$username = $details['username'] ? $details['username'] : $details['fullname'];
			$details['fullname'] = $details['fullname'] ? $details['fullname'] : $username;
		}

		$username = $this->getUniqueUsername($username);

		jimport('joomla.application.component.helper');

		// Get required system objects
		$user = clone JFactory::getUser(0);
		$password = JUserHelper::genRandomPassword();

		$config = JComponentHelper::getParams('com_users');

		// Default to Registered.
		$defaultUserGroup = $config->get('new_usertype', 2);

		// Set some initial user values
		$user->set('id', 0);
		$user->set('name', $details['fullname']);
		$user->set('username', $username);
		$user->set('email', $details['email']);
		$user->set('groups', array($defaultUserGroup));
		$user->set('password', md5($password));

		if (!$user->save())
		{
			RedeventError::raiseWarning('', JText::_($user->getError()));

			return false;
		}

		// Send email using juser controller
		$this->_sendUserCreatedMail($user, $password);

		return $user;
	}

	/**
	 * Returns userid if a user exists
	 *
	 * @param   string  $email  The email to search on
	 *
	 * @return integer The user id or 0 if not found
	 */
	protected function _getUserIdFromEmail($email)
	{
		// Initialize some variables
		$db = JFactory::getDBO();

		$query = 'SELECT id FROM #__users WHERE email = ' . $db->Quote($email);
		$db->setQuery($query, 0, 1);

		return $db->loadResult();
	}

	/**
	 * Make sure username is unique, adding suffix if necessary
	 *
	 * @param   string  $username  the username to check
	 *
	 * @return string
	 */
	protected function getUniqueUsername($username)
	{
		$db = JFactory::getDBO();

		$i = 2;

		while (true)
		{
			$query = 'SELECT id FROM #__users WHERE username = ' . $db->Quote($username);
			$db->setQuery($query, 0, 1);

			if ($db->loadResult())
			{
				// Username exists, add a suffix
				$username = $username . '_' . ($i++);
			}
			else
			{
				break;
			}
		}

		return $username;
	}
}
