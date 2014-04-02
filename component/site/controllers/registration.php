<?php
/**
 * @version 1.0 $Id$
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

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');

/**
 * redEVENT Component Registration Controller
 *
 * @package Joomla
 * @subpackage redEVENT
 * @since 2.0
*/
class RedEventControllerRegistration extends RedEventController
{
	/**
	 * Constructor
	 *
	 * @since 2.0
	 */
	function __construct()
	{
		parent::__construct();
		$this->registerTask( 'manageredit',   'edit' );
		$this->registerTask( 'managerupdate', 'update' );
		$this->registerTask( 'review', 'confirm' );
	}

	/**
	 * handle registration
	 *
	 * @return void
	 */
	public function register()
	{
		if (JRequest::getVar('cancel', '', 'post'))
		{
			return $this->cancelreg();
		}

		JPluginHelper::importPlugin('redevent');
		$dispatcher = JDispatcher::getInstance();

		$app = JFactory::getApplication();

		$xref        = JRequest::getInt('xref');
		$pricegroups = JRequest::getVar('sessionpricegroup_id', array(), 'post', 'array');
		$review      = JRequest::getVar('hasreview', 0);
		$isedit      = JRequest::getVar('isedit', 0);
		JArrayHelper::toInteger($pricegroups);

		if (!$xref)
		{
			$msg = JText::_('COM_REDEVENT_REGISTRATION_MISSING_XREF');
			$this->setRedirect('index.php', $msg, 'error');

			return false;
		}

		$status = redEVENTHelper::canRegister($xref);

		if (!$status->canregister)
		{
			$msg = $status->status;
			$this->setRedirect('index.php', $msg, 'error');

			return false;
		}

		$model = $this->getModel('registration');
		$model->setXref($xref);

		$details = $model->getSessionDetails();

		// Get prices associated to pricegroups
		$prices = array();

		foreach ($pricegroups as $p)
		{
			$regPricegroup = $model->getRegistrationPrice($p);

			if (!$regPricegroup)
			{
				$msg = JText::_('COM_REDEVENT_REGISTRATION_MISSING_PRICE');
				$this->setRedirect('index.php', $msg, 'error');

				return false;
			}

			$prices[] = $regPricegroup->price;
		}

		// First, ask redform to save it's fields, and return the corresponding sids.
		$options = array('baseprice' => $prices, $regPricegroup->currency);

		if ($review)
		{
			$options['savetosession'] = 1;
		}

		$rfcore = new RedformCore;
		$result = $rfcore->saveAnswers('redevent', $options);

		if (!$result)
		{
			// Redform saving failed
			$msg = JText::_('COM_REDEVENT_REGISTRATION_REDFORM_SAVE_FAILED') . ' - ' . $rfcore->getError();
			$this->setRedirect(JRoute::_(RedeventHelperRoute::getDetailsRoute($details->did, $xref)), $msg, 'error');

			return false;
		}

		// Trigger before registration plugin, that can alter redform data, or even stop the registration process
		$notification = false;
		$dispatcher->trigger('onBeforeRegistration', array($xref, &$result, &$notification));

		if ($notification)
		{
			echo $notification;

			return;
		}

		$submit_key = $result->submit_key;
		JRequest::setVar('submit_key', $submit_key);

		if ($review)
		{
			// Remember set prices groups
			$app->setUserState('spgids' . $submit_key, $pricegroups);
		}
		else
		{
			$app->setUserState('spgids' . $submit_key, null);
		}

		if (!$isedit && !$review)
		{
			// Redform saved fine, now add the attendees
			$user = JFactory::getUser();

			if (!$user->get('id') && $details->juser)
			{
				if ($new = $this->_createUser($result->posts[0]['sid']))
				{
					$user = $new;
				}
			}

			$k = 0;

			foreach ($result->posts as $rfpost)
			{
				$pricegroup = isset($pricegroups[$k]) ? $pricegroups[$k] : null;
				$k++;

				if (!$res = $model->register($user, $rfpost['sid'], $result->submit_key, $pricegroup))
				{
					$msg = JText::_('COM_REDEVENT_REGISTRATION_REGISTRATION_FAILED');
					$this->setRedirect(JRoute::_(RedeventHelperRoute::getDetailsRoute($details->did, $xref)), $msg, 'error');

					return false;
				}

				$dispatcher->trigger('onAttendeeCreated', array($res->id));
			}

			if ($details->notify)
			{
				$model->sendNotificationEmail($submit_key);
			}

			$model->notifyManagers($submit_key);

			$dispatcher->trigger('onEventUserRegistered', array($xref));
		}

		if (!$review)
		{
			$cache = JFactory::getCache('com_redevent');
			$cache->clean();

			$gateway = $app->input->get('gw');

			$rfredirect = $rfcore->getFormRedirect($details->redform_id);

			if ($rfredirect)
			{
				$link = $rfredirect;
			}
			elseif ($gateway)
			{
				$link = RedformHelperRoute::getPaymentProcessRoute($submit_key, $gateway);
			}
			else
			{
				$link = RedeventHelperRoute::getRegistrationRoute($xref, 'confirm', $submit_key);
			}
		}
		else
		{
			$link = RedeventHelperRoute::getRegistrationRoute($xref, 'review', $submit_key);
		}

		if ($app->input->getInt('modal', 0))
		{
			$link .= '&tmpl=component';
		}

		// Redirect to prevent resending the form on refresh
		$this->setRedirect(JRoute::_($link, false));
	}

