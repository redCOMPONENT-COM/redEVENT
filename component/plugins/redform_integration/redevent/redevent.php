<?php
/**
 * @package    Redevent.integration
 * @copyright  redEVENT (C) 2008-2015 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

// Import library dependencies
jimport('joomla.plugin.plugin');
jimport('redevent.bootstrap');

RLoader::registerPrefix('Redevent', JPATH_LIBRARIES . '/redevent');
RLoader::registerPrefix('Rdf', JPATH_LIBRARIES . '/redform');

/**
 * Class plgRedform_integrationRedevent
 *
 * @package  Redevent.integration
 * @since    2.5
 */
class PlgRedform_IntegrationRedevent extends JPlugin
{
	private $rfcore;

	/**
	 * Constructor
	 *
	 * @param   object  $subject  The object to observe
	 * @param   array   $config   An optional associative array of configuration settings.
	 */
	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}

	/**
	 * returns a title for the object reference in redform
	 *
	 * @param   string                     $object_key           should be 'redevent' for this plugin to do something
	 * @param   string                     $submit_key           submit ley
	 * @param   RdfPaymentInfointegration  $paymentDetailFields  object to return
	 *
	 * @return boolean true on success
	 *
	 * @throws Exception
	 */
	public function getRFSubmissionPaymentDetailFields($object_key, $submit_key, RdfPaymentInfointegration &$paymentDetailFields)
	{
		if ($object_key !== 'redevent')
		{
			return false;
		}

		$attendees = RedeventEntityAttendee::loadBySubmitKey($submit_key);

		if (!$attendees)
		{
			throw new Exception('Registration not found for specified key');
		}

		$attendee = reset($attendees);
		$session = $attendee->getSession();

		$date = $session->getFormattedStartDate();

		$uniqueId = $attendee->getRegistrationUniqueId();

		$paymentDetailFields->title = $attendee->replaceTags(JText::_('PLG_REDFORM_INTEGRATION_REDFORM_TITLE'));

		// Legacy sprintf replacement
		$paymentDetailFields->title = sprintf(
			$paymentDetailFields->title,
			$uniqueId,
			$session->getEvent()->title,
			$session->getVenue()->venue,
			$date
		);

		$paymentDetailFields->adminDesc = $attendee->replaceTags(JText::_('PLG_REDFORM_INTEGRATION_REDFORM_ADMIN_DESC'));

		// Legacy sprintf replacement
		$paymentDetailFields->adminDesc = JText::sprintf(
			$paymentDetailFields->adminDesc,
			$uniqueId,
			$this->getFullname($submit_key),
			$session->getEvent()->title,
			$session->getVenue()->venue,
			$date
		);

		$paymentDetailFields->paymentIntroText = $attendee->replaceTags(JText::_('PLG_REDFORM_INTEGRATION_REDFORM_PAYMENT_INTRO_DESC'));

		$paymentDetailFields->uniqueid = $uniqueId;

		return true;
	}

	/**
	 * Tag replacement for redFORM
	 *
	 * @param   string      $text      text to replace
	 * @param   object      $formData  form data
	 * @param   RdfAnswers  $answers   answers
	 *
	 * @return void
	 */
	public function onRedformTagReplace(&$text, $formData, $answers)
	{
		$sid = $answers->getSid();

		// Get associated session
		$table = RTable::getAdminInstance('Attendee', array(), 'com_redevent');
		$table->load(array('sid' => $sid));

		if (!$table->id)
		{
			return;
		}

		$replacer = new RedeventTags;
		$replacer->setSubmitkey($table->submit_key);
		$replacer->setXref($table->xref);

		$text = $replacer->replaceTags($text);
	}

	/**
	 * Handle redFORM after cart payment accepted callback
	 *
	 * @param   RdfEntityCart  $cart  redFORM cart object
	 *
	 * @return void
	 *
	 * @since  __deploy_version__
	 */
	public function onAfterRedformCartPaymentAccepted(RdfEntityCart $cart)
	{
		$submitKeys = array();

		foreach ($cart->getSubmitters() as $submitter)
		{
			if ($submitter->integration != 'redevent')
			{
				continue;
			}

			$attendee = RedeventEntityAttendee::loadBySubmitterId($submitter->id);

			if (!$attendee->confirm())
			{
				RedeventHelperLog::simpleLog('Redevent error confirming ' . $attendee->id);
			}

			$submitKeys[] = $submitter->submit_key;
		}

		$submitKeys = array_unique($submitKeys);

		foreach ($submitKeys as $submitKey)
		{
			// Trigger event for custom handling
			JPluginHelper::importPlugin('redevent');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('onAfterPaymentVerifiedRedevent', array($submitKey));
		}
	}

	/**
	 * Return fullname(s) associated to sumbission
	 *
	 * @param   string  $submit_key  submit_key
	 *
	 * @return string
	 */
	private function getFullname($submit_key)
	{
		$submissions = $this->getRedformCore()->getAnswers($submit_key)->getSingleSubmissions();

		$fullnames = array();

		foreach ($submissions as $answers)
		{
			if ($fullname = $answers->getFullname())
			{
				$fullnames[] = $fullname;
			}
		}

		return implode(', ', $fullnames);
	}

	/**
	 * return redformcore object
	 *
	 * @return RedformCore
	 */
	private function getRedformCore()
	{
		if (!$this->rfcore)
		{
			$this->rfcore = new RdfCore;
		}

		return $this->rfcore;
	}
}
