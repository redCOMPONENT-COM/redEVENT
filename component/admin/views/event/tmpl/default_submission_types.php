<div class="section-intro">
<?php echo JText::_('COM_REDEVENT_SUBMISSION_TAB_DESC' ); ?>
</div>

<div class="type-params">
	<input type="checkbox" class="reg-type" id="submission_type_external" name="submission_types[]" value="external"
	<?php if (in_array('external', $this->submission_types)) echo ' checked="checked"'; ?>
	/><label for="submission_type_external"><?php echo JText::_('COM_REDEVENT_EXTERNAL'); ?></label>
	<div class="clear"></div>
	
	<?php 
		$display = 'none';
		if (in_array('external', $this->submission_types)) {
			echo ' checked="checked"';
			$display = 'block';
		}
	?>
			
	<fieldset id="external-params" class="adminform" style="display: <?php echo $display;?>">
	<legend><?php echo JText::_('COM_REDEVENT_EXTERNAL'); ?></legend>
	
	<table class="editevent">
		<tr>
			<td class="key">
				<label for="submission_type_external" class="hasTip" title="<?php echo JText::_('COM_REDEVENT_EXTERNAL_URL_LABEL').'::'.JText::_('COM_REDEVENT_EXTERNAL_URL_TIP'); ?>">
					<?php echo JText::_('COM_REDEVENT_EXTERNAL_URL_LABEL' ); ?>:</label>
			</td>
			<td>
				<input type="text" class="inputbox" name="submission_type_external" value="<?php echo $this->row->submission_type_external; ?>" size="120" id="submission_type_external" />
			</td>
		</tr>
	</table>
	</fieldset>
</div>

<div class="type-params">
	<input type="checkbox" class="reg-type" id="submission_type_phone_check" name="submission_types[]" value="phone"
	<?php 
		$display = 'none';
		if (in_array('phone', $this->submission_types)) {
			echo ' checked="checked"';
			$display = 'block';
		}
	?>
	/><label for="submission_type_phone"><?php echo JText::_('COM_REDEVENT_PHONE'); ?></label>
	<div class="clear"></div>
	
	<fieldset id="phone-params" class="adminform" style="display: <?php echo $display;?>">
	<legend><?php echo JText::_('COM_REDEVENT_PHONE'); ?></legend>
	
	<table class="editevent">
		<tr>
			<td class="key">
				<label for="submission_type_external" class="hasTip" title="<?php echo JText::_('COM_REDEVENT_TYPES_PARAMS_SCREEN_LABEL').'::'.JText::_('COM_REDEVENT_TYPES_PARAMS_SCREEN_TIP'); ?>">
					<?php echo JText::_('COM_REDEVENT_TYPES_PARAMS_SCREEN_LABEL' ); ?>:</label><br/><?php echo JText::_('COM_REDEVENT_TYPES_PARAMS_SCREEN_TIP'); ?>
			</td>
			<td>
				<?php echo $this->printTags('submission_type_phone'); ?>
				<?php echo $this->editor->display( 'submission_type_phone',  $this->row->submission_type_phone, '100%;', '350', '75', '20', array('pagebreak', 'readmore', 'image') ) ; ?>
			</td>
		</tr>
	</table>
	</fieldset>
</div>

<div class="type-params">
	<input type="checkbox" class="reg-type" id="submission_type_webform_check" name="submission_types[]" value="webform"
	<?php 
		$display = 'none';
		if (in_array('webform', $this->submission_types)) {
			echo ' checked="checked"';
			$display = 'block';
		}
	?>
	/><label for="submission_type_webform"><?php echo JText::_('COM_REDEVENT_WEBFORM'); ?></label>
	<div class="clear"></div>
			
	<fieldset id="webform-params" class="adminform" style="display: <?php echo $display;?>">
	<legend><?php echo JText::_('COM_REDEVENT_WEBFORM'); ?></legend>	
	<table class="editevent">
		<tr>
			<td class="key">
				<label for="submission_type_external" class="hasTip" title="<?php echo JText::_('COM_REDEVENT_TYPES_PARAMS_SCREEN_LABEL').'::'.JText::_('COM_REDEVENT_TYPES_PARAMS_SCREEN_TIP'); ?>">
					<?php echo JText::_('COM_REDEVENT_TYPES_PARAMS_SCREEN_LABEL' ); ?>:</label><br/><?php echo JText::_('COM_REDEVENT_TYPES_PARAMS_WEBFORM_SCREEN_TIP'); ?>
			</td>
			<td>
        <?php echo $this->printTags('submission_type_webform'); ?>
				<?php echo $this->editor->display( 'submission_type_webform',  $this->row->submission_type_webform, '100%;', '350', '75', '20', array('pagebreak', 'readmore', 'image') ) ; ?>
			</td>
		</tr>			
		<tr>
			<td class="key">
				<label for="submission_type_external" class="hasTip" title="<?php echo JText::_('COM_REDEVENT_REVIEW_SCREEN').'::'.JText::_('COM_REDEVENT_REVIEW_SCREEN_INFO'); ?>">
					<?php echo JText::_('COM_REDEVENT_REVIEW_SCREEN' ); ?>:</label><br/><?php echo JText::_('COM_REDEVENT_REVIEW_SCREEN_INFO'); ?>
			</td>
			<td>
        <?php echo $this->printTags('review_message'); ?>
				<?php echo $this->editor->display( 'review_message',  $this->row->review_message, '100%;', '350', '75', '20', array('pagebreak', 'readmore', 'image') ) ; ?>
			</td>
		</tr>
		<tr>
			<td class="key">
				<label for="submission_type_external" class="hasTip" title="<?php echo JText::_('COM_REDEVENT_CONFIRMATION').'::'.JText::_('COM_REDEVENT_CONFIRMATION_INFO'); ?>">
					<?php echo JText::_('COM_REDEVENT_ENTER_CONFIRMATION_MESSAGE' ); ?>:</label><br/><?php echo JText::_('COM_REDEVENT_CONFIRMATION_INFO'); ?>
			</td>
			<td>
				<?php echo $this->printTags('confirmation_message'); ?>
				<?php echo $this->editor->display( 'confirmation_message',  $this->row->confirmation_message, '100%;', '350', '75', '20', array('pagebreak', 'readmore', 'image') ) ; ?>
			</td>
		</tr>
		
	</table>
	</fieldset>
