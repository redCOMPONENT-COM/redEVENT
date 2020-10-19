<?php
/**
 * @package    Redevent.Library
 * @copyright  Copyright (C) 2008 - 2020 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Helper class to get session admin emails
 *
 * @package  Redevent.Library
 * @since    2.5
 */
class RedeventHelperSessionadmins
{
	/**
	 * @var int
	 */
	private $xref;

	/**
	 * @var object
	 */
	private $sessionDetails;

	/**
	 * @var array
	 */
	private $addresses;

	/**
	 * return email for the registration admins
	 *
	 * @param   int  $xref  session id
	 *
	 * @return array
	 */
	public function getAdminEmails($xref)
	{
		$this->xref = $xref;
		$this->addresses = array();

		$this->addGeneralDefaultRecipients();
		$this->addCreator();
		$this->addVenueEmail();
		$this->addGroupAdmins();

		return $this->addresses;
	}

	/**
	 * Return venue contact email for this session
	 *
	 * @param   int  $xref  session id
	 *
	 * @return mixed
	 */
	public function getVenueContactEmail($xref)
	{
		$this->xref = $xref;
		$event = $this->getSessionDetails();
		$venue = RedeventEntityVenue::load($event->venueid);

		return $venue->contactAdminEmail ?: $venue->venue_email;
	}

	/**
	 * Add default recipients from options
	 *
	 * @return void
	 */
	private function addGeneralDefaultRecipients()
	{
		$params = JComponentHelper::getParams('com_redevent');

		// Default recipients
		$default = $params->get('registration_default_recipients');

		if (!empty($default))
		{
			if (strstr($default, ';'))
			{
				$addresses = explode(";", $default);
			}
			else
			{
				$addresses = explode(",", $default);
			}

			foreach ($addresses as $a)
			{
				$a = trim($a);

				if (JMailHelper::isEmailAddress($a))
				{
					$this->addresses[] = array('email' => $a, 'name' => '');
				}
			}
		}
	}

	/**
	 * Get session details
	 *
	 * @return object
	 */
	private function getSessionDetails()
	{
		if (!$this->sessionDetails)
		{
			JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_redevent/models');
			$model = JModelLegacy::getInstance('Details', 'RedeventModel');
			$model->setXref($this->xref);
			$this->sessionDetails = $model->getDetails();
		}

		return $this->sessionDetails;
	}

	/**
	 * Add creator
	 *
	 * @return void
	 */
	private function addCreator()
	{
		$params = JComponentHelper::getParams('com_redevent');
		$event = $this->getSessionDetails();

		if ($params->get('registration_notify_creator', 1))
		{
			if (JMailHelper::isEmailAddress($event->creator_email))
			{
				$this->addresses[] = array('email' => $event->creator_email, 'name' => $event->creator_name);
			}
		}
	}

	/**
	 * Add venue admin email
	 *
	 * @return void
	 */
	private function addVenueEmail()
	{
		$event = $this->getSessionDetails();

		if (!empty($event->venue_email))
		{
			$this->addresses[] = array('email' => $event->venue_email, 'name' => $event->venue);
		}
	}

	/**
	 * Add managing group admin emails
	 *
	 * @return void
	 */
	private function addGroupAdmins()
	{
		$gprecipients = $this->getXrefRegistrationRecipients();

		if ($gprecipients)
		{
			foreach ($gprecipients AS $r)
			{
				if (JMailHelper::isEmailAddress($r->email))
				{
					$this->addresses[] = array('email' => $r->email, 'name' => $r->name);
				}
			}
		}
	}

	/**
	 * returns registration recipients from groups acl
	 *
	 * @return array
	 */
	private function getXrefRegistrationRecipients()
	{
		$event = $this->getSessionDetails();
		$usersIds = RedeventUserAcl::getInstance()->getXrefRegistrationRecipients($event->xref);

		if (!$usersIds)
		{
			return false;
		}

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('u.name, u.email');
		$query->from('#__users AS u');
		$query->where('u.id IN (' . implode(",", $usersIds) . ')');

		$db->setQuery($query);
		$xref_group_recipients = $db->loadObjectList();

		return $xref_group_recipients;
	}
}
