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
		<th><?php echo JText::_('COM_REDEVENT_FRONTEND_ADMIN_USER_NAME'); ?></th>
		<th><?php echo JText::_('COM_REDEVENT_FRONTEND_ADMIN_USER_EMAIL'); ?></th>
		<th><?php echo JText::_('COM_REDEVENT_FRONTEND_ADMIN_USER_PO_NUMBER'); ?></th>
		<th><?php echo JText::_('COM_REDEVENT_FRONTEND_ADMIN_USER_COMMENTS'); ?></th>
		<th><?php echo JText::_('COM_REDEVENT_FRONTEND_ADMIN_EDIT_MEMBER'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php if ($this->attendees): ?>
		<?php foreach($this->attendees as $a): ?>
			<tr<?php echo ($a->registered ? ' class="registered"' : ''); ?> rid="<?php echo $a->registered ? $a->registered->id : ''; ?>" uid="<?php echo $a->id; ?>">
				<td class="attendee-name"><?php echo $a->name; ?></td>
				<td><?php echo $a->email; ?></td>

				<?php if ($a->registered): ?>
					<td>
						<input name="ponumber[]" class="input-small ponumber" type="text" maxlength="10" value="<?php echo $a->registered->ponumber; ?>" />
					</td>
					<td>
					<textarea name="comments[]" class="input-medium comments hasTip"
					          title="<?php echo JText::_('COM_REDEVENT_FRONTEND_ADMIN_USER_COMMENTS'); ?>"
					          tip="<?php echo nl2br($a->registered->comments); ?>"
					          rows="1" cols="30"><?php echo trim($a->registered->comments); ?></textarea>
					</td>
				<?php else: ?>
					<td></td>
					<td></td>
				<?php endif; ?>

				<td>
					<?php
					if ($a->registered)
					{
						if ($a->pastCancellationPeriod)
						{
							$cancelTip = JText::sprintf(
								'COM_REDEVENT_FRONTEND_ADMIN_PAST_CANCEL_REGISTRATION_PERIOD_D_TIP',
								$a->cancellationPeriod
							);
						}
						else
						{
							$cancelTip = JText::_('COM_REDEVENT_FRONTEND_ADMIN_CONFIRM');
						}
					}
					?>
					<?php echo JHTML::image('com_redevent/b2b-edit.png', 'edit'
							, array('class' => 'hasTip editmember'
							, 'title' => JText::_('COM_REDEVENT_EDIT_PARTICIPANT')
							,  'tip' => JText::_('COM_REDEVENT_EDIT_PARTICIPANT_TIP')), true)
						. ($a->registered ? ' '	. JHTML::image('com_redevent/b2b-delete.png', 'remove'
								, array('class' => 'unregister hasTip'
								, 'title' => JText::_('COM_REDEVENT_FRONTEND_ADMIN_CANCEL_REGISTRATION')
								, 'tip' => JText::_('COM_REDEVENT_FRONTEND_ADMIN_CANCEL_REGISTRATION_TIP')
								, 'confirmtext' => $cancelTip), true) : ''); ?>
				</td>
			</tr>
		<?php endforeach;?>
	<?php endif; ?>
	</tbody>
</table>
