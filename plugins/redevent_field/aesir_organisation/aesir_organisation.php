<?php
/**
 * @package    Redevent.Plugin
 *
 * @copyright  Copyright (C) 2017 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

// Import library dependencies
jimport('joomla.plugin.plugin');

/**
 * redEVENT Integration plugin
 *
 * @since  __deploy_version__
 */
class PlgRedevent_FieldAesir_Organisation extends JPlugin
{
	/**
	 * @var bool
	 */
	protected $autoloadLanguage = true;

	/**
	 * The plugin identifier.
	 *
	 * @var    string
	 * @since  __deploy_version__
	 */
	protected $context = 'aesir_organisation';

	/**
	 * Add custom field for redEVENT
	 *
	 * @param   string                       $classname  class name
	 * @param   RedeventAbstractCustomfield  $instance   instance
	 *
	 * @return void
	 *
	 * @since  __deploy_version__
	 */
	public function onRedeventGetCustomField($classname, &$instance)
	{
		if ($classname == 'RedeventCustomfieldAesir_organisation')
		{
			require_once 'customfield/aesir_organisation.php';

			$instance = new $classname;
		}
	}

	/**
	 * Add redevent custom field type
	 *
	 * @param   string[]  $types  types
	 *
	 * @return void
	 *
	 * @since  __deploy_version__
	 */
	public function onRedeventGetCustomFieldTypes(&$types)
	{
		$types[] = 'aesir_organisation';
	}
}
