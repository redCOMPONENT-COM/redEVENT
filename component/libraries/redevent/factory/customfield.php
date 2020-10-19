<?php
/**
 * @package    Redevent.Library
 * @copyright  Copyright (C) 2008 - 2020 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * Custom field factory
 *
 * @package  Redevent.Library
 * @since    2.5
 */
abstract class RedeventFactoryCustomfield
{
	/**
	 * Returns field object
	 *
	 * @param   string  $type  field type
	 *
	 * @return RedeventAbstractCustomfield
	 *
	 * @throws RuntimeException
	 */
	public static function getField($type)
	{
		if ($type == 'select_multiple') // Backwards compatibility
		{
			$type = 'selectmultiple';
		}

		$classname = 'RedeventCustomfield' . ucfirst($type);

		$instance = null;

		JPluginHelper::importPlugin('redevent');
		JPluginHelper::importPlugin('redevent_field');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onRedeventGetCustomField', array($classname, &$instance));

		if ($instance instanceof RedeventAbstractCustomfield)
		{
			return $instance;
		}

		if (class_exists($classname))
		{
			return new $classname;
		}

		// Type not found, display a warning and return text custom field
		JFactory::getApplication()->enqueueMessage('Custom field type not found: ' . $type, 'error');

		return self::getField('text');
	}

	/**
	 * Return all supported field types
	 *
	 * @return array
	 */
	public static function getTypes()
	{
		jimport('joomla.filesystem.folder');
		$path = JPATH_SITE . '/libraries/redevent/customfield';

		$files = JFolder::files($path, '.*php$');
		$types = array();

		foreach ($files as $filename)
		{
			$types[] = substr($filename, 0, -4);
		}

		JPluginHelper::importPlugin('redevent');
		JPluginHelper::importPlugin('redevent_field');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onRedeventGetCustomFieldTypes', array(&$types));

		return $types;
	}
}
