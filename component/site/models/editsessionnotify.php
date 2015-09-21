<?php
/**
 * @package    Redevent.Site
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * RedEvent Model Session
 *
 * @package  Redevent.admin
 * @since    0.9
 */
class RedeventModelEditsessionnotify extends RModel
{
	private $sessionId;

	private $isNew;

	private $session;

	/**
	 * Send notification
	 *
	 * @param   int   $sessionId  session id
	 * @param   bool  $isNew      is new
	 *
	 * @return void
	 */
	public function notify($sessionId, $isNew)
	{
		$this->sessionId = (int) $sessionId;
		$this->isNew = (int) $isNew;
		$this->session = null;

		$this->notifyAdmins();
		$this->notifyUser();
	}

	/**
	 * Notify admins
	 *
	 * @return void
	 */
	private function notifyAdmins()
	{
		$app = JFactory::getApplication();
		$params = RedeventHelper::config();
		$user = JFactory::getUser();
		$session = $this->getSession();

		$SiteName = $app->getCfg('sitename');
		$MailFrom = $app->getCfg('mailfrom');
		$FromName = $app->getCfg('fromname');

		$link = JRoute::_(JURI::base() . RedeventHelperRoute::getDetailsRoute($session->eventid, $session->id));

		$date = $this->formatDate($session);

		// Create the mail for the site owner
		if (($params->get('mailinform') == 1) || ($params->get('mailinform') == 3))
		{
			$recipients = explode(',', trim($params->get('mailinformrec')));

			if (!count($recipients) || !JMailHelper::isEmailAddress($recipients[0]))
			{
				$app->enqueueMessage(JText::_('COM_REDEVENT_EDIT_EVENT_NOTIFICATION_MISSING_RECIPIENT'), 'notice');
			}
			else
			{
				$mail = JFactory::getMailer();

				$state = $session->published ? JText::sprintf('COM_REDEVENT_MAIL_SESSION_PUBLISHED', $link) : JText::_('COM_REDEVENT_MAIL_SESSION_UNPUBLISHED');

				if (!$this->isNew)
				{
					$modified_ip = getenv('REMOTE_ADDR');
					$edited = JHTML::Date($session->modified, JText::_('COM_REDEVENT_JDATE_FORMAT_DATETIME'));
					$mailbody = JText::sprintf('COM_REDEVENT_FRONTEND_EDITED_SESSION_NOTIFICATION_BODY_S',
						$user->name, $user->username, $user->email, $modified_ip, $edited,
						$session->title, $date, $session->times,
						$session->venue, $session->city, $session->datdescription, $state
					);
					$mail->setSubject($SiteName . JText::_('COM_REDEVENT_FRONTEND_EDITED_SESSION_NOTIFICATION_SUBJECT_S'));
				}
				else
				{
					$created = JHTML::Date($session->created, JText::_('COM_REDEVENT_JDATE_FORMAT_DATETIME'));
					$mailbody = JText::sprintf('COM_REDEVENT_FRONTEND_NEW_SESSION_NOTIFICATION_BODY_S',
						$user->name, $user->username, $user->email, $session->author_ip, $created,
						$session->title, $date, $session->times,
						$session->venue, $session->city, $session->datdescription, $state
					);
					$mail->setSubject($SiteName . JText::_('COM_REDEVENT_FRONTEND_NEW_SESSION_NOTIFICATION_SUBJECT_S'));
				}

				$mail->addRecipient($recipients);
				$mail->setSender(array($MailFrom, $FromName));
				$mail->setBody($mailbody);

				$sent = $mail->Send();

				if (!$sent)
				{
					RedeventHelperLog::simpleLog('Error sending created/edited event notification to site owner');
				}
			}
		}
	}

	/**
	 * Notify user
	 *
	 * @return void
	 */
	private function notifyUser()
	{
		$app = JFactory::getApplication();
		$params = RedeventHelper::config();
		$user = JFactory::getUser();
		$session = $this->getSession();

		$SiteName = $app->getCfg('sitename');
		$MailFrom = $app->getCfg('mailfrom');
		$FromName = $app->getCfg('fromname');

		$link = JRoute::_(JURI::base() . RedeventHelperRoute::getDetailsRoute($session->eventid, $session->id));

		if (($params->get('mailinformuser') == 1) || ($params->get('mailinformuser') == 3))
		{
			$usermail = JFactory::getMailer();

			$state 	= $session->published ?
				JText::sprintf('COM_REDEVENT_USER_MAIL_SESSION_PUBLISHED', $link) : JText::_('COM_REDEVENT_USER_MAIL_SESSION_UNPUBLISHED');

			$date = $this->formatDate($session);

			if (!$this->isNew)
			{
				$edited = JHTML::Date($session->modified, JText::_('COM_REDEVENT_JDATE_FORMAT_DATETIME'));
				$mailbody = JText::sprintf('COM_REDEVENT_USER_MAIL_EDITED_SESSION_BODY',
					$user->name, $user->username, $edited,
					$session->title, $date, $session->times,
					$session->venue, $session->city, $session->datdescription, $state
				);
				$usermail->setSubject($SiteName . JText::_('COM_REDEVENT_USER_MAIL_EDITED_SESSION_SUBJECT'));
			}
			else
			{
				$created = JHTML::Date($session->created, JText::_('COM_REDEVENT_JDATE_FORMAT_DATETIME'));
				$mailbody = JText::sprintf('COM_REDEVENT_USER_MAIL_NEW_SESSION_BODY',
					$user->name, $user->username, $created,
					$session->title, $date, $session->times,
					$session->venue, $session->city, $session->datdescription, $state
				);
				$usermail->setSubject($SiteName . JText::_('COM_REDEVENT_USER_MAIL_NEW_SESSION_SUBJECT'));
			}

			$usermail->addRecipient($user->email);
			$usermail->setSender(array($MailFrom, $FromName));
			$usermail->setBody($mailbody);

			$sent = $usermail->Send();

			if (!$sent)
			{
				RedeventHelperLog::simpleLog('Error sending created/edited event notification to event owner');
			}
		}
	}

	/**
	 * return session info
	 *
	 * @return mixed
	 */
	private function getSession()
	{
		if (!$this->session)
		{
			$query = $this->_db->getQuery(true);

			$query->select('e.published, e.modified, e.created, e.author_ip, e.title, e.datdescription')
				->select('v.venue, v.city')
				->select('x.id, x.eventid, x.dates, x.times')
				->from('#__redevent_event_venue_xref AS x')
				->join('INNER', '#__redevent_events AS e ON e.id = x.eventid')
				->join('LEFT', '#__redevent_venues AS v ON v.id = x.venueid')
				->where('x.id = ' . $this->sessionId);

			$this->_db->setQuery($query, 0, 1);
			$this->session = $this->_db->loadObject();
		}

		return $this->session;
	}

	/**
	 * Format date
	 *
	 * @param   string  $session  data
	 *
	 * @return string
	 */
	private function formatDate($session)
	{
		if (RedeventHelperDate::isValidDate($session->dates))
		{
			$date = is_null($session->times)
				? JHTML::date($session->dates, JText::_('COM_REDEVENT_JDATE_FORMAT_DATE'))
				: JHTML::date($session->dates . ' ' . $session->times, JText::_('COM_REDEVENT_JDATE_FORMAT_DATE'));

			return $date;
		}
		else
		{
			$date = JText::_('COM_REDEVENT_OPEN_DATE');

			return $date;
		}
	}
}
