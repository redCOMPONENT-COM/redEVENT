<?php
/**
 * @package    Redevent.Site
 *
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

/**
 * Redevent Component Controller
 *
 * @package    Joomla
 * @subpackage redEVENT
 * @since      0.9
 */
class RedeventController extends RedeventControllerFront
{
	/**
	 * Constructor.
	 *
	 * @param   array $config An optional associative array of configuration settings.
	 *                        Recognized key values include 'name', 'default_task', 'model_path', and
	 *                        'view_path' (this list is not meant to be comprehensive).
	 */
	public function __construct($config = array())
	{
		parent::__construct();

		// Register extra tasks
		$this->registerTask('ical', 'vcal');
		$this->registerTask('unpublishxref', 'publishxref');
		$this->registerTask('archivexref', 'publishxref');
	}

	/**
	 * first step in unreg process by email
	 *
	 */
	function cancelreg()
	{
		$xref = JRequest::getInt('xref');
		if (!RedeventHelper::canUnregister($xref))
		{
			echo JText::_('COM_REDEVENT_UNREGISTRATION_NOT_ALLOWED');
			return;
		}

		// display the unreg form confirmation
		JRequest::setVar('view', 'registration');
		JRequest::setVar('layout', 'cancel');
		parent::display();
	}

	/**
	 * Display the select venue modal popup
	 *
	 * @since 0.9
	 */
	function selectvenue()
	{
		JRequest::setVar('view', 'editevent');
		JRequest::setVar('layout', 'selectvenue');

		parent::display();
	}

	/**
	 * offers the vcal/ical functonality
	 *
	 * @todo   Not yet working
	 *
	 * @author Lybegard Karl-Olof
	 * @since  0.9
	 */
	function vcal()
	{
		$mainframe = JFactory::getApplication();

		$task = JRequest::getWord('task');
		$id = JRequest::getInt('id');
		$user_offset = $mainframe->getCfg('offset_user');

		//get Data from model
		$model = &$this->getModel('Details', 'RedEventModel');
		$model->setId((int) $id);

		$row = $model->getDetails();

		$Start = mktime(strftime('%H', strtotime($row->times)),
			strftime('%M', strtotime($row->times)),
			strftime('%S', strtotime($row->times)),
			strftime('%m', strtotime($row->dates)),
			strftime('%d', strtotime($row->dates)),
			strftime('%Y', strtotime($row->dates)), 0);

		$End = mktime(strftime('%H', strtotime($row->endtimes)),
			strftime('%M', strtotime($row->endtimes)),
			strftime('%S', strtotime($row->endtimes)),
			strftime('%m', strtotime($row->enddates)),
			strftime('%d', strtotime($row->enddates)),
			strftime('%Y', strtotime($row->enddates)), 0);

		require_once(JPATH_COMPONENT_SITE . DS . 'classes' . DS . 'vcal.class.php');

		$v = new vCal();

		$v->setTimeZone($user_offset);
		$v->setSummary($row->venue . '-' . $row->name . '-' . RedeventHelper::getSessionFullTitle($row));
		$v->setDescription($row->datdescription);
		$v->setStartDate($Start);
		$v->setEndDate($End);
		$v->setLocation($row->street . ', ' . $row->plz . ', ' . $row->city . ', ' . $row->country);
		$v->setFilename((int) $row->did);

		if ($task == 'vcal')
		{
			$v->generateHTMLvCal();
		}
		else
		{
			$v->generateHTMLiCal();
		}

	}

