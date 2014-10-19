<?php
/**
 * @package     Redcomponent.redeventsync
 * @subpackage  com_redeventsync
 * @copyright   Copyright (C) 2013 redCOMPONENT.com
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die();

class RedeventsyncModelSendqueuedmessages extends FOFModel
{
	public function process()
	{
		$app = JFactory::getApplication();

		JPluginHelper::importPlugin('redeventsyncclient');
		$dispatcher = JDispatcher::getInstance();

		foreach ($this->getIds() as $id)
		{
			$message = $this->getTable('Queuedmessages');
			$message->load($id);

			$res = null;
			$dispatcher->trigger('onSend', array($message->plugin, $message->message, &$res));

			if ($res)
			{
				$this->dequeueMessage($message);

				$msg = new ResyncQueueMessage($message->message);

				ResyncHelperMessagelog::log(REDEVENTSYNC_LOG_DIRECTION_OUTGOING, $msg->getType(), $msg->getTransactionId(), $message->message, 'dequeued');
				$app->enqueueMessage('Send message ' . $message->redeventsync_queuedmessage_id . ': success');
			}
			else
			{
				$app->enqueueMessage('Error Sending message ' . $message->redeventsync_queuedmessage_id . ': error', 'error');
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
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->delete('#__redeventsync_queuedmessages');
		$query->where('redeventsync_queuedmessage_id = ' . $message->redeventsync_queuedmessage_id);

		$db->setQuery($query);
		$db->execute();
	}
}
