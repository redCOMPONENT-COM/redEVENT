<?php
/**
 * @package    Redevent.Library
 * @copyright  Copyright (C) 2008 - 2020 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Holds the logic for more info recipients
 *
 * @package  Redevent.Library
 * @since    3.0
 */
class RedeventHelperMoreinforecipients
{
	const RECIPIENT_SITE_EMAIL = 0;
	const RECIPIENT_EVENT_CREATOR = 1;
	const RECIPIENT_RECIPIENT_SESSION_ADMINS = 2;
	const RECIPIENT_RECIPIENT_CUSTOM = 3;

	/**
	 * @var  int
	 */
	private $sessionId;

	/**
	 * Constructor
	 *
	 * @param   integer  $sessionId  session id
	 */
	public function __construct($sessionId)
	{
		$this->sessionId = (int) $sessionId;
	}

	/**
	 * Return array of recipients
	 *
	 * @return array
	 */
	public function getRecipients()
	{
		$app = JFactory::getApplication();
		$type = RedeventHelper::config()->get('moreinfo_recipient', 0);

		switch ($type)
		{
			case static::RECIPIENT_SITE_EMAIL:
				return array(
					array('email' => $app->getCfg('mailfrom'), 'name' => $app->getCfg('sitename'))
				);

			case static::RECIPIENT_RECIPIENT_CUSTOM:
				return array(
					array('email' => RedeventHelper::config()->get('moreinfo_recipient_custom', $app->getCfg('mailfrom')))
				);

			case static::RECIPIENT_EVENT_CREATOR:
				$session = RedeventEntitySession::getInstance($this->sessionId);
				$user = $session->getEvent()->getCreator();

				return array(array('email' => $user->get('email'), 'name' => $user->get('name')));

			case static::RECIPIENT_RECIPIENT_SESSION_ADMINS:
				$helper = new RedeventHelperSessionadmins;

				return $helper->getAdminEmails($this->sessionId);
		}
	}
}
