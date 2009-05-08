<?php $infoimage = JHTML::image('components/com_redevent/assets/images/icon-16-hint.png', JText::_( 'NOTES' ) ); ?>
<?php $k = 0; ?>
<table class="adminform">
	<tr class="row<?php echo $k = 1 - $k; ?>">
		<td>
			<label for="show_attendants">
				<?php echo JText::_( 'SHOW_ATTENDANTS' ).':'; ?>
			</label>
		</td>
		<td>
			<?php echo JHTML::_('select.booleanlist', 'show_attendants', '', $this->row->show_attendants ); ?>
		</td>
		<td>
			<span class="editlinktip hasTip" title="<?php echo JText::_( 'SHOW_ATTENDANTS' ); ?>::<?php echo JText::_('SHOW_ATTENDANTS_TIP'); ?>">
				<?php echo $infoimage; ?>
			</span>
		</td>
	</tr>
	<tr class="row<?php echo $k = 1 - $k; ?>">
		<td>
			<label for="show_waitinglist">
				<?php echo JText::_( 'SHOW_WAITINGLIST' ).':'; ?>
			</label>
		</td>
		<td>
			<?php echo JHTML::_('select.booleanlist', 'show_waitinglist', '', $this->row->show_waitinglist ); ?>
		</td>
		<td>
			<span class="editlinktip hasTip" title="<?php echo JText::_( 'SHOW_WAITINGLIST' ); ?>::<?php echo JText::_('SHOW_WAITINGLIST_TIP'); ?>">
				<?php echo $infoimage; ?>
			</span>
		</td>
	</tr>
	<tr class="row<?php echo $k = 1 - $k; ?>">
		<td>
			<label for="notify_on_list_subject">
				<?php echo JText::_( 'NOTIFY ON LIST SUBJECT' ).':'; ?>
			</label>
			</td>
		<td>
			<input class="inputbox" name="notify_on_list_subject" value="<?php echo $this->row->notify_on_list_subject; ?>" size="45" id="notify_on_list_subject" />
		</td>
	</tr>
	<tr class="row<?php echo $k = 1 - $k; ?>">
		<td>
			<label for="notify_on_list_body">
				<?php echo JText::_( 'NOTIFY ON LIST BODY' ).':'; ?>
			</label>
		</td>
		<td>
			<?php echo $this->editor->display( 'notify_on_list_body',  $this->row->notify_on_list_body, '100%;', '550', '75', '20', array('pagebreak', 'readmore', 'image') ) ; ?>
		</td>
	</tr>
	<tr class="row<?php echo $k = 1 - $k; ?>">
		<td>
			<label for="notify_off_list_subject">
				<?php echo JText::_( 'NOTIFY OFF LIST SUBJECT' ).':'; ?>
			</label>
		</td>
		<td>
			<input class="inputbox" name="notify_off_list_subject" value="<?php echo $this->row->notify_off_list_subject; ?>" size="45" id="notify_off_list_subject" />
		</td>
	</tr>
	<tr class="row<?php echo $k = 1 - $k; ?>">
		<td>
			<label for="notify_off_list_body">
				<?php echo JText::_( 'NOTIFY OFF LIST BODY' ).':'; ?>
			</label>
		</td>
		<td>
			<?php echo $this->editor->display( 'notify_off_list_body',  $this->row->notify_off_list_body, '100%;', '550', '75', '20', array('pagebreak', 'readmore', 'image') ) ; ?>
		</td>
	</tr>
</table>
