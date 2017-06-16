<?php
/**
 * @package     Redcomponent
 * @subpackage  ibc
 * @copyright   Copyright (C) 2013 redCOMPONENT.com
 * @license     GNU General Public License version 2 or later
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Import library dependencies
jimport('joomla.plugin.plugin');

/**
 * Create affiliate conversions
 *
 * @since  2.5
 */
class PlgRedeventIbcaffiliate extends JPlugin
{
	/**
	 * constructor
	 *
	 * @param   object  $subject  subject
	 * @param   array   $params   params
	 */
	public function __construct($subject, $params)
	{
		parent::__construct($subject, $params);
		$this->loadLanguage();

		$lang = JFactory::getLanguage();
		$lang->load('com_affiliatetracker', JPATH_SITE);
	}

	/**
	 * Create lead on new attendee
	 *
	 * @param   int  $attendeeId  attendee id
	 *
	 * @return void
	 */
	public function onAttendeeCreated($attendeeId)
	{
		$attendee = $this->getAttendeeDetails($attendeeId);

		if ($attendee->track_affiliate)
		{
			$this->create_conversion($attendee);
		}
	}

	/**
	 * Create conversion
	 *
	 * @param   object  $attendee  attendee
	 *
	 * @return void
	 */
	private function create_conversion($attendee)
	{
		$db = JFactory::getDBO();

		// We check if this particular order had already been tracked
		$query = $db->getQuery(true)
			->select('id')
			->from('#__affiliate_tracker_conversions')
			->where('component = ' . $db->quote('com_redevent'))
			->where('type = 1')
			->where('reference_id = ' . $attendee->id);

		$db->setQuery($query);
		$exists = $db->loadResult();

		// If it didn't exist, we attemp to create the conversion
		if (!$exists)
		{
			$user_id = $attendee->uid;

			$conversion_data = array(
				"name" => "redEVENT registration",
				"component" => "com_redevent",
				"extended_name" => "registration " . $this->getUniqueId($attendee),
				"type" => 1,
				"value" => (float) $attendee->price ,
				"reference_id" => $attendee->id,
				"approved" => $this->params->get('activation', 0)
			);

			require_once JPATH_SITE . '/components/com_affiliatetracker/helpers/helpers.php';

			AffiliateHelper::create_conversion($conversion_data, $user_id);
		}
	}

	/**
	 * get unique id
	 *
	 * @param   object  $attendee  attendee
	 *
	 * @return string
	 */
	private function getUniqueId($attendee)
	{
		return ($attendee->course_code ?: 'E' . $attendee->eventid) . '-' . $attendee->xref . '-' . $attendee->id;
	}

	/**
	 * Get attendees details
	 *
	 * @param   int  $attendeeId  attendee id
	 *
	 * @return boolean|mixed
	 */
	private function getAttendeeDetails($attendeeId)
	{
		$trackingStateField = 'x.custom' . (int) $this->params->get('enable_field_id', 0) . ' AS track_affiliate';

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('r.*, s.price');
		$query->select($trackingStateField);
		$query->select('e.title, e.course_code');
		$query->select('f.formname');

		$query->from('#__redevent_register AS r');
		$query->join('INNER', '#__redevent_event_venue_xref AS x ON x.id = r.xref');
		$query->join('INNER', '#__redevent_events AS e ON e.id = x.eventid');
		$query->join('INNER', '#__rwf_forms AS f ON f.id = e.redform_id');
		$query->join('INNER', '#__rwf_submitters AS s ON r.sid = s.id');
		$query->where('r.id = ' . $attendeeId);

		$db->setQuery($query);
		$res = $db->loadObject();

		return $res;
	}
}
