<table class="adminform">
<tr>
<td colspan="2">
	<?php 
		$display = 'none';
		if (in_array('email', $this->submission_types)) {
			$display = 'block';
		}
		?>
	<?php echo JText::_('Submission email');	?>
	<div id="submission_type_email_body_input" style="display: <?php echo $display;?>">
		<?php echo JHTML::_('link', '#', JText::_('TAGS'), "onClick='jQuery(\"div#email_body_tags\").toggle(\"slideUp\"); return false;'"); ?>
		<div id="email_body_tags" style="display: none;">
			[event_description] = <?php echo JText::_('SUBMISSION_COURSE_DESCRIPTION');?><br />
			[event_title] = <?php echo JText::_('SUBMISSION_EVENT_TITLE');?><br />
			[price] = <?php echo JText::_('SUBMISSION_EVENT_PRICE');?><br />
			[credits] = <?php echo JText::_('SUBMISSION_EVENT_CREDITS');?><br />
			[code] = <?php echo JText::_('SUBMISSION_EVENT_CODE');?><br />
			[username] = <?php echo JText::_('SUBMISSION_OUTPUT_NAME');?><br />
			[useremail] = <?php echo JText::_('SUBMISSION_OUTPUT_EMAIL');?><br />
			[event_info_text] = <?php echo JText::_('SUBMISSION_EVENT_INFO_TEXT');?><br />
			[time] = <?php echo JText::_('SUBMISSION_EVENT_TIME');?><br />
			[date] = <?php echo JText::_('SUBMISSION_EVENT_DATE');?><br />
			[duration] = <?php echo JText::_('SUBMISSION_EVENT_DURATION');?><br />
			[venue] = <?php echo JText::_('SUBMISSION_EVENT_VENUE');?><br />
			[city] = <?php echo JText::_('SUBMISSION_EVENT_CITY');?><br />
			[webformsignup] = <?php echo JText::_('SUBMISSION_WEBFORM_SIGNUP_LINK');?><br />
			[emailsignup] = <?php echo JText::_('SUBMISSION_EMAIL_SIGNUP_LINK');?><br />
			[formalsignup] = <?php echo JText::_('SUBMISSION_FORMAL_SIGNUP_LINK');?><br />
			[externalsignup] = <?php echo JText::_('SUBMISSION_EXTERNAL_SIGNUP_LINK');?><br />
			[phonesignup] = <?php echo JText::_('SUBMISSION_PHONE_SIGNUP_LINK');?><br />
			[webformsignuppage] = <?php echo JText::_('SUBMISSION_WEBFORM_SIGNUP_PAGE');?><br />
			[emailsignuppage] = <?php echo JText::_('SUBMISSION_EMAIL_SIGNUP_PAGE');?><br />
			[formalsignuppage] = <?php echo JText::_('SUBMISSION_FORMAL_SIGNUP_PAGE');?><br />
			[phonesignuppage] = <?php echo JText::_('SUBMISSION_PHONE_SIGNUP_PAGE');?><br />
			[venueimage] = <?php echo JText::_('SUBMISSION_VENUE_IMAGE');?><br />
			[eventimage] = <?php echo JText::_('SUBMISSION_EVENT_IMAGE');?><br />
			[categoryimage] = <?php echo JText::_('SUBMISSION_CATEGORY_IMAGE');?><br />
			[eventcomments] = <?php echo JText::_('SUBMISSION_EVENT_COMMENTS');?><br />
			[category] = <?php echo JText::_('SUBMISSION_CATEGORY');?><br />
			[eventplaces] = <?php echo JText::_('SUBMISSION_EVENTPLACES');?><br />
			[waitinglistplaces] = <?php echo JText::_('SUBMISSION_WAITINGLISTPLACES');?><br />
			[eventplacesleft] = <?php echo JText::_('SUBMISSION_EVENTPLACES_LEFT');?><br />
			[waitinglistplacesleft] = <?php echo JText::_('SUBMISSION_WAITINGLISTPLACES_LEFT');?>
		</div>
		<div>
		<table class="adminform">
			<tr>
				<td>
					<label for="title">
						<?php echo JText::_( 'EMAIL_SUBJECT' ).':'; ?>
					</label>
				</td>
				<td>
					<input class="inputbox" name="submission_type_email_subject" value="<?php echo $this->row->submission_type_email_subject; ?>" size="50" maxlength="255" id="email_subject" />
				</td>
			</tr>
		</table>
		</div>
		<?php echo $this->editor->display( 'submission_type_email_body',  $this->row->submission_type_email_body, '100%;', '350', '75', '20', array('pagebreak', 'readmore') ) ; ?>
	</div>
