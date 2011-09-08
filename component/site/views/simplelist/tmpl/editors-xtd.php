<?php
/**
 * @version 1.0 $Id: default.php 30 2009-05-08 10:22:21Z roland $
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

defined( '_JEXEC' ) or die( 'Restricted access' );

		$eName	= JRequest::getVar('e_name');
		$eName	= preg_replace( '#[^A-Z0-9\-\_\[\]]#i', '', $eName );
?>

		<script type="text/javascript">
			function insertEvent(id, title, link)
			{
				var tag = "<a href=\""+link+"\">"+title+"</a>";

				window.parent.jInsertEditorText(tag, '<?php echo $eName; ?>');
				window.parent.document.getElementById('sbox-window').close();
				return false;
			}
		</script>

<div id="redevent" class="el_eventlist">

<!--table-->

<form action="<?php echo $this->action; ?>" method="post" id="adminForm">

<?php if ($this->params->get('filter_text',1) || $this->params->get('display')) : ?>
<div id="el_filter" class="floattext">
		<?php if ($this->params->get('filter_text',1)) : ?>
		<div class="el_fleft">
			<?php
			echo '<label for="filter_type">'.JText::_('FILTER').'</label>&nbsp;';
			echo $this->lists['filter_type'].'&nbsp;';
			?>
			<input type="text" name="filter" id="filter" value="<?php echo $this->lists['filter'];?>" class="inputbox" onchange="document.getElementById('adminForm').submit();" title="<?php echo JText::_('EVENTS_FILTER_HINT'); ?>"/>
			<button onclick="document.getElementById('adminForm').submit();"><?php echo JText::_( 'GO' ); ?></button>
			<button onclick="document.getElementById('filter').value='';document.getElementById('adminForm').submit();"><?php echo JText::_( 'RESET' ); ?></button>
		</div>
		<?php endif; ?>
		<?php if ($this->params->get('display')) : ?>
		<div class="el_fright">
			<?php
			echo '<label for="limit">'.JText::_('DISPLAY NUM').'</label>&nbsp;';
			echo $this->pageNav->getLimitBox();
			?>
		</div>
		<?php endif; ?>
</div>
<?php endif; ?>

<table class="eventtable" summary="eventlist">

	<colgroup>
		<col class="el_col_date" />
		<?php if ($this->params->get('showtitle', 1)) : ?>
			<col class="el_col_title" />
		<?php endif; ?>
		<?php if ($this->params->get('showlocate', 1)) :	?>
			<col class="el_col_venue" />
		<?php endif; ?>
		<?php if ($this->params->get('showcity', 1)) :	?>
			<col class="el_col_city" />
		<?php endif; ?>
		<?php if ($this->params->get('showstate', 0)) :	?>
			<col class="el_col_state" />
		<?php endif; ?>
		<?php if ($this->params->get('showcat', 1)) :	?>
			<col class="el_col_category" />
		<?php endif; ?>
	</colgroup>

	<thead>
			<tr>
				<th id="el_date" class="sectiontableheader" align="left"><?php echo JHTML::_('grid.sort', JText::_('COM_REDEVENT_TABLE_HEADER_DATE'), 'x.dates', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
				<?php
				if ($this->params->get('showtitle', 1)) :
				?>
				<th id="el_title" class="sectiontableheader" align="left"><?php echo JHTML::_('grid.sort', JText::_('COM_REDEVENT_TABLE_HEADER_TITLE'), 'a.title', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
				<?php
				endif;
				if ($this->params->get('showlocate', 1)) :
				?>
				<th id="el_location" class="sectiontableheader" align="left"><?php echo JHTML::_('grid.sort', JText::_('COM_REDEVENT_TABLE_HEADER_VENUE'), 'l.venue', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
				<?php
				endif;
				if ($this->params->get('showcity', 1)) :
				?>
				<th id="el_city" class="sectiontableheader" align="left"><?php echo JHTML::_('grid.sort', JText::_('COM_REDEVENT_TABLE_HEADER_CITY'), 'l.city', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
				<?php
				endif;
				if ($this->params->get('showstate', 0)) :
				?>
				<th id="el_state" class="sectiontableheader" align="left"><?php echo JHTML::_('grid.sort', JText::_('COM_REDEVENT_TABLE_HEADER_STATE'), 'l.state', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
				<?php
				endif;
				if ($this->params->get('showcat', 1)) :
				?>
				<th id="el_category" class="sectiontableheader" align="left"><?php echo JHTML::_('grid.sort', JText::_('COM_REDEVENT_TABLE_HEADER_CATEGORY'), 'c.catname', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
				<?php
				endif;
				?>
			</tr>
	</thead>
	<tbody>
	<?php
	if ($this->noevents == 1) :
		?>
		<tr align="center"><td><?php echo JText::_( 'NO EVENTS' ); ?></td></tr>
		<?php
	else :

	$k = 0;
	foreach ($this->rows as $row) :
		?>
  			<tr class="sectiontableentry<?php echo ($k +1 ) . $this->params->get( 'pageclass_sfx' ); ?>" >

    			<td headers="el_date" align="left">
    				<strong>
    					<?php echo ELOutput::formatdate($row->dates, $row->times); ?>
    					
    					<?php
    					if (redEVENTHelper::isValidDate($row->enddates) && $row->enddates != $row->dates) :
    						echo ' - '.ELOutput::formatdate($row->enddates, $row->endtimes);
    					endif;
    					?>
    				</strong>
    				
					<?php
					if ($this->params->get('showtime', 1)) :
					?>
						<br />
						<?php
						echo ELOutput::formattime($row->dates, $row->times);
						
						if ($row->endtimes) :
							echo ' - '.ELOutput::formattime($row->enddates, $row->endtimes);
						endif;
					endif;
					?>
				</td>

				<td headers="el_title" align="left" valign="top">
					<a onclick="insertEvent('<?php echo $row->id; ?>', '<?php echo str_replace( array("'", "\""), array("\\'", ""), $row->title ); ?>', '<?php echo JRoute::_('index.php?option=com_redevent&view=details&id='. $row->slug, true); ?>');">
				  <?php echo $this->escape($row->title); ?>
				  </a>
				</td>

				<?php if (( $this->params->get('showtitle', 1) ) && ($this->elsettings->showdetails == 0) ) :
				?>

				<td headers="el_title" align="left" valign="top"><?php echo $this->escape($row->title); ?></td>

				<?php
				endif;
				if ($this->params->get('showlocate', 1)) :
				?>

					<td headers="el_location" align="left" valign="top">
						<?php	echo $row->xref ? $this->escape($row->venue) : '-';	?>
					</td>

				<?php
				endif;

				if ($this->params->get('showcity', 1)) :
				?>

					<td headers="el_city" align="left" valign="top"><?php echo $row->city ? $this->escape($row->city) : '-'; ?></td>

				<?php
				endif;

				if ($this->params->get('showstate', 0)) :
				?>

					<td headers="el_state" align="left" valign="top"><?php echo $row->state ? $this->escape($row->state) : '-'; ?></td>

				<?php
				endif;

				if ($this->params->get('showcat', 1)) : ?>
				  <td headers="el_category" align="left" valign="top">
				  <?php $cats = array();
					      foreach ($row->categories as $cat)
					      {
					      	$cats[] = $this->escape($cat->catname);
					      }
					      echo implode("<br/>", $cats);
					?>
					</td>	
				<?php endif; ?>
			</tr>

  		<?php
			$k = 1 - $k;
		endforeach;
		endif;
		?>

	</tbody>
</table>

<p>
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="" />
</p>
</form>

<!--footer-->

<div class="pageslinks">
	<?php echo $this->pageNav->getPagesLinks(); ?>
</div>

<p class="pagescounter">
	<?php echo $this->pageNav->getPagesCounter(); ?>
</p>

<p class="copyright">
	<?php echo ELOutput::footer( ); ?>
</p>

</div>