	function cancelreg()
	{
		$submit_key = JRequest::getVar('submit_key');
		$xref = JRequest::getInt('xref');

		$model = $this->getModel('registration');
		$model->setXref($xref);
		$model->cancel($submit_key);
		$eventdata = $model->getSessionDetails();

		$msg = JText::_('COM_REDEVENT_Registration_cancelled');
		$this->setRedirect(JRoute::_(RedeventHelperRoute::getDetailsRoute($eventdata->did, $xref)), $msg);
	}

	function edit()
	{
		JRequest::setvar('view', 'registration');
		JRequest::setvar('layout', 'edit');
		parent::display();
	}

	function manageattendees()
	{
		$acl = RedeventUserAcl::getInstance();
		$xref = JRequest::getInt('xref');
		if ($acl->canManageAttendees($xref)) {
			$layout = 'manageattendees';
		}
		else if ($acl->canViewAttendees($xref)){
			$layout = 'default';
		}
		else {
			$this->setRedirect(RedeventHelperRoute::getMyEventsRoute(), JText::_('COM_REDEVENT_ACCESS_NOT_ALLOWED'), 'error');
			$this->redirect();
		}
		JRequest::setvar('view', 'attendees');
		JRequest::setvar('layout', $layout);
		parent::display();
	}

	function update()
	{
		$xref = JRequest::getInt('xref');
		$task = JRequest::getVar('task');
		if (JRequest::getVar('cancel', '', 'post'))
		{
			$msg = JText::_('COM_REDEVENT_Registration_Edit_cancelled');
			if ($task == 'managerupdate') {
				$this->setRedirect(JRoute::_(RedeventHelperRoute::getManageAttendees($xref)), $msg);
			}
			else {
				$this->setRedirect(JRoute::_(RedeventHelperRoute::getDetailsRoute(null, $xref), false), $msg);
			}
			$this->redirect();
		}

		$xref = JRequest::getInt('xref');
		$pricegroups = JRequest::getVar('pricegroup_id', array(), 'post', 'array');
		JArrayHelper::toInteger($pricegroups);

		$model = $this->getModel('registration');
		$model->setXref(JRequest::getInt('xref'));
		$details = $model->getSessionDetails();

		$submit_key = JRequest::getVar('submit_key');

		$prices = array();
		foreach ($pricegroups as $p)
		{
			$regPricegroup = $model->getRegistrationPrice($p);

			if (!$regPricegroup)
			{
				$msg = JText::_('COM_REDEVENT_REGISTRATION_MISSING_PRICE');
				$this->setRedirect('index.php', $msg, 'error');

				return;
			}

			$prices[] = $regPricegroup->price;
		}

		if (!$xref) {
			$msg = JText::_('COM_REDEVENT_REGISTRATION_MISSING_XREF');
			$this->setRedirect('index.php', $msg, 'error');
			return;
		}

		// first, ask redform to save it's fields, and return the corresponding sids.
		$options = array('baseprice' => $prices, 'currency' => $regPricegroup->currency);
		$rfcore = new RedformCore();
		$result = $rfcore->saveAnswers('redevent', $options);
		if (!$result) {
			$msg = JText::_('COM_REDEVENT_REGISTRATION_REDFORM_SAVE_FAILED').' - '.$rfcore->getError();
			if ($task == 'managerupdate') {
				$this->setRedirect(JRoute::_(RedeventHelperRoute::getManageAttendees($xref)), $msg, 'error');
			}
			else {
				$this->setRedirect(JRoute::_(RedeventHelperRoute::getDetailsRoute(null, $xref)), $msg, 'error');
			}
			return;
		}
		JRequest::setVar('submit_key', $result->submit_key);

		// redform save fine, now save corresponding bookings
		foreach ($result->posts as $rfpost)
		{
			$k = 0;
			if (!$res = $model->update($rfpost['sid'], $result->submit_key, $pricegroups[$k++])) {
				$msg = JText::_('COM_REDEVENT_REGISTRATION_REGISTRATION_UPDATE_FAILED');
				if ($task == 'managerupdate') {
					$this->setRedirect(JRoute::_(RedeventHelperRoute::getManageAttendees($xref)), $msg, 'error');
				}
				else {
					$this->setRedirect(JRoute::_(RedeventHelperRoute::getDetailsRoute(null, $xref)), $msg, 'error');
				}
				return;
			}
		}

		//		$mail = $model->sendNotificationEmail();
		//		$mail = $model->notifyManagers();

		$cache = JFactory::getCache('com_redevent');
		$cache->clean();

		$msg = JTEXT::_('COM_REDEVENT_REGISTRATION_UPDATED');
		if ($task == 'managerupdate') {
			$this->setRedirect(RedeventHelperRoute::getManageAttendees($xref), $msg);
		}
		else {
			$this->setRedirect(RedeventHelperRoute::getDetailsRoute(null, $xref), $msg);
		}
	}

