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

// no direct access
defined('_JEXEC') or die('Restricted access');
?>

<script type="text/javascript">

	function tableOrdering( order, dir, view )
	{
		var form = document.getElementById("venueselectform");

		form.filter_order.value 	= order;
		form.filter_order_Dir.value	= dir;
		form.submit( view );
	}
</script>

<div id="redevent" class="re_selectvenue">

<h1 class='componentheading'>
	<?php
		echo JText::_('COM_REDEVENT_SELECTVENUE');
	?>
</h1>

<div class="clear"></div>

<form action="index.php?option=com_redevent&amp;view=editevent&amp;layout=selectvenue&amp;tmpl=component" method="post" id="venueselectform">

<div id="re_filter" class="floattext">
		<div class="re_fleft">
			<?php
			echo '<label for="filter_type">'.JText::_('COM_REDEVENT_FILTER').'</label>&nbsp;';
			echo $this->searchfilter.'&nbsp;';
			?>
			<input type="text" name="filter" id="filter" value="<?php echo $this->filter;?>" class="text_area" onchange="document.getElementById('venueselectform').submit();" />
			<button onclick="document.getElementById('venueselectform').submit();"><?php echo JText::_('COM_REDEVENT_Go' ); ?></button>
			<button onclick="document.getElementById('filter').value='';document.getElementById('venueselectform').submit();"><?php echo JText::_('COM_REDEVENT_Reset' ); ?></button>
		</div>
		<div class="re_fright">
			<?php
			echo '<label for="limit">'.JText::_('COM_REDEVENT_DISPLAY_NUM').'</label>&nbsp;';
			echo $this->pageNav->getLimitBox();
			?>
		</div>

</div>

<table class="eventtable" width="100%" border="0" cellspacing="0" cellpadding="0" summary="eventlist">
	<thead>
		<tr>
			<th width="7" class="sectiontableheader" align="left">#</th>
			<th align="left" class="sectiontableheader" align="left"><?php echo JHTML::_('grid.sort', 'COM_REDEVENT_VENUE', 'l.venue', $this->lists['order_Dir'], $this->lists['order'], 'selectvenue' ); ?></th>
			<th align="left" class="sectiontableheader" align="left"><?php echo JHTML::_('grid.sort', 'COM_REDEVENT_CITY', 'l.city', $this->lists['order_Dir'], $this->lists['order'], 'selectvenue' ); ?></th>
			<th align="left" class="sectiontableheader" align="left"><?php echo JText::_('COM_REDEVENT_COUNTRY' ); ?></th>
		</tr>
	</thead>

	<tbody>
		<?php
		$k = 0;
		for ($i=0, $n=count( $this->rows ); $i < $n; $i++) {
			$row = &$this->rows[$i];
		?>
		<tr class="<?php echo "row$k"; ?>">
			<td><?php echo $this->pageNav->getRowOffset( $i ); ?></td>
			<td align="left">
				<a style="cursor:pointer" onclick="window.parent.reSelectVenue('<?php echo $row->id; ?>', '<?php echo str_replace( array("'", "\""), array("\\'", ""), $row->venue); ?>');">
						<?php echo $this->escape($row->venue); ?>
				</a>
			</td>
			<td align="left"><?php echo $this->escape($row->city); ?></td>
			<td align="left"><?php echo $row->country; ?></td>
		</tr>
		<?php $k = 1 - $k; } ?>
	</tbody>
</table>

<p>
<input type="hidden" name="task" value="selectvenue" />
<input type="hidden" name="option" value="com_redevent" />
<input type="hidden" name="tmpl" value="component" />
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="" />
</p>
</form>

<p class="pageslinks">
	<?php echo $this->pageNav->getPagesLinks(); ?>
</p>

<p class="pagescounter">
	<?php echo $this->pageNav->getPagesCounter(); ?>
</p>

</div>