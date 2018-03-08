<?php
/**
 * @package    Redevent.Plugin
 *
 * @copyright  Copyright (C) 2016 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

// Import library dependencies
jimport('joomla.plugin.plugin');

/**
 * redEVENT Integration plugin
 *
 * @since  2.0
 */
class PlgRedeventAcymailinglists extends JPlugin
{
	/**
	 * @var bool
	 */
	protected $autoloadLanguage = true;

	/**
	 * The plugin identifier.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $context = 'acymailinglists';

	/**
	 * Add redMAILFLOW custom field for redEVENT
	 *
	 * @param   string                       $classname  class name
	 * @param   RedeventAbstractCustomfield  $instance   instance
	 *
	 * @return void
	 */
	public function onRedeventGetCustomField($classname, &$instance)
	{
		if ($classname == 'RedeventCustomfieldAcymailinglists')
		{
			require_once 'customfield/acymailinglists.php';

			$instance = new $classname;
		}
	}

	/**
	 * Add redevent custom field type
	 *
	 * @param   string[]  $types  types
	 *
	 * @return void
	 */
	public function onRedeventGetCustomFieldTypes(&$types)
	{
		$types[] = 'acymailinglists';
	}
}
