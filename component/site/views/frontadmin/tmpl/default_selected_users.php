<?php
/**
 * @package    Redevent
 * @copyright  redEVENT (C) 2008 redCOMPONENT.com / EventList (C) 2005 - 2008 Christoph Lukes
 * @license    GNU/GPL, see LICENSE.php
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
?>
<div id="selected_users">
	<h2><?php echo JText::_('COM_REDEVENT_FRONTEND_ADMIN_SELECTED_USERS'); ?></h2>

	<div id="select-list" class="nouser">
		<div class="notice">
			<div><i class="icon-info-sign"></i> <?php echo JText::_('COM_REDEVENT_FRONTEND_ADMIN_SELECT_COURSE_TO_SELECT_USERS')?></div>
			<div><?php echo JText::_('COM_REDEVENT_FRONTEND_ADMIN_SELECT_COURSE_TO_SELECT_USERS_DESC')?></div>
		</div>
	</div>
	<button type="button" id="add-employee" class="btn"><?php echo JText::_('COM_REDEVENT_FRONTEND_ADMIN_ADD_EMPLOYEE')?></button>
</div>