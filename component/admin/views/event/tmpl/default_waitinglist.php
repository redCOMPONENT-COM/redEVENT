<?php $infoimage = JHTML::image('components/com_redevent/assets/images/icon-16-hint.png', JText::_('COM_REDEVENT_NOTES' ) ); ?>
<?php $k = 0; ?>
<table class="adminform">
	<tr class="row<?php echo $k = 1 - $k; ?>">
		<td>
			<label for="notify_on_list_subject">
				<?php echo JText::_('COM_REDEVENT_NOTIFY_ON_LIST_SUBJECT' ).':'; ?>
			</label>
			</td>
		<td>
			<input class="inputbox" name="notify_on_list_subject" value="<?php echo $this->row->notify_on_list_subject; ?>" size="45" id="notify_on_list_subject" />
		</td>
	</tr>
	<tr class="row<?php echo $k = 1 - $k; ?>">
		<td>
			<label for="notify_on_list_body">
				<?php echo JText::_('COM_REDEVENT_NOTIFY_ON_LIST_BODY' ).':'; ?>
			</label>
		</td>
		<td>
      <?php echo $this->printTags('notify_on_list_body'); ?>
			<?php echo $this->editor->display( 'notify_on_list_body',  $this->row->notify_on_list_body, '100%;', '550', '75', '20', array('pagebreak', 'readmore', 'image') ) ; ?>
		</td>
	</tr>
	<tr class="row<?php echo $k = 1 - $k; ?>">
		<td>
			<label for="notify_off_list_subject">
				<?php echo JText::_('COM_REDEVENT_NOTIFY_OFF_LIST_SUBJECT' ).':'; ?>
			</label>
		</td>
		<td>
			<input class="inputbox" name="notify_off_list_subject" value="<?php echo $this->row->notify_off_list_subject; ?>" size="45" id="notify_off_list_subject" />
		</td>
	</tr>
	<tr class="row<?php echo $k = 1 - $k; ?>">
		<td>
			<label for="notify_off_list_body">
				<?php echo JText::_('COM_REDEVENT_NOTIFY_OFF_LIST_BODY' ).':'; ?>
			</label>
		</td>
		<td>
      <?php echo $this->printTags('notify_off_list_body'); ?>
			<?php echo $this->editor->display( 'notify_off_list_body',  $this->row->notify_off_list_body, '100%;', '550', '75', '20', array('pagebreak', 'readmore', 'image') ) ; ?>
		</td>
	</tr>
</table>
