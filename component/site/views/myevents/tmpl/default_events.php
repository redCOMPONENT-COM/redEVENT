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

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
?>

<h2><?php echo JText::_('Manage Events'); ?></h2>

<script type="text/javascript">

	function tableOrdering( order, dir, view )
	{
		var form = document.getElementById("adminForm");

		form.filter_order.value 	= order;
		form.filter_order_Dir.value	= dir;
		form.submit( view );
	}
</script>

<form action="<?php echo $this->action; ?>" method="post" id="adminForm">

<?php if ($this->params->get('filter') || $this->params->get('display')) : ?>
<div id="el_filter" class="floattext">
    <?php if ($this->params->get('filter')) : ?>
    <div class="el_fleft">
      <?php
      echo '<label for="filter_type">'.JText::_('FILTER').'</label>&nbsp;';
      echo $this->lists['filter_types'].'&nbsp;';
      ?>
      <input type="text" name="filter" id="filter" value="<?php echo $this->lists['filter'];?>" class="inputbox" onchange="document.getElementById('adminForm').submit();" />
      <button onclick="document.getElementById('adminForm').submit();"><?php echo JText::_( 'GO' ); ?></button>
      <button onclick="document.getElementById('filter').value='';document.getElementById('adminForm').submit();"><?php echo JText::_( 'RESET' ); ?></button>
    </div>
    <?php endif; ?>
    <?php if ($this->params->get('display')) : ?>
    <div class="el_fright">
      <?php
      echo '<label for="limit">'.JText::_('DISPLAY NUM').'</label>&nbsp;';
      echo $this->events_pageNav->getLimitBox();
      ?>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>
