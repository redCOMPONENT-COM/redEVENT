<?php
/**
 * @package    Redeventsync.Cli
 * @copyright  Copyright (C) 2013 redCOMPONENT.com
 * @license    GNU General Public License version 2 or later
 */

// Make sure we're being called from the command line, not a web interface
if (PHP_SAPI !== 'cli')
{
	die('This is a command line only application.');
}

// We are a valid entry point.
const _JEXEC = 1;

// Load system defines
if (file_exists(dirname(__DIR__) . '/defines.php'))
{
	require_once dirname(__DIR__) . '/defines.php';
}

if (!defined('_JDEFINES'))
{
	define('JPATH_BASE', dirname(__DIR__));
	require_once JPATH_BASE . '/includes/defines.php';
}

// Get the framework.
require_once JPATH_LIBRARIES . '/import.legacy.php';

// Bootstrap the CMS libraries.
require_once JPATH_LIBRARIES . '/cms.php';

// Import the configuration.
require_once JPATH_CONFIGURATION . '/configuration.php';

// System configuration.
$config = new JConfig;

// Configure error reporting to maximum for CLI output.
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once dirname(__FILE__) . '/bootstrap_redcore.php';

require_once dirname(__FILE__) . '/bootstrap_resync.php';

/**
 * This script will checkin all checked out items in database
 *
 * @package  Redshopb2b.Cli
 * @since    1.0.19
 */
class RedeventsyncQueueStats extends JApplicationCli
{
	const MAX_RETRY = 20;
	const RECIPIENTS = 'ronni@redweb.dk';

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
		$this->out('Redeventsync queue stats');
		$this->out('============================');

		if ($messages = $this->getMessagesWithErrors())
		{
			$this->sendMail($messages);
		}

		$this->out('Done !');
	}

	/**
	 * Get messages
	 *
	 * @return mixed
	 */
	private function getMessagesWithErrors()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('*')
			->from('#__redeventsync_queuedmessages')
			->where('errors > 0')
			->order('queued DESC');

		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Send email
	 *
	 * @param   array  $messages  messages
	 *
	 * @return void
	 */
	private function sendMail($messages)
	{
		if (!$messages)
		{
			return;
		}

		$mail = JFactory::getMailer();
		$mail->isHtml(true);
		$mail->setSubject(sprintf('%d messages with errors in maersktraining sync queue', count($messages)));

		$body = array();
		$body[] = '<p>' . count($messages) . ' messages in error</p>';
		$body[] = '<table border="1" valign="top" align="left" cellpadding="2" cellspacing="0">';
		$body[] = "<tr><th>Id</th><th>date</th><th>Errors</th></tr>";

		foreach ($messages as $message)
		{
			$body[] = "<tr><td>{$message->id}</td><td>{$message->queued}</td><td>{$message->errors}</td></tr>";
		}

		$body[] = '</table>';

		$mail->setBody(implode("\n", $body));

		foreach (explode(";", static::RECIPIENTS) as $address)
		{
			$mail->addRecipient($address);
		}

		$mail->send();
	}
}

JApplicationCli::getInstance('RedeventsyncQueueStats')->execute();
