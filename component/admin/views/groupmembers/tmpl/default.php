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
?>

<form action="index.php" method="post" name="adminForm" id="adminForm">

<table class="adminform">
	<tr>
		<td width="100%">
			<?php echo JText::_('COM_REDEVENT_SEARCH' );?>
			<input type="text" name="search" id="search" value="<?php echo $this->lists['search']; ?>" class="text_area" onChange="document.adminForm.submit();" />
			<button onclick="this.form.submit();"><?php echo JText::_('COM_REDEVENT_Go' ); ?></button>
			<button onclick="this.form.getElementById('search').value='';this.form.submit();"><?php echo JText::_('COM_REDEVENT_Reset' ); ?></button>
		</td>
	</tr>
</table>

<table class="adminlist" cellspacing="1">
	<thead>
		<tr>
			<th width="20">#</th>
			<th width="20"><input type="checkbox" name="toggle" value="" onClick="checkAll(<?php echo count( $this->rows ); ?>);" /></th>
			<th class="title"><?php echo JHTML::_('grid.sort', 'COM_REDEVENT_NAME', 'name', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
			<th><?php echo JHTML::_('grid.sort', 'COM_REDEVENT_USERNAME', 'username', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
			<th width="30"><?php echo JText::_('COM_REDEVENT_Admin' ); ?></th>
			<th width="30"><?php echo JText::_('COM_REDEVENT_Manage_events' ); ?></th>
			<th width="30"><?php echo JText::_('COM_REDEVENT_Manage_events_dates' ); ?></th>
			<th width="30"><?php echo JText::_('COM_REDEVENT_Manage_Venues' ); ?></th>
			<th width="30"><?php echo JText::_( 'COM_REDEVENT_GROUPMEMBERS_MANAGE_ATTENDEES' ); ?></th>
			<th width="30"><?php echo JText::_('COM_REDEVENT_Get_registrations' ); ?></th>
		</tr>
	</thead>

	<tfoot>
		<tr>
			<td colspan="10">
				<?php echo $this->pageNav->getListFooter(); ?>
			</td>
		</tr>
	</tfoot>

	<tbody>
		<?php
		$k = 0;
		for($i=0, $n=count( $this->rows ); $i < $n; $i++) {
			$row = &$this->rows[$i];

			$link 		= 'index.php?option=com_redevent&amp;controller=groupmembers&amp;task=edit&amp;group_id='. $this->group_id .'&amp;cid[]='.$row->id;
			$checked 	= JHTML::_('grid.checkedout', $row, $i );
   		?>
		<tr class="<?php echo "row$k"; ?>">
			<td><?php echo $this->pageNav->getRowOffset( $i ); ?></td>
			<td><?php echo $checked; ?></td>
			<td>
				<?php
					if ( $row->checked_out && ( $row->checked_out != $this->user->get('id') ) ) {
						echo htmlspecialchars($row->name, ENT_QUOTES, 'UTF-8');
					} else {
				?>
				<span class="editlinktip hasTip" title="<?php echo JText::_('COM_REDEVENT_EDIT_MEMBER' );?>::<?php echo $row->name; ?>">
				<a href="<?php echo $link; ?>">
				<?php echo htmlspecialchars($row->name, ENT_QUOTES, 'UTF-8'); ?>
				</a></span>
				<?php } ?>
			</td>
			<td><?php echo $row->username; ?></td>
			<td style="text-align:center;">
			<?php if ($row->is_admin): ?>
				<?php echo JHTML::_(	'image', 'administrator/components/com_redevent/assets/images/ok.png',
																	         JText::_('COM_REDEVENT_Yes' ), 
																	         'title= "'. JText::_('COM_REDEVENT_yes' ) . '"' ); ?>
			<?php else: ?>
				<?php echo JHTML::_(	'image', 'administrator/components/com_redevent/assets/images/no.png',
																	         JText::_('COM_REDEVENT_No' ), 
																	         'title= "'. JText::_('COM_REDEVENT_No' ) . '"' ); ?>
			<?php endif; ?>
			</td>
			<td style="text-align:center;">
			<?php if ($row->manage_events): ?>
				<?php echo JHTML::_(	'image', 'administrator/components/com_redevent/assets/images/ok.png',
																	         JText::_('COM_REDEVENT_Yes' ), 
																	         'title= "'. JText::_('COM_REDEVENT_yes' ) . '"' ); ?>
			<?php else: ?>
				<?php echo JHTML::_(	'image', 'administrator/components/com_redevent/assets/images/no.png',
																	         JText::_('COM_REDEVENT_No' ), 
																	         'title= "'. JText::_('COM_REDEVENT_No' ) . '"' ); ?>
			<?php endif; ?>
			</td>
			<td style="text-align:center;">
			<?php if ($row->manage_xrefs): ?>
				<?php echo JHTML::_(	'image', 'administrator/components/com_redevent/assets/images/ok.png',
																	         JText::_('COM_REDEVENT_Yes' ), 
																	         'title= "'. JText::_('COM_REDEVENT_yes' ) . '"' ); ?>
			<?php else: ?>
				<?php echo JHTML::_(	'image', 'administrator/components/com_redevent/assets/images/no.png',
																	         JText::_('COM_REDEVENT_No' ), 
																	         'title= "'. JText::_('COM_REDEVENT_No' ) . '"' ); ?>
			<?php endif; ?>
			</td>
			<td style="text-align:center;">
			<?php if ($row->edit_venues): ?>
				<?php echo JHTML::_(	'image', 'administrator/components/com_redevent/assets/images/ok.png',
																	         JText::_('COM_REDEVENT_Yes' ), 
																	         'title= "'. JText::_('COM_REDEVENT_yes' ) . '"' ); ?>
			<?php else: ?>
				<?php echo JHTML::_(	'image', 'administrator/components/com_redevent/assets/images/no.png',
																	         JText::_('COM_REDEVENT_No' ), 
																	         'title= "'. JText::_('COM_REDEVENT_No' ) . '"' ); ?>
			<?php endif; ?>
			</td>
			<td style="text-align:center;">
			<?php if ($row->manage_attendees == 0): ?>
				<?php echo JHTML::_(	'image', 'administrator/components/com_redevent/assets/images/no.png',
																	         JText::_('COM_REDEVENT_No' ), 
																	         'title= "'. JText::_('COM_REDEVENT_No' ) . '"' ); ?>
			<?php elseif ($row->manage_attendees == 1): ?>
				<?php echo JText::_( 'COM_REDEVENT_GROUPMEMBERS_MANAGE_ATTENDEES_VIEW' ); ?>
			<?php elseif ($row->manage_attendees == 2): ?>
				<?php echo JText::_( 'COM_REDEVENT_GROUPMEMBERS_MANAGE_ATTENDEES_EDIT' ); ?>
			<?php endif; ?>
			</td>
			<td style="text-align:center;">
			<?php if ($row->receive_registrations): ?>
				<?php echo JHTML::_(	'image', 'administrator/components/com_redevent/assets/images/ok.png',
																	         JText::_('COM_REDEVENT_Yes' ), 
																	         'title= "'. JText::_('COM_REDEVENT_yes' ) . '"' ); ?>
			<?php else: ?>
				<?php echo JHTML::_(	'image', 'administrator/components/com_redevent/assets/images/no.png',
																	         JText::_('COM_REDEVENT_No' ), 
																	         'title= "'. JText::_('COM_REDEVENT_No' ) . '"' ); ?>
			<?php endif; ?>
			</td>
		</tr>
		<?php $k = 1 - $k;  } ?>

	</tbody>

</table>

<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="option" value="com_redevent" />
<input type="hidden" name="controller" value="groupmembers" />
<input type="hidden" name="view" value="groupmembers" />
<input type="hidden" name="group_id" value="<?php echo $this->group_id; ?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
</form>