<table class="eventtable" width="<?php echo $this->elsettings->tablewidth; ?>" border="0" cellspacing="0" cellpadding="0" summary="eventlist">

	<colgroup>
		<col width="<?php echo $this->elsettings->datewidth; ?>" class="el_col_date" />
		<?php if ($this->elsettings->showtitle == 1) : ?>
			<col width="<?php echo $this->elsettings->titlewidth; ?>" class="el_col_title" />
		<?php endif; ?>
		<?php if ($this->elsettings->showlocate == 1) :	?>
			<col width="<?php echo $this->elsettings->locationwidth; ?>" class="el_col_venue" />
		<?php endif; ?>
		<?php if ($this->elsettings->showcity == 1) :	?>
			<col width="<?php echo $this->elsettings->citywidth; ?>" class="el_col_city" />
		<?php endif; ?>
		<?php if ($this->elsettings->showstate == 1) :	?>
			<col width="<?php echo $this->elsettings->statewidth; ?>" class="el_col_state" />
		<?php endif; ?>
		<?php if ($this->elsettings->showcat == 1) :	?>
			<col width="<?php echo $this->elsettings->catfrowidth; ?>" class="el_col_category" />
		<?php endif; ?>
			<col width="5" class="el_col_attendees" />
			<col width="5" class="el_col_edit" />
			<col width="5" class="el_col_published" />
			<col width="5" class="el_col_delete" />
	</colgroup>

	<thead>
			<tr>
				<th id="el_date" class="sectiontableheader" align="left"><?php echo JHTML::_('grid.sort', $this->escape($this->elsettings->datename), 'a.dates', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
				<?php
				if ($this->elsettings->showtitle == 1) :
				?>
				<th id="el_title" class="sectiontableheader" align="left"><?php echo JHTML::_('grid.sort', $this->escape($this->elsettings->titlename), 'a.title', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
				<?php
				endif;
				if ($this->elsettings->showlocate == 1) :
				?>
				<th id="el_location" class="sectiontableheader" align="left"><?php echo JHTML::_('grid.sort', $this->escape($this->elsettings->locationname), 'l.venue', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
				<?php
				endif;
				if ($this->elsettings->showcity == 1) :
				?>
				<th id="el_city" class="sectiontableheader" align="left"><?php echo JHTML::_('grid.sort', $this->escape($this->elsettings->cityname), 'l.city', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
				<?php
				endif;
				if ($this->elsettings->showstate == 1) :
				?>
				<th id="el_state" class="sectiontableheader" align="left"><?php echo JHTML::_('grid.sort', $this->escape($this->elsettings->statename), 'l.state', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
				<?php
				endif;
				if ($this->elsettings->showcat == 1) :
				?>
				<th id="el_category" class="sectiontableheader" align="left"><?php echo JHTML::_('grid.sort', $this->escape($this->elsettings->catfroname), 'c.catname', $this->lists['order_Dir'], $this->lists['order'] ); ?></th>
				<?php
				endif;
				?>				
				<th id="el_attendees" class="sectiontableheader" align="left"><?php echo JText::_('Attendees'); ?></th>
				<th id="el_edit" class="sectiontableheader" align="left"><?php echo JText::_('Edit'); ?></th>
				<th id="el_edit" class="sectiontableheader" align="left"><?php echo JText::_('Published'); ?></th>
				<th id="el_edit" class="sectiontableheader" align="left"><?php echo JText::_('Delete'); ?></th>
			</tr>
	</thead>
	<tbody>
	<?php
	if (count((array)$this->events) == 0) :
		?>
		<tr align="center"><td><?php echo JText::_( 'NO EVENTS' ); ?></td></tr>
		<?php
	else :
	
	$i = 0;
	foreach ((array) $this->events as $row) :
		?>
  			<tr class="sectiontableentry<?php echo $i +1 . $this->params->get( 'pageclass_sfx' ); ?>" >

    			<td headers="el_date" align="left">
    				<strong>
    					<?php echo ELOutput::formatdate($row->dates, $row->times); ?>
    					
    					<?php
    					if ($row->enddates) :
    						echo ' - '.ELOutput::formatdate($row->enddates, $row->endtimes);
    					endif;
    					?>
    				</strong>
    				
					<?php
					if ($this->elsettings->showtime == 1) :
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

				<?php
				//Link to details
				$detaillink = JRoute::_( 'index.php?option=com_redevent&view=details&id='. $row->slug .'&xref=' . $row->xref);
				//title
				if (($this->elsettings->showtitle == 1 ) && ($this->elsettings->showdetails == 1) ) :
				?>

				<td headers="el_title" align="left" valign="top"><a href="<?php echo $detaillink ; ?>"> <?php echo $this->escape($row->title); ?></a></td>

				<?php
				endif;

				if (( $this->elsettings->showtitle == 1 ) && ($this->elsettings->showdetails == 0) ) :
				?>

				<td headers="el_title" align="left" valign="top"><?php echo $this->escape($row->title); ?></td>

				<?php
				endif;
				if ($this->elsettings->showlocate == 1) :
				?>

					<td headers="el_location" align="left" valign="top">
						<?php
						if ($this->elsettings->showlinkvenue == 1 ) :
							echo $row->locid != 0 ? "<a href='".JRoute::_('index.php?option=com_redevent&view=venueevents&id='.$row->venueslug)."'>".$this->escape($row->venue)."</a>" : '-';
						else :
							echo $row->locid ? $this->escape($row->venue) : '-';
						endif;
						?>
					</td>

				<?php
				endif;

				if ($this->elsettings->showcity == 1) :
				?>

					<td headers="el_city" align="left" valign="top"><?php echo $row->city ? $this->escape($row->city) : '-'; ?></td>

				<?php
				endif;

				if ($this->elsettings->showstate == 1) :
				?>

					<td headers="el_state" align="left" valign="top"><?php echo $row->state ? $this->escape($row->state) : '-'; ?></td>

				<?php
				endif;

				if ($this->elsettings->showcat == 1) : ?>
          <td headers="el_category" align="left" valign="top">
          <?php foreach ($row->categories as $k => $cat): ?>
            <?php if ($this->elsettings->catlinklist == 1) : ?> 
              <a href="<?php echo JRoute::_('index.php?option=com_redevent&view=categoryevents&id='.$cat->slug); ?>">
                <?php echo $cat->catname ? $this->escape($cat->catname) : '-' ; ?>
              </a>
            <?php else: ?>
              <?php echo $cat->catname ? $this->escape($cat->catname) : '-'; ?>
            <?php endif; ?>
            <?php echo ($k < count($row->categories)) ? '<br/>' : '' ; ?>
          <?php endforeach; ?>
          </td> 
        <?php endif; ?>

				<td headers="el_edit" align="left" valign="top"><?php echo $this->xrefattendeesbutton($row->xref); ?></td>
				<td headers="el_edit" align="left" valign="top"><?php echo $this->xrefeditbutton($row->xref); ?></td>
				<td headers="el_edit" align="left" valign="top">
					<?php if ($row->published == '1'): ?>
						<?php echo JHTML::link('index.php?option=com_redevent&task=unpublishxref&xref='. $row->xref, JHTML::_('image.site', 'ok.png', 'components/com_redevent/assets/images/', NULL, NULL, JText::_( 'Published' ))); ?>
					<?php elseif ($row->published == '0'):?>
						<?php echo JHTML::link('index.php?option=com_redevent&task=publishxref&xref='. $row->xref, JHTML::_('image.site', 'no.png', 'components/com_redevent/assets/images/', NULL, NULL, JText::_( 'Unpublished' ))); ?>
					<?php endif;?>
				</td>
				<td headers="el_delete" align="left" valign="top"><?php echo $this->xrefdeletebutton($row->xref); ?></td>
			</tr>

  		<?php
  		$i = 1 - $i;
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

<div class="pageslinks">
  <?php echo $this->events_pageNav->getPagesLinks(); ?>
</div>

<p class="pagescounter">
  <?php echo $this->events_pageNav->getPagesCounter(); ?>
</p>

<?php if ($this->canAddXref): ?>
<div><?php echo JHTML::link(JRoute::_('index.php?option=com_redevent&view=editevent&layout=eventdate', false), JText::_('Add new event date')); ?></div>
<?php endif; ?>