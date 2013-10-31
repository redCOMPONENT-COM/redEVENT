<?php
/**
 * @package     Redcomponent.redeventsync
 * @subpackage  com_redeventsync
 * @copyright   Copyright (C) 2013 redCOMPONENT.com
 * @license     GNU General Public License version 2 or later
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// Import library dependencies
jimport('joomla.plugin.plugin');

/**
 * Class plgRedeventRedeventsync
 *
 * @package  Redcomponent.redeventsync
 * @since    2.5
 */
class plgRedeventRedeventsync extends JPlugin
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
	 * handles session save to generate Create/Modify SessionRQ
	 *
	 * @param   int  $session_id  session id
	 * @param   bool $isNew       is new session
	 *
	 * @return bool
	 */
	public function onAfterSessionSave($session_id, $isNew = false)
	{
		require_once JPATH_SITE . '/components/com_redeventsync/models/sessionsrq.php';

		$model = JModel::getInstance('Sessionsrq', 'RedeventsyncModel');

		if ($isNew)
		{
			$model->sendCreateSessionRq($session_id);
		}
		else
		{
			$model->sendModifySessionRq($session_id);
		}

		return true;
	}

	/**
	 * handles session delete to generate DeleteSessionRQ
	 *
	 * @param $session_code
	 *
	 * @return bool
	 */
	public function onAfterSessionDelete($session_code)
	{
		require_once JPATH_SITE . '/components/com_redeventsync/models/sessionsrq.php';

		$model = JModel::getInstance('Sessionsrq', 'RedeventsyncModel');
		$model->sendDeleteSessionRQ($session_code);

		return true;
	}

	public function onAttendeeCreated($attendee_id)
	{
		require_once JPATH_SITE . '/components/com_redeventsync/models/attendeesrq.php';

		$model = JModel::getInstance('Attendeesrq', 'RedeventsyncModel');
		$model->sendCreateAttendeeRQ($attendee_id);

		return true;
	}

	public function onAttendeeModified($attendee_id)
	{
		require_once JPATH_SITE . '/components/com_redeventsync/models/attendeesrq.php';

		$model = JModel::getInstance('Attendeesrq', 'RedeventsyncModel');
		$model->sendModifyAttendeeRQ($attendee_id);

		return true;
	}

	public function onAttendeeDeleted($attendee_id)
	{
		require_once JPATH_SITE . '/components/com_redeventsync/models/attendeesrq.php';

		$model = JModel::getInstance('Attendeesrq', 'RedeventsyncModel');
		$model->sendDeleteAttendeeRQ($attendee_id);

		return true;
	}
}
