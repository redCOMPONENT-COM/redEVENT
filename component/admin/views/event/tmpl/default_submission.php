<?php $infoimage = JHTML::image('components/com_redevent/assets/images/icon-16-hint.png', JText::_( 'NOTES' ) ); ?>
<?php $k = 0; ?>
<table class="adminform">
	<tr class="row<?php echo $k = 1 - $k; ?>">
		<td>
			<label for="activate">
				<?php echo JText::_( 'ENABLE ACTIVATION' ).':'; ?>
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
				<?php echo JText::_( 'ENABLE NOTIFICATION' ).':'; ?>
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
				<?php echo JText::_( 'NOTIFY SUBJECT' ).':'; ?>
			</label>
		</td>
		<td>
			<input class="inputbox" name="notify_subject" value="<?php echo $this->row->notify_subject; ?>" size="45" id="notify_subject" />
		</td>
	</tr>
	<tr class="row<?php echo $k = 1 - $k; ?>">
		<td>
			<label for="notify_body">
				<?php echo JText::_( 'NOTIFY BODY' ).':'; ?>
			</label>
			<span class="editlinktip hasTip" title="<?php echo JText::_( 'NOTIFY BODY' ); ?>::<?php echo JText::_('NOTIFY BODY NOTE'); ?>">
				<?php echo $infoimage; ?>
			</span>
		</td>
		<td>
			<?php echo JHTML::_('link', '#', JText::_('TAGS'), "onClick='jQuery(\"div#notify_body_tags\").toggle(\"slideUp\"); return false;'"); ?>
			<div id="notify_body_tags" style="display: none;">
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
				[activatelink] = <?php echo JText::_('SUBMISSION_EVENT_ACTIVATELINK');?><br />
				[fullname] = <?php echo JText::_('SUBMISSION_EVENT_FULLNAME');?><br />
				[username] = <?php echo JText::_('SUBMISSION_EVENT_USERNAME');?><br />
				[password] = <?php echo JText::_('SUBMISSION_EVENT_PASSWORD');?><br />
				[eventplaces] = <?php echo JText::_('SUBMISSION_EVENTPLACES');?><br />
				[waitinglistplaces] = <?php echo JText::_('SUBMISSION_WAITINGLISTPLACES');?><br />
				[eventplacesleft] = <?php echo JText::_('SUBMISSION_EVENTPLACES_LEFT');?><br />
				[waitinglistplacesleft] = <?php echo JText::_('SUBMISSION_WAITINGLISTPLACES_LEFT');?>
			</div>
			<?php echo $this->editor->display( 'notify_body',  $this->row->notify_body, '100%;', '550', '75', '20', array('pagebreak', 'readmore', 'image') ) ; ?>
		</td>
	</tr>
	<tr class="row<?php echo $k = 1 - $k; ?>">
		<td>
			<label for="notify_confirm_subject">
				<?php echo JText::_( 'NOTIFY CONFIRM SUBJECT' ).':'; ?>
			</label>
		</td>
		<td>
			<input class="inputbox" name="notify_confirm_subject" value="<?php echo $this->row->notify_confirm_subject; ?>" size="45" id="notify_confirm_subject" />
		</td>
	<tr class="row<?php echo $k = 1 - $k; ?>">
		<td>
			<label for="notify_confirm_body">
				<?php echo JText::_( 'NOTIFY CONFIRM BODY' ).':'; ?>
			</label>
		</td>
		<td>
			<?php echo JHTML::_('link', '#', JText::_('TAGS'), "onClick='jQuery(\"div#confirm_body_tags\").toggle(\"slideUp\"); return false;'"); ?>
			<div id="confirm_body_tags" style="display: none;">
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
			<?php echo $this->editor->display( 'notify_confirm_body',  $this->row->notify_confirm_body, '100%;', '550', '75', '20', array('pagebreak', 'readmore', 'image') ) ; ?>
		</td>
	<tr>
</table>