	function confirm()
	{
		if (JRequest::getVar('task') == 'review') {
			JRequest::setVar('layout', 'review');
		}
		else {
			JRequest::setVar('layout', 'confirmed');
		}
		JRequest::setVar('view', 'registration');
		parent::display();
	}


	/**
	 * Confirms the users request
	 */
	function activate()
	{
		$mainframe = &JFactory::getApplication();
		$msgtype = 'message';

		/* Get the confirm ID */
		$confirmid = JRequest::getVar('confirmid', '', 'get');

		/* Get the details out of the confirmid */
		list($uip, $xref, $uid, $register_id, $submit_key) = explode("x", $confirmid);


		/* Confirm sign up via mail */
		$model = $this->getModel('Registration', 'RedEventModel');
		$model->setXref($xref);
		$eventdata = $model->getSessionDetails();

		/* This loads the tags replacer */
		JRequest::setVar('xref', $xref);
		require_once(JPATH_COMPONENT_SITE.DS.'helpers'.DS.'tags.php');
		$tags = new redEVENT_tags();
		$tags->setXref($xref);
		$tags->setSubmitkey($submit_key);

		/* Check the db if this entry exists */
		$db = JFactory::getDBO();
		$q = ' SELECT r.confirmed '
		. ' FROM #__redevent_register r '
		. ' WHERE r.uid = '.$db->Quote($uid)
		. ' AND r.submit_key = '.$db->Quote($submit_key)
		. ' AND r.xref = '.$db->Quote($xref)
		. ' AND r.id = '.$db->Quote($register_id)
		;
		$db->setQuery($q);
		$regdata = $db->loadObject();

		if ($regdata && $regdata->confirmed == 0)
		{
			$model->confirm($register_id);

			// send activation confirmation email if activated
			if ($eventdata->enable_activation_confirmation)
			{
				$this->_Mailer();

				$rfcore = new RedformCore();
				$addresses = $rfcore->getSubmissionContactEmail($submit_key);

				/* Check if there are any addresses to be mailed */
				if (count($addresses) > 0)
				{
					/* Start mailing */
					foreach ($addresses as $key => $sid)
					{
						foreach ($sid as $email)
						{
							/* Send a off mailinglist mail to the submitter if set */
							/* Add the email address */
							$this->mailer->AddAddress($email['email']);

							/* Mail submitter */
							$htmlmsg = '<html><head><title></title></title></head><body>'.$tags->ReplaceTags($eventdata->notify_confirm_body).'</body></html>';
							// convert urls
							$htmlmsg = REOutput::ImgRelAbs($htmlmsg);

							$this->mailer->setBody($htmlmsg);
							$this->mailer->setSubject($tags->ReplaceTags($eventdata->notify_confirm_subject));

							/* Send the mail */
							if (!$this->mailer->Send()) {
								$mainframe->enqueueMessage(JText::_('COM_REDEVENT_THERE_WAS_A_PROBLEM_SENDING_MAIL'));
								RedeventHelperLog::simpleLog('Error sending confirm email'.': '.$this->mailer->error);
							}

							/* Clear the mail details */
							$this->mailer->ClearAddresses();
						}
					}
				}
			}
			$msg = JText::_('COM_REDEVENT_REGISTRATION_ACTIVATION_SUCCESSFULL');

			JPluginHelper::importPlugin( 'redevent' );
			$dispatcher =& JDispatcher::getInstance();
			$res = $dispatcher->trigger('onUserConfirmed', array($register_id));
		}
		else if ($regdata && $regdata->confirmed == 1) {
			$msg = JText::_('COM_REDEVENT_YOUR_SUBMISSION_HAS_ALREADY_BEEN_CONFIRMED');
			$msgtype = 'error';
		}
		else {
			$msg = JText::_('COM_REDEVENT_YOUR_SUBMISSION_CANNOT_BE_CONFIRMED');
			$msgtype = 'error';
		}

		$this->setRedirect(JRoute::_(RedeventHelperRoute::getDetailsRoute($eventdata->did, $xref)), $msg, $msgtype);
	}


