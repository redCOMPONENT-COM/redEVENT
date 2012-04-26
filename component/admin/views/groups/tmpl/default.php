<?php
/**
 * @version 1.0 $Id$
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
			<th width="5">#</th>
			<th width="20"><input type="checkbox" name="toggle" value="" onClick="checkAll(<?php echo count( $this->rows ); ?>);" /></th>
			<th width="30%" class="title"><?php echo JHTML::_('grid.sort', 'COM_REDEVENT_GROUP_NAME', 'name', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
			<th><?php echo JText::_('COM_REDEVENT_DESCRIPTION' ); ?></th>
			<th width="5"><?php echo JText::_('COM_REDEVENT_Default' ); ?></th>
			<th width="5"><?php echo JText::_('COM_REDEVENT_Members' ); ?></th>
			<th width="5"><?php echo JText::_('COM_REDEVENT_Group_ACL' ); ?></th>
		</tr>
	</thead>

	<tfoot>
		<tr>
			<td colspan="7">
				<?php echo $this->pageNav->getListFooter(); ?>
			</td>
		</tr>
	</tfoot>

	<tbody>
		<?php
		$k = 0;
		for($i=0, $n=count( $this->rows ); $i < $n; $i++) {
			$row = &$this->rows[$i];

			$link 		= 'index.php?option=com_redevent&amp;controller=groups&amp;task=edit&amp;cid[]='.$row->id;
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
				<span class="editlinktip hasTip" title="<?php echo JText::_('COM_REDEVENT_EDIT_GROUP' );?>::<?php echo $row->name; ?>">
				<a href="<?php echo $link; ?>">
				<?php echo htmlspecialchars($row->name, ENT_QUOTES, 'UTF-8'); ?>
				</a></span>
				<?php } ?>
			</td>
			<td><?php echo htmlspecialchars($row->description, ENT_QUOTES, 'UTF-8'); ?></td>
			<td><?php echo ($row->isdefault ? 'yes' : 'no'); ?></td>
			<td style="text-align:center;"><?php echo JHTML::link('index.php?option=com_redevent&amp;controller=groups&amp;task=editmembers&amp;group_id='.$row->id,
			                            $row->members . ' ' .	JHTML::_(	'image', 'administrator/components/com_redevent/assets/images/groupmembers.png',
																	         JText::_('COM_REDEVENT_Edit_group_members' ), 
																	         'title= "'. JText::_('COM_REDEVENT_Edit_group_members' ) . '"' )); ?>
			</td>
			<td style="text-align:center;"><?php echo JHTML::link('index.php?option=com_redevent&amp;controller=groups&amp;task=groupacl&amp;group_id='.$row->id, 
																	JHTML::_(	'image', 'administrator/components/com_redevent/assets/images/icon-16-categories.png',
																	         JText::_('COM_REDEVENT_Edit_group_ACL' ), 
																	         'title= "'. JText::_('COM_REDEVENT_Edit_group_ACL' ) . '"' )); ?>
			</td>
		</tr>
		<?php $k = 1 - $k;  } ?>

	</tbody>

</table>

<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="option" value="com_redevent" />
<input type="hidden" name="controller" value="groups" />
<input type="hidden" name="view" value="groups" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
</form>