	/**
	 * Initialise the mailer object to start sending mails
	 */
	private function Mailer()
	{
		$mainframe = JFactory::getApplication();
		jimport('joomla.mail.helper');
		/* Start the mailer object */
		$this->mailer = JFactory::getMailer();
		$this->mailer->isHTML(true);
		$this->mailer->From = $mainframe->getCfg('mailfrom');
		$this->mailer->FromName = $mainframe->getCfg('sitename');
		$this->mailer->AddReplyTo(array($mainframe->getCfg('mailfrom'), $mainframe->getCfg('sitename')));
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
		$acl = RedeventUserAcl::getInstance();
		$xref = $this->input->getInt('xref');

		if (!$acl->canEditXref($xref))
		{
			$msg = JText::_('COM_REDEVENT_MYEVENTS_DELETE_XREF_NOTE_ALLOWED');
			$this->setRedirect(JRoute::_(RedeventHelperRoute::getMyEventsRoute(), false), $msg, 'error');
			$this->redirect();
		}

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

	function insertevent()
	{
		JRequest::setVar('view', 'simplelist');
		JRequest::setVar('layout', 'editors-xtd');
		JRequest::setVar('filter_state', 'P');

		parent::display();
	}

	/**
	 * send reminder emails
	 */
	function reminder()
	{
		jimport('joomla.filesystem.file');
		$app = JFactory::getApplication();
		$params = $app->getParams('com_redevent');

		$file = JPATH_COMPONENT_SITE . DS . 'reminder.txt';
		if (JFile::exists($file))
		{
			$date = (int) JFile::read($file);
		}
		else
		{
			$date = 0;
		}

		// only run this once a day
		echo sprintf("last update on %s<br/>", strftime('%Y-%m-%d %H:%M', $date));
		if (time() - $date < 3600 * 23.9 && !JRequest::getVar('force', 0))
		{
			echo "reminder sent less the 24 hours ago<br/>";
			return;
		}

		$model = $this->getModel('attendees');

		$events = $model->getReminderEvents($params->get('reminder_days', 14));

		if ($events && count($events))
		{
			$mailer = JFactory::getMailer();
			$MailFrom = $app->getCfg('mailfrom');
			$FromName = $app->getCfg('fromname');
			$mailer->setSender(array($MailFrom, $FromName));
			$mailer->IsHTML(true);

			$subject = $params->get('reminder_subject');
			$body = $params->get('reminder_body');

			foreach ($events as $event)
			{
				echo "sending reminder for event: " . RedeventHelper::getSessionFullTitle($event) . "<br>";

				$tags = new RedeventTags();
				$tags->setXref($event->id);

				// get attendees
				$attendees = $model->getAttendeesEmails($event->id, $params->get('reminder_include_waiting', 1));
				if (!$attendees)
				{
					continue;
				}
				foreach ($attendees as $sid => $a)
				{
					$msubject = $tags->ReplaceTags($subject, array('sids' => array($sid)));
					$mbody = '<html><body>' . $tags->ReplaceTags($body) . '</body></html>';

					// convert urls
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

		// update file
		JFile::write($file, time());
	}

	/**
	 * for attachement downloads
	 *
	 * @return void
	 */
	public function getfile()
	{
		$app = JFactory::getApplication();
		$id = $app->input->getInt('file', 0);
		$user = JFactory::getUser();
		$helper = new RedeventHelperAttachment;
		$path = $helper->getAttachmentPath($id, max($user->getAuthorisedViewLevels()));

		// The header is fine tuned to work with grump ie8... if you modify a property, make sure it's still ok !
		header('Content-Description: File Transfer');

		// Mime
		$mime = RedeventHelper::getMimeType($path);
		$doc = JFactory::getDocument();
		$doc->setMimeEncoding($mime);

		header('Content-Disposition: attachment; filename="' . basename($path) . '"');
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: no-store, no-cache');
		header('Pragma: no-cache');

		if ($fd = fopen($path, "r"))
		{
			$fsize = filesize($path);
			header("Content-length: $fsize");

			while (!feof($fd))
			{
				$buffer = fread($fd, 2048);
				echo $buffer;
			}
		}

		fclose($fd);
		return;
	}

	/**
	 * Delete attachment
	 *
	 * @return true on sucess
	 * @access private
	 * @since  1.1
	 */
	function ajaxattachremove()
	{
		$mainframe = JFactory::getApplication();
		$id = JRequest::getVar('id', 0, 'request', 'int');

		$helper = new RedeventHelperAttachment;
		$res = $helper->remove($id);

		if (!$res)
		{
			echo 0;
			$mainframe->close();
		}

		$cache = JFactory::getCache('com_redevent');
		$cache->clean();

		echo 1;
		$mainframe->close();
	}

	function debugrel()
	{
		$image = JHTML::image('components/com_redevent/assets/images/calendar_edit.png', 'blabla');
		echo ELoutput::ImgRelAbs($image);
		exit;
	}

	function registrationexpiration()
	{
		RedeventHelper::registrationexpiration();
	}

	public function dbgajax()
	{
		echo 'test';
		JFactory::getApplication()->close();
	}
}
