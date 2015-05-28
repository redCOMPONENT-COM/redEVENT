<?php
/**
 * @package    Redeventsync.Cli
 * @copyright  Copyright (C) 2013 redCOMPONENT.com
 * @license    GNU General Public License version 2 or later
 */

// Make sure we're being called from the command line, not a web interface
if (array_key_exists('REQUEST_METHOD', $_SERVER)) die();

// We are a valid entry point.
define('_JEXEC', 1);
define('DS', DIRECTORY_SEPARATOR);

// Load system defines
if (file_exists(dirname(dirname(__FILE__)) . '/defines.php'))
{
	require_once dirname(dirname(__FILE__)) . '/defines.php';
}

if (!defined('_JDEFINES'))
{
	define('JPATH_BASE', dirname(dirname(__FILE__)));
	require_once JPATH_BASE . '/includes/defines.php';
}

// Get the framework.
require_once JPATH_LIBRARIES . '/import.php';
require_once JPATH_LIBRARIES . '/joomla/application/component/helper.php';

// Bootstrap the CMS libraries.
require_once JPATH_LIBRARIES . '/cms.php';

// Force library to be in JError legacy mode
JError::$legacy = true;

// Import necessary classes not handled by the autoloaders
jimport('joomla.application.menu');
jimport('joomla.environment.uri');
jimport('joomla.event.dispatcher');
jimport('joomla.utilities.utility');
jimport('joomla.utilities.arrayhelper');

// Import the configuration.
require_once JPATH_CONFIGURATION . '/configuration.php';

// System configuration.
$config = new JConfig;

// Configure error reporting to maximum for CLI output.
error_reporting(E_ALL);
ini_set('display_errors', 1);

$loader = JPATH_LIBRARIES . '/redeventsync/bootstrap.php';

if (!file_exists($loader))
{
	throw new Exception(JText::_('COM_redeventsync_LIB_INIT_FAILED'), 404);
}

include_once $loader;

// Bootstraps redEVENTSYNC
ResyncBootstrap::bootstrap();

/**
 * This script will checkin all checked out items in database
 *
 * @package  Redshopb2b.Cli
 * @since    1.0.19
 */
class RedeventsyncDequeue extends JApplicationCli
{
	/**
	 * Entry point for the script
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	public function doExecute()
	{
		$this->out('============================');
		$this->out('Redeventsync Dequeue');
		$this->out('============================');

		JPluginHelper::importPlugin('redeventsyncclient');

		try
		{
			$this->dequeue();
		}
		catch (Exception $e)
		{
			$this->out('Error: ' . $e->getMessage());
		}

		$this->out('Done !');
	}

	/**
	 * Dequeue messages
	 *
	 * @return void
	 */
	private function dequeue()
	{
		$messages = $this->getMessages();

		foreach ($messages as $message)
		{
			$this->handleMessage($message);
		}
	}

	/**
	 * Handle single message
	 *
	 * @param   string  $message  message
	 *
	 * @return void
	 */
	private function handleMessage($message)
	{
		$res = null;
		$this->dispatcher->trigger('onSend', array($message->plugin, $message->message, &$res));

		$msg = new ResyncQueueMessage($message->message);

		if ($res)
		{
			$this->dequeueMessage($message);

			ResyncHelperMessagelog::log(REDEVENTSYNC_LOG_DIRECTION_OUTGOING, $msg->getType(), $msg->getTransactionId(), $message->message, 'dequeued');
			$this->out('Send message ' . $message->id . ': success');
		}
		else
		{
			ResyncHelperMessagelog::log(REDEVENTSYNC_LOG_DIRECTION_OUTGOING, $msg->getType(), $msg->getTransactionId(), $message->message, 'dequeueing failed');
			$this->out('Send message ' . $message->id . ': error');
		}
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
		$query->where('id = ' . $message->id);

		$db->setQuery($query);
		$db->execute();
	}

	/**
	 * Get messages in queue
	 *
	 * @return mixed
	 */
	private function getMessages()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('*')
			->from('#__redeventsync_queuedmessages')
			->order('id ASC');

		$db->setQuery($query);
		$messages = $db->loadObjectList();

		return $messages;
	}
}

JApplicationCli::getInstance('RedeventsyncDequeue')->execute();
