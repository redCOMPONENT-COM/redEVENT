<?php $infoimage = JHTML::image('components/com_redevent/assets/images/icon-16-hint.png', JText::_( 'NOTES' ) ); ?>
<table class="adminform">
	<tr>
		<td colspan="2">
			<span class="editlinktip hasTip" title="<?php echo JText::_( 'CONFIRMATION' ); ?>::<?php echo JText::_('CONFIRMATION_INFO'); ?>">
				<?php echo $infoimage; ?>
			</span><label for="confirmation_message"><?php echo JText::_('ENTER_CONFIRMATION_MESSAGE'); ?></label>
			
			<div id="confirmation_screen">
				<?php echo $this->printTags('confirmation_message'); ?>
				<?php echo $this->editor->display( 'confirmation_message',  $this->row->confirmation_message, '100%;', '350', '75', '20', array('pagebreak', 'readmore', 'image') ) ; ?>
			</div>
		</td>
	</tr>
	<tr>
		<td colspan="2">			
			<span class="editlinktip hasTip" title="<?php echo JText::_('REVIEW_SCREEN'); ?>::<?php echo JText::_('REVIEW_SCREEN_INFO'); ?>">
				<?php echo $infoimage; ?>
			</span><label for="review_message"><?php echo JText::_('REVIEW_SCREEN'); ?></label>
			<div id="review_screen">
        <?php echo $this->printTags('review_message'); ?>
				<?php echo $this->editor->display( 'review_message',  $this->row->review_message, '100%;', '350', '75', '20', array('pagebreak', 'readmore', 'image') ) ; ?>
			</div>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<label><?php echo JText::_('WEBFORM_PRINT_FORMAL_OFFER'); ?></label><br/>
			<?php echo JText::_('SHOW SUBMIT AND PRINT BUTTON') . ': ' . JHTML::_('select.booleanlist', 'show_submission_type_webform_formal_offer', '', $this->row->show_submission_type_webform_formal_offer); ?>
			<div id="submission_type_webform_input" style="display: block">
        <?php echo $this->printTags('submission_type_webform_input'); ?>
				<?php echo $this->editor->display( 'submission_type_webform_formal_offer',  $this->row->submission_type_webform_formal_offer, '100%;', '350', '75', '20', array('pagebreak', 'readmore', 'image') ) ; ?>
			</div>
		</td>
	</tr>
</table>
