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

defined('_JEXEC') or die('Restricted access'); ?>

<?php
$user 	=& JFactory::getUser();

//Ordering allowed ?
$ordering = ($this->lists['order'] == 'obj.ordering');
JHTML::_('behavior.tooltip');
?>
<script type="text/javascript">
 /**
  * Overrides default function.
  */
  function submitbutton(pressbutton) {
	  if (pressbutton == 'remove') {
		  if (confirm('<?php echo JText::_('COM_REDEVENT_CONFIRM_CUSTOM_FIELD_DELETE'); ?>')) {
				submitform( pressbutton );
		  }
			return;
		} 
    submitform(pressbutton);
  }
</script>

<form action="<?php echo $this->request_url; ?>" method="post" name="adminForm" id="adminForm">
<table>
<tr>
	<td align="left" width="100%">
		<?php echo JText::_('COM_REDEVENT_Filter' ); ?>:
		<input type="text" name="search" id="search" value="<?php echo $this->lists['search'];?>" class="text_area" onchange="document.adminForm.submit();" />
		<button onclick="this.form.submit();"><?php echo JText::_('COM_REDEVENT_Go' ); ?></button>
		<button onclick="document.getElementById('search').value='';this.form.submit();"><?php echo JText::_('COM_REDEVENT_Reset' ); ?></button>
	</td>
</tr>
</table>
<div id="editcell">
	<table class="adminlist">
	<thead>
		<tr>
			<th width="5">
				#
			</th>
			<th width="20">
				<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->items ); ?>);" />
			</th>
			<th class="title">
				<?php echo JHTML::_('grid.sort', 'COM_REDEVENT_Name', 'obj.name', $this->lists['order_Dir'], $this->lists['order'] ); ?>
			</th>
      <th class="title">
        <?php echo JHTML::_('grid.sort', 'COM_REDEVENT_Tag', 'obj.tag', $this->lists['order_Dir'], $this->lists['order'] ); ?>
      </th>
			<th class="title">
				<?php echo JHTML::_('grid.sort', 'COM_REDEVENT_Assigned', 'obj.object', $this->lists['order_Dir'], $this->lists['order'] ); ?>
			</th>
      <th class="title">
        <?php echo JHTML::_('grid.sort', 'COM_REDEVENT_Type', 'obj.type', $this->lists['order_Dir'], $this->lists['order'] ); ?>
      </th>
      <th class="title">
        <?php echo JHTML::_('grid.sort', 'COM_REDEVENT_Searchable', 'obj.searchable', $this->lists['order_Dir'], $this->lists['order'] ); ?>
      </th>
      <th class="title">
        <?php echo JHTML::_('grid.sort', 'COM_REDEVENT_In_lists', 'obj.in_lists', $this->lists['order_Dir'], $this->lists['order'] ); ?>
      </th>
      <th class="title">
        <?php echo JHTML::_('grid.sort', 'COM_REDEVENT_Frontend_edit', 'obj.frontend_edit', $this->lists['order_Dir'], $this->lists['order'] ); ?>
      </th>
      <th width="5%" nowrap="nowrap"><?php echo JHTML::_('grid.sort', 'COM_REDEVENT_Published', 'p.published', $this->lists['order_Dir'], $this->lists['order'] ); ?>
      </th>
      <th width="8%" nowrap="nowrap">
        <?php echo JHTML::_('grid.sort', 'COM_REDEVENT_Order', 'obj.ordering', $this->lists['order_Dir'], $this->lists['order'] ); ?>
        <?php echo JHTML::_('grid.order',  $this->items ); ?>
      </th>
      <th width="1%" nowrap="nowrap">
        <?php echo JHTML::_('grid.sort', 'COM_REDEVENT_ID', 'obj.id', $this->lists['order_Dir'], $this->lists['order'] ); ?>
      </th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="12">
				<?php echo $this->pagination->getListFooter(); ?>
			</td>
		</tr>
	</tfoot>
	<tbody>
	<?php
	$k = 0;
	for ($i=0, $n=count( $this->items ); $i < $n; $i++)
	{
		$row = &$this->items[$i];

		$link 	= JRoute::_( 'index.php?option=com_redevent&controller=customfield&task=edit&cid[]='. $row->id );

		$checked 	= JHTML::_('grid.checkedout',   $row, $i );
		$published 	= JHTML::_('grid.published', $row, $i );

		?>
		<tr class="<?php echo "row$k"; ?>">
			<td>
				<?php echo $this->pagination->getRowOffset( $i ); ?>
			</td>
			<td>
				<?php echo $checked; ?>
			</td>
			<td>
				<?php
				if (  JTable::isCheckedOut($this->user->get ('id'), $row->checked_out ) ) {
					echo $row->name;
				} else {
				?>
					<a href="<?php echo $link; ?>" title="<?php echo JText::_('COM_REDEVENT_Edit_Individual' ); ?>">
						<?php echo $row->name; ?></a>
				<?php
				}
				?>
			</td>
      <td>
        <?php
        echo $row->tag;
        ?>
      </td>
			<td>
				<?php
				echo $row->object_key;
				?>
			</td>
      <td align="center"><?php echo $row->type;?></td>
      <td align="center"><?php echo ($row->searchable ? JText::_('COM_REDEVENT_Yes') : JText::_('COM_REDEVENT_No'));?></td>
      <td align="center"><?php echo ($row->in_lists ? JText::_('COM_REDEVENT_Yes') : JText::_('COM_REDEVENT_No'));?></td>
      <td align="center"><?php echo ($row->frontend_edit ? JText::_('COM_REDEVENT_Yes') : JText::_('COM_REDEVENT_No'));?></td>
      <td align="center"><?php echo $published;?></td>
      <td class="order">
        <span><?php echo $this->pagination->orderUpIcon( $i, $i > 0 , 'orderup', 'Move Up', $ordering ); ?></span>
        <span><?php echo $this->pagination->orderDownIcon( $i, $n, $i < $n, 'orderdown', 'Move Down', $ordering ); ?></span>
        <?php $disabled = true ?  '' : 'disabled="disabled"'; ?>
        <input type="text" name="order[]" size="5" value="<?php echo $row->ordering;?>" <?php echo $disabled ?> class="text_area" style="text-align: center" />
      </td>
      <td align="center">
        <?php echo $row->id; ?>
      </td>
		</tr>
		<?php
		$k = 1 - $k;
	}
	?>
	</tbody>
	</table>
</div>

<input type="hidden" name="controller" value="customfield" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
</form>