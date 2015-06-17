<?php
/**
 * @package    Redevent.Site
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Redevent Component Signup Model
 *
 * @TODO: group with registration model
 *
 * @package  Redevent.Site
 * @since    0.9
 */
class RedeventModelSignup extends RModel
{
	/**
	 * registered in array
	 *
	 * @var array
	 */
	protected $registers = null;

	/**
	 * @var JMail
	 */
	protected $mailer;

	protected $id = null;

	protected $xref = null;

	protected $details;

	/**
	 * Constructor
	 *
	 * @since 0.9
	 */
	public function __construct()
	{
		parent::__construct();

		$input = JFactory::getApplication()->input;

		$id = $input->getInt('id');
		$this->setId((int) $id);
		$xref = $input->getInt('xref');
		$this->setXref((int) $xref);
	}

	/**
	 * Method to set the details event id
	 *
	 * @param   int  $id  details ID number
	 *
	 * @return void
	 */
	public function setId($id)
	{
		// Set new details ID and wipe data
		$this->id = $id;
	}

	/**
	 * Method to set the session id
	 *
	 * @param   int  $xref  session ID number
	 *
	 * @return void
	 */
	public function setXref($xref)
	{
		// Set new details ID and wipe data
		$this->xref = $xref;
	}

	/**
	 * Method to get event data for the Detailsview
	 *
	 * @return array
	 */
	public function getDetails()
	{
		if (!$this->details)
		{
			$model = RModel::getFrontInstance('Details', array('ignore_request' => true));
			$model->setXref($this->xref);

			$this->details = $model->getDetails();
		}

		return $this->details;
	}

	/**
	 * Initialise the mailer object to start sending mails
	 *
	 * @return void
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
	 * Get the details of a venue
	 *
	 * @return object
	 */
	public function getVenue()
	{
		$db = JFactory::getDBO();
		$q = "SELECT *
			FROM #__redevent_venues v
			LEFT JOIN #__redevent_event_venue_xref x
			ON v.id = x.venueid
			WHERE x.id = " . $this->xref;
		$db->setQuery($q);

		return $db->loadObject();
	}

	/**
	 * Send the signup email
	 *
	 * @param   array  $tags             tags
	 * @param   bool   $send_attachment  send attachment
	 *
	 * @return bool
	 */
	public function getSendSignupEmail($tags, $send_attachment)
	{
		/* Initialise the mailer */
		$this->Mailer();

		/* Check if the attachment needs to be send */
		if ($send_attachment)
		{
			$pdf = file_get_contents(
				JURI::root() . 'index.php?option=com_redevent&view=signup&task=signup.createpdfemail&format=pdf&xref='
				. JFactory::getApplication()->input->getInt('xref') . '&id=' . JFactory::getApplication()->input->getInt('id')
			);
			$pdffile = JPATH_CACHE . '/signup.pdf';
			file_put_contents($pdffile, $pdf);
			$this->mailer->AddAttachment($pdffile);
		}

		/* Add the recipient */
		$this->mailer->AddAddress(JFactory::getApplication()->input->get('subemailaddress'), JFactory::getApplication()->input->get('subemailname'));

		/* Add the body to the mail */
		/* Read the template */
		$db = JFactory::getDBO();
		$q = "SELECT submission_type_email_body, submission_type_email_subject FROM #__redevent_events WHERE id = " . $this->id;
		$db->setQuery($q);
		$email_settings = $db->loadObject();
		$message = $tags->ReplaceTags($email_settings->submission_type_email_body);

		// Convert urls
		$message = RedeventHelperOutput::ImgRelAbs($message);

		$this->mailer->setBody($message);

		/* Set the subject */
		$this->mailer->setSubject($tags->ReplaceTags($email_settings->submission_type_email_subject));

		/* Sent out the mail */
		if (!$this->mailer->Send())
		{
			RedeventError::raiseWarning(0, JText::_('COM_REDEVENT_NO_MAIL_SEND') . ' ' . $this->mailer->error);

			return false;
		}

		/* Clear the mail details */
		$this->mailer->ClearAddresses();

		/* Remove the temporary file */
		if ($send_attachment)
		{
			unlink($pdffile);
		}

		return true;
	}