</td>
</tr>
</table>
<table class="adminform">
<tr>
	<td>
		<?php echo JText::_('SEND_PDF_FORM'); ?>
	</td>
	<td>
		<?php echo JHTML::_('select.booleanlist', 'send_pdf_form', 'class="inputbox', $this->row->send_pdf_form); ?>
	</td>
</tr>
<tr>
	<td>
		<?php echo JText::_('PDF_FORM_DATA'); ?>
	</td>
	<td>
		<?php echo JHTML::_('select.booleanlist', 'pdf_form_data', 'class="inputbox', $this->row->pdf_form_data); ?>
	</td>
</tr>
</table>
<table class="adminform">
<tr>
<td colspan="2">
	<?php echo JText::_('email_pdf');
	?>
	<div id="submission_type_email_pdf_input" style="display: <?php echo $display;?>">
		<?php echo JHTML::_('link', '#', JText::_('TAGS'), "onClick='jQuery(\"div#email_pdf_tags\").toggle(\"slideUp\"); return false;'"); ?>
		<div id="email_pdf_tags" style="display: none;">
			[event_title] = <?php echo JText::_('SUBMISSION_EVENT_TITLE');?><br />
			[price] = <?php echo JText::_('SUBMISSION_EVENT_PRICE');?><br />
			[credits] = <?php echo JText::_('SUBMISSION_EVENT_CREDITS');?><br />
			[code] = <?php echo JText::_('SUBMISSION_EVENT_CODE');?><br />
			[event_info_text] = <?php echo JText::_('SUBMISSION_EVENT_INFO_TEXT');?><br />
			[time] = <?php echo JText::_('SUBMISSION_EVENT_TIME');?><br />
			[date] = <?php echo JText::_('SUBMISSION_EVENT_DATE');?><br />
			[duration] = <?php echo JText::_('SUBMISSION_EVENT_DURATION');?><br />
			[venue] = <?php echo JText::_('SUBMISSION_EVENT_VENUE');?><br />
			[city] = <?php echo JText::_('SUBMISSION_EVENT_CITY');?><br />
			[webformsignup] = <?php echo JText::_('SUBMISSION_WEBFORM_SIGNUP_LINK');?><br />
			[emailsignup] = <?php echo JText::_('SUBMISSION_EMAIL_SIGNUP_LINK');?><br />
			[formalsignup] = <?php echo JText::_('SUBMISSION_FORMAL_SIGNUP_LINK');?><br />
			[externalsignup] = <?php echo JText::_('SUBMISSION_EXTERNAL_SIGNUP_LINK');?><br />
			[phonesignup] = <?php echo JText::_('SUBMISSION_PHONE_SIGNUP_LINK');?><br />
			[webformsignuppage] = <?php echo JText::_('SUBMISSION_WEBFORM_SIGNUP_PAGE');?><br />
			[emailsignuppage] = <?php echo JText::_('SUBMISSION_EMAIL_SIGNUP_PAGE');?><br />
			[formalsignuppage] = <?php echo JText::_('SUBMISSION_FORMAL_SIGNUP_PAGE');?><br />
			[phonesignuppage] = <?php echo JText::_('SUBMISSION_PHONE_SIGNUP_PAGE');?><br />
			[venueimage] = <?php echo JText::_('SUBMISSION_VENUE_IMAGE');?><br />
			[eventimage] = <?php echo JText::_('SUBMISSION_EVENT_IMAGE');?><br />
			[categoryimage] = <?php echo JText::_('SUBMISSION_CATEGORY_IMAGE');?><br />
			[eventcomments] = <?php echo JText::_('SUBMISSION_EVENT_COMMENTS');?><br />
			[category] = <?php echo JText::_('SUBMISSION_CATEGORY');?><br />
			[eventplaces] = <?php echo JText::_('SUBMISSION_EVENTPLACES');?><br />
			[waitinglistplaces] = <?php echo JText::_('SUBMISSION_WAITINGLISTPLACES');?><br />
			[eventplacesleft] = <?php echo JText::_('SUBMISSION_EVENTPLACES_LEFT');?><br />
			[waitinglistplacesleft] = <?php echo JText::_('SUBMISSION_WAITINGLISTPLACES_LEFT');?>
		</div>
		<?php echo $this->editor->display( 'submission_type_email_pdf',  $this->row->submission_type_email_pdf, '100%;', '350', '75', '20', array('pagebreak', 'readmore') ) ; ?>
	</div>
