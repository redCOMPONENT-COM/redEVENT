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
 * Class plgRedeventIbcglobase
 *
 * @since  2.5
 */
class plgRedeventIbcglobase extends JPlugin
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
		$query = "SELECT id FROM #__affiliate_tracker_conversions WHERE component = 'com_redevent' AND type = 1 AND reference_id = " . $attendee->id;
		$db->setQuery($query);
		$exists = $db->loadResult();

		// If it didn't exist, we attemp to create the conversion
		if (!$exists)
		{
			$user_id = $attendee->uid;

			$conversion_data = array(
				"name" => "redEVENT registration",
				"component" => "com_redevent",
				"extended_name" => "registration",
				"type" => 1,
				"value" => (float) $attendee->price ,
				"reference_id" => $attendee->id,
				"approved" => $this->params->get('activation'),
				"atid" => 0
			);

			require_once(JPATH_SITE.DS.'components'.DS.'com_affiliatetracker'.DS.'helpers'.DS.'helpers.php');

			AffiliateHelper::create_conversion($conversion_data, $user_id);
		}
	}

	/**
	 * Get attendees details
	 *
	 * @param   int  $attendeeId  attendee id
	 *
	 * @return bool|mixed
	 */
	private function getAttendeeDetails($attendeeId)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('r.*, s.price, x.track_affiliate');
		$query->select('e.title');
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
