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
class RedEventModelRegistration extends RModel
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

	protected $prices = null;

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
			$this->setXref(JRequest::getInt('xref', 0));
		}
	}

	/**
	 * Set session id
	 *
	 * @param   int  $xref_id  session id
	 *
	 * @return void
	 */
	public function setXref($xref_id)
	{
		$this->xref = (int) $xref_id;
	}

	/**
	 * Set submit key
	 *
	 * @param   string  $submit_key  submit key
	 *
	 * @return void
	 */
	public function setSubmitKey($submit_key)
	{
		if ($submit_key && $this->submit_key != $submit_key)
		{
			$this->submit_key = $submit_key;
			$this->rf_answers = null;
			$this->rf_fields  = null;
		}
	}

	/**
	 * create a new attendee
	 *
	 * @param   object  $user                  performing the registration
	 * @param   int     $sid                   associated redform submitter id
	 * @param   string  $submit_key            associated redform submit key
	 * @param   int     $sessionpricegroup_id  pricegroup id for registration
	 *
	 * @return boolean|object attendee row or false if failed
	 */
	public function register($user, $sid, $submit_key, $sessionpricegroup_id)
	{
		$config  = redEventHelper::config();
		$session = $this->getSessionDetails();

		if (!$sid)
		{
			$this->setError(JText::_('COM_REDEVENT_REGISTRATION_UPDATE_XREF_REQUIRED'));

			return false;
		}

		$obj = $this->getTable('Attendee', 'RedeventTable');
		$obj->load(array('sid' => $sid));
		$obj->sid        = $sid;
		$obj->xref       = $this->xref;
		$obj->sessionpricegroup_id = $sessionpricegroup_id;
		$obj->submit_key = $submit_key;
		$obj->uid        = $user ? $user->get('id') : 0;
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

		if ($session->activate == 0 // No activation
			&& !$this->confirmOnPayment($obj))
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
	 * Check if we should only confirm on payment
	 *
	 * @param   object  $registration  registration data
	 *
	 * @return bool
	 */
	protected function confirmOnPayment($registration)
	{
		if (!$registration->sessionpricegroup_id)
		{
			// Session is free
			return false;
		}

		$config = redEventHelper::config();

		return $config->get('payBeforeConfirm', 0);
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
	 * @return bool|mixed|object
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
				->select('a.id AS did, x.id AS xref, a.title as event_name, a.datdescription, a.meta_keywords, a.meta_description, a.datimage')
				->select('a.registra, a.unregistra, a.activate, a.notify, a.redform_id as form_id')
				->select('a.enable_activation_confirmation, a.notify_confirm_body, a.notify_confirm_subject, a.notify_subject, a.notify_body')
				->select('a.notify_off_list_subject, a.notify_off_list_body, a.notify_on_list_subject, a.notify_on_list_body')
				->select('x.*, x.title as session_name, a.created_by, a.redform_id, x.maxwaitinglist, x.maxattendees, a.juser, a.show_names, a.showfields')
				->select('a.submission_type_email, a.submission_type_external, a.submission_type_phone')
				->select('v.venue')
				->select('u.name AS creator_name, u.email AS creator_email')
				->select('a.confirmation_message, a.review_message')
				->select('IF (x.course_credit = 0, "", x.course_credit) AS course_credit')
				->select('a.course_code, a.submission_types, c.name AS catname, c.published, c.access')
				->select('CASE WHEN CHAR_LENGTH(x.title) THEN CONCAT_WS(\' - \', a.title, x.title) ELSE a.title END as full_title')
				->select('CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug')
				->select('CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as categoryslug')
				->from('#__redevent_events AS a')
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
			->select('SELECT c.id, c.name AS catname, c.access')
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
		/* Load database connection */
		$db = JFactory::getDBO();

		/* Get registration settings */
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
	 * @return bool
	 */
	public function notifyManagers($submit_key, $unreg = false, $reg_id = 0)
	{
		if ($reg_id)
		{
			$registrations = array($reg_id);
		}
		else
		{
			/* Get registration settings */
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
	 * @return bool|mixed
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
	 * @return object session price group
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
	 * @return array
	 */
	public function getPricegroups()
	{
		if (!$this->prices)
		{
			$event = $this->getSessionDetails();
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('sp.*, p.name, p.alias, p.tooltip, f.currency AS form_currency');
			$query->select('CASE WHEN CHAR_LENGTH(p.alias) THEN CONCAT_WS(\':\', p.id, p.alias) ELSE p.id END as slug');
			$query->select('CASE WHEN CHAR_LENGTH(sp.currency) THEN sp.currency ELSE f.currency END as currency');
			$query->from('#__redevent_sessions_pricegroups AS sp');
			$query->join('INNER', '#__redevent_pricegroups AS p on p.id = sp.pricegroup_id');
			$query->join('INNER', '#__redevent_event_venue_xref AS x on x.id = sp.xref');
			$query->join('INNER', '#__redevent_events AS e on e.id = x.eventid');
			$query->join('LEFT', '#__rwf_forms AS f on e.redform_id = f.id');
			$query->where('sp.xref = ' . $db->Quote($event->xref));
			$query->order('p.ordering ASC');

			$db->setQuery($query);
			$this->prices = $db->loadObjectList();
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
			JError::raiseError(403, JText::_('COM_REDEVENT_ALERTNOTAUTH'));

			return;
		}

		// First, check if the user is allowed to unregister from this
		// He must be the one that submitted the form, plus the unregistration must be allowed
		$query = $this->_db->getQuery(true)
			->select('s.*, r.uid, r.xref, e.unregistra, x.dates, x.times, x.registrationend')
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
			$this->setError(JText::_('COM_REDEVENT_UNREGISTRATION_NOT_ALLOWED'));

			return false;
		}

		if (($submitterinfo->uid <> $userid || $submitterinfo->unregistra == 0) && !$manager)
		{
			$this->setError(JText::_('COM_REDEVENT_UNREGISTRATION_NOT_ALLOWED'));

			return false;
		}

		// Now that we made sure, we can set as cancelled
		$query = $this->_db->getQuery(true)
			->update('#__redevent_register')
			->set('cancelled = 1')
			->where('id = ' . $this->_db->Quote($register_id));

		$this->_db->setQuery($query);

		if (!$this->_db->execute())
		{
			$this->setError(JText::_('COM_REDEVENT_ERROR_CANNOT_DELETE_REGISTRATION'));

			return false;
		}

		return true;
	}
}
