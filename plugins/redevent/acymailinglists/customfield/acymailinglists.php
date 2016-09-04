<?php
/**
 * @package    Redevent.Plugin
 *
 * @copyright  Copyright (C) 2016 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('JPATH_BASE') or die;

/**
 * Renders a select Custom field
 *
 * @package  Redevent.Library
 * @since    2.0
 */
class RedeventCustomfieldAcymailinglists extends RedeventCustomfieldCheckbox
{
	/**
	 * return options
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		if (!include_once JPATH_ADMINISTRATOR . '/components/com_acymailing/helpers/helper.php')
		{
			JFactory::getApplication()->enqueueMessage('This code can not work without the AcyMailing Component');

			return false;
		}

		$listClass = acymailing_get('class.list');
		$allLists = $listClass->getLists();

		if (!$allLists)
		{
			return array();
		}

		return array_map(
			function ($element)
			{
				return JHtml::_('select.option', $element->listid, $element->name);
			}, $allLists
		);
	}
}
