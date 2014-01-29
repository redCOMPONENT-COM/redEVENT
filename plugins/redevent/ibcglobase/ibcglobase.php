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
 * Class plgRedeventRedeventsync
 *
 * @package     Redcomponent
 * @subpackage  ibc
 * @since    2.5
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
	}

	/**
	 * handles attendee created
	 *
	 * @param   int  $attendee_id  attendee id
	 *
	 * @return bool
	 */
	public function onAttendeeCreated($attendee_id)
	{
		return $this->saveProfile($attendee_id);
	}

	/**
	 * handles attendee modified
	 *
	 * @param   int  $attendee_id  attendee id
	 *
	 * @return bool
	 */
	public function onAttendeeModified($attendee_id)
	{
		return $this->saveProfile($attendee_id);
	}

	/**
	 * handles attendee cancelled
	 *
	 * @param   int  $attendee_id  attendee id
	 *
	 * @return bool
	 */
	public function onAttendeeCancelled($attendee_id)
	{
		return true;
	}

	/**
	 * handles attendee deleted
	 *
	 * @param   int  $attendee_id  attendee id
	 *
	 * @return bool
	 */
	public function onAttendeeDeleted($attendee_id)
	{
		return true;
	}

	/**
	 * Save profile to globase
	 *
	 * @param   int $attendeeId attendee id
	 *
	 * @return true on success
	 */
	protected function saveProfile($attendeeId)
	{
		require_once JPATH_SITE . '/components/com_redform/redform.core.php';

		$details = $this->getAttendeeDetails($attendeeId);

		$rfcore = new RedFormCore;
		$answers = $rfcore->getAnswers(array($details->sid));

		if ($answers)
		{
			$answers = reset($answers);
		}

		echo '<pre>'; echo print_r($answers, true); echo '</pre>'; exit;
	}

	/**
	 * Get attendees details
	 *
	 * @param   int  $attendeeId  attendee id
	 *
	 * @return bool|mixed
	 */
	protected function getAttendeeDetails($attendeeId)
	{
		$app = JFactory::getApplication();

		$listFieldId       = (int) $this->params->get('listFieldId');

		if (!$listFieldId)
		{
			$app->enqueueMessage('ibcglobase plugin: missing listFieldId field id', 'error');

			return false;
		}

		$uddannelseFieldId = (int) $this->params->get('uddannelseFieldId');

		if (!$uddannelseFieldId)
		{
			$app->enqueueMessage('ibcglobase plugin: missing uddannelse field id', 'error');

			return false;
		}

		$nyhedsbrevFieldId = (int) $this->params->get('nyhedsbrevFieldId', 'error');

		if (!$nyhedsbrevFieldId)
		{
			$app->enqueueMessage('ibcglobase plugin: missing nyhedsbrev field id');

			return false;
		}

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('r.*');
		$query->select('e.title');
		$query->select('f.formname');

		// Custom fields for integration
		$query->select(array('e.custom' . $listFieldId, 'e.custom' . $uddannelseFieldId, 'e.custom' . $nyhedsbrevFieldId));

		$query->from('#__redevent_register AS r');
		$query->join('INNER', '#__redevent_event_venue_xref AS x ON x.id = r.xref');
		$query->join('INNER', '#__redevent_events AS e ON e.id = x.eventid');
		$query->join('INNER', '#__rwf_forms AS f ON f.id = e.redform_id');
		$query->where('r.id = ' . $attendeeId);

		$db->setQuery($query);
		$res = $db->loadObject();

		return $res;
	}
}
