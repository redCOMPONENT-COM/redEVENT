<?php
/**
 * @package     Redevent.Plugin
 * @subpackage  Ibc.Sessionsdump
 *
 * @copyright   Copyright (C) 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

// Import library dependencies
jimport('joomla.plugin.plugin');

jimport('redevent.bootstrap');

JLoader::import('reditem.library');

require_once 'includes/model.php';
require_once 'includes/helper.php';
require_once 'includes/tablerow.php';

/**
 * Specific parameters for redEVENT.
 *
 * @package     Redevent.Plugin
 * @subpackage  Config.Example
 * @since       2.5
 */
class PlgIbcSessionsdump extends JPlugin
{
	/**
	 * The plugin identifier.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $context = 'sessionsdump';

	/**
	 * Constructor
	 *
	 * @param   object &$subject   The object to observe
	 * @param   array  $config     An optional associative array of configuration settings.
	 *                             Recognized key values include 'name', 'group', 'params', 'language'
	 *                             (this list is not meant to be comprehensive).
	 *
	 * @since   1.5
	 */
	public function __construct($subject, array $config)
	{
		parent::__construct($subject, $config);
		RedeventBootstrap::bootstrap();
	}

	/**
	 * Ajax handler
	 *
	 * @return void
	 */
	public function onAjaxSessionsdump()
	{
		$model = new DumpModel();
		$sessions = $model->getItems();

		$rows = DumpHelper::groupSessions($sessions);
		$active = DumpHelper::countActive($rows);
		$inactive = count($rows) - $active;

		ob_start();
		include 'layouts/dump.php';
		$html = ob_get_contents();
		ob_end_clean();

		echo $html;
	}
}
