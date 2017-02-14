<?php
/**
 * @package     Redeventsync
 * @subpackage  Admin
 * @copyright   Copyright (C) 2013 - 2015 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die();

/**
 * Class RedeventsyncModelQueuedmessage
 *
 * @package     Redeventsync
 * @subpackage  Admin
 * @since       3.0
 */
class RedeventsyncModelQueuedmessage extends RModelAdmin
{
	/**
	 * Process selected messages
	 *
	 * @param   mixed  $ids  id or array of ids of items to be processed
	 *
	 * @return bool
	 *
	 * @throws Exception
	 */
	public function process($ids = null)
	{
		$app = JFactory::getApplication();

		JPluginHelper::importPlugin('redeventsyncclient');
		$dispatcher = JDispatcher::getInstance();

		if (!is_array($ids) && is_int($ids))
		{
			$ids = array($ids);
		}

		foreach ($ids as $id)
		{
			$message = $this->getTable();
			$message->load($id);

			$res = null;
			$dispatcher->trigger('onSend', array($message->plugin, $message->message, &$res));

			$msg = new ResyncQueueMessage($message->message);

			if ($res)
			{
				$this->dequeueMessage($message);

				ResyncHelperMessagelog::log(
					REDEVENTSYNC_LOG_DIRECTION_OUTGOING, $msg->getType(), $msg->getTransactionId(), $message->message, 'dequeued'
				);
				$app->enqueueMessage('Send message ' . $message->id . ': success');
			}
			else
			{
				$this->updateErrorCount($message);
				ResyncHelperMessagelog::log(
					REDEVENTSYNC_LOG_DIRECTION_OUTGOING, $msg->getType(), $msg->getTransactionId(), $message->message, 'dequeueing failed'
				);
				$app->enqueueMessage('Error Sending message ' . $message->id . ': error', 'error');
			}
		}

		return true;
	}

	/**
	 * Remove from queue table
	 *
	 * @param   string  $message  message
	 *
	 * @return void
	 */
	private function dequeueMessage($message)
	{
		$db = $this->_db;
		$query = $db->getQuery(true);

		$query->delete('#__redeventsync_queuedmessages');
		$query->where('id = ' . $message->id);

		$db->setQuery($query);
		$db->execute();
	}

	/**
	 * Update error count
	 *
	 * @param   object  $message  message
	 *
	 * @return void
	 */
	private function updateErrorCount($message)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true)
			->update('#__redeventsync_queuedmessages')
			->set('errors = errors  + 1')
			->where('id = ' . $message->id);

		$db->setQuery($query);
		$db->execute();
	}
}