</div>

<div class="type-params">
	<input type="checkbox" class="reg-type" id="submission_type_email_check" name="submission_types[]" value="email"
	<?php 
		$display = 'none';
		if (in_array('email', $this->submission_types)) {
			echo ' checked="checked"';
			$display = 'block';
		}
	?>
	/><label for="submission_type_email"><?php echo JText::_('COM_REDEVENT_EMAIL'); ?></label>
	<div class="clear"></div>
			
	<fieldset id="email-params" class="adminform" style="display: <?php echo $display;?>">
	<legend><?php echo JText::_('COM_REDEVENT_EMAIL'); ?></legend>	
	<table class="editevent">
		<tr>
			<td class="key">
				<label for="submission_type_external" class="hasTip" title="<?php echo JText::_('COM_REDEVENT_TYPES_PARAMS_SCREEN_LABEL').'::'.JText::_('COM_REDEVENT_TYPES_PARAMS_SCREEN_TIP'); ?>">
					<?php echo JText::_('COM_REDEVENT_TYPES_PARAMS_SCREEN_LABEL' ); ?>:</label><br/><?php echo JText::_('COM_REDEVENT_SUBMISSION_TYPE_EMAIL_TIP'); ?>
			</td>
			<td>
        <?php echo $this->printTags('submission_type_email'); ?>
				<?php echo $this->editor->display( 'submission_type_email',  $this->row->submission_type_email, '100%;', '350', '75', '20', array('pagebreak', 'readmore', 'image') ) ; ?>
			</td>
		</tr>
		<tr>
		<td class="key">
			<label for="submission_type_email_subject">
				<?php echo JText::_('COM_REDEVENT_EMAIL_SUBJECT' ).':'; ?>
			</label>
		</td>
		<td>
			<input class="inputbox" name="submission_type_email_subject" value="<?php echo $this->row->submission_type_email_subject; ?>" size="50" maxlength="255" id="email_subject" />
		</td>
	</tr>	
	<tr>
		<td class="key">
			<label for="submission_type_email_body"><?php echo JText::_('COM_REDEVENT_SUBMISSION_EMAIL_EMAIL_BODY')?></label>
		</td>
		<td>
  		<?php echo $this->printTags('submission_type_email_body'); ?>
			<?php echo $this->editor->display( 'submission_type_email_body',  $this->row->submission_type_email_body, '100%;', '350', '75', '20', array('pagebreak', 'readmore') ) ; ?>
		</td>
	</tr>	
	
	<tr>
		<td class="key hasTip"" title="<?php echo JText::_('COM_REDEVENT_SEND_PDF_FORM'); ?>::<?php echo JText::_('COM_REDEVENT_SEND_PDF_FORM_TIP'); ?>">
			<label for="send_pdf_form"><?php echo JText::_('COM_REDEVENT_SEND_PDF_FORM'); ?></label>
		</td>
		<td>
			<?php echo JHTML::_('select.booleanlist', 'send_pdf_form', 'class="inputbox', $this->row->send_pdf_form); ?>
		</td>
	</tr>
	<tr class="submission_type_email_pdf_options">
		<td class="key hasTip" title="<?php echo JText::_('COM_REDEVENT_PDF_FORM_DATA'); ?>::<?php echo JText::_('COM_REDEVENT_PDF_FORM_DATA_TIP'); ?>">
			<label for="pdf_form_data"><?php echo JText::_('COM_REDEVENT_PDF_FORM_DATA'); ?></label>
		</td>
		<td>
			<?php echo JHTML::_('select.booleanlist', 'pdf_form_data', 'class="inputbox', $this->row->pdf_form_data); ?>
		</td>
	</tr>
	<tr class="submission_type_email_pdf_options">
		<td class="key hasTip" title="<?php echo JText::_('COM_REDEVENT_SUBMISSION_EMAIL_PDF_CONTENT'); ?>::<?php echo JText::_('COM_REDEVENT_SUBMISSION_EMAIL_PDF_CONTENT_TIP'); ?>">
			<label for="submission_type_email_pdf"><?php echo JText::_('COM_REDEVENT_SUBMISSION_EMAIL_PDF_CONTENT');	?></label>
		</td>
		<td>			
			<?php echo $this->printTags('submission_type_email_pdf'); ?>
			<?php echo $this->editor->display( 'submission_type_email_pdf',  $this->row->submission_type_email_pdf, '100%;', '350', '75', '20', array('pagebreak', 'readmore') ) ; ?>
		</td>
	</tr>
	
	</table>
	</fieldset>
