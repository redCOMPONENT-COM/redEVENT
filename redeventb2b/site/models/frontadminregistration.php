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

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Redevents Component events list Model
 *
 * @package  Redevent
 * @since    2.5
 */
class Redeventb2bModelFrontadminregistration extends JModelLegacy
{
	private $user_id;

	private $xref;

	private $organizationId;

	private $registrationmodel;

	private $registration;

	private $pricegroup;

	private $session;

	/**
	 * Set user id
	 *
	 * @param   int  $userId  user id
	 *
	 * @return object
	 */
	public function setUserId($userId)
	{
		$this->user_id = $userId;

		return $this;
	}

	/**
	 * Set xref
	 *
	 * @param   int  $xref  xref
	 *
	 * @return object
	 */
	public function setXref($xref)
	{
		$this->xref = $xref;

		return $this;
	}

	/**
	 * Set organizationId
	 *
	 * @param   int  $organizationId  organization id
	 *
	 * @return object
	 */
	public function setOrganizationId($organizationId)
	{
		$this->organizationId = $organizationId;

		return $this;
	}

	/**
	 * book an user in b2b
	 *
	 * @param   int  $user_id  user id
	 * @param   int  $xref     session id
	 * @param   int  $orgId    org id
	 *
	 * @return  object  attendee
	 */
	public function book($user_id, $xref, $orgId)
	{
		$this->setUserId($user_id);
		$this->setXref($xref);
		$this->setOrganizationId($orgId);

		try
		{
			// Check if user is already registered
			if ($this->isRegistered())
			{
				throw new Exception(JText::_('COM_REDEVENT_ALREADY_REGISTERED'));
			}

			// Do the registration
			$redformResult = $this->redformRegistration();
			$registration = $this->register($redformResult);

			// Force confirm
			$this->getRegistrationModel()->confirm($registration->id);

			// Send notifications
			$this->notify();

			return $registration;
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}
	}

	/**
	 * Get registration model
	 *
	 * @return mixed
	 */
	private function getRegistrationModel()
	{
		if (!$this->registrationmodel)
		{
			$this->registrationmodel = RModel::getFrontInstance('Registration', array('ignore_request' => true), 'com_redevent');
			$this->registrationmodel->setXref($this->xref);
		}

		return $this->registrationmodel;
	}


	/**
	 * Check if current user is already registered
	 *
	 * @return mixed
	 */
	private function isRegistered()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('id')
			->from('#__redevent_register')
			->where('uid = ' . $this->user_id)
			->where('cancelled = 0')
			->where('xref = ' . $this->xref);
		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * redFORM submission
	 *
	 * @return object
	 *
	 * @throws Exception
	 */
	private function redformRegistration()
	{
		$pricegroup = $this->getPricegroup();

		$field = new RedeventRfieldSessionprice;
		$field->setOptions(array($pricegroup));
		$field->setValue($pricegroup->id);
		$field->setFormIndex(1);

		$options = array('extrafields' => array(array($field)), 'currency' => $pricegroup->currency);

		$redformId = $this->getRedformId();

		$redform = RdfCore::getInstance($redformId);
		$result = $redform->quickSubmit($this->user_id, 'redevent', $options);

		if (!$result)
		{
			throw new Exception(JText::_('COM_REDEVENT_REGISTRATION_REDFORM_SAVE_FAILED') . ' - ' . $redform->getError());
		}

		return $result;
	}

	/**
	 * redEVENT registration
	 *
	 * @param   RdfCoreFormSubmission  $redformResult  redform submission result
	 *
	 * @return mixed
	 *
	 * @throws Exception
	 */
	private function register($redformResult)
	{
		$pricegroup = $this->getPricegroup();

		$user = JFactory::getUser($this->user_id);
		$rfpost = $redformResult->posts[0];

		if (!$reg = $this->getRegistrationModel()
			->setOrigin('b2b')
			->register($user, $rfpost['sid'], $redformResult->submit_key, $pricegroup->id, 1))
		{
			throw new Exception(JText::_('COM_REDEVENT_REGISTRATION_REGISTRATION_FAILED'));
		}

		// For tracking
		$details = $this->getSession();
		$reg->event_name   = $details->event_name;
		$reg->session_name = $details->session_name;
		$reg->venue        = $details->venue;
		$reg->categories   = $details->categories;
		$reg->price = $pricegroup->price;
		$reg->currency = $pricegroup->currency;

		$this->registration = $reg;

		return $this->registration;
	}

