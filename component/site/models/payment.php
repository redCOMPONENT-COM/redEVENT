<?php
/**
 * @package    Redevent.Site
 * @copyright  Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

/**
 * redEvent Component payment Model
 *
 * @package  RedEVENT
 * @since    2.0
 */
class RedeventModelPayment extends JModelLegacy
{
	/**
	 * Caching for session details
	 *
	 * @var object
	 */
	protected  $event = null;

	/**
	 * Caching for submit key
	 *
	 * @var string
	 */
	protected $submit_key = null;

	/**
	 * Constructor
	 *
	 * @since 2.0
	 */
	public function __construct()
	{
		parent::__construct();

		$submit_key = JFactory::getApplication()->input->get('submit_key');
		$this->setSubmitKey($submit_key);
	}

	/**
	 * Set submit key
	 *
	 * @param   string  $key  sumbit key
	 *
	 * @return true
	 */
	public function setSubmitKey($key)
	{
		$this->submit_key = $key;
	}

	/**
	 * get event details associated to submit_key
	 *
	 * @return object
	 */
	public function getEvent()
	{
		if (empty($this->event))
		{
			if (empty($this->submit_key))
			{
				JError::raiseError(0, JText::_('COM_REDEVENT_Missing_key'));

				return false;
			}

			// Find session associated to key
			$db = $this->_db;
			$query = $db->getQuery(true);

			$query->select('xref');
			$query->from('#__redevent_register');
			$query->where('submit_key = ' . $db->q($this->submit_key));

			$db->setQuery($query);
			$xref = $db->loadResult();

			$helper = RModel::getFrontInstance('Eventhelper');
			$helper->setXref($xref);

			$this->event = $helper->getData();
		}

		return $this->event;
	}

	/**
	 * Check that the registration was indeed paid, and confirm the attendee if not yet done
	 *
	 * @return true on success
	 */
	public function checkAndConfirm()
	{
		$rfcore = RdfCore::getInstance();

		if ($rfcore->isPaidSubmitkey($this->submit_key))
		{
			$this->confirmAttendees();
		}
	}

	/**
	 * Confirm attendees for this registration
	 *
	 * @return bool
	 */
	protected function confirmAttendees()
	{
		$attendeeIds = $this->getAttendeeIds();

		foreach ($attendeeIds as $attendeeId)
		{
			$attendee = new RedeventAttendee($attendeeId);
			$attendee->confirm();
		}

		return true;
	}

	/**
	 * Get id of attendees associated to this payment
	 *
	 * @return mixed
	 */
	protected function getAttendeeIds()
	{
		$db = $this->_db;
		$query = $db->getQuery(true);

		$query->select('r.id');
		$query->from('#__redevent_register AS r');
		$query->where('r.submit_key = ' . $db->quote($this->submit_key));

		$db->setQuery($query);
		$res = $db->loadColumn();

		return $res;
	}
}
