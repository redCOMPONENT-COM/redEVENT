<?php
/**
 * @package     Redevent.Plugin
 * @subpackage  paymentnotificationemail
 *
 * @copyright   Copyright (C) 2008-2015 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

// Import library dependencies
jimport('joomla.plugin.plugin');

jimport('redevent.bootstrap');

RLoader::registerPrefix('Eventacymailinglists', __DIR__);

/**
 * redFORM custom acymailing lists from redEVENT event
 *
 * @since  3.0
 */
class PlgRedform_FieldEventacymailinglists extends JPlugin
{
	protected $autoloadLanguage = true;

	/**
	 * The plugin identifier.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $context = 'eventacymailinglists';

	/**
	 * Constructor
	 *
	 * @param   object  $subject  The object to observe
	 * @param   array   $config   An optional associative array of configuration settings.
	 *                            Recognized key values include 'name', 'group', 'params', 'language'
	 *                            (this list is not meant to be comprehensive).
	 */
	public function __construct($subject, array $config)
	{
		parent::__construct($subject, $config);

		RedeventBootstrap::bootstrap();
	}

	/**
	 * Add supported field type(s)
	 *
	 * @param   string[]  &$types  types
	 *
	 * @return void
	 */
	public function onRedformGetFieldTypes(&$types)
	{
		$types[] = 'eventacymailinglists';
	}

	/**
	 * Add supported field type(s) as option(s)
	 *
	 * @param   object[]  &$options  options
	 *
	 * @return void
	 */
	public function onRedformGetFieldTypesOptions(&$options)
	{
		$options[] = JHtml::_('select.option', 'eventacymailinglists', JText::_('PLG_REDFORM_FIELD_EVENTACYMAILINGLISTS_FIELD_EVENTACYMAILINGLISTS'));
	}

	/**
	 * Return an instance of supported types, if matches.
	 *
	 * @param   string     $type       type of field
	 * @param   RdfRfield  &$instance  instance of field
	 *
	 * @return void
	 */
	public function onRedformGetFieldInstance($type, &$instance)
	{
		if ($type == 'eventacymailinglists')
		{
			$instance = new EventacymailinglistsFieldEventacymailinglists;
			$instance->setPluginParams($this->params);
		}
	}
}
