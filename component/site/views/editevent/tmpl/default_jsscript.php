<script type="text/javascript" charset="utf-8">
	jQuery("input[name='locid[]']").bind('click', function() {
		var parent = jQuery(this).parent().attr("id");
		if (this.checked) {
			jQuery("#"+parent+" .adddatetime").show();
			jQuery("#"+parent+" .showalldatetime").show();

		}
		else {
			jQuery("#"+parent+" .adddatetime").hide();
			jQuery("#"+parent+" .showalldatetime").hide();
		}
	});

	jQuery("input[name='adddatetime']").bind('click', function() {
		/* Get some values */
		var random = jQuery.random(<?php echo time(); ?>);
		var parentid = jQuery(this).parent().parent().attr("id");
		var childitem = jQuery("#"+parentid).children().get(0);
		var childvalue = jQuery(childitem).val();
		
		/* Create the div to hold the fields */
		var datetime = '<div id="datetimecontainer'+random+'" style="display: block;">';
		datetime += '<input type="button" name="removedatetime" value="<?php echo JText::_('SHOW_HIDE_DATE_TIME'); ?>" onClick=\'jQuery("#datetime'+childvalue+'-'+random+'").toggle("slideUp");\'/>';
		datetime += '<input type="button" name="removedatetime" value="<?php echo JText::_('REMOVE_DATE_TIME'); ?>" onClick=\'removeDateTimeFields('+random+');\'/>';
		//datetime += '<br />';
		datetime += '<div id="datetime'+childvalue+'-'+random+'" name="datetime'+childvalue+'-'+random+'">';
		
		/* Add the date time fields */
		datetime += '<table class="adminform">';
		/* Start date and start time */
		datetime += '<tr class="row0"><td class="redevent_settings_details"><?php echo JText::_('DATE'); ?></td><td><input type="text" id="dates'+random+'" name="'+parentid+'['+random+'][dates]" value="" /><img id="dates'+random+'_img" class="calendar" alt="calendar" src="/templates/system/images/calendar.png"/></td>';
		datetime += '<td><?php echo JText::_('TIME'); ?></td><td><input type="text" name="'+parentid+'['+random+'][times]" value="" /></td></tr>';
		/* End date and end time */
		datetime += '<tr class="row1"><td><?php echo JText::_('ENDDATE'); ?></td><td><input type="text" id="enddates'+random+'" name="'+parentid+'['+random+'][enddates]" value="" /><img id="enddates'+random+'_img" class="calendar" alt="calendar" src="/templates/system/images/calendar.png"/></td>';
		datetime += '<td><?php echo JText::_('END TIME'); ?></td><td><input type="text" name="'+parentid+'['+random+'][endtimes]" value="" /></td></tr>';
		/* Attendees and waitinglist */
		<?php $infoimage = JHTML::image('components/com_redevent/assets/images/icon-16-hint.png', JText::_( 'NOTES' ) ); ?>
		datetime += '<tr class="row0"><td><span class="editlinktip hasTip" title="<?php echo JText::_( 'MAXIMUM_ATTENDEES' ); ?>::<?php echo JText::_('MAXIMUM_ATTENDEES_TIP'); ?>" <?php echo $infoimage;?></span><?php echo JText::_('MAXIMUM_ATTENDEES');?></td><td><input type="text" id="maxattendees'+random+'" name="'+parentid+'['+random+'][maxattendees]" value="" size="15" maxlength="8" /></td>';
		datetime += '<td><span class="editlinktip hasTip" title="<?php echo JText::_( 'MAXIMUM_WAITINGLIST' ); ?>::<?php echo JText::_('MAXIMUM_WAITINGLIST_TIP'); ?>" <?php echo $infoimage;?></span><?php echo JText::_('MAXIMUM_WAITINGLIST');?></td><td><input type="text" id="maxwaitinglist'+random+'" name="'+parentid+'['+random+'][maxwaitinglist]" value="" size="15" maxlength="8" /></td>';
		/* Course credit and price */
		datetime += '<tr class="row1"><td><?php echo JText::_('COURSE_PRICE');?></td><td><input type="text" id="course_price'+random+'" name="'+parentid+'['+random+'][course_price]" value="" size="15" maxlength="8" /></td>';
		datetime += '<td><?php echo JText::_('COURSE_CREDIT');?></td><td><input type="text" id="course_credit'+random+'" name="'+parentid+'['+random+'][course_credit]" value="" size="15" maxlength="8" /></td>';
		datetime += '</table>';
		datetime += '</div></div>';
		jQuery(datetime).appendTo("div#locid"+childvalue);
		
		/* Add the date picker */
		createDatePicker("dates"+random);
		createDatePicker("enddates"+random);
	});
	
	function createDatePicker(id) {
		Calendar.setup({
			inputField     :    id,     // id of the input field
			ifFormat       :    "%Y-%m-%d",      // format of the input field
			button         :    id+"_img",  // trigger for the calendar (button ID)
			align          :    "Tl",           // alignment (defaults to "Bl")
			singleClick    :    true
			});
	}

	function removeDateTimeFields(childvalue) {
		if (confirm('<?php echo JText::_('REMOVE_DATE_TIME_BLOCK'); ?>')) {
			jQuery("#datetimecontainer"+childvalue).remove();
		}
	}

	jQuery("input[name='showalldatetime']").bind('click', function() {
		var parentid = jQuery(this).parent().parent().attr("id");
		var childitem = jQuery("#"+parentid).children().get(0);
		var childvalue = jQuery(childitem).val();
		jQuery("[id^='datetime"+childvalue+"']").each(function(i) {
			jQuery(this).toggle();

		})

	});
</script>