	/**
	 * Send the signup email
	 *
	 * @param   array  $tags  tags
	 *
	 * @return bool
	 */
	public function getSendFormalOfferEmail($tags)
	{
		/* Initialise the mailer */
		$this->Mailer();

		/* Load the details for this course */
		$db = JFactory::getDBO();
		$q = "SELECT *
			FROM #__redevent_event_venue_xref x
			LEFT JOIN #__redevent_events e
			ON e.id = x.eventid
			LEFT JOIN #__redevent_venues v
			ON v.id = x.venueid
			WHERE x.id = " . JFactory::getApplication()->input->getInt('xref');
		$db->setQuery($q);
		$details = $db->loadObject();

		/* Add the recipient */
		$this->mailer->AddAddress(JFactory::getApplication()->input->get('subemailaddress'), JFactory::getApplication()->input->get('subemailname'));

		/* Set the subject */
		$this->mailer->setSubject($tags->ReplaceTags($details->submission_type_formal_offer_subject));

		/* Add the body to the mail */
		/* Read the template */
		$message = $tags->ReplaceTags($details->submission_type_formal_offer_body);

		// Convert urls
		$message = RedeventHelperOutput::ImgRelAbs($message);
		$this->mailer->setBody($message);

		/* Sent out the mail */
		if (!$this->mailer->Send())
		{
			RedeventError::raiseWarning(0, JText::_('COM_REDEVENT_NO_MAIL_SEND') . ' ' . $this->mailer->error);

			return false;
		}

		/* Clear the mail details */
		$this->mailer->ClearAddresses();

		return true;
	}

	/**
	 * Is session full ?
	 *
	 * @return bool
	 */
	public function getIsFull()
	{
		$details = $this->getDetails();

		if (!$details->maxattendees)
		{
			// No max number, the event is never full
			return false;
		}

		$max = $details->maxwaitinglist + $details->maxattendees;

		$query = ' SELECT COUNT(*) as total '
			. ' FROM #__redevent_event_venue_xref AS x'
			. ' INNER JOIN #__redevent_register AS r on r.xref = x.id '
			. ' INNER JOIN #__rwf_submitters AS s ON s.id = r.id'
			. ' WHERE x.id = ' . $this->_db->Quote($this->xref)
			. '   AND r.confirmed = 1'
			. '   AND r.cancelled = 0';
		$this->_db->setQuery($query);
		$res = $this->_db->loadResult();

		if ($res >= $max)
		{
			return true;
		}
	}

	/**
	 * returns the registration status as an object (canregister, status)
	 *
	 * @return object (canregister, status)
	 */
	public function getRegistrationStatus()
	{
		return RedeventHelper::canRegister($this->xref);
	}

	/**
	 * Get registration
	 *
	 * @param   int  $submitter_id  submitter id
	 *
	 * @return bool|mixed
	 */
	public function getRegistration($submitter_id)
	{
		$query = ' SELECT s.*, r.uid, e.unregistra '
			. ' FROM #__rwf_submitters AS s '
			. ' INNER JOIN #__redevent_register AS r ON r.sid = s.id '
			. ' INNER JOIN #__redevent_event_venue_xref AS x ON x.id = r.xref '
			. ' INNER JOIN #__redevent_events AS e ON x.eventid = e.id '
			. ' WHERE s.id = ' . $this->_db->Quote($submitter_id);
		$this->_db->setQuery($query);
		$registration = $this->_db->loadObject();

		if (!$registration)
		{
			$this->setError(JText::_('COM_REDEVENT_REGISTRATION_NOT_VALID'));

			return false;
		}

		$query = ' SELECT * '
			. ' FROM #__rwf_forms_' . $registration->form_id
			. ' WHERE id = ' . $registration->answer_id;
		$this->_db->setQuery($query);
		$registration->answers = $this->_db->loadObject();

		return $registration;
	}

	/**
	 * Can manage attendees
	 *
	 * @param   int  $xref_id  xref id
	 *
	 * @return bool
	 */
	public function getManageAttendees($xref_id)
	{
		return RedeventUserAcl::getInstance()->canManageAttendees($xref_id);
	}
}
