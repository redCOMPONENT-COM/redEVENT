<?php
/**
 * @package    Redevent.Library
 * @copyright  Copyright (C) 2008 - 2014 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * Renders a select Custom field
 *
 * @package  Redevent.Library
 * @since    2.0
 */
class RedeventCustomfieldMailflow extends RedeventCustomfieldSelect
{

	/**
	 * Element name
	 *
	 * @access protected
	 * @var    string
	 */
	var $_name = 'mailflow';

	/**
	 * return options
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		$db = JFactory::getDbo();

		$query = $db->getQuery(true)
			->select('m.id AS value, m.name AS text')
			->from('#__redmailflow_mailflows AS m')
			->order('m.name');

		$db->setQuery($query);
		$options = $db->loadObjectList();

		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