	private function notify()
	{
		$orgSettings = RedeventHelperOrganization::getSettings($this->organizationId);
		$registration = $this->getRegistration();

		$email = $this->prepareNotify();

		// Check the organization flow setting for 'attendee' notification
		switch ($orgSettings->get('b2b_attendee_notification_mailflow', 0))
		{
			case '0':
				// Just the attendee
				$this->addAttendee($email);
				break;

			case '1':
				// Just the organizations admins
				$this->addOrganizationAdmin($email);
				break;

			case '2':
				// Both
				$this->addAttendee($email);
				$this->addOrganizationAdmin($email);
				break;
		}

		if (!$email->send())
		{
			RedeventHelperLog::simpleLog('B2B registration: failed sending org admins email');
		}

		// Notify managers

		JPluginHelper::importPlugin( 'redevent' );
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onB2BRegistrationNotifyAdmins', array($registration->id));
	}

	/**
	 * Notify attendee
	 *
	 * @param   JMail  $email  email
	 *
	 * @return void
	 */
	private function addAttendee($email)
	{
		$registration = $this->getRegistration();
		$attendee = new RedeventAttendee($registration->id);
		$email->addRecipient($attendee->getEmail());
	}

	/**
	 * Notify org admin
	 *
	 * @param   JMail  $email  email
	 *
	 * @return void
	 */
	private function addOrganizationAdmin($email)
	{
		$user = JFactory::getUser();
		$email->addRecipient($user->get('email'));
	}

	/**
	 * Notify organization admins
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	private function prepareNotify()
	{
		$registration = $this->getRegistration();
		$attendee = new RedeventAttendee($registration->id);

		$orgSettings = RedeventHelperOrganization::getSettings($this->organizationId);

		$subject = $orgSettings->get('b2b_orgadmin_mailflow_confirmation_subject_tag')
			? '[' . $orgSettings->get('b2b_orgadmin_mailflow_confirmation_subject_tag') . ']'
			: JText::_('COM_REDEVENT_ATTENDEE_NOTIFICATION_MAILFLOW_ORGADMIN_CONFIRMATION_DEFAULT_SUBJECT');

		$body = $orgSettings->get('b2b_orgadmin_mailflow_confirmation_body_tag')
			? '[' . $orgSettings->get('b2b_orgadmin_mailflow_confirmation_body_tag') . ']'
			: JText::_('COM_REDEVENT_ATTENDEE_NOTIFICATION_MAILFLOW_ORGADMIN_CONFIRMATION_DEFAULT_BODY');
		$email = $attendee->prepareEmail($subject, $body);

		return $email;
	}

	/**
	 * get a pricegroup id, price, and currency associated to session
	 *
	 * @return object
	 */
	private function getPricegroup()
	{
		if ($this->pricegroup === null)
		{
			$model = RModel::getFrontInstance('Registration', array('ignore_request' => true), 'com_redevent');
			$model->setXref($this->xref);

			$priceGroups = $model->getPricegroups();

			if (!empty($priceGroups))
			{
				$this->pricegroup = reset($priceGroups);
			}
			else
			{
				$this->pricegroup = false;
			}
		}


		return $this->pricegroup;
	}

	/**
	 * Get registration result
	 *
	 * @return mixed
	 *
	 * @throws LogicException
	 */
	private function getRegistration()
	{
		if (!$this->registration)
		{
			throw new LogicException('Registration not done yet');
		}

		return $this->registration;
	}

	/**
	 * Return redform id
	 *
	 * @return mixed
	 */
	private function getRedformId()
	{
		$details = $this->getSession();

		return $details->redform_id;
	}

	/**
	 * Return session details
	 *
	 * @return object
	 */
	private function getSession()
	{
		if (!$this->session)
		{
			$this->session = $this->getRegistrationModel()->getSessionDetails();
		}

		return $this->session;
	}
}
