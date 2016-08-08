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

require_once JPATH_BASE . '/plugins/redform_integration/redevent/redevent.php';

/**
 * Class plgRedform_integrationRedevent
 *
 * @package  Redevent.integration
 * @since    2.5
 */
class PlgRedform_IntegrationRedevent_maersk extends PlgRedform_IntegrationRedevent
{
	/**
	 * Constructor
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $config    An optional associative array of configuration settings.
	 */
	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}

	/**
	 * returns a title for the object reference in redform
	 *
	 * @param   string                     $object_key            should be 'redevent' for this plugin to do something
	 * @param   string                     $submit_key            submit ley
	 * @param   RdfPaymentInfointegration  &$paymentDetailFields  object to return
	 *
	 * @return bool true on success
	 *
	 * @throws Exception
	 */
	public function getRFSubmissionPaymentDetailFields($object_key, $submit_key, &$paymentDetailFields)
	{
		if (!parent::getRFSubmissionPaymentDetailFields($object_key, $submit_key, $paymentDetailFields))
		{
			return false;
		}

		$attendees = RedeventEntityAttendee::loadBySubmitKey($submit_key);
		$attendee = reset($attendees);
		$paymentDetailFields->uniqueid = $attendee->getSession()->getEvent()->course_code . '-' . $attendee->id;

		return true;
	}
}
