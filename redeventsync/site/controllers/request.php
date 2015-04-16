<?php
/**
 * @package		redcomponent.redeventsync
 * @subpackage	com_redeventsync
 * @copyright	Copyright (C) 2013 redCOMPONENT.com
 * @license		GNU General Public License version 2 or later
 */
defined('_JEXEC') or die();

/**
 * redEVENT sync request Controller
 *
 * @package  RED.redeventsync
 * @since    2.5
 */
class RedeventsyncControllerRequest extends FOFController
{
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->registerTask('edit', 'add');

	}

	/**
	 * Handle receiving xml files in post data
	 *
	 * validate and pass to model, then returns answer
	 *
	 * @return void
	 */
	public function add()
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
	 * ACL check before adding a new record; override to customise
	 *
	 * @return  boolean  True to allow the method to run
	 */
	protected function onBeforeAdd()
	{
		return true;
	}

	/**
	 * ACL check before adding a new record; override to customise
	 *
	 * @return  boolean  True to allow the method to run
	 */
	protected function onBeforeEdit()
	{
		return true;
	}
}
