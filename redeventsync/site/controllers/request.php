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
		$xml_post = file_get_contents('php://input');

		if ($xml_post)
		{
			// Enable user error handling
			$prev = libxml_use_internal_errors(true);

			$xml = new DOMDocument;

			if (!$xml->loadXml($xml_post))
			{
				foreach (libxml_get_errors() as $error)
				{
					echo $error->message . "\n";
				}

				libxml_clear_errors();
				JFactory::getApplication()->close();
			}

			$xml->preserveWhiteSpace = false;

			$type = $xml->firstChild->nodeName;

			$supported = array(
					'AttendeesRQ', 'AttendeesRS',
					'CustomersRQ', 'CustomersRS',
					'SessionsRQ', 'SessionsRS',
			);

			// Check if it's a supported type
			if (! in_array($type, $supported))
			{
				echo 'Unsupported schema: ' . $type;
				JFactory::getApplication()->close();
			}

			// Validate
			if (! $xml->schemaValidate(JPATH_COMPONENT_SITE . '/schemas/' . $type . '.xsd'))
			{
				echo "Invalid xml data !\n";

				foreach (libxml_get_errors() as $error)
				{
					echo $error->message . "\n";
				}

				libxml_clear_errors();
				JFactory::getApplication()->close();
			}

			// Get the model for the message type
			$model = FOFModel::getTmpInstance($type, 'RedeventsyncModel');

			try
			{
				$model->handle($xml_post);

				if ($msg = $model->getResponseMessage())
				{
					echo $msg;
				}

				JFactory::getApplication()->close();
			}
			catch (Exception $e)
			{
				echo $e->getMessage();
				JFactory::getApplication()->close();
			}

			libxml_use_internal_errors($prev);
		}
		else
		{
			echo 'error no xml received';
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
