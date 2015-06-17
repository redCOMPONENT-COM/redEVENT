<?php
/**
 * @package    Redevent.Site
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * redEVENT Component Attendees Model
 *
 * @package  Redevent.Site
 * @since    2.0
 */
class RedeventModelAttendees extends RModel
{
	protected $xref = 0;

	/**
	 * @var RedeventModelDetails
	 */
	protected $sessionModel;

	/**
	 * Constructor
	 *
	 * @since 0.9
	 */
	public function __construct()
	{
		parent::__construct();

		$app = JFactory::getApplication();

		$xref = $app->input->getInt('xref');
		$this->setXref((int) $xref);

		$filter_order     = $app->getUserStateFromRequest('com_redevent.attendees.filter_order', 'filter_order', 'r.id', 'cmd');
		$filter_order_Dir = $app->getUserStateFromRequest('com_redevent.attendees.filter_order_Dir', 'filter_order_Dir', 'asc', 'word');

		$this->setState('filter_order', $filter_order);
		$this->setState('filter_order_Dir', $filter_order_Dir);
	}

	/**
	 * Method to set the details id
	 *
	 * @param   int  $xref  session id
	 *
	 * @return void
	 */
	public function setXref($xref)
	{
		// Set new details ID and wipe data
		$this->xref    = intval($xref);
	}

	/**
	 * Get sessions that need a reminder now
	 *
	 * @param   int  $days  days after date start
	 *
	 * @return mixed
	 */
	public function getReminderEvents($days = 14)
	{
		$query = $this->_db->getQuery(true)
			->select('x.id, e.title')
			->select('CASE WHEN CHAR_LENGTH(x.title) THEN CONCAT_WS(\' - \', e.title, x.title) ELSE e.title END as full_title')
			->from('#__redevent_events AS e')
			->join('INNER', '#__redevent_event_venue_xref AS x ON x.eventid = e.id')
			->where('DATEDIFF(x.dates, NOW()) = ' . $days);

		$this->_db->setQuery($query);
		$res = $this->_db->loadObjectList();

		return $res;
	}

	/**
	 * return array of attendees emails indexed by sid
	 *
	 * @param   int  $xref        session id
	 * @param   int  $include_wl  include waiting list
	 *
	 * @return array
	 */
	public function getAttendeesEmails($xref, $include_wl)
	{
		$query = $this->_db->getQuery(true)
			->select('r.sid')
			->from('#__redevent_register AS r')
			->where('r.xref = ' . $xref)
			->where('r.confirmed = 1')
			->where('r.cancelled = 0');

		if ($include_wl == 0)
		{
			$query->where('r.waitinglist = 0');
		}

		$this->_db->setQuery($query);
		$res = $this->_db->loadColumn();

		if (!count($res))
		{
			return false;
		}

		$rfcore = RdfCore::getInstance();
		$emails = $rfcore->getSubmissionContactEmails($res);

		return $emails;
	}

	/**
	 * Get session details
	 *
	 * @return mixed|null
	 */
	public function getSession()
	{
		return $this->getSessionModel()->getDetails();
	}

	/**
	 * Method to get the registered users
	 *
	 * @param   bool  $all_fields  get all fields
	 * @param   bool  $admin       is admin
	 *
	 * @return array|null
	 */
	public function getRegisters($all_fields = false, $admin = false)
	{
		return $this->getSessionModel()->getRegisters($all_fields, $admin);
	}

	/**
	 * returns the fields to be shown in attendees list
	 *
	 * @param   boolean  $all_fields  get all fields
	 *
	 * @return array;
	 */
	public function getFormFields($all_fields = false)
	{
		return $this->getSessionModel()->getFormFields($all_fields);
	}

	/**
	 * return true if user allowed to manage attendees
	 *
	 * @return boolean
	 */
	public function getManageAttendees()
	{
		$acl = RedeventUserAcl::getInstance();

		return $acl->canManageAttendees($this->xref);
	}

	/**
	 * return true if user allowed to manage attendees
	 *
	 * @return boolean
	 */
	public function getViewAttendees()
	{
		$acl = RedeventUserAcl::getInstance();

		return $acl->canViewAttendees($this->xref);
	}

	/**
	 * Return session model
	 *
	 * @return RedeventModelDetails
	 */
	protected function getSessionModel()
	{
		if (!$this->sessionModel)
		{
			$model = RModel::getFrontInstance('Details', array('ignore_request' => true));
			$model->setXref($this->xref);

			$this->sessionModel = $model;
		}

		return $this->sessionModel;
	}

	/**
	 * return roles for the session
	 *
	 * @return array
	 */
	public function getRoles()
	{
		return $this->getSessionModel()->getRoles();
	}
}
