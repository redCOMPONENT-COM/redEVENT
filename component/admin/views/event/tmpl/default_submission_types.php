<table class="adminform">
	<tr>
		<td colspan="2" class="redevent_settings_details">
			<label for="venueid">
				<?php echo JText::_( 'SUBMIT_TYPES' ).':'; ?>
			</label>
		</td>
	</tr>
	<tr>
		<td>
			<input type="checkbox" id="submission_type_external" name="submission_types[]" value="external"
			<?php if (in_array('external', $this->submission_types)) echo ' checked="checked"'; ?>
			/><?php echo JText::_('EXTERNAL'); ?>
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
			/><?php echo JText::_('PHONE'); ?>
			<div id="submission_type_phone_input" style="display: <?php echo $display;?>">
				<?php echo JHTML::_('link', '#', JText::_('TAGS'), "onClick='jQuery(\"div#phone_tags\").toggle(\"slideUp\"); return false;'"); ?>
				<div id="phone_tags" style="display: none;">
					[event_description] = <?php echo JText::_('SUBMISSION_EVENT_DESCRIPTION');?><br />
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
			/><?php echo JText::_('WEBFORM'); ?>
			<div id="submission_type_webform_input" style="display: <?php echo $display;?>">
				<?php echo JHTML::_('link', '#', JText::_('TAGS'), "onClick='jQuery(\"div#webform_tags\").toggle(\"slideUp\"); return false;'"); ?>
				<div id="webform_tags" style="display: none;">
					[event_description] = <?php echo JText::_('SUBMISSION_COURSE_DESCRIPTION');?><br />
					[event_title] = <?php echo JText::_('SUBMISSION_EVENT_TITLE');?><br />
					[price] = <?php echo JText::_('SUBMISSION_EVENT_PRICE');?><br />
					[credits] = <?php echo JText::_('SUBMISSION_EVENT_CREDITS');?><br />
					[code] = <?php echo JText::_('SUBMISSION_EVENT_CODE');?><br />
					[redform] = <?php echo JText::_('SUBMISSION_EVENT_REDFORM');?><br />
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
				<?php echo $this->editor->display( 'submission_type_webform',  $this->row->submission_type_webform, '100%;', '350', '75', '20', array('pagebreak', 'readmore', 'image') ) ; ?>
				<?php echo JText::_('SHOW SUBMIT AND PRINT BUTTON') . ': ' . JHTML::_('select.booleanlist', 'show_submission_type_webform_formal_offer', '', $this->row->show_submission_type_webform_formal_offer); ?>
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
			/><?php echo JText::_('EMAIL');
			?>
			<div id="submission_type_email_input" style="display: <?php echo $display;?>">
				<?php echo JHTML::_('link', '#', JText::_('TAGS'), "onClick='jQuery(\"div#email_tags\").toggle(\"slideUp\"); return false;'"); ?>
				<div id="email_tags" style="display: none;">
					[event_description] = <?php echo JText::_('SUBMISSION_COURSE_DESCRIPTION');?><br />
					[event_title] = <?php echo JText::_('SUBMISSION_EVENT_TITLE');?><br />
					[price] = <?php echo JText::_('SUBMISSION_EVENT_PRICE');?><br />
					[credits] = <?php echo JText::_('SUBMISSION_EVENT_CREDITS');?><br />
					[code] = <?php echo JText::_('SUBMISSION_EVENT_CODE');?><br />
					[inputname] = <?php echo JText::_('SUBMISSION_INPUT_NAME');?><br />
					[inputemail] = <?php echo JText::_('SUBMISSION_INPUT_EMAIL');?><br />
					[submit] = <?php echo JText::_('SUBMISSION_INPUT_SUBMIT');?><br />
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
							<input class="inputbox" name="submission_type_email_subject" value="<?php echo $this->row->submission_type_email_subject; ?>" size="50" maxlength="255" id="submission_type_email_subject" />
						</td>
					</tr>
				</table>
				</div>
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
			/><?php echo JText::_('FORMALOFFER'); ?>
			<div id="submission_type_formaloffer_input" style="display: <?php echo $display;?>">
				<?php echo JHTML::_('link', '#', JText::_('TAGS'), "onClick='jQuery(\"div#formal_offer_tags\").toggle(\"slideUp\"); return false;'"); ?>
				<div id="formal_offer_tags" style="display: none;">
					[event_description] = <?php echo JText::_('SUBMISSION_COURSE_DESCRIPTION');?><br />
					[event_title] = <?php echo JText::_('SUBMISSION_EVENT_TITLE');?><br />
					[price] = <?php echo JText::_('SUBMISSION_EVENT_PRICE');?><br />
					[credits] = <?php echo JText::_('SUBMISSION_EVENT_CREDITS');?><br />
					[code] = <?php echo JText::_('SUBMISSION_EVENT_CODE');?><br />
					[inputname] = <?php echo JText::_('SUBMISSION_INPUT_NAME');?><br />
					[inputemail] = <?php echo JText::_('SUBMISSION_INPUT_EMAIL');?><br />
					[submit] = <?php echo JText::_('SUBMISSION_INPUT_SUBMIT');?><br />
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
							<input class="inputbox" name="submission_type_formal_offer_subject" value="<?php echo $this->row->submission_type_formal_offer_subject; ?>" size="50" maxlength="255" id="submission_type_formal_offer_subject" />
						</td>
					</tr>
				</table>
				</div>
				<?php echo $this->editor->display( 'submission_type_formal_offer',  $this->row->submission_type_formal_offer, '100%;', '350', '75', '20', array('pagebreak', 'readmore', 'image') ) ; ?>
			</div>
		</td>
	</tr>
</table>
