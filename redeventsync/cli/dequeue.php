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

/**
 * This script will checkin all checked out items in database
 *
 * @package  Redshopb2b.Cli
 * @since    1.0.19
 */
class RedeventsyncDequeue extends JApplicationCli
{
	/**
	 * @var JDatabaseDriver
	 */
	private $db;

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

		$this->db = JFactory::getDbo();



		$this->out('Done !');
	}

	public function 
}

JApplicationCli::getInstance('RedeventsyncDequeue')->execute();
