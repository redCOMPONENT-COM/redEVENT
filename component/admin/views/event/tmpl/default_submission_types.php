<table class="adminform">
	<tr>
		<td colspan="2" class="redevent_settings_details">
				<?php echo JText::_('COM_REDEVENT_SUBMIT_TYPES' ).':'; ?>
		</td>
	</tr>
  <tr>
    <td colspan="2" class="redevent_settings_details_info">
        <?php echo JText::_('COM_REDEVENT_SUBMISSION_TAB_DESC' ); ?>
    </td>
  </tr>
	<tr>
		<td>
			<input type="checkbox" id="submission_type_external" name="submission_types[]" value="external"
			<?php if (in_array('external', $this->submission_types)) echo ' checked="checked"'; ?>
			/><?php echo JText::_('COM_REDEVENT_EXTERNAL'); ?>
		</td>
		<td>
			<input type="text" class="inputbox" name="submission_type_external" value="<?php echo $this->row->submission_type_external; ?>" size="120" id="submission_type_external" />
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<input type="checkbox" id="submission_type_phone_check" name="submission_types[]" value="phone"
			<?php 
				$display = 'none';
				if (in_array('phone', $this->submission_types)) {
					echo ' checked="checked"';
					$display = 'block';
				}
			?>
			/><label for="submission_type_phone"><?php echo JText::_('COM_REDEVENT_PHONE'); ?></label>
			<div id="submission_type_phone_input" style="display: <?php echo $display;?>">
        <?php echo $this->printTags('submission_type_phone'); ?>
				<?php echo $this->editor->display( 'submission_type_phone',  $this->row->submission_type_phone, '100%;', '350', '75', '20', array('pagebreak', 'readmore', 'image') ) ; ?>
			</div>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<input type="checkbox" id="submission_type_webform_check" name="submission_types[]" value="webform"
			<?php 
				$display = 'none';
				if (in_array('webform', $this->submission_types)) {
					echo ' checked="checked"';
					$display = 'block';
				}
			?>
			/><label for="submission_type_webform"><?php echo JText::_('COM_REDEVENT_WEBFORM'); ?></label>
			<div id="submission_type_webform_input" style="display: <?php echo $display;?>">
        <?php echo $this->printTags('submission_type_webform'); ?>
				<?php echo $this->editor->display( 'submission_type_webform',  $this->row->submission_type_webform, '100%;', '350', '75', '20', array('pagebreak', 'readmore', 'image') ) ; ?>
			</div>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<input type="checkbox" id="submission_type_email_check" name="submission_types[]" value="email"
			<?php 
				$display = 'none';
				if (in_array('email', $this->submission_types)) {
					echo ' checked="checked"';
					$display = 'block';
				}
			?>
			/><label for="submission_type_email"><?php echo JText::_('COM_REDEVENT_EMAIL'); ?></label>
			<span class="editlinktip hasTip" title="<?php echo JText::_('COM_REDEVENT_SUBMISSION_TYPE_EMAIL_TIP' ); ?>">
				<?php echo $this->infoimage; ?>
			</span>
			<div id="submission_type_email_input" style="display: <?php echo $display;?>">
        <?php echo $this->printTags('submission_type_email'); ?>
				<?php echo $this->editor->display( 'submission_type_email',  $this->row->submission_type_email, '100%;', '350', '75', '20', array('pagebreak', 'readmore', 'image') ) ; ?>
			</div>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<input type="checkbox" id="submission_type_formaloffer_check" name="submission_types[]" value="formaloffer"
			<?php 
				$display = 'none';
				if (in_array('formaloffer', $this->submission_types)) {
					echo ' checked="checked"';
					$display = 'block';
				}
			?>
			/><label for="submission_type_formal_offer"><?php echo JText::_('COM_REDEVENT_FORMALOFFER'); ?></label>
			<span class="editlinktip hasTip" title="<?php echo JText::_('COM_REDEVENT_SUBMISSION_TYPE_FORMALOFFER_TIP' ); ?>">
				<?php echo $this->infoimage; ?>
			</span>
			<div id="submission_type_formaloffer_input" style="display: <?php echo $display;?>">
        <?php echo $this->printTags('submission_type_formal_offer'); ?>
				<?php echo $this->editor->display( 'submission_type_formal_offer',  $this->row->submission_type_formal_offer, '100%;', '350', '75', '20', array('pagebreak', 'readmore', 'image') ) ; ?>
			</div>
		</td>
	</tr>
</table>
