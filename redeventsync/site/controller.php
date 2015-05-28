<?php
/**
 * @package     Redcomponent.redeventsync
 * @subpackage  com_redeventsync
 * @copyright   Copyright (C) 2013-2015 redCOMPONENT.com
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

/**
 * Front-end Controller
 *
 * @package     Redcomponent.redeventsync
 * @subpackage  com_redeventsync
 * @since       3.0
 */
class RedeventsyncController extends JControllerLegacy
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 * Recognized key values include 'name', 'default_task', 'model_path', and
	 * 'view_path' (this list is not meant to be comprehensive).
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->registerDefaultTask('request');
	}

	/**
	 * Handle receiving xml files in post data
	 *
	 * validate and pass to model, then returns answer
	 *
	 * @return void
	 */
	public function request()
	{
		$this->input->set('tmpl', 'component');
		$debug = $this->input->getInt('debug', 0);
		$data = file_get_contents('php://input');

		$client = $this->input->get('client');

		if (!$client)
		{
			echo 'Client is required';
			JFactory::getApplication()->close();
		}

		if ($data)
		{
			// For a proper xml response, turn off error reporting
			if (!$debug)
			{
				error_reporting(0);
			}

			JPluginHelper::importPlugin('redeventsyncclient');
			$dispatcher = JDispatcher::getInstance();

			try
			{
				$dispatcher->trigger('onHandle', array($client, $data));
			}
			catch (Exception $e)
			{
				echo 'Error: ' . $e->getMessage();
			}
		}
		else
		{
			echo 'error no data received';
		}

		JFactory::getApplication()->close();
	}

	/**
	 * Just a way to test our own requests...
	 *
	 * @throws Exception
	 *
	 * @return void
	 */
	public function test()
	{
		echo 'testing for posted data' . "\n";

		$tmp_path = JFactory::getApplication()->getCfg('tmp_path') . '/resync';
		JFolder::create($tmp_path);

		echo "Will save in " . $tmp_path;

		$this->input->set('tmpl', 'component');
		$debug = $this->input->getInt('debug', 0);
		$data = file_get_contents('php://input');

		if ($data)
		{
			$filename = $tmp_path . '/received' . time();

			if (!file_put_contents($filename, $data))
			{
				throw new Exception('error writing posted file');
			}
		}
		else
		{
			echo 'error no data received';
		}

		JFactory::getApplication()->close();
	}
}