	/**
	 * Initialise the mailer object to start sending mails
	 */
	private function _Mailer()
	{
		if (empty($this->mailer))
		{
			$mainframe = & JFactory::getApplication();
			jimport('joomla.mail.helper');
			/* Start the mailer object */
			$this->mailer = JFactory::getMailer();
			$this->mailer->isHTML(true);
			$this->mailer->From = $mainframe->getCfg('mailfrom');
			$this->mailer->FromName = $mainframe->getCfg('sitename');
			$this->mailer->AddReplyTo(array($mainframe->getCfg('mailfrom'), $mainframe->getCfg('sitename')));
		}
		return $this->mailer;
	}

	/**
	 * create user from posted data
	 *
	 * @param   int  $sid  redform submission id
	 *
	 * @return object|false created user
	 */
	protected function _createUser($sid)
	{
		if (file_exists(JPATH_SITE . '/components/com_redmember/lib/redmemberlib.php'))
		{
			require_once JPATH_SITE . '/components/com_redmember/lib/redmemberlib.php';

			return $this->createRedmemberUser($sid);
		}
		else
		{
			return $this->createJoomlaUser($sid);
		}
	}

	/**
	 * Create a Joomla user from form data
	 *
	 * @param   $sid
	 *
	 * @return bool|JUser
	 * @throws Exception
	 */
	protected function createJoomlaUser($sid)
	{
		jimport('joomla.user.helper');

		$db		=& JFactory::getDBO();
		$rfcore = new RedformCore();
		$answers = $rfcore->getSidContactEmails($sid);

		if (!$answers)
		{
			throw new Exception(JText::_('COM_REDEVENT_NO_ANSWERS_FOUND_FOR_SID').' '.$sid);
		}

		$details = current($answers);

		if (!$details['email'])
		{
			//throw new Exception(JText::_('COM_REDEVENT_NEED_MISSING_EMAIL_TO_CREATE_USER'));
			RedeventError::raiseWarning('', JText::_('COM_REDEVENT_NEED_MISSING_EMAIL_TO_CREATE_USER'));
			return false;
		}

		if ($uid = $this->_getUserIdFromEmail($details['email']))
		{
			return JFactory::getUser($uid);
		}

		if (!$details['username'] && !$details['fullname'])
		{
			$username = 'redeventuser'.$sid;
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
		$user 		= clone(JFactory::getUser(0));
		$usersParams = JComponentHelper::getParams( 'com_users' ); // load the Params
		$password   = JUserHelper::genRandomPassword();

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
				// username exists, add a suffix
				$username = $username . '_' . $i++;
			}
			else
			{
				break;
			}
		}