</td>
</tr>
</table>
<table class="adminform">
<tr>
<td colspan="2">
	<?php 
		$display = 'none';
		if (in_array('formaloffer', $this->submission_types)) {
			$display = 'block';
		}
	?>
	<?php echo JText::_('formaloffer'); ?>
	<div id="submission_type_formaloffer_body_input" style="display: <?php echo $display;?>">
		<?php echo JHTML::_('link', '#', JText::_('TAGS'), "onClick='jQuery(\"div#formal_offer_body_tags\").toggle(\"slideUp\"); return false;'"); ?>
		<div id="formal_offer_body_tags" style="display: none;">
			[event_description] = <?php echo JText::_('SUBMISSION_COURSE_DESCRIPTION');?><br />
			[event_title] = <?php echo JText::_('SUBMISSION_EVENT_TITLE');?><br />
			[price] = <?php echo JText::_('SUBMISSION_EVENT_PRICE');?><br />
			[credits] = <?php echo JText::_('SUBMISSION_EVENT_CREDITS');?><br />
			[code] = <?php echo JText::_('SUBMISSION_EVENT_CODE');?><br />
			[username] = <?php echo JText::_('SUBMISSION_OUTPUT_NAME');?><br />
			[useremail] = <?php echo JText::_('SUBMISSION_OUTPUT_EMAIL');?><br />
			[event_info_text] = <?php echo JText::_('SUBMISSION_EVENT_INFO_TEXT');?><br />
			[time] = <?php echo JText::_('SUBMISSION_EVENT_TIME');?><br />
			[date] = <?php echo JText::_('SUBMISSION_EVENT_DATE');?><br />
			[duration] = <?php echo JText::_('SUBMISSION_EVENT_DURATION');?><br />
			[venue] = <?php echo JText::_('SUBMISSION_EVENT_VENUE');?><br />
			[city] = <?php echo JText::_('SUBMISSION_EVENT_CITY');?><br />
			[webformsignup] = <?php echo JText::_('SUBMISSION_WEBFORM_SIGNUP_LINK');?><br />
			[emailsignup] = <?php echo JText::_('SUBMISSION_EMAIL_SIGNUP_LINK');?><br />
			[formalsignup] = <?php echo JText::_('SUBMISSION_FORMAL_SIGNUP_LINK');?><br />
			[externalsignup] = <?php echo JText::_('SUBMISSION_EXTERNAL_SIGNUP_LINK');?><br />
			[phonesignup] = <?php echo JText::_('SUBMISSION_PHONE_SIGNUP_LINK');?><br />
			[webformsignuppage] = <?php echo JText::_('SUBMISSION_WEBFORM_SIGNUP_PAGE');?><br />
			[emailsignuppage] = <?php echo JText::_('SUBMISSION_EMAIL_SIGNUP_PAGE');?><br />
			[formalsignuppage] = <?php echo JText::_('SUBMISSION_FORMAL_SIGNUP_PAGE');?><br />
			[phonesignuppage] = <?php echo JText::_('SUBMISSION_PHONE_SIGNUP_PAGE');?><br />
			[venueimage] = <?php echo JText::_('SUBMISSION_VENUE_IMAGE');?><br />
			[eventimage] = <?php echo JText::_('SUBMISSION_EVENT_IMAGE');?><br />
			[categoryimage] = <?php echo JText::_('SUBMISSION_CATEGORY_IMAGE');?><br />
			[eventcomments] = <?php echo JText::_('SUBMISSION_EVENT_COMMENTS');?><br />
			[category] = <?php echo JText::_('SUBMISSION_CATEGORY');?><br />
			[eventplaces] = <?php echo JText::_('SUBMISSION_EVENTPLACES');?><br />
			[waitinglistplaces] = <?php echo JText::_('SUBMISSION_WAITINGLISTPLACES');?><br />
			[eventplacesleft] = <?php echo JText::_('SUBMISSION_EVENTPLACES_LEFT');?><br />
			[waitinglistplacesleft] = <?php echo JText::_('SUBMISSION_WAITINGLISTPLACES_LEFT');?>
		</div>
		<div>
		<table class="adminform">
			<tr>
				<td>
					<label for="title">
						<?php echo JText::_( 'FORMAL_OFFER_SUBJECT' ).':'; ?>
					</label>
				</td>
				<td>
					<input class="inputbox" name="submission_type_formal_offer_subject" value="<?php echo $this->row->submission_type_formal_offer_subject; ?>" size="50" maxlength="255" id="formal_offer_subject" />
				</td>
			</tr>
		</table>
		</div>
		<?php echo $this->editor->display( 'submission_type_formal_offer_body',  $this->row->submission_type_formal_offer_body, '100%;', '350', '75', '20', array('pagebreak', 'readmore') ) ; ?>
	</div>
</td>
</tr>
</table>
