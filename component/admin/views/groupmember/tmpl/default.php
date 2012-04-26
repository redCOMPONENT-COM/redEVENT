<?php
/**
 * @version 1.0 $Id: default.php 1586 2009-11-17 16:39:21Z julien $
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

JHTML::_('behavior.tooltip');
?>

<script language="javascript" type="text/javascript">
	function submitbutton(task)
	{

		var form = document.adminForm;

		if (task == 'cancel') {
			submitform( task );
			return;
		}
		else {
			submitform( task );
		}
	}
</script>

<form action="index.php" method="post" name="adminForm" id="adminForm">

<fieldset class="adminform"><legend><?php echo JText::_('COM_REDEVENT_Group_member' ); ?></legend>

<table class="editevent">
	<tr>
		<td width="100" align="right" class="key">
			<?php echo $this->form->getLabel('member'); ?>
		</td>
		<td>
			<?php echo $this->form->getInput('member'); ?>
		</td>
	</tr>
	<tr>
		<td width="100" align="right" class="key hasTip" title="<?php echo JText::_('COM_REDEVENT_MEMBER_IS_ADMIN' ).'::'.JText::_('COM_REDEVENT_MEMBER_IS_ADMIN_TIP' ); ?>">
			<label for="is_admin"> <?php echo JText::_('COM_REDEVENT_MEMBER_IS_ADMIN' ); ?>:
		</label></td>
		<td>
			<?php echo $this->lists['is_admin']; ?>
		</td>
	</tr>
	<tr>
		<td width="100" align="right" class="key hasTip" title="<?php echo JText::_('COM_REDEVENT_MEMBER_MANAGES_EVENTS' ).'::'.JText::_('COM_REDEVENT_MEMBER_MANAGES_EVENTS_TIP' ); ?>">
			<label for="is_admin"> <?php echo JText::_('COM_REDEVENT_MEMBER_MANAGES_EVENTS' ); ?>:
		</label></td>
		<td>
			<?php echo $this->lists['manage_events']; ?>
		</td>
	</tr>
	<tr>
		<td width="100" align="right" class="key hasTip" title="<?php echo JText::_('COM_REDEVENT_MEMBER_MANAGES_XREFS' ).'::'.JText::_('COM_REDEVENT_MEMBER_MANAGES_XREFS_TIP' ); ?>">
			<label for="is_admin"> <?php echo JText::_('COM_REDEVENT_MEMBER_MANAGES_XREFS' ); ?>:
		</label></td>
		<td>
			<?php echo $this->lists['manage_xrefs']; ?>
		</td>
	</tr>
	<tr>
		<td width="100" align="right" class="key hasTip" title="<?php echo JText::_( 'COM_REDEVENT_GROUPMEMBERS_MANAGE_ATTENDEES' ).'::'.JText::_( 'COM_REDEVENT_GROUPMEMBERS_MANAGE_ATTENDEES_TIP' ); ?>">
			<label for="is_admin"> <?php echo JText::_( 'COM_REDEVENT_GROUPMEMBERS_MANAGE_ATTENDEES' ); ?>:
		</label></td>
		<td>
			<?php echo $this->lists['manage_attendees']; ?>
		</td>
	</tr>
	<tr>
		<td width="100" align="right" class="key hasTip" title="<?php echo JText::_('COM_REDEVENT_MEMBER_MANAGES_VENUES' ).'::'.JText::_('COM_REDEVENT_MEMBER_MANAGES_VENUES_TIP' ); ?>">
			<label for="edit_venues"> <?php echo JText::_('COM_REDEVENT_MEMBER_MANAGES_VENUES' ); ?>:
		</label></td>
		<td>
			<?php echo $this->lists['edit_venues']; ?>
		</td>
	</tr>
	<tr>
		<td width="100" align="right" class="key hasTip" title="<?php echo JText::_('COM_REDEVENT_MEMBER_PUBLISH_EVENTS' ).'::'.JText::_('COM_REDEVENT_MEMBER_PUBLISH_EVENTS_TIP' ); ?>">
			<label for="publish_events"> <?php echo JText::_('COM_REDEVENT_MEMBER_PUBLISH_EVENTS' ); ?>:
		</label></td>
		<td>
			<?php echo $this->lists['publish_events']; ?>
		</td>
	</tr>
	<tr>
		<td width="100" align="right" class="key hasTip" title="<?php echo JText::_('COM_REDEVENT_MEMBER_PUBLISH_VENUES' ).'::'.JText::_('COM_REDEVENT_MEMBER_PUBLISH_VENUES_TIP' ); ?>">
			<label for="publish_venues"> <?php echo JText::_('COM_REDEVENT_MEMBER_PUBLISH_VENUES' ); ?>:
		</label></td>
		<td>
			<?php echo $this->lists['publish_venues']; ?>
		</td>
	</tr>
	<tr>
		<td width="100" align="right" class="key hasTip" title="<?php echo JText::_('COM_REDEVENT_MEMBER_RECEIVE_REGISTRATIONS' ).'::'.JText::_('COM_REDEVENT_MEMBER_RECEIVE_REGISTRATIONS_TIP' ); ?>">
			<label for="is_admin"> <?php echo JText::_('COM_REDEVENT_MEMBER_RECEIVE_REGISTRATIONS' ); ?>:
		</label></td>
		<td>
			<?php echo $this->lists['receive_registrations']; ?>
		</td>
	</tr>
</table>
	
</fieldset>

<?php if ($this->row->groups): ?>
<fieldset class="adminform">
<legend><?php echo JText::_('COM_REDEVENT_GROUPMEMBER_CURRENT_GROUPS'); ?></legend>
<table class="adminlist">
<thead>
	<tr>
		<th><?php echo JText::_('COM_REDEVENT_GROUP_NAME'); ?></th>
	</tr>
</thead>
<tbody>
	<?php foreach ($this->row->groups as $group): ?>
	<tr>
		<td><?php echo $group->name; ?></td>
	</tr>
	<?php endforeach; ?>
</tbody>
</table>
</fieldset>
<?php endif; ?>

<?php echo JHTML::_( 'form.token' ); ?>
<input type="hidden" name="option" value="com_redevent" />
<input type="hidden" name="controller" value="groupmembers" />
<input type="hidden" name="view" value="groupmember" />
<input type="hidden" name="id" value="<?php echo $this->row->id; ?>" />
<input type="hidden" name="group_id" value="<?php echo $this->group_id; ?>" />
<input type="hidden" name="task" value="" />
</form>

<?php
//keep session alive while editing
JHTML::_('behavior.keepalive');
?>