		return $username;
	}

	protected function createRedmemberUser($sid)
	{
		$rfcore = new RedformCore;

		$answers = $rfcore->getSidsFieldsAnswers(array($sid));
		$answers = $answers[$sid];

		$data = array();

		foreach ($answers as $a)
		{
			switch ($a->fieldtype)
			{
				case 'fullname':
					$data['name'] = $a->answer;

				case 'username':
					$data['username'] = $a->answer;

				case 'email':
					if ($a->parameters->get('notify') && $a->answer)
					{
						$data['email'] = $a->answer;
					}

				default:
					if ($a->redmember_field)
					{
						$data[$a->redmember_field] = $a->answer;
					}
			}

		}

		if (!isset($data['email']) || !$data['email'])
		{
			//throw new Exception(JText::_('COM_REDEVENT_NEED_MISSING_EMAIL_TO_CREATE_USER'));
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

		$user = RedmemberLib::saveUser(false, $data);

		return $user;
	}

	/**
	 * inspired from com_user controller function
	 *
	 * @param   object  $user  user object
	 * @param   string  $password  user password
	 *
	 * @return void
	 */
	protected function _sendUserCreatedMail(&$user, $password)
	{
		$lang = JFactory::getLanguage();
		$lang->load('com_user');

		$mainframe = JFactory::getApplication();

		$db		= JFactory::getDBO();

		$name 		= $user->get('name');
		$email 		= $user->get('email');
		$username 	= $user->get('username');

		$usersConfig 	= JComponentHelper::getParams( 'com_users' );
		$sitename 		= $mainframe->getCfg( 'sitename' );
		$mailfrom 		= $mainframe->getCfg( 'mailfrom' );
		$fromname 		= $mainframe->getCfg( 'fromname' );
		$siteURL		= JURI::base();

		$subject 	= JText::sprintf('COM_REDEVENT_CREATED_ACCOUNT_EMAIL_SUBJECT', $name, $sitename);
		$subject 	= html_entity_decode($subject, ENT_QUOTES);

		$message = JText::_('COM_REDEVENT_INFORM_USERNAME');
		$message = str_replace('[fullname]', $name, $message);
		$message = str_replace('[username]', $username, $message);
		$message = str_replace('[password]', $password, $message);

		$message = html_entity_decode($message, ENT_QUOTES);

		// Get all super administrator
		$query = 'SELECT name, email, sendEmail' .
		' FROM #__users' .
		' WHERE LOWER( usertype ) = "super administrator"';
		$db->setQuery( $query );
		$rows = $db->loadObjectList();

		// Send email to user
		if ( ! $mailfrom  || ! $fromname )
		{
			$fromname = $rows[0]->name;
			$mailfrom = $rows[0]->email;
		}

		JUtility::sendMail($mailfrom, $fromname, $email, $subject, $message);

		// Send notification to all administrators
		$subject2 = JText::sprintf('COM_REDEVENT_CREATED_ACCOUNT_EMAIL_SUBJECT', $name, $sitename);
		$subject2 = html_entity_decode($subject2, ENT_QUOTES);

		// get superadministrators id
		foreach ( $rows as $row )
		{
			if ($row->sendEmail)
			{
				$message2 = sprintf ( JText::_( 'SEND_MSG_ADMIN' ), $row->name, $sitename, $name, $email, $username);
				$message2 = html_entity_decode($message2, ENT_QUOTES);
				JUtility::sendMail($mailfrom, $fromname, $row->email, $subject2, $message2);
			}
		}
	}

	/**
	 * Returns userid if a user exists
	 *
	 * @param   string  $email  The email to search on
	 *
	 * @return int The user id or 0 if not found
	 */
	protected function _getUserIdFromEmail($email)
	{
		// Initialize some variables
		$db = & JFactory::getDBO();

		$query = 'SELECT id FROM #__users WHERE email = ' . $db->Quote( $email );
		$db->setQuery($query, 0, 1);
		return $db->loadResult();
	}
}
