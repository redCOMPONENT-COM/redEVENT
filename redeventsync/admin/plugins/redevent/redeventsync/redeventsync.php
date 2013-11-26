<?php
/**
 * @package     Redcomponent.redeventsync
 * @subpackage  com_redeventsync
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
		JPluginHelper::importPlugin('redeventsyncclient');
		$dispatcher = JDispatcher::getInstance();

		try
		{
			$dispatcher->trigger('onHandleAfterSessionSave', array($session_id, $isNew));
		}
		catch (Exception $e)
		{
			echo $e->getMessage();
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
		JPluginHelper::importPlugin('redeventsyncclient');
		$dispatcher = JDispatcher::getInstance();

		try
		{
			$dispatcher->trigger('onHandleAfterSessionDelete', array($session_code));
		}
		catch (Exception $e)
		{
			echo $e->getMessage();
		}

		return true;
	}

	public function onAttendeeCreated($attendee_id)
	{
		JPluginHelper::importPlugin('redeventsyncclient');
		$dispatcher = JDispatcher::getInstance();

		try
		{
			$dispatcher->trigger('onHandleAttendeeCreated', array($attendee_id));
		}
		catch (Exception $e)
		{
			echo $e->getMessage();
		}

		return true;
	}

	public function onAttendeeModified($attendee_id)
	{
		JPluginHelper::importPlugin('redeventsyncclient');
		$dispatcher = JDispatcher::getInstance();

		try
		{
			$dispatcher->trigger('onHandleAttendeeModified', array($attendee_id));
		}
		catch (Exception $e)
		{
			echo $e->getMessage();
		}

		return true;
	}

	public function onAttendeeDeleted($attendee_id)
	{
		JPluginHelper::importPlugin('redeventsyncclient');
		$dispatcher = JDispatcher::getInstance();

		try
		{
			$dispatcher->trigger('onHandleAttendeeDeleted', array($attendee_id));
		}
		catch (Exception $e)
		{
			echo $e->getMessage();
		}

		return true;
	}
}
