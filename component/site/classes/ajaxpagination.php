<?php
/**
 * @version 1.0 $Id: image.class.php 298 2009-06-24 07:42:35Z julien $
 * @package Joomla
 * @subpackage redEVENT
 * @copyright redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license GNU/GPL, see LICENSE.php
 * redEVENT is based on EventList made by Christoph Lukes from schlu.net
 * redEVENT can be downloaded from www.redcomponent.com
 * redEVENT is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * redEVENT is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with redEVENT; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.html.pagination');

/**
 * override pagination for ajax display
 *
 * @package Joomla
 * @subpackage redEVENT
 */
class REAjaxPagination extends JPagination
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
		FOFTemplateUtils::addJS('media://com_redevent/js/ajaxnav.js');

		$app = JFactory::getApplication();
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