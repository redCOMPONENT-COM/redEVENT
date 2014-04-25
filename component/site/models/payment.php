<?php
/**
 * @package    RedEVENT
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
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

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

/**
 * redEvent Component payment Model
 *
 * @package  RedEVENT
 * @since    2.0
 */
class RedeventModelPayment extends JModel
{
	/**
	 * Caching for session details
	 *
	 * @var object
	 */
	protected  $_event = null;

	/**
	 * Caching for submit key
	 *
	 * @var string
	 */
	protected $_submit_key = null;

	/**
	 * Constructor
	 *
	 * @since 2.0
	 */
	public function __construct()
	{
		parent::__construct();

		$submit_key = JRequest::getVar('submit_key');
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
		$this->_submit_key = $key;
	}

	/**
	 * get event details associated to submit_key
	 *
	 * @return object
	 */
	public function getEvent()
	{
		if (empty($this->_event))
		{
			if (empty($this->_submit_key))
			{
				JError::raiseError(0, JText::_('COM_REDEVENT_Missing_key'));

				return false;
			}

			// Find session associated to key
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('xref');
			$query->from('#__redevent_register');
			$query->where('submit_key = ' . $db->q($this->_submit_key));

			$db->setQuery($query);
			$xref = $db->loadResult();

			$helper = JModel::getInstance('Eventhelper', 'RedeventModel');
			$helper->setXref($xref);

			$this->_event = $helper->getData();
		}

		return $this->_event;
	}

	/**
	 * Check that the registration was indeed paid, and confirm the attendee if not yet done
	 *
	 * @return true on success
	 */
	public function checkAndConfirm()
	{
		$rfcore = RdfCore::getInstance();

		if ($rfcore->isPaidSubmitkey($this->_submit_key))
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
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('r.id');
		$query->from('#__redevent_register AS r');
		$query->where('r.submit_key = ' . $db->quote($this->_submit_key));

		$db->setQuery($query);
		$res = $db->loadColumn();

		return $res;
	}
}
