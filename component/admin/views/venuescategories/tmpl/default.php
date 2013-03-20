<?php
/**
 * @version 1.0 $Id: default.php 160 2009-05-29 16:16:39Z julien $
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

<form action="index.php" method="post" name="adminForm" id="adminForm">

	<table class="adminform">
		<tr>
			<td width="100%">
			  	<?php echo JText::_('COM_REDEVENT_SEARCH' ); ?>
				<input type="text" name="search" id="search" value="<?php echo $this->lists['search']; ?>" class="text_area" onChange="document.adminForm.submit();" />
				<button onclick="this.form.submit();"><?php echo JText::_('COM_REDEVENT_Go' ); ?></button>
				<button onclick="this.form.getElementById('search').value='';this.form.submit();"><?php echo JText::_('COM_REDEVENT_Reset' ); ?></button>
			</td>
			<td nowrap="nowrap">
			  <?php
			  echo $this->lists['state'];
				?>

				<select name="filter_language" class="inputbox" onchange="this.form.submit()">
					<option value=""><?php echo JText::_('JOPTION_SELECT_LANGUAGE');?></option>
					<?php echo JHtml::_('select.options', JHtml::_('contentlanguage.existing', true, true), 'value', 'text', $this->state->get('filter_language')); ?>
				</select>
			</td>
		</tr>
	</table>

	<table class="adminlist" cellspacing="1">
	<thead>
		<tr>
			<th width="5">#</th>
			<th width="5"><input type="checkbox" name="toggle" value="" onClick="checkAll(<?php echo count( $this->rows ); ?>);" /></th>
			<th class="title"><?php echo JHTML::_('grid.sort', 'COM_REDEVENT_CATEGORY', 'name', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
			<th width="20%"><?php echo JHTML::_('grid.sort', 'COM_REDEVENT_ALIAS', 'c.alias', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
			<th width="20%"><?php echo JHTML::_('grid.sort', 'COM_REDEVENT_PARENT_CATEGORY', 'c.lft', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
			<th width="15%"><?php echo JHTML::_('grid.sort', 'COM_REDEVENT_GROUP', 'gr.name', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
			<th width="1%" nowrap="nowrap"><?php echo JText::_('COM_REDEVENT_VENUES' ); ?></th>
			<th width="1%" nowrap="nowrap"><?php echo JText::_('COM_REDEVENT_PUBLISHED' ); ?></th>
			<th width="7%"><?php echo JHTML::_('grid.sort', 'COM_REDEVENT_ACCESS', 'c.access', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
			<th width="80"><?php echo JHTML::_('grid.sort', 'COM_REDEVENT_REORDER', 'c.ordering', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
			<th width="1%"><?php echo JHTML::_('grid.order', $this->rows, 'filesave.png', 'saveordercat' ); ?></th>
			<th width="1%" nowrap="nowrap"><?php echo JHTML::_('grid.sort', 'JGRID_HEADING_LANGUAGE', 'c.language', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
			<th width="1%" nowrap="nowrap"><?php echo JHTML::_('grid.sort', 'COM_REDEVENT_ID', 'c.id', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
		</tr>
	</thead>

	<tfoot>
		<tr>
			<td colspan="20">
				<?php echo $this->pageNav->getListFooter(); ?>
			</td>
		</tr>
	</tfoot>

	<tbody>
		<?php
		$k = 0;
		for ($i=0, $n=count($this->rows); $i < $n; $i++) {
			$row = $this->rows[$i];

			$link 		= 'index.php?option=com_redevent&amp;controller=venuescategories&view=venuescategory&amp;task=edit&amp;cid[]='. $row->id;
			$grouplink 	= 'index.php?option=com_redevent&amp;controller=groups&view=group&amp;task=edit&amp;cid[]='. $row->groupid;
			$published 	= JHTML::_('grid.published', $row, $i );
			$checked 	= JHTML::_('grid.checkedout', $row, $i );
   		?>
		<tr class="<?php echo "row$k"; ?>">
			<td><?php echo $this->pageNav->getRowOffset( $i ); ?></td>
			<td width="7"><?php echo $checked; ?></td>
			<td align="left">
			<?php if ($this->filter_order == 'c.lft') :?>
			<?php echo str_repeat('-', $row->depth). ' '; ?>
			<?php endif;?>
				<?php
				if ( $row->checked_out && ( $row->checked_out != $this->user->get('id') ) ) {
					echo htmlspecialchars($row->name, ENT_QUOTES, 'UTF-8');
				} else {
				?>
					<span class="editlinktip hasTip" title="<?php echo JText::_('COM_REDEVENT_EDIT_CATEGORY' );?>::<?php echo $row->name; ?>">
					<a href="<?php echo $link; ?>">
					<?php echo htmlspecialchars($row->name, ENT_QUOTES, 'UTF-8'); ?>
					</a></span>
				<?php
				}
				?>
			</td>
			<td>
				<?php
				if (JString::strlen($row->alias) > 25) {
					echo JString::substr( htmlspecialchars($row->alias, ENT_QUOTES, 'UTF-8'), 0 , 25).'...';
				} else {
					echo htmlspecialchars($row->alias, ENT_QUOTES, 'UTF-8');
				}
				?>
			</td>
			<td>
				<?php echo ($row->parent_name) ? htmlspecialchars($row->parent_name, ENT_QUOTES, 'UTF-8') : '-'; ?>
			</td>
			<td align="center">
				<?php if ($row->catgroup) {	?>
					<span class="editlinktip hasTip" title="<?php echo JText::_('COM_REDEVENT_EDIT_GROUP' );?>::<?php echo $row->catgroup; ?>">
					<a href="<?php echo $grouplink; ?>">
						<?php echo htmlspecialchars($row->catgroup, ENT_QUOTES, 'UTF-8'); ?>
					</a></span>
				<?php
				} else {
					echo '-';
				}
				?>
			</td>
			<td align="center">
				<?php echo $row->assignedvenues; ?>
			</td>
			<td align="center">
				<?php echo $published; ?>
			</td>
			<td align="center">
				<?php echo $row->access_level; ?>
			</td>
			<td class="order" colspan="2">
				<span><?php echo $this->pageNav->orderUpIcon( $i, true, 'orderup', 'Move Up', $this->ordering ); ?></span>

				<span><?php echo $this->pageNav->orderDownIcon( $i, $n, true, 'orderdown', 'Move Down', $this->ordering );?></span>

				<?php $disabled = $this->ordering ?  '' : '"disabled=disabled"'; ?>

				<input type="text" name="order[]" size="5" value="<?php echo $row->ordering; ?>" <?php echo $disabled; ?> class="text_area" style="text-align: center" />
			</td>
			<td align="center"><?php echo $row->language == '*' ? Jtext::_('All') : $row->language_title; ?></td>
			<td align="center"><?php echo $row->id; ?></td>
		</tr>
		<?php $k = 1 - $k; } ?>
	</tbody>

	</table>

	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="option" value="com_redevent" />
	<input type="hidden" name="controller" value="venuescategories" />
	<input type="hidden" name="view" value="venuescategories" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
</form>