<?php
/**
 * @package     Redevent.Plugins
 * @subpackage  Editors-xtd
 * @copyright   Copyright (C) 2008 - 2015 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');

/**
 * Editor Pagebreak buton
 *
 * @since  1.5
 */
class PlgButtonRedevent extends JPlugin
{
	/**
	 * Constructor
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $config    An array that holds the plugin configuration
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}

	/**
	 * Display the button
	 *
	 * @param   string  $name  name
	 *
	 * @return object button
	 */
	public function onDisplay($name)
	{
		$mainframe = JFactory::getApplication();

		$doc = JFactory::getDocument();
		$template = $mainframe->getTemplate();

		$declaration = ".button2-left .redevent {background: url(media/com_redevent/images/editor_button.png) 100% 0 no-repeat;}";

		$doc->addStyleDeclaration($declaration);

		$link = 'index.php?option=com_redevent&amp;task=insertevent&amp;tmpl=component&amp;e_name=' . $name;

		JHTML::_('behavior.modal');

		$button = new JObject;
		$button->set('modal', true);
		$button->set('link', $link);
		$button->set('text', JText::_('PLG_REDEVENT_EDITORXTD_Event'));
		$button->set('name', 'redevent');
		$button->set('options', "{handler: 'iframe', size: {x: 600, y: 500}}");

		return $button;
	}
}
