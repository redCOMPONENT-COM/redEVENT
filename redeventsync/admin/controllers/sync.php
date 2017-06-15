<?php
/**
 * @package     Redcomponent.redeventsync
 * @subpackage  com_redeventsync
 * @copyright   Copyright (C) 2013 redCOMPONENT.com
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die();

/**
 * controller for sync
 *
 * @package  RED.redeventsync
 * @since    2.5
 */
class RedeventsyncControllerSync extends JControllerLegacy
{
	/**
	 * Executes a given controller task. The onBefore<task> and onAfter<task>
	 * methods are called automatically if they exist.
	 *
	 * @param   string  $task  The task to execute, e.g. "browse"
	 *
	 * @return  void
	 */
	public function execute($task)
	{
		if (!$task)
		{
			$task = 'browse';
		}

		parent::execute($task);
	}

	/**
	 * sync sessions
	 *
	 * @return void
	 */
	public function sessions()
	{
		$app = JFactory::getApplication();

		$dateFrom = $app->input->get('sessionsfrom');
		$dateTo   = $app->input->get('sessionsto');

		JPluginHelper::importPlugin('redeventsyncclient');
		$dispatcher = JDispatcher::getInstance();

		$logger = new ResyncLoggerSession;
		$logger->init();

		try
		{
			$dispatcher->trigger('onSync', array(null, &$logger, array(
				'from'   => $dateFrom,
				'to'     => $dateTo,
				'object' => 'sessions'))
			);

			$log = $logger->read();
			echo json_encode($log);
		}
		catch (Exception $e)
		{
			echo json_encode(array($e->getMessage()));
		}

		$app->close();
	}

	/**
	 * sync attendees
	 *
	 * @return void
	 */
	public function attendees()
	{
		$app = JFactory::getApplication();

		$dateFrom = $app->input->get('sessionsfrom');
		$dateTo   = $app->input->get('sessionsto');

		JPluginHelper::importPlugin('redeventsyncclient');
		$dispatcher = JDispatcher::getInstance();

		$logger = new ResyncLoggerSession;
		$logger->init();

		try
		{
			$dispatcher->trigger('onSync', array(null, &$logger, array(
					'from'   => $dateFrom,
					'to'     => $dateTo,
					'object' => 'attendees'))
			);

			$log = $logger->read();
			echo json_encode($log);
		}
		catch (Exception $e)
		{
			echo json_encode(array($e->getMessage()));
		}

		$app->close();
	}
}
