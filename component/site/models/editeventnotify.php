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
class RedeventModelEditeventnotify extends RModel
{
	private $eventId;

	private $isNew;

	private $event;

	/**
	 * Send notification
	 *
	 * @param   int   $eventId  event id
	 * @param   bool  $isNew    is new
	 *
	 * @return void
	 */
	public function notify($eventId, $isNew)
	{
		$this->eventId = (int) $eventId;
		$this->isNew = (int) $isNew;
		$this->event = null;

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
		$event = $this->getEvent();

		$SiteName = $app->getCfg('sitename');
		$MailFrom = $app->getCfg('mailfrom');
		$FromName = $app->getCfg('fromname');

		$link = JRoute::_(JURI::base() . RedeventHelperRoute::getDetailsRoute($event->id));

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

				$state = $event->published ? JText::sprintf('COM_REDEVENT_MAIL_EVENT_PUBLISHED', $link) : JText::_('COM_REDEVENT_MAIL_EVENT_UNPUBLISHED');

				if (!$this->isNew)
				{
					$modified_ip = getenv('REMOTE_ADDR');
					$edited = JHTML::Date($event->modified, JText::_('COM_REDEVENT_JDATE_FORMAT_DATETIME'));
					$mailbody = JText::sprintf('COM_REDEVENT_FRONTEND_EDITED_EVENT_NOTIFICATION_BODY_S',
						$event->title,
						$user->name, $user->username, $user->email, $modified_ip, $edited,
						$event->datdescription, $state
					);
					$mail->setSubject(JText::sprintf('COM_REDEVENT_FRONTEND_EDITED_EVENT_NOTIFICATION_SUBJECT_S', $SiteName));
				}
				else
				{
					$created = JHTML::Date($event->created, JText::_('COM_REDEVENT_JDATE_FORMAT_DATETIME'));
					$mailbody = JText::sprintf('COM_REDEVENT_FRONTEND_NEW_EVENT_NOTIFICATION_BODY_S',
						$event->title,
						$user->name, $user->username, $user->email, $event->author_ip, $created,
						$event->datdescription, $state
					);
					$mail->setSubject(JText::sprintf('COM_REDEVENT_FRONTEND_NEW_EVENT_NOTIFICATION_SUBJECT_S', $SiteName));
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
		$event = $this->getEvent();

		$SiteName = $app->getCfg('sitename');
		$MailFrom = $app->getCfg('mailfrom');
		$FromName = $app->getCfg('fromname');

		$link = JRoute::_(JURI::base() . RedeventHelperRoute::getDetailsRoute($event->id));

		if (($params->get('mailinformuser') == 1) || ($params->get('mailinformuser') == 3))
		{
			$usermail = JFactory::getMailer();

			$state 	= $event->published ?
				JText::sprintf('COM_REDEVENT_USER_MAIL_EVENT_PUBLISHED', $link) : JText::_('COM_REDEVENT_USER_MAIL_EVENT_UNPUBLISHED');

			if (!$this->isNew)
			{
				$edited = JHTML::Date($event->modified, JText::_('COM_REDEVENT_JDATE_FORMAT_DATETIME'));
				$mailbody = JText::sprintf('COM_REDEVENT_USER_MAIL_EDITED_EVENT_BODY',
					$user->name, $edited,
					$event->title, $event->datdescription, $state
				);
				$usermail->setSubject($SiteName . JText::_('COM_REDEVENT_USER_MAIL_EDITED_EVENT_SUBJECT'));
			}
			else
			{
				$created = JHTML::Date($event->created, JText::_('COM_REDEVENT_JDATE_FORMAT_DATETIME'));
				$mailbody = JText::sprintf('COM_REDEVENT_USER_MAIL_NEW_EVENT_BODY',
					$user->name, $user->username, $created,
					$event->title, $event->datdescription, $state
				);
				$usermail->setSubject($SiteName . JText::_('COM_REDEVENT_USER_MAIL_NEW_EVENT_SUBJECT'));
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
	private function getEvent()
	{
		if (!$this->event)
		{
			$query = $this->_db->getQuery(true);

			$query->select('e.published, e.modified, e.created, e.author_ip, e.title, e.datdescription')
				->from('#__redevent_events AS e')
				->where('e.id = ' . $this->eventId);

			$this->_db->setQuery($query, 0, 1);
			$this->event = $this->_db->loadObject();
		}

		return $this->event;
	}
}