</div>

<div class="type-params">
	<input type="checkbox" class="reg-type" id="submission_type_formaloffer_check" name="submission_types[]" value="formaloffer"
	<?php 
		$display = 'none';
		if (in_array('formaloffer', $this->submission_types)) {
			echo ' checked="checked"';
			$display = 'block';
		}
	?>
	/><label for="submission_type_formal_offer"><?php echo JText::_('COM_REDEVENT_FORMALOFFER'); ?></label>
	<div class="clear"></div>
			
	<fieldset id="formaloffer-params" class="adminform" style="display: <?php echo $display;?>">
	<legend><?php echo JText::_('COM_REDEVENT_FORMALOFFER'); ?></legend>	
	<table class="editevent">
		<tr>
			<td class="key">
				<label for="submission_type_external" class="hasTip" title="<?php echo JText::_('COM_REDEVENT_TYPES_PARAMS_SCREEN_LABEL').'::'.JText::_('COM_REDEVENT_TYPES_PARAMS_SCREEN_TIP'); ?>">
					<?php echo JText::_('COM_REDEVENT_TYPES_PARAMS_SCREEN_LABEL' ); ?>:</label><br/><?php echo JText::_('COM_REDEVENT_SUBMISSION_TYPE_FORMALOFFER_TIP'); ?>
			</td>
			<td>
        <?php echo $this->printTags('submission_type_formal_offer'); ?>
				<?php echo $this->editor->display( 'submission_type_formal_offer',  $this->row->submission_type_formal_offer, '100%;', '350', '75', '20', array('pagebreak', 'readmore', 'image') ) ; ?>
			</td>
		</tr>		
		<tr>
			<td class="key">
				<label for="submission_type_formal_offer_subject">
					<?php echo JText::_('COM_REDEVENT_FORMAL_OFFER_SUBJECT' ).':'; ?>
				</label>
			</td>
			<td>
				<input class="inputbox" name="submission_type_formal_offer_subject" value="<?php echo $this->row->submission_type_formal_offer_subject; ?>" size="50" maxlength="255" id="formal_offer_subject" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<label for="submission_type_formal_offer_body">
					<?php echo JText::_('COM_REDEVENT_FORMAL_OFFER_BODY' ).':'; ?>
				</label>
			</td>
			<td>
				<?php echo $this->printTags('submission_type_formal_offer_body'); ?>
				<?php echo $this->editor->display( 'submission_type_formal_offer_body',  $this->row->submission_type_formal_offer_body, '100%;', '350', '75', '20', array('pagebreak', 'readmore') ) ; ?>
			</td>	
		</tr>
		<tr>
			<td class="key">
				<label for="show_submission_type_webform_formal_offer">
					<?php echo JText::_('COM_REDEVENT_SHOW_SUBMIT_AND_PRINT_BUTTON' ).':'; ?>
				</label>
			</td>
			<td>
				<?php echo JHTML::_('select.booleanlist', 'show_submission_type_webform_formal_offer', '', $this->row->show_submission_type_webform_formal_offer); ?>
			</td>	
		</tr>
		<tr>
			<td class="key">
				<label for="submission_type_webform_formal_offer">
					<?php echo JText::_('COM_REDEVENT_WEBFORM_PRINT_FORMAL_OFFER' ).':'; ?>
				</label>
			</td>
			<td>
	        <?php echo $this->printTags('submission_type_webform_input'); ?>
					<?php echo $this->editor->display( 'submission_type_webform_formal_offer',  $this->row->submission_type_webform_formal_offer, '100%;', '350', '75', '20', array('pagebreak', 'readmore', 'image') ) ; ?>
			</td>	
		</tr>	
	</table>
	</fieldset>
</div>
