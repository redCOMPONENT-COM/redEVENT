<?php $infoimage = JHTML::image('components/com_redevent/assets/images/icon-16-hint.png', JText::_( 'NOTES' ) ); ?>
<table class="adminform">
	<tr>
		<td colspan="2">
			<?php echo JText::_('REVIEW_SCREEN'); ?>
			<div id="review_screen">
				<?php echo JHTML::_('link', '#', JText::_('TAGS'), "onClick='jQuery(\"div#review_tags\").toggle(\"slideUp\"); return false;'"); ?>
				<div id="review_tags" style="display: none;">
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
				<?php echo $this->editor->display( 'review_message',  $this->row->review_message, '100%;', '350', '75', '20', array('pagebreak', 'readmore', 'image') ) ; ?>
			</div>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<?php echo JText::_('WEBFORM_PRINT_FORMAL_OFFER'); ?>
			<div id="submission_type_webform_input" style="display: block">
				<?php echo JHTML::_('link', '#', JText::_('TAGS'), "onClick='jQuery(\"div#webform_formal_offer_tags\").toggle(\"slideUp\"); return false;'"); ?>
				<div id="webform_formal_offer_tags" style="display: none;">
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
					[regurl] = <?php echo JText::_('SUBMISSION_EVENT_REGURL');?><br />
					[eventplaces] = <?php echo JText::_('SUBMISSION_EVENTPLACES');?><br />
					[waitinglistplaces] = <?php echo JText::_('SUBMISSION_WAITINGLISTPLACES');?><br />
					[eventplacesleft] = <?php echo JText::_('SUBMISSION_EVENTPLACES_LEFT');?><br />
					[waitinglistplacesleft] = <?php echo JText::_('SUBMISSION_WAITINGLISTPLACES_LEFT');?>
				</div>
				<?php echo $this->editor->display( 'submission_type_webform_formal_offer',  $this->row->submission_type_webform_formal_offer, '100%;', '350', '75', '20', array('pagebreak', 'readmore', 'image') ) ; ?>
			</div>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<span class="editlinktip hasTip" title="<?php echo JText::_( 'CONFIRMATION' ); ?>::<?php echo JText::_('CONFIRMATION_INFO'); ?>">
				<?php echo $infoimage; ?>
			</span><label for="confirmation_message"><?php echo JText::_('ENTER_CONFIRMATION_MESSAGE'); ?></label>
			
			<div id="confirmation_screen">
				<?php echo JHTML::_('link', '#', JText::_('TAGS'), "onClick='jQuery(\"div#confirmation_tags\").toggle(\"slideUp\"); return false;'"); ?>
				<div id="confirmation_tags" style="display: none;">
					[event_description] = <?php echo JText::_('SUBMISSION_COURSE_DESCRIPTION');?><br />
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
				<?php echo $this->editor->display( 'confirmation_message',  $this->row->confirmation_message, '100%;', '350', '75', '20', array('pagebreak', 'readmore', 'image') ) ; ?>
			</div>
		</td>
	</tr>
</table>
