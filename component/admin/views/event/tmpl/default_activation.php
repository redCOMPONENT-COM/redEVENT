<?php $infoimage = JHTML::image('components/com_redevent/assets/images/icon-16-hint.png', JText::_('COM_REDEVENT_NOTES' ) ); ?>
<?php $k = 0; ?>
<table class="adminform">
	<tr class="row<?php echo $k = 1 - $k; ?>">
		<td>
			<label for="activate">
				<?php echo JText::_('COM_REDEVENT_ENABLE_ACTIVATION' ).':'; ?>
			</label>
		</td>
		<td>
			<?php
			$html = JHTML::_('select.booleanlist', 'activate', '', $this->row->activate );
			echo $html;
			?>
		</td>
	</tr>
	<tr class="row<?php echo $k = 1 - $k; ?>">
		<td>
			<label for="notify">
				<?php echo JText::_('COM_REDEVENT_ENABLE_NOTIFICATION' ).':'; ?>
			</label>
		</td>
		<td>
			<?php
			$html = JHTML::_('select.booleanlist', 'notify', '', $this->row->notify );
			echo $html;
			?>
		</td>
	</tr>
	<tr class="row<?php echo $k = 1 - $k; ?>">
		<td>
			<label for="notify_subject">
				<?php echo JText::_('COM_REDEVENT_NOTIFY_SUBJECT' ).':'; ?>
			</label>
		</td>
		<td>
			<input class="inputbox" name="notify_subject" value="<?php echo $this->row->notify_subject; ?>" size="45" id="notify_subject" />
		</td>
	</tr>
	<tr class="row<?php echo $k = 1 - $k; ?>">
		<td>
			<label for="notify_body">
				<?php echo JText::_('COM_REDEVENT_NOTIFY_BODY' ).':'; ?>
			</label>
			<span class="editlinktip hasTip" title="<?php echo JText::_('COM_REDEVENT_NOTIFY_BODY' ); ?>::<?php echo JText::_('COM_REDEVENT_NOTIFY_BODY_NOTE'); ?>">
				<?php echo $infoimage; ?>
			</span>
		</td>
		<td>
        <?php echo $this->printTags('notify_body'); ?>
			<?php echo $this->editor->display( 'notify_body',  $this->row->notify_body, '100%;', '550', '75', '20', array('pagebreak', 'readmore', 'image') ) ; ?>
		</td>
	</tr>
	<tr class="row<?php echo $k = 1 - $k; ?>">
		<td>
			<label for="notify_confirm_subject">
				<?php echo JText::_('COM_REDEVENT_NOTIFY_CONFIRM_SUBJECT' ).':'; ?>
			</label>
			<span class="editlinktip hasTip" title="<?php echo JText::_('COM_REDEVENT_NOTIFY_CONFIRM_SUBJECT' ); ?>::<?php echo JText::_('COM_REDEVENT_NOTIFY_CONFIRM_SUBJECT_NOTE'); ?>">
				<?php echo $infoimage; ?>
			</span>
		</td>
		<td>
			<input class="inputbox" name="notify_confirm_subject" value="<?php echo $this->row->notify_confirm_subject; ?>" size="45" id="notify_confirm_subject" />
		</td>
	</tr>
	<tr class="row<?php echo $k = 1 - $k; ?>">
		<td>
			<label for="notify_confirm_body">
				<?php echo JText::_('COM_REDEVENT_NOTIFY_CONFIRM_BODY' ).':'; ?>
			</label>
		</td>
		<td>
        <?php echo $this->printTags('notify_confirm_body'); ?>
			<?php echo $this->editor->display( 'notify_confirm_body',  $this->row->notify_confirm_body, '100%;', '550', '75', '20', array('pagebreak', 'readmore', 'image') ) ; ?>
		</td>
	</tr>
</table>
