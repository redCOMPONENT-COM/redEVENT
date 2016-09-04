<?php
/**
 * @package     Redevent.Plugin
 * @subpackage  Redevent.Eventnewslettersignup
 *
 * @copyright   Copyright (C) 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

// Import library dependencies
jimport('joomla.plugin.plugin');
jimport('redevent.bootstrap');

RLoader::registerPrefix('Eventnewslettersignup', __DIR__);

/**
 * Specific plugin for acymailing newsletter signup for redEVENT based on event selected newsletters
 *
 * @since  3.0
 */
class PlgRedeventEventnewslettersignup extends JPlugin
{
	protected $autoloadLanguage = true;

	/**
	 * The plugin identifier.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $context = 'eventnewslettersignup';

	/**
	 * Constructor
	 *
	 * @param   object  $subject  The object to observe
	 * @param   array   $config   An optional associative array of configuration settings.
	 *                            Recognized key values include 'name', 'group', 'params', 'language'
	 *                            (this list is not meant to be comprehensive).
	 */
	public function __construct($subject, array $config)
	{
		parent::__construct($subject, $config);

		RedeventBootstrap::bootstrap();
	}

	/**
	 * Handle trigger onAttendeeCreated
	 *
	 * @param   int  $attendeeId  attendee Id
	 *
	 * @return void
	 */
	public function onAttendeeCreated($attendeeId)
	{
		$attendee = RedeventEntityAttendee::load($attendeeId);
		$newsletterFieldId = $this->params->get('newslettersfield');
		$newsletters = $attendee->getAnswers()->getFieldAnswer($newsletterFieldId);

		if (!$newsletters)
		{
			return;
		}

		if (!include_once JPATH_ADMINISTRATOR . '/components/com_acymailing/helpers/helper.php')
		{
			JFactory::getApplication()->enqueueMessage('You need acymailing installed for this plugin to work');

			return false;
		}

		$userClass = acymailing_get('class.subscriber');

		$newSubscription = array();

		foreach ($newsletters as $listId)
		{
			$newSubscription[$listId] = array('status' => 1);
		}

		$email = $attendee->getEmail();
		$subid = $this->getSubscriberId($email);

		$userClass->saveSubscription($subid, $newSubscription);
	}

	/**
	 * Get subscriber id
	 *
	 * @param   string  $email  email
	 *
	 * @return mixed
	 */
	private function getSubscriberId($email)
	{
		$userClass = acymailing_get('class.subscriber');

		if ($subid = $userClass->subid($email))
		{
			return $subid;
		}

		$myUser = new stdClass;
		$myUser->email = $email;

		$subscriberClass = acymailing_get('class.subscriber');

		if (!$subid = $subscriberClass->save($myUser))
		{
			throw new RuntimeException('couldn\'t create acymailing user using email: ' . $email);
		}

		return $subid;
	}
}
