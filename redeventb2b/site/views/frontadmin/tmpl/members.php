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
<table id="members-tbl" class="table">
	<thead>
	<tr>
		<th><?php echo RedeventHelper::ajaxSortColumn(JText::_('COM_REDEVENT_FRONTEND_ADMIN_USER_NAME'), 'u.name', $this->members_order_dir, $this->members_order); ?></th>
		<th><?php echo RedeventHelper::ajaxSortColumn(JText::_('COM_REDEVENT_FRONTEND_ADMIN_USER_EMAIL'), 'u.email', $this->members_order_dir, $this->members_order); ?></th>
		<th><?php echo JText::_('COM_REDEVENT_FRONTEND_ADMIN_EDIT_MEMBER'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php if ($this->members): ?>
		<?php foreach($this->members as $a): ?>
			<tr uid="<?php echo $a->id; ?>">
				<td class="member-name"><?php echo $a->name; ?></td>
				<td><?php echo $a->email; ?></td>
				<td>
					<?php echo JHTML::image('com_redevent/b2b-edit.png', 'edit'
							, array('class' => 'hasTip editmember'
							, 'title' => JText::_('COM_REDEVENT_EDIT_PARTICIPANT')
							,  'tip' => JText::_('COM_REDEVENT_EDIT_PARTICIPANT_TIP')), true
					); ?>
				</td>
			</tr>
		<?php endforeach;?>
	<?php endif; ?>
	</tbody>
</table>

<!--pagination-->
<div class="pagination">
	<div class="pagination-label"><?php echo JText::_('COM_REDEVENT_FRONTADMIN_PAGINATION_SELECT_LIMIT'); ?></div>
	<div class="styled-select-admin">
		<?php echo $this->getLimitBox(); ?>
	</div>
	<?php if (($this->members_pagination->get('pages.total') > 1)) : ?>
		<?php echo $this->members_pagination->getPagesLinks(); ?>
	<?php  endif; ?>
</div>
<!-- pagination end -->
