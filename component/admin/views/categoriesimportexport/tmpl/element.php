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
$function = $this->input->get('function');
?>

<form action="index.php?option=com_redevent&view=categories&layout=element&tmpl=component&function=<?php echo $function; ?>" method="post" name="adminForm" id="adminForm">

<table class="adminform">
	<tr>
		<td width="100%">
			<?php echo JText::_('COM_REDEVENT_SEARCH' ); ?>
			<input type="text" name="search" id="search" value="<?php echo $this->lists['search']; ?>" class="text_area" onChange="document.adminForm.submit();" />
			<button onclick="this.form.submit();"><?php echo JText::_('COM_REDEVENT_Go' ); ?></button>
			<button onclick="this.form.getElementById('search').value='';this.form.submit();"><?php echo JText::_('COM_REDEVENT_Reset' ); ?></button>
		</td>
		<td nowrap="nowrap">
		<?php  echo $this->lists['state']; ?>
			<select name="language" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_LANGUAGE');?></option>
				<?php echo JHtml::_('select.options', JHtml::_('contentlanguage.existing', true, true), 'value', 'text', $this->state->get('language')); ?>
			</select>
			</td>
	</tr>
</table>

<table class="adminlist" cellspacing="1">
	<thead>
		<tr>
			<th width="7">#</th>
			<th align="left" class="title"><?php echo JHTML::_('grid.sort', 'COM_REDEVENT_CATEGORY', 'catname', $this->lists['order_Dir'], $this->lists['order'], 'categoryelement' ); ?></th>
			<th width="1%" nowrap="nowrap"><?php echo JText::_('COM_REDEVENT_ACCESS' ); ?></th>
			<th width="1%" nowrap="nowrap"><?php echo JText::_('COM_REDEVENT_PUBLISHED' ); ?></th>
		</tr>
	</thead>

	<tfoot>
		<tr>
			<td colspan="4">
				<?php echo $this->pageNav->getListFooter(); ?>
			</td>
		</tr>
	</tfoot>

	<tbody>
		<?php
		$k = 0;
		for ($i=0, $n=count($this->rows); $i < $n; $i++) {
			$row = $this->rows[$i];

			if (!$row->access) {
				$access = 'Public';
			} else if ($row->access == 1) {
				$access = 'Registered';
			} else {
				$access = 'Special';
			}
   		?>
		<tr class="<?php echo "row$k"; ?>">
			<td width="7"><?php echo $this->pageNav->getRowOffset( $i ); ?></td>
			<td align="left">
				<span class="editlinktip hasTip" title="<?php echo JText::_('COM_REDEVENT_SELECT' );?>::<?php echo $row->catname; ?>">
        <a class="pointer" onclick="if (window.parent) window.parent.<?php echo $this->escape($function);?>('<?php echo $row->id; ?>', '<?php echo $this->escape(addslashes($row->catname)); ?>');">
        	<?php echo $this->escape($row->catname); ?>
        </a>
        </span>
			</td>
			<td align="center"><?php echo $access; ?></td>
			<td align="center">
				<?php
				$img = $row->published ? 'tick.png' : 'publish_x.png';
				$alt = $row->published ? 'Published' : 'Unpublished';
				echo JHTML::_('image', 'admin/'.$img, $alt, '', true);
				?>
			</td>
		</tr>
			<?php $k = 1 - $k; } ?>
	</tbody>

</table>

<input type="hidden" name="task" value="">
<input type="hidden" name="tmpl" value="component">
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="" />
</form>