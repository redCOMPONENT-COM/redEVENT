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
<table id="attendees-tbl" class="table">
	<thead>
		<tr>
			<th><?php echo JText::_('COM_REDEVENT_FRONTEND_ADMIN_SELECT_MEMBER'); ?></th>
			<th><?php echo redEVENTHelper::ajaxSortColumn(JText::_('COM_REDEVENT_FRONTEND_ADMIN_USER_NAME'), 'u.name', $this->members_order_dir, $this->members_order); ?></th>
			<th><?php echo redEVENTHelper::ajaxSortColumn(JText::_('COM_REDEVENT_FRONTEND_ADMIN_USER_USERNAME'), 'u.username', $this->members_order_dir, $this->members_order); ?></th>
			<th><?php echo redEVENTHelper::ajaxSortColumn(JText::_('COM_REDEVENT_FRONTEND_ADMIN_USER_EMAIL'), 'u.email', $this->members_order_dir, $this->members_order); ?></th>
			<th><?php echo redEVENTHelper::ajaxSortColumn(JText::_('COM_REDEVENT_FRONTEND_ADMIN_USER_LANGUAGE'), 'l.name', $this->members_order_dir, $this->members_order); ?></th>
			<th><?php echo JText::_('COM_REDEVENT_FRONTEND_ADMIN_USER_STATUS'); ?></th>
			<th><?php echo JText::_('COM_REDEVENT_FRONTEND_ADMIN_USER_PO_NUMBER'); ?></th>
			<th><?php echo JText::_('COM_REDEVENT_FRONTEND_ADMIN_USER_COMMENTS'); ?></th>
			<th><?php echo JText::_('COM_REDEVENT_FRONTEND_ADMIN_EDIT_MEMBER'); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($this->attendees as $a): ?>
		<tr<?php echo ($a->registered ? ' class="registered"' : ''); ?> rid="<?php echo $a->registered ? $a->registered->id : ''; ?>" uid="<?php echo $a->id; ?>">
			<td>
				<?php if (!$a->registered): ?>
				<input name="cid[]" id="cid<?php echo $a->id; ?>" class="attendee-sel" type="checkbox"/>
				<?php endif; ?>
			</td>
			<td class="attendee-name"><?php echo $a->name; ?></td>
			<td><?php echo $a->username; ?></td>
			<td><?php echo $a->email; ?></td>
			<td><?php echo JFactory::getUser($a->id)->getParameters()->get('language'); ?></td>
			<?php if ($a->registered): ?>
			<?php
			$imgstatus = $a->registered->waitinglist ?
				JHtml::image('media/com_redevent/images/b2b-waiting.png', 'waiting',
					array('class' => "hasTip", 'title' => JText::_('COM_REDEVENT_WAITING_LIST'))) :
				JHtml::image('media/com_redevent/images/b2b-attending.png', 'attending',
					array('class' => "hasTip", 'title' => JText::_('COM_REDEVENT_ATTENDING')));
			?>
			<td><?php echo $imgstatus; ?></td>
			<td><input name="ponumber[]" class="input-small ponumber" type="text" value="<?php echo $a->registered->ponumber; ?>" /></td>
			<td><input name="comments[]" class="input-small comments" type="text" value="<?php echo $a->registered->comments; ?>" /></td>
			<?php else: ?>
			<td></td>
			<td></td>
			<td></td>
			<?php endif; ?>
			<td><?php echo JHTML::image('media/com_redevent/images/b2b-edit.png', 'edit'
				, array('class' => 'hasTip editmember'
						, 'title' => JText::_('COM_REDEVENT_EDIT_PARTICIPANT')
						,  'tip' => JText::_('COM_REDEVENT_EDIT_PARTICIPANT_TIP')))
				. ($a->registered ? ' '	. JHTML::image('media/com_redevent/images/b2b-delete.png', 'remove'
					, array('class' => 'unregister hasTip'
							, 'title' => JText::_('COM_REDEVENT_FRONTEND_ADMIN_CANCEL_REGISTRATION')
							, 'tip' => JText::_('COM_REDEVENT_FRONTEND_ADMIN_CANCEL_REGISTRATION_TIP'))) : ''); ?>
			</td>
		</tr>
		<?php endforeach;?>
	</tbody>
</table>
