<?php
/**
 * @package    Redevent.Library
 * @copyright  Copyright (C) 2008 - 2014 redCOMPONENT.com. All rights reserved.
 * @license    GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.html.pagination');

/**
 * override pagination for ajax display
 *
 * @package  Redevent.Library
 * @since    2.5
 */
class RedeventAjaxPagination extends JPagination
{
	/**
	 * Method to create an active pagination link to the item
	 *
	 * @param   JPaginationObject  &$item  The object with which to make an active link.
	 *
	 * @return   string  HTML link
	 *
	 * @since    11.1
	 */
	protected function _item_active(&$item)
	{
		RHelperAsset::load('ajaxnav.js');

		if ($item->base > 0)
		{
			return "<a href=\"#\" title=\"" . $item->text . "\" class=\"itemnav\"  startvalue=\"" . $item->base . "\">" . $item->text . "</a>";
		}
		else
		{
			return "<a href=\"#\" title=\"" . $item->text . "\" class=\"itemnav\"  startvalue=\"0\">" . $item->text . "</a>";
		}
	}
}
