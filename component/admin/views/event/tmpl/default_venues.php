<?php if ($this->row->id): ?>
<table class="adminlist">
	<thead>
		<tr>
      <th>&nbsp;</th>
			<th><?php echo JText::_('VENUE'); ?></th>
			<th><?php echo JText::_('DATE'); ?></th>
			<th><?php echo JText::_('TIME'); ?></th>
      <th><?php echo JText::_('PUBLISHED'); ?></th>
      <th>&nbsp;</th>
		</tr>
	</thead>
	<tbody>
		<?php
		foreach ($this->xrefs as $eventdetails) 
		{
			/* Get the date */
			$date = (!isset($eventdetails->dates) ? Jtext::_('Open date') : strftime( $this->elsettings->formatdate, strtotime( $eventdetails->dates )));
			$enddate  = strftime( $this->elsettings->formatdate, strtotime( $eventdetails->enddates ));
			$displaydate = $date. ($eventdetails->enddates ? ' - '.$enddate: '');
	
			$displaytime = '';
			/* Get the time */
			if (isset($eventdetails->times)) {
				$displaytime = strftime( $this->elsettings->formattime, strtotime( $eventdetails->times )).' '.$this->elsettings->timename;
	
				if (isset($eventdetails->endtimes)) {
					$displaytime .= ' - '.strftime( $this->elsettings->formattime, strtotime( $eventdetails->endtimes )). ' '.$this->elsettings->timename;
				}
			}
			?>
			<tr id="xref-<?php echo $eventdetails->id; ?>" class="xref-details">
        <td><a href="<?php echo JRoute::_('index.php?option=com_redevent&controller=events&task=editxref&tmpl=component&xref=' .$eventdetails->id .'&eventid='. $this->row->id); ?>" class="xrefmodal"><?php echo JText::_('Edit'); ?></a></td>
			  <td><?php echo $eventdetails->venue; ?></td>
	      <td><?php echo $displaydate; ?></td>
	      <td><?php echo $displaytime; ?></td>
        <td><?php echo ($eventdetails->published) ? JText::_('YES') : JText::_('No'); ?></td>
	      <td class="cell-delxref"><?php echo ''; ?></td>
	    </tr>
	    <?php
		}
		?>
		<tr id="add-xref">
			<td><a href="<?php echo JRoute::_('index.php?option=com_redevent&controller=events&task=editxref&tmpl=component&eventid='. $this->row->id); ?>" class="xrefmodal"><?php echo JText::_('Add'); ?></a></td>
      <td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
      <td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
	</tbody>
</table>
<?php else: ?>
<?php echo JText::_('PLEASE SAVE EVENT TO ADD DATES'); ?>
<?php endif; ?>