<?php
/**
 * @package    Redevent.Site
 *
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevent Component Controller
 *
 * @package  Redevent.Site
 * @since    0.9
 */
class RedeventController extends RedeventControllerFront
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 * Recognized key values include 'name', 'default_task', 'model_path', and
	 * 'view_path' (this list is not meant to be comprehensive).
	 *
	 * @since   11.1
	 */
	public function __construct($config = array())
	{
		parent::__construct();

		// Register extra tasks
		$this->registerTask('unpublishxref', 'publishxref');
		$this->registerTask('archivexref', 'publishxref');
	}

	/**
	 * Publish a session
	 *
	 * @return void
	 */
	public function publishxref()
	{
		$acl = RedeventUserAcl::getInstance();
		$xref = $this->input->getInt('xref');

		if (!$acl->canPublishXref($xref))
		{
			$msg = JText::_('COM_REDEVENT_MYEVENTS_CHANGE_PUBLISHED_STATE_NOTE_ALLOWED');
			$this->setRedirect(JRoute::_(RedeventHelperRoute::getMyEventsRoute(), false), $msg, 'error');
			$this->redirect();
		}

		$model = $this->getModel('editsession');

		switch ($this->input->get('task'))
		{
			case 'publishxref':
				$newstate = 1;
				break;
			case 'unpublishxref':
				$newstate = 0;
				break;
			case 'archivexref':
				$newstate = -1;
				break;
		}

		$pks = array($xref);

		if ($model->publish($pks, $newstate))
		{
			$msg = JText::_('COM_REDEVENT_PUBLISHED_STATE_UPDATED');
			$this->setRedirect(JRoute::_(RedeventHelperRoute::getMyEventsRoute(), false), $msg);
		}
		else
		{
			$msg = JText::_('COM_REDEVENT_PUBLISHED_STATE_UPDATE_ERROR') . '<br>' . $model->getError();
			$this->setRedirect(JRoute::_(RedeventHelperRoute::getMyEventsRoute(), false), $msg, 'error');
		}
	}

	/**
	 * Delete a session
	 *
	 * @return void
	 */
	public function deletexref()
	{
		$xref = $this->input->getInt('xref');

		$model = $this->getModel('editsession');
		$pks = array($xref);

		if ($model->delete($pks))
		{
			$msg = JText::_('COM_REDEVENT_EVENT_DATE_DELETED');
			$this->setRedirect(JRoute::_(RedeventHelperRoute::getMyEventsRoute(), false), $msg);
		}
		else
		{
			$msg = JText::_('COM_REDEVENT_EVENT_DATE_DELETION_ERROR') . '<br>' . $model->getError();
			$this->setRedirect(JRoute::_(RedeventHelperRoute::getMyEventsRoute(), false), $msg, 'error');
		}
	}

	/**
	 * Used by editor-xtd plugin
	 *
	 * @return void
	 */
	public function insertevent()
	{
		$this->input->set('view', 'simplelist');
		$this->input->set('layout', 'editors-xtd');
		$this->input->set('filter_state', 'P');

		parent::display();
	}

	/**
	 * send reminder emails
	 *
	 * @return void
	 *
	 * @TODO: needs a layout
	 */
	public function reminder()
	{
		jimport('joomla.filesystem.file');
		$app = JFactory::getApplication();
		$params = $app->getParams('com_redevent');

		$file = JPATH_COMPONENT_SITE . '/reminder.txt';

		if (JFile::exists($file))
		{
			$date = (int) JFile::read($file);
		}
		else
		{
			$date = 0;
		}

		// Only run this once a day
		echo sprintf("last update on %s<br/>", strftime('%Y-%m-%d %H:%M', $date));

		if (time() - $date < 3600 * 23.9 && !$this->input->getInt('force', 0))
		{
			echo "reminder sent less the 24 hours ago<br/>";

			return;
		}

		$model = $this->getModel('attendees');

		$events = $model->getReminderEvents($params->get('reminder_days', 14));

		if ($events && count($events))
		{
			$mailer = RdfHelper::getMailer();
			$MailFrom = $app->getCfg('mailfrom');
			$FromName = $app->getCfg('fromname');
			$mailer->setSender(array($MailFrom, $FromName));
			$mailer->IsHTML(true);

			$subject = $params->get('reminder_subject');
			$body = $params->get('reminder_body');

			foreach ($events as $event)
			{
				echo "sending reminder for event: " . RedeventHelper::getSessionFullTitle($event) . "<br>";

				$tags = new RedeventTags;
				$tags->setXref($event->id);

				// Get attendees
				$attendees = $model->getAttendeesEmails($event->id, $params->get('reminder_include_waiting', 1));

				if (!$attendees)
				{
					continue;
				}

				foreach ($attendees as $sid => $a)
				{
					$msubject = $tags->replaceTags($subject, array('sids' => array($sid)));
					$mbody = '<html><body>' . $tags->replaceTags($body) . '</body></html>';

					// Convert urls
					$mbody = RedeventHelperOutput::ImgRelAbs($mbody);

					$mailer->setSubject($msubject);
					$mailer->setBody($mbody);

					$mailer->clearAllRecipients();
					$mailer->addRecipient($a);

					$sent = $mailer->Send();
				}
			}
		}
		else
		{
			echo 'No events for this reminder interval<br/>';
		}

		// Update file
		JFile::write($file, time());
	}

	/**
	 * Task handler
	 *
	 * @return void
	 */
	public function registrationexpiration()
	{
		RedeventHelper::registrationexpiration();
	}
}
