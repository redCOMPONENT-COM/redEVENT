<fieldset>
<legend><?php echo JText::_('Submission email');	?></legend>
	<?php 
		$display = 'none';
		if (in_array('email', $this->submission_types)) {
			$display = 'block';
		}
		?>
	<div id="submission_type_email_body_input" style="display: <?php echo $display;?>">
  <?php echo $this->printTags('submission_type_email_body'); ?>
	
	<table class="admintable" width="100%">
	
	<tr>
		<td class="key">
			<label for="submission_type_email_subject">
				<?php echo JText::_( 'EMAIL_SUBJECT' ).':'; ?>
			</label>
		</td>
		<td>
			<input class="inputbox" name="submission_type_email_subject" value="<?php echo $this->row->submission_type_email_subject; ?>" size="50" maxlength="255" id="email_subject" />
		</td>
	</tr>	
	<tr>
		<td class="key">
			<label for="submission_type_email_body"><?php echo JText::_('REDEVENT_SUBMISSION_EMAIL_EMAIL_BODY')?></label>
		</td>
		<td>
			<?php echo $this->editor->display( 'submission_type_email_body',  $this->row->submission_type_email_body, '100%;', '350', '75', '20', array('pagebreak', 'readmore') ) ; ?>
		</td>
	</tr>	
	
	<tr>
		<td class="key hasTip"" title="<?php echo JText::_('SEND_PDF_FORM'); ?>::<?php echo JText::_('SEND_PDF_FORM_TIP'); ?>">
			<label for="send_pdf_form"><?php echo JText::_('SEND_PDF_FORM'); ?></label>
		</td>
		<td>
			<?php echo JHTML::_('select.booleanlist', 'send_pdf_form', 'class="inputbox', $this->row->send_pdf_form); ?>
		</td>
	</tr>
	<tr class="submission_type_email_pdf_options">
		<td class="key hasTip" title="<?php echo JText::_('PDF_FORM_DATA'); ?>::<?php echo JText::_('PDF_FORM_DATA_TIP'); ?>">
			<label for="pdf_form_data"><?php echo JText::_('PDF_FORM_DATA'); ?></label>
		</td>
		<td>
			<?php echo JHTML::_('select.booleanlist', 'pdf_form_data', 'class="inputbox', $this->row->pdf_form_data); ?>
		</td>
	</tr>
	<tr class="submission_type_email_pdf_options">
		<td class="key hasTip" title="<?php echo JText::_('REDEVENT_SUBMISSION_EMAIL_PDF_CONTENT'); ?>::<?php echo JText::_('REDEVENT_SUBMISSION_EMAIL_PDF_CONTENT_TIP'); ?>">
			<label for="submission_type_email_pdf"><?php echo JText::_('REDEVENT_SUBMISSION_EMAIL_PDF_CONTENT');	?></label>
		</td>
		<td>			
			<?php echo $this->printTags('submission_type_email_pdf'); ?>
			<?php echo $this->editor->display( 'submission_type_email_pdf',  $this->row->submission_type_email_pdf, '100%;', '350', '75', '20', array('pagebreak', 'readmore') ) ; ?>
		</td>
	</tr>
	
	</table>
	</div>
</fieldset>

<fieldset>
<legend><?php echo JText::_('formaloffer'); ?></legend>
	<?php 
		$display = 'none';
		if (in_array('formaloffer', $this->submission_types)) {
			$display = 'block';
		}
	?>
	<div id="submission_type_formaloffer_body_input" style="display: <?php echo $display;?>">
	<?php echo $this->printTags('submission_type_formal_offer_body'); ?>
	<table class="admintable" width="100%">
	<tr>
		<td class="key">
			<label for="submission_type_formal_offer_subject">
				<?php echo JText::_( 'FORMAL_OFFER_SUBJECT' ).':'; ?>
			</label>
		</td>
		<td>
			<input class="inputbox" name="submission_type_formal_offer_subject" value="<?php echo $this->row->submission_type_formal_offer_subject; ?>" size="50" maxlength="255" id="formal_offer_subject" />
		</td>
	</tr>
	<tr>
		<td class="key">
			<label for="submission_type_formal_offer_body">
				<?php echo JText::_( 'REDEVENT_FORMAL_OFFER_BODY' ).':'; ?>
			</label>
		</td>
		<td>
			<?php echo $this->editor->display( 'submission_type_formal_offer_body',  $this->row->submission_type_formal_offer_body, '100%;', '350', '75', '20', array('pagebreak', 'readmore') ) ; ?>
		</td>	
	</tr>
	</table>
	</div>
</fieldset>