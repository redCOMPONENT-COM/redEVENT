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

$loader = JPATH_LIBRARIES . '/redeventsync/bootstrap.php';

if (!file_exists($loader))
{
	throw new Exception(JText::_('COM_redeventsync_LIB_INIT_FAILED'), 404);
}

include_once $loader;

// Bootstraps redEVENTSYNC
ResyncBootstrap::bootstrap();

/**
 * Class plgRedeventRedeventsync
 *
 * @package  Redcomponent.redeventsync
 * @since    2.5
 */
class PlgRedeventRedeventsync extends JPlugin
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
	 * @param   int   $session_id  session id
	 * @param   bool  $isNew       is new session
	 *
	 * @return boolean
	 */
	public function onAfterSessionSave($session_id, $isNew = false)
	{
		try
		{
			JPluginHelper::importPlugin('redeventsyncclient');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('onHandleAfterSessionSave', array($session_id, $isNew));
		}
		catch (ResyncException $e)
		{
			ResyncHelperMessagelog::log(REDEVENTSYNC_LOG_DIRECTION_OUTGOING, 'onHandleAfterSessionSave', 0, $e->getMessage(), $e->status, $e->debug);
		}
		catch (Exception $e)
		{
			ResyncHelperMessagelog::log(REDEVENTSYNC_LOG_DIRECTION_OUTGOING, 'onHandleAfterSessionSave', 0, $e->getMessage(), 'error');
		}

		return true;
	}

	/**
	 * handles session delete to generate DeleteSessionRQ
	 *
	 * @param   string  $session_code  session code
	 *
	 * @return boolean
	 */
	public function onAfterSessionDelete($session_code)
	{
		try
		{
			JPluginHelper::importPlugin('redeventsyncclient');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('onHandleAfterSessionDelete', array($session_code));
		}
		catch (ResyncException $e)
		{
			ResyncHelperMessagelog::log(
				REDEVENTSYNC_LOG_DIRECTION_OUTGOING, 'onHandleAfterSessionDelete', 0, $e->getMessage(), $e->status, $e->debug
			);
		}
		catch (Exception $e)
		{
			ResyncHelperMessagelog::log(REDEVENTSYNC_LOG_DIRECTION_OUTGOING, 'onHandleAfterSessionDelete', 0, $e->getMessage(), 'error');
		}

		return true;
	}

	/**
	 * handles attendee created
	 *
	 * @param   int  $attendee_id  attendee id
	 *
	 * @return boolean
	 */
	public function onAttendeeCreated($attendee_id)
	{
		try
		{
			JPluginHelper::importPlugin('redeventsyncclient');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('onHandleAttendeeCreated', array($attendee_id));
		}
		catch (ResyncException $e)
		{
			ResyncHelperMessagelog::log(REDEVENTSYNC_LOG_DIRECTION_OUTGOING, 'onHandleAttendeeCreated', 0, $e->getMessage(), $e->status, $e->debug);
		}
		catch (Exception $e)
		{
			ResyncHelperMessagelog::log(REDEVENTSYNC_LOG_DIRECTION_OUTGOING, 'onHandleAttendeeCreated', 0, $e->getMessage(), 'error');
		}

		return true;
	}

	/**
	 * handles attendee confirmed
	 *
	 * @param   int  $attendee_id  attendee id
	 *
	 * @return boolean
	 */
	public function onAttendeeConfirmed($attendee_id)
	{
		try
		{
			JPluginHelper::importPlugin('redeventsyncclient');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('onHandleAttendeeConfirmed', array($attendee_id));
		}
		catch (ResyncException $e)
		{
			ResyncHelperMessagelog::log(REDEVENTSYNC_LOG_DIRECTION_OUTGOING, 'onHandleAttendeeConfirmed', 0, $e->getMessage(), $e->status, $e->debug);
		}
		catch (Exception $e)
		{
			ResyncHelperMessagelog::log(REDEVENTSYNC_LOG_DIRECTION_OUTGOING, 'onHandleAttendeeConfirmed', 0, $e->getMessage(), 'error');
		}

		return true;
	}

	/**
	 * handles attendee modified
	 *
	 * @param   int  $attendee_id  attendee id
	 *
	 * @return boolean
	 */
	public function onAttendeeModified($attendee_id)
	{
		try
		{
			JPluginHelper::importPlugin('redeventsyncclient');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('onHandleAttendeeModified', array($attendee_id));
		}
		catch (ResyncException $e)
		{
			ResyncHelperMessagelog::log(REDEVENTSYNC_LOG_DIRECTION_OUTGOING, 'onHandleAttendeeModified', 0, $e->getMessage(), $e->status, $e->debug);
		}
		catch (Exception $e)
		{
			ResyncHelperMessagelog::log(REDEVENTSYNC_LOG_DIRECTION_OUTGOING, 'onHandleAttendeeModified', 0, $e->getMessage(), 'error');
		}

		return true;
	}

	/**
	 * handles attendee cancelled
	 *
	 * @param   int  $attendee_id  attendee id
	 *
	 * @return boolean
	 */
	public function onAttendeeCancelled($attendee_id)
	{
		try
		{
			JPluginHelper::importPlugin('redeventsyncclient');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('onHandleAttendeeCancelled', array($attendee_id));
		}
		catch (ResyncException $e)
		{
			ResyncHelperMessagelog::log(REDEVENTSYNC_LOG_DIRECTION_OUTGOING, 'onHandleAttendeeCancelled', 0, $e->getMessage(), $e->status, $e->debug);
		}
		catch (Exception $e)
		{
			ResyncHelperMessagelog::log(REDEVENTSYNC_LOG_DIRECTION_OUTGOING, 'onHandleAttendeeCancelled', 0, $e->getMessage(), 'error');
		}

		return true;
	}

	/**
	 * handles attendee deleted
	 *
	 * @param   int  $attendee_id  attendee id
	 *
	 * @return boolean
	 */
	public function onAttendeeDeleted($attendee_id)
	{
		try
		{
			JPluginHelper::importPlugin('redeventsyncclient');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('onHandleAttendeeDeleted', array($attendee_id));
		}
		catch (ResyncException $e)
		{
			ResyncHelperMessagelog::log(REDEVENTSYNC_LOG_DIRECTION_OUTGOING, 'onHandleAttendeeDeleted', 0, $e->getMessage(), $e->status, $e->debug);
		}
		catch (Exception $e)
		{
			ResyncHelperMessagelog::log(REDEVENTSYNC_LOG_DIRECTION_OUTGOING, 'onHandleAttendeeDeleted', 0, $e->getMessage(), 'error');
		}

		return true;
	}

	/**
	 * handles payment verified
	 *
	 * @param   string  $submit_key  submit key
	 *
	 * @return boolean
	 */
	public function onAfterPaymentVerified($submit_key)
	{
		try
		{
			JPluginHelper::importPlugin('redeventsyncclient');
			$dispatcher = JDispatcher::getInstance();

			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('id');
			$query->from('#__redevent_register');
			$query->where('submit_key = ' . $db->quote($submit_key));

			$db->setQuery($query);
			$res = $db->loadColumn();

			foreach ($res as $attendee_id)
			{
				$dispatcher->trigger('onHandleAttendeePaid', array($attendee_id));
			}
		}
		catch (ResyncException $e)
		{
			ResyncHelperMessagelog::log(REDEVENTSYNC_LOG_DIRECTION_OUTGOING, 'onAfterPaymentVerified', 0, $e->getMessage(), $e->status, $e->debug);
		}
		catch (Exception $e)
		{
			ResyncHelperMessagelog::log(REDEVENTSYNC_LOG_DIRECTION_OUTGOING, 'onAfterPaymentVerified', 0, $e->getMessage(), 'error');
		}

		return true;
	}

	/**
	 * handles payment verified
	 *
	 * @param   string  $submit_key  submit key
	 *
	 * @return boolean
	 */
	public function onAfterPaymentVerifiedRedevent($submit_key)
	{
		try
		{
			JPluginHelper::importPlugin('redeventsyncclient');
			$dispatcher = JDispatcher::getInstance();

			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('id');
			$query->from('#__redevent_register');
			$query->where('submit_key = ' . $db->quote($submit_key));

			$db->setQuery($query);
			$res = $db->loadColumn();

			foreach ($res as $attendee_id)
			{
				$dispatcher->trigger('onHandleAttendeePaid', array($attendee_id));
			}
		}
		catch (ResyncException $e)
		{
			ResyncHelperMessagelog::log(REDEVENTSYNC_LOG_DIRECTION_OUTGOING, 'onAfterPaymentVerified', 0, $e->getMessage(), $e->status, $e->debug);
		}
		catch (Exception $e)
		{
			ResyncHelperMessagelog::log(REDEVENTSYNC_LOG_DIRECTION_OUTGOING, 'onAfterPaymentVerified', 0, $e->getMessage(), 'error');
		}

		return true;
	}

	/**
	 * handle user saved
	 *
	 * @param   int   $userId  user id
	 * @param   bool  $isNew   is it inew ?
	 *
	 * @return boolean
	 */
	public function onUserSaved($userId, $isNew)
	{
		try
		{
			JPluginHelper::importPlugin('redeventsyncclient');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('onHandleUserSaved', array($userId, $isNew));
		}
		catch (ResyncException $e)
		{
			ResyncHelperMessagelog::log(REDEVENTSYNC_LOG_DIRECTION_OUTGOING, 'onHandleUserSaved', 0, $e->getMessage(), $e->status, $e->debug);
		}
		catch (Exception $e)
		{
			ResyncHelperMessagelog::log(REDEVENTSYNC_LOG_DIRECTION_OUTGOING, 'onHandleUserSaved', 0, $e->getMessage(), 'error');
		}

		return true;
	}
}
