<?php $infoimage = JHTML::image('components/com_redevent/assets/images/icon-16-hint.png', JText::_( 'NOTES' ) ); ?>
<?php $k = 0; ?>
<table class="adminform">

<?php /* TODO: to be removed ? these fields are not used any more, as there is a tag for the display now 
	<tr class="row<?php echo $k = 1 - $k; ?>">
		<td>
			<label for="show_attendants">
				<?php echo JText::_( 'SHOW_ATTENDANTS' ).':'; ?>
			</label>
		</td>
		<td>
			<?php echo JHTML::_('select.booleanlist', 'show_attendants', '', $this->row->show_attendants ); ?>
		</td>
		<td>
			<span class="editlinktip hasTip" title="<?php echo JText::_( 'SHOW_ATTENDANTS' ); ?>::<?php echo JText::_('SHOW_ATTENDANTS_TIP'); ?>">
				<?php echo $infoimage; ?>
			</span>
		</td>
	</tr>
	<tr class="row<?php echo $k = 1 - $k; ?>">
		<td>
			<label for="show_waitinglist">
				<?php echo JText::_( 'SHOW_WAITINGLIST' ).':'; ?>
			</label>
		</td>
		<td>
			<?php echo JHTML::_('select.booleanlist', 'show_waitinglist', '', $this->row->show_waitinglist ); ?>
		</td>
		<td>
			<span class="editlinktip hasTip" title="<?php echo JText::_( 'SHOW_WAITINGLIST' ); ?>::<?php echo JText::_('SHOW_WAITINGLIST_TIP'); ?>">
				<?php echo $infoimage; ?>
			</span>
		</td>
	</tr>
	*/ ?>
	<tr class="row<?php echo $k = 1 - $k; ?>">
		<td>
			<label for="notify_on_list_subject">
				<?php echo JText::_( 'NOTIFY ON LIST SUBJECT' ).':'; ?>
			</label>
			</td>
		<td>
			<input class="inputbox" name="notify_on_list_subject" value="<?php echo $this->row->notify_on_list_subject; ?>" size="45" id="notify_on_list_subject" />
		</td>
	</tr>
	<tr class="row<?php echo $k = 1 - $k; ?>">
		<td>
			<label for="notify_on_list_body">
				<?php echo JText::_( 'NOTIFY ON LIST BODY' ).':'; ?>
			</label>
		</td>
		<td>
        <?php echo $this->printTags(); ?>
			<?php echo $this->editor->display( 'notify_on_list_body',  $this->row->notify_on_list_body, '100%;', '550', '75', '20', array('pagebreak', 'readmore', 'image') ) ; ?>
		</td>
	</tr>
	<tr class="row<?php echo $k = 1 - $k; ?>">
		<td>
			<label for="notify_off_list_subject">
				<?php echo JText::_( 'NOTIFY OFF LIST SUBJECT' ).':'; ?>
			</label>
		</td>
		<td>
			<input class="inputbox" name="notify_off_list_subject" value="<?php echo $this->row->notify_off_list_subject; ?>" size="45" id="notify_off_list_subject" />
		</td>
	</tr>
	<tr class="row<?php echo $k = 1 - $k; ?>">
		<td>
			<label for="notify_off_list_body">
				<?php echo JText::_( 'NOTIFY OFF LIST BODY' ).':'; ?>
			</label>
		</td>
		<td>
		  <?php echo JHTML::_('link', '#', JText::_('TAGS'), "onClick='jQuery(\"div#notify_off_list_body_tags\").toggle(\"slideUp\"); return false;'"); ?>
      <div id="notify_off_list_body_tags" style="display: none;">
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
			<?php echo $this->editor->display( 'notify_off_list_body',  $this->row->notify_off_list_body, '100%;', '550', '75', '20', array('pagebreak', 'readmore', 'image') ) ; ?>
		</td>
	</tr>
